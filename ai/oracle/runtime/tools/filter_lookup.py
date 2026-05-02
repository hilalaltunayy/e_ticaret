"""Read-only CodeIgniter filter lookup tool.

The tool inspects approved filter and route source files as text only. It does
not execute PHP, read secrets, or access the database.
"""

from pathlib import Path
import re


FILTERS_CONFIG = "app/Config/Filters.php"
ROUTES_FILE = "app/Config/Routes.php"
FILTERS_DIR = "app/Filters"

DEFAULT_MAX_RESULTS = 20
HARD_MAX_RESULTS = 100

ALIAS_PATTERN = re.compile(r"['\"]([^'\"]+)['\"]\s*=>\s*([^,]+),?")
GROUP_PATTERN = re.compile(
    r"\$routes->group\(\s*['\"]([^'\"]*)['\"]\s*,\s*\[([^\]]*)\]",
    re.IGNORECASE,
)
ROUTE_PATTERN = re.compile(
    r"\$routes->(get|post|put|delete|patch|options)\(\s*['\"]([^'\"]*)['\"]",
    re.IGNORECASE,
)
FILTER_PATTERN = re.compile(r"['\"]filter['\"]\s*=>\s*['\"]([^'\"]+)['\"]")
ROLE_PATTERN = re.compile(r"role:([a-z0-9_,]+)", re.IGNORECASE)
PERM_PATTERN = re.compile(r"perm:([a-z0-9_]+)", re.IGNORECASE)


def _join_route(prefixes: list[str], route_path: str) -> str:
    parts = [part.strip("/") for part in prefixes + [route_path] if part.strip("/")]
    return "/".join(parts)


def _extract_filter(text: str) -> str | None:
    match = FILTER_PATTERN.search(text)
    return match.group(1) if match else None


def _extract_alias(text: str) -> str | None:
    alias = ALIAS_PATTERN.search(text)
    if alias:
        return alias.group(1)
    match = re.search(r"\b(auth|role|perm|csrf|secureheaders|campaign_access)\b", text, re.IGNORECASE)
    return match.group(1) if match else None


def _extract_role(text: str) -> str | None:
    match = ROLE_PATTERN.search(text)
    return match.group(1) if match else None


def _extract_permission(text: str) -> str | None:
    match = PERM_PATTERN.search(text)
    return match.group(1) if match else None


def _risk_hint(detected_type: str, matched_text: str, filter_text: str | None = None) -> str | None:
    lowered = matched_text.lower()
    filter_lowered = (filter_text or "").lower()
    if detected_type == "route_filter" and ("role:" in filter_lowered or "perm:" in filter_lowered) and "auth" not in filter_lowered:
        return "Route filter uses role/permission without explicit auth alias; verify filter class login enforcement."
    if "csrf" in lowered and lowered.lstrip().startswith("//"):
        return "CSRF appears commented out in this source line."
    if "secureheaders" in lowered and lowered.lstrip().startswith("//"):
        return "Secure headers appear commented out in this source line."
    return None


def _detected_config_type(line: str, section: str) -> str:
    stripped = line.strip()
    if "=>" in stripped and "::class" in stripped:
        return "alias"
    if section in {"globals_before", "globals_after", "required_before", "required_after"}:
        return "global_filter"
    return "alias" if "aliases" in section else "global_filter"


def _matches_query(query: str, *values: str | None) -> bool:
    lowered = query.lower()
    return any(value and lowered in value.lower() for value in values)


def _match_score(query: str, related_route: str | None, matched_text: str) -> int:
    lowered = query.lower()
    route = (related_route or "").lower()
    text = matched_text.lower()
    if route == lowered:
        return 0
    if route.startswith(f"{lowered}/"):
        return 1
    if lowered in text:
        return 2
    return 3


def _row(
    *,
    query: str,
    source_file: str,
    line_number: int,
    matched_text: str,
    detected_type: str,
    related_route: str | None = None,
    related_alias: str | None = None,
    filter_text: str | None = None,
) -> dict | None:
    related_role = _extract_role(filter_text or matched_text)
    related_permission = _extract_permission(filter_text or matched_text)
    alias = related_alias or _extract_alias(matched_text)

    if not _matches_query(query, matched_text, related_route, alias, related_role, related_permission, filter_text):
        return None

    return {
        "_score": _match_score(query, related_route, matched_text),
        "status": "success",
        "source_file": source_file,
        "line_number": line_number,
        "matched_text": matched_text.strip(),
        "detected_type": detected_type,
        "related_route": related_route,
        "related_alias": alias,
        "related_role": related_role,
        "related_permission": related_permission,
        "risk_hint": _risk_hint(detected_type, matched_text, filter_text),
    }


