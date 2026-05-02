"""Read-only controller lookup tool.

The tool inspects controller and route source files as text only. It does not
execute PHP, instantiate controllers, read secrets, or access the database.
"""

from pathlib import Path
import os
import re


CONTROLLERS_DIR = "app/Controllers"
ROUTES_FILE = "app/Config/Routes.php"
DEFAULT_MAX_RESULTS = 20
HARD_MAX_RESULTS = 100

IGNORED_FOLDERS = {
    ".git",
    "vendor",
    "node_modules",
    "writable",
    "public/uploads",
    "ai/oracle/output",
    "ai/oracle/runtime/output",
}

GROUP_PATTERN = re.compile(
    r"\$routes->group\(\s*['\"]([^'\"]*)['\"]",
    re.IGNORECASE,
)
ROUTE_PATTERN = re.compile(
    r"\$routes->(get|post|put|delete|patch|options)\(\s*['\"]([^'\"]*)['\"]\s*,\s*['\"]([^'\"]*)['\"]",
    re.IGNORECASE,
)
CLASS_PATTERN = re.compile(r"\bclass\s+([A-Za-z_][A-Za-z0-9_]*)")
METHOD_PATTERN = re.compile(r"\bpublic\s+function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(")
USE_SERVICE_PATTERN = re.compile(r"use\s+App\\Services\\([^;]+);")
USE_MODEL_PATTERN = re.compile(r"use\s+App\\Models\\([^;]+);")
NEW_SERVICE_PATTERN = re.compile(r"new\s+([A-Za-z_][A-Za-z0-9_\\\\]*Service)\s*\(")
NEW_MODEL_PATTERN = re.compile(r"new\s+([A-Za-z_][A-Za-z0-9_\\\\]*Model)\s*\(")


def _to_posix_relative(path: Path, root: Path) -> str:
    return path.relative_to(root).as_posix()


def _is_ignored_folder(relative_path: str) -> bool:
    if not relative_path:
        return False
    return any(
        relative_path == ignored or relative_path.startswith(f"{ignored}/")
        for ignored in IGNORED_FOLDERS
    )


def _join_route(prefixes: list[str], route_path: str) -> str:
    parts = [part.strip("/") for part in prefixes + [route_path] if part.strip("/")]
    return "/".join(parts)


def _controller_target_to_file(target: str) -> str:
    controller = target.split("::", 1)[0].replace("\\", "/")
    return f"{CONTROLLERS_DIR}/{controller}.php"


def _controller_target_method(target: str) -> str | None:
    if "::" not in target:
        return None
    return target.split("::", 1)[1].split("/", 1)[0]


def _normalize_controller_query(query: str) -> str:
    normalized = query.replace("\\\\", "\\").replace("/", "\\").strip("\\")
    if normalized.startswith("App\\Controllers\\"):
        normalized = normalized[len("App\\Controllers\\"):]
    return normalized


def _detect_line_type(line: str) -> str:
    stripped = line.strip()
    lowered = stripped.lower()
    if CLASS_PATTERN.search(stripped):
        return "controller_class"
    if METHOD_PATTERN.search(stripped):
        return "method"
    if "return redirect()->" in lowered:
        return "redirect"
    if "return view(" in lowered:
        return "view_return"
    if (
        "permission" in lowered
        or "role" in lowered
        or "session()->get('user')" in lowered
        or 'session()->get("user")' in lowered
        or "session('role')" in lowered
    ):
        return "permission_check"
    if USE_SERVICE_PATTERN.search(stripped) or NEW_SERVICE_PATTERN.search(stripped):
        return "service_call"
    if USE_MODEL_PATTERN.search(stripped) or NEW_MODEL_PATTERN.search(stripped):
        return "service_call"
    return "method"


def _extract_class(line: str, fallback: str | None = None) -> str | None:
    match = CLASS_PATTERN.search(line)
    return match.group(1) if match else fallback


