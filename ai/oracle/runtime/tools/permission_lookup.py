"""Read-only RBAC and permission lookup tool.

The tool inspects approved source files as text only. It does not execute PHP,
read secrets, or access the database.
"""

from pathlib import Path
import re


APPROVED_SOURCES = (
    "app/Config/Routes.php",
    "app/Config/Filters.php",
    "app/Database/Seeds/InitialAuthSeeder.php",
    "app/Models/PermissionModel.php",
    "app/Models/RolePermissionModel.php",
    "app/Models/UserPermissionModel.php",
    "app/Services/AuthService.php",
)

DEFAULT_MAX_RESULTS = 20
HARD_MAX_RESULTS = 100

GROUP_PATTERN = re.compile(
    r"\$routes->group\(\s*['\"]([^'\"]*)['\"]\s*,\s*\[([^\]]*)\]",
    re.IGNORECASE,
)
ROUTE_PATTERN = re.compile(
    r"\$routes->(get|post|put|delete|patch|options)\(\s*['\"]([^'\"]*)['\"]\s*,\s*['\"]([^'\"]*)['\"]",
    re.IGNORECASE,
)
PERMISSION_PATTERN = re.compile(r"\b(?:perm:)?([a-z]+_[a-z0-9_]+)\b", re.IGNORECASE)
ROLE_FILTER_PATTERN = re.compile(r"role:([a-z0-9_,]+)", re.IGNORECASE)
KNOWN_ROLES = {"admin", "secretary", "user"}


def _join_route(prefixes: list[str], route_path: str) -> str:
    parts = [part.strip("/") for part in prefixes + [route_path] if part.strip("/")]
    return "/".join(parts)


def _extract_filter(text: str) -> str | None:
    match = re.search(r"['\"]filter['\"]\s*=>\s*['\"]([^'\"]+)['\"]", text)
    return match.group(1) if match else None


def _extract_permission(text: str) -> str | None:
    match = PERMISSION_PATTERN.search(text)
    return match.group(1) if match else None


def _extract_role(text: str) -> str | None:
    filter_match = ROLE_FILTER_PATTERN.search(text)
    if filter_match:
        return filter_match.group(1)

    lowered = text.lower()
    for role in KNOWN_ROLES:
        if re.search(rf"\b{re.escape(role)}\b", lowered):
            return role
    return None


def _detected_type(source_file: str, text: str) -> str:
    lowered = text.lower()
    if source_file.endswith("Routes.php") and "$routes->" in text:
        return "route"
    if source_file.endswith("Filters.php") or "filter" in lowered or "perm:" in lowered:
        return "filter"
    if source_file.endswith("InitialAuthSeeder.php") or "permission" in lowered:
        return "permission"
    if source_file.endswith("AuthService.php"):
        return "service_rule"
    if source_file.endswith("Model.php"):
        return "model_reference"
    return "unknown"


def _matches_query(query: str, *values: str | None) -> bool:
    lowered = query.lower()
    return any(value and lowered in value.lower() for value in values)


def _match_score(query: str, route_path: str | None, matched_text: str) -> int:
    lowered = query.lower()
    lowered_route = (route_path or "").lower()
    lowered_text = matched_text.lower()
    if lowered_route == lowered:
        return 0
    if lowered_route.startswith(f"{lowered}/"):
        return 1
    if lowered_route.startswith(lowered):
        return 2
    if lowered in lowered_text:
        return 3
    return 4


def _match_row(
    *,
    source_file: str,
    line_number: int,
    matched_text: str,
    detected_type: str,
    query: str,
    route_path: str | None = None,
    filter_text: str | None = None,
) -> dict | None:
    permission_code = _extract_permission(matched_text if not filter_text else f"{filter_text} {matched_text}")
    role = _extract_role(matched_text if not filter_text else f"{filter_text} {matched_text}")

    if not _matches_query(query, matched_text, route_path, filter_text, permission_code, role):
        return None

    return {
        "_score": _match_score(query, route_path, matched_text),
        "source_file": source_file,
        "line_number": line_number,
        "matched_text": matched_text.strip(),
        "detected_type": detected_type,
        "route_path": route_path,
        "filter": filter_text,
        "permission_code": permission_code,
        "role": role,
        "status": "success",
    }


def _route_matches(repo_root: Path, query: str) -> list[dict]:
    source_file = "app/Config/Routes.php"
    route_file = repo_root / source_file
    if not route_file.exists():
        return []

    group_prefixes: list[str] = []
    group_filters: list[str | None] = []
    matches: list[dict] = []

    lines = route_file.read_text(encoding="utf-8", errors="replace").splitlines()
    for line_number, raw_line in enumerate(lines, start=1):
        stripped = raw_line.strip()
        group_match = GROUP_PATTERN.search(stripped)
        if group_match:
            group_prefixes.append(group_match.group(1))
            group_filters.append(_extract_filter(group_match.group(2)))

        route_match = ROUTE_PATTERN.search(stripped)
        if route_match:
            route_path = _join_route(group_prefixes, route_match.group(2))
            active_filter = next((item for item in reversed(group_filters) if item), None)
            row = _match_row(
                source_file=source_file,
                line_number=line_number,
                matched_text=stripped,
                detected_type="route",
                query=query,
                route_path=route_path,
                filter_text=active_filter,
            )
            if row:
                matches.append(row)

        if stripped == "});" and group_prefixes:
            group_prefixes.pop()
            group_filters.pop()

    return matches


def _source_text_matches(repo_root: Path, query: str) -> list[dict]:
    matches: list[dict] = []
    for source_file in APPROVED_SOURCES:
        if source_file == "app/Config/Routes.php":
            continue

        path = repo_root / source_file
        if not path.exists() or not path.is_file():
            continue

        lines = path.read_text(encoding="utf-8", errors="replace").splitlines()
        for line_number, raw_line in enumerate(lines, start=1):
            stripped = raw_line.strip()
            if not stripped or stripped.startswith("//"):
                continue

            row = _match_row(
                source_file=source_file,
                line_number=line_number,
                matched_text=stripped,
                detected_type=_detected_type(source_file, stripped),
                query=query,
            )
            if row:
                matches.append(row)

    return matches


def permission_lookup(query: str, repo_root: str | Path = "/workspace", max_results: int = DEFAULT_MAX_RESULTS) -> dict:
    """Inspect approved RBAC source files as read-only text."""
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
            "sources": list(APPROVED_SOURCES),
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
            "sources": list(APPROVED_SOURCES),
        }

    limit = max(1, min(int(max_results or DEFAULT_MAX_RESULTS), HARD_MAX_RESULTS))
    all_matches = _route_matches(root, safe_query) + _source_text_matches(root, safe_query)
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
        "sources": list(APPROVED_SOURCES),
    }