def _config_matches(repo_root: Path, query: str) -> list[dict]:
    path = repo_root / FILTERS_CONFIG
    if not path.exists():
        return []

    section = ""
    matches = []
    for line_number, line in enumerate(path.read_text(encoding="utf-8", errors="replace").splitlines(), start=1):
        stripped = line.strip()
        lowered = stripped.lower()
        if "public array $aliases" in lowered:
            section = "aliases"
        elif "public array $required" in lowered:
            section = "required"
        elif "public array $globals" in lowered:
            section = "globals"
        elif section == "required" and "'before'" in lowered:
            section = "required_before"
        elif section == "required" and "'after'" in lowered:
            section = "required_after"
        elif section == "globals" and "'before'" in lowered:
            section = "globals_before"
        elif section == "globals" and "'after'" in lowered:
            section = "globals_after"

        detected_type = _detected_config_type(stripped, section)
        row = _row(
            query=query,
            source_file=FILTERS_CONFIG,
            line_number=line_number,
            matched_text=stripped,
            detected_type=detected_type,
        )
        if row:
            matches.append(row)
    return matches


def _route_filter_matches(repo_root: Path, query: str) -> list[dict]:
    path = repo_root / ROUTES_FILE
    if not path.exists():
        return []

    prefixes: list[str] = []
    filters: list[str | None] = []
    matches = []

    for line_number, line in enumerate(path.read_text(encoding="utf-8", errors="replace").splitlines(), start=1):
        stripped = line.strip()
        group_match = GROUP_PATTERN.search(stripped)
        if group_match:
            prefix = group_match.group(1)
            filter_text = _extract_filter(group_match.group(2))
            prefixes.append(prefix)
            filters.append(filter_text)

            route_prefix = _join_route(prefixes[:-1], prefix)
            row = _row(
                query=query,
                source_file=ROUTES_FILE,
                line_number=line_number,
                matched_text=stripped,
                detected_type="route_filter",
                related_route=route_prefix,
                filter_text=filter_text,
            )
            if row:
                matches.append(row)

        route_match = ROUTE_PATTERN.search(stripped)
        if route_match:
            route_path = _join_route(prefixes, route_match.group(2))
            active_filter = next((item for item in reversed(filters) if item), None)
            row = _row(
                query=query,
                source_file=ROUTES_FILE,
                line_number=line_number,
                matched_text=stripped,
                detected_type="route_filter",
                related_route=route_path,
                filter_text=active_filter,
            )
            if row:
                matches.append(row)

        if stripped == "});" and prefixes:
            prefixes.pop()
            filters.pop()

    return matches


def _filter_class_matches(repo_root: Path, query: str) -> list[dict]:
    filters_path = repo_root / FILTERS_DIR
    if not filters_path.exists() or not filters_path.is_dir():
        return []

    matches = []
    for path in sorted(filters_path.glob("*.php")):
        source_file = path.relative_to(repo_root).as_posix()
        for line_number, line in enumerate(path.read_text(encoding="utf-8", errors="replace").splitlines(), start=1):
            stripped = line.strip()
            if not stripped:
                continue
            lowered = stripped.lower()
            if "function before" in lowered:
                detected_type = "before_logic"
            elif "function after" in lowered:
                detected_type = "after_logic"
            elif "class " in lowered or source_file.lower().endswith(f"{query.lower()}.php"):
                detected_type = "filter_class"
            else:
                detected_type = "filter_class"

            row = _row(
                query=query,
                source_file=source_file,
                line_number=line_number,
                matched_text=stripped,
                detected_type=detected_type,
            )
            if row:
                matches.append(row)
    return matches


def filter_lookup(query: str, repo_root: str | Path = "/workspace", max_results: int = DEFAULT_MAX_RESULTS) -> dict:
    """Inspect filter-related source files as read-only text."""
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
            "sources": [FILTERS_CONFIG, ROUTES_FILE, FILTERS_DIR],
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
            "sources": [FILTERS_CONFIG, ROUTES_FILE, FILTERS_DIR],
        }

    limit = max(1, min(int(max_results or DEFAULT_MAX_RESULTS), HARD_MAX_RESULTS))
    all_matches = (
        _config_matches(root, safe_query)
        + _route_filter_matches(root, safe_query)
        + _filter_class_matches(root, safe_query)
    )
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
        "sources": [FILTERS_CONFIG, ROUTES_FILE, FILTERS_DIR],
    }