def _extract_method(line: str) -> str | None:
    match = METHOD_PATTERN.search(line)
    return match.group(1) if match else None


def _extract_service(line: str) -> str | None:
    match = USE_SERVICE_PATTERN.search(line) or NEW_SERVICE_PATTERN.search(line)
    return match.group(1).split("\\")[-1] if match else None


def _extract_model(line: str) -> str | None:
    match = USE_MODEL_PATTERN.search(line) or NEW_MODEL_PATTERN.search(line)
    return match.group(1).split("\\")[-1] if match else None


def _risk_hint(detected_type: str, line: str) -> str | None:
    lowered = line.lower()
    if detected_type == "permission_check" and "session" in lowered:
        return "Controller reads session/user role directly; verify route-level filters remain primary access control."
    if detected_type == "redirect":
        return "Redirect behavior should be checked for access-flow assumptions."
    if detected_type == "view_return":
        return "View return found; verify required data and access context during controller review."
    return None


def _matches_query(query: str, *values: str | None) -> bool:
    lowered = query.lower().replace("\\\\", "\\").replace("/", "\\")
    candidates = []
    for value in values:
        if value:
            candidates.append(value.lower().replace("\\\\", "\\").replace("/", "\\"))
            candidates.append(value.lower().replace("\\", "/"))
    return any(lowered in candidate for candidate in candidates)


def _match_score(query: str, related_route: str | None, controller_class: str | None, method: str | None, text: str) -> int:
    lowered = query.lower().replace("\\\\", "\\").replace("/", "\\")
    route = (related_route or "").lower().replace("/", "\\")
    controller = (controller_class or "").lower()
    method_name = (method or "").lower()
    if route == lowered:
        return 0
    if controller == lowered or controller.endswith(f"\\{lowered}"):
        return 1
    if method_name == lowered:
        return 2
    if lowered in route or lowered in controller or lowered in method_name:
        return 3
    if lowered in text.lower().replace("/", "\\"):
        return 4
    return 5


def _route_targets(repo_root: Path) -> list[dict]:
    route_file = repo_root / ROUTES_FILE
    if not route_file.exists():
        return []

    prefixes: list[str] = []
    targets = []
    for line_number, line in enumerate(route_file.read_text(encoding="utf-8", errors="replace").splitlines(), start=1):
        stripped = line.strip()
        group_match = GROUP_PATTERN.search(stripped)
        if group_match:
            prefixes.append(group_match.group(1))

        route_match = ROUTE_PATTERN.search(stripped)
        if route_match:
            route_path = _join_route(prefixes, route_match.group(2))
            target = route_match.group(3)
            targets.append(
                {
                    "source_file": ROUTES_FILE,
                    "line_number": line_number,
                    "matched_text": stripped,
                    "route_path": route_path,
                    "controller_target": target,
                    "controller_file": _controller_target_to_file(target),
                    "method": _controller_target_method(target),
                }
            )

        if stripped == "});" and prefixes:
            prefixes.pop()

    return targets


def _controller_files(repo_root: Path) -> list[Path]:
    root = repo_root / CONTROLLERS_DIR
    if not root.exists():
        return []

    files = []
    for current_root, dir_names, file_names in os.walk(root, followlinks=False):
        current_path = Path(current_root)
        relative_current = _to_posix_relative(current_path, repo_root)
        if _is_ignored_folder(relative_current):
            dir_names[:] = []
            continue

        dir_names[:] = [
            name for name in dir_names
            if not _is_ignored_folder(f"{relative_current}/{name}".strip("/"))
        ]

        for file_name in file_names:
            if file_name.endswith(".php"):
                files.append(current_path / file_name)
    return sorted(files)


