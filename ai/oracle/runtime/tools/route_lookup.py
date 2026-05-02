"""Read-only CodeIgniter route lookup tool.

The tool parses route definition text. It does not execute PHP.
"""

from pathlib import Path
import re


ROUTES_FILE = Path("app/Config/Routes.php")
DEFAULT_MAX_RESULTS = 20
HARD_MAX_RESULTS = 100

GROUP_PATTERN = re.compile(
    r"\$routes->group\(\s*['\"]([^'\"]*)['\"]",
    re.IGNORECASE,
)
ROUTE_PATTERN = re.compile(
    r"\$routes->(get|post|put|delete|patch|options)\(\s*['\"]([^'\"]*)['\"]\s*,\s*['\"]([^'\"]*)['\"]",
    re.IGNORECASE,
)
MATCH_PATTERN = re.compile(
    r"\$routes->match\(\s*\[([^\]]+)\]\s*,\s*['\"]([^'\"]*)['\"]\s*,\s*['\"]([^'\"]*)['\"]",
    re.IGNORECASE,
)


def _join_route(prefixes: list[str], route_path: str) -> str:
    parts = [part.strip("/") for part in prefixes + [route_path] if part.strip("/")]
    return "/".join(parts)


def _extract_route_name(line: str) -> str | None:
    match = re.search(r"['\"]as['\"]\s*=>\s*['\"]([^'\"]+)['\"]", line)
    return match.group(1) if match else None


def _controller_action(target: str) -> dict:
    if "::" not in target:
        return {
            "controller": target,
            "action": None,
            "controller_target": target,
        }

    controller, action = target.split("::", 1)
    return {
        "controller": controller,
        "action": action,
        "controller_target": target,
    }


def _matches_query(query: str, route_path: str, target: str, line: str) -> bool:
    lowered = query.lower()
    return (
        lowered in route_path.lower()
        or lowered in target.lower()
        or lowered in line.lower()
    )


def _match_score(query: str, route_path: str, target: str) -> int:
    lowered = query.lower()
    lowered_route = route_path.lower()
    lowered_target = target.lower()
    if lowered_route == lowered:
        return 0
    if lowered_route.startswith(f"{lowered}/"):
        return 1
    if lowered_route.startswith(lowered):
        return 2
    if lowered_target == lowered:
        return 3
    if lowered in lowered_target:
        return 4
    return 5


def _match_methods(raw_methods: str) -> str:
    methods = re.findall(r"['\"]([^'\"]+)['\"]", raw_methods)
    if not methods:
        return "UNKNOWN"
    return ",".join(method.upper() for method in methods)


def route_lookup(query: str, repo_root: str | Path = "/workspace", max_results: int = DEFAULT_MAX_RESULTS) -> dict:
    """Search CodeIgniter route definitions in app/Config/Routes.php.

    Returns route metadata only and never executes PHP or reads secrets.
    """
    safe_query = (query or "").strip()
    source_file = ROUTES_FILE.as_posix()

    if not safe_query:
        return {
            "status": "error",
            "query": query,
            "matches": [],
            "result_count": 0,
            "truncated": False,
            "warnings": ["query is required"],
            "sources": [source_file],
        }

    root = Path(repo_root).resolve()
    route_file = root / ROUTES_FILE
    if not route_file.exists() or not route_file.is_file():
        return {
            "status": "error",
            "query": safe_query,
            "matches": [],
            "result_count": 0,
            "truncated": False,
            "warnings": [f"route file not found: {source_file}"],
            "sources": [source_file],
        }

    limit = max(1, min(int(max_results or DEFAULT_MAX_RESULTS), HARD_MAX_RESULTS))
    group_stack: list[str] = []
    matches: list[dict] = []
    all_matches: list[dict] = []

    lines = route_file.read_text(encoding="utf-8", errors="replace").splitlines()
    for line_number, raw_line in enumerate(lines, start=1):
        stripped = raw_line.strip()

        group_match = GROUP_PATTERN.search(stripped)
        if group_match:
            group_stack.append(group_match.group(1))

        route_match = ROUTE_PATTERN.search(stripped)
        match_route = MATCH_PATTERN.search(stripped)

        if route_match:
            http_method = route_match.group(1).upper()
            child_path = route_match.group(2)
            target = route_match.group(3)
        elif match_route:
            http_method = _match_methods(match_route.group(1))
            child_path = match_route.group(2)
            target = match_route.group(3)
        else:
            if stripped == "});" and group_stack:
                group_stack.pop()
            continue

        route_path = _join_route(group_stack, child_path)
        if _matches_query(safe_query, route_path, target, stripped):
            target_parts = _controller_action(target)
            all_matches.append(
                {
                    "_score": _match_score(safe_query, route_path, target),
                    "matched_route_text": stripped,
                    "http_method": http_method,
                    "route_path": route_path,
                    "controller": target_parts["controller"],
                    "action": target_parts["action"],
                    "controller_target": target_parts["controller_target"],
                    "route_name": _extract_route_name(stripped),
                    "source_file": source_file,
                    "line_number": line_number,
                    "status": "success",
                }
            )

        if stripped == "});" and group_stack:
            group_stack.pop()

    all_matches.sort(key=lambda match: (match["_score"], match["line_number"], match["route_path"]))
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
        "warnings": [],
        "sources": [source_file],
    }