def _route_rows(query: str, targets: list[dict]) -> list[dict]:
    rows = []
    for target in targets:
        controller_class = target["controller_target"].split("::", 1)[0]
        if not _matches_query(
            query,
            target["route_path"],
            target["controller_target"],
            controller_class,
            target["method"],
        ):
            continue
        rows.append(
            {
                "_score": _match_score(query, target["route_path"], controller_class, target["method"], target["matched_text"]),
                "status": "success",
                "source_file": target["source_file"],
                "line_number": target["line_number"],
                "matched_text": target["matched_text"],
                "detected_type": "route_handler",
                "controller_class": controller_class,
                "method": target["method"],
                "related_route": target["route_path"],
                "related_service": None,
                "related_model": None,
                "risk_hint": None,
            }
        )
    return rows


def _controller_rows(repo_root: Path, query: str, targets: list[dict]) -> list[dict]:
    route_by_file: dict[str, list[dict]] = {}
    for target in targets:
        route_by_file.setdefault(target["controller_file"], []).append(target)

    rows = []
    for path in _controller_files(repo_root):
        source_file = _to_posix_relative(path, repo_root)
        route_targets = route_by_file.get(source_file, [])
        fallback_class = source_file.replace(f"{CONTROLLERS_DIR}/", "").removesuffix(".php").replace("/", "\\")

        for line_number, line in enumerate(path.read_text(encoding="utf-8", errors="replace").splitlines(), start=1):
            stripped = line.strip()
            if not stripped:
                continue

            detected_type = _detect_line_type(stripped)
            controller_class = _extract_class(stripped, fallback_class)
            method = _extract_method(stripped)
            service = _extract_service(stripped)
            model = _extract_model(stripped)

            related_route = None
            if method:
                for target in route_targets:
                    if target["method"] == method:
                        related_route = target["route_path"]
                        break

            if not _matches_query(
                query,
                stripped,
                source_file,
                controller_class,
                fallback_class,
                method,
                service,
                model,
                related_route,
            ):
                continue

            rows.append(
                {
                    "_score": _match_score(query, related_route, controller_class, method, stripped),
                    "status": "success",
                    "source_file": source_file,
                    "line_number": line_number,
                    "matched_text": stripped,
                    "detected_type": detected_type,
                    "controller_class": controller_class,
                    "method": method,
                    "related_route": related_route,
                    "related_service": service,
                    "related_model": model,
                    "risk_hint": _risk_hint(detected_type, stripped),
                }
            )

    return rows


def controller_lookup(query: str, repo_root: str | Path = "/workspace", max_results: int = DEFAULT_MAX_RESULTS) -> dict:
    """Inspect controller and route files as read-only text."""
    safe_query = (query or "").strip()
    if not safe_query:
        return {
            "status": "error",
            "query": query,
            "matches": [],
            "result_count": 0,
            "truncated": False,
            "max_results": DEFAULT_MAX_RESULTS,
            "warnings": ["query is required"],
            "sources": [CONTROLLERS_DIR, ROUTES_FILE],
        }

    root = Path(repo_root).resolve()
    if not root.exists() or not root.is_dir():
        return {
            "status": "error",
            "query": safe_query,
            "matches": [],
            "result_count": 0,
            "truncated": False,
            "max_results": DEFAULT_MAX_RESULTS,
            "warnings": [f"repo root does not exist: {root}"],
            "sources": [CONTROLLERS_DIR, ROUTES_FILE],
        }

    limit = max(1, min(int(max_results or DEFAULT_MAX_RESULTS), HARD_MAX_RESULTS))
    targets = _route_targets(root)
    all_matches = _route_rows(safe_query, targets) + _controller_rows(root, safe_query, targets)
    all_matches.sort(key=lambda match: (match["_score"], match["line_number"], match["source_file"]))
    truncated = len(all_matches) > limit
    matches = []
    for match in all_matches[:limit]:
        match.pop("_score", None)
        matches.append(match)

    return {
        "status": "success" if matches else "no_results",
        "query": safe_query,
        "matches": matches,
        "result_count": len(matches),
        "truncated": truncated,
        "max_results": limit,
        "warnings": [],
        "sources": [CONTROLLERS_DIR, ROUTES_FILE],
    }
