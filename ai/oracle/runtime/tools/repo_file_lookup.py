"""Read-only repository file path lookup tool.

The tool searches file paths only. It does not read file contents.
"""

from fnmatch import fnmatch
import os
from pathlib import Path


IGNORED_FOLDERS = {
    ".git",
    "vendor",
    "node_modules",
    "writable",
    "public/uploads",
    "ai/oracle/output",
    "ai/oracle/runtime/output",
}

SECRET_PATTERNS = (
    ".env",
    ".env.*",
    "*.key",
    "*.pem",
    "*.p12",
    "*.pfx",
    "*wallet*",
    "*credential*",
    "*secret*",
    "*token*",
)

DEFAULT_MAX_RESULTS = 20
HARD_MAX_RESULTS = 100


def _to_posix_relative(path: Path, root: Path) -> str:
    return path.relative_to(root).as_posix()


def _is_ignored_folder(relative_path: str) -> bool:
    if not relative_path:
        return False
    return any(
        relative_path == ignored or relative_path.startswith(f"{ignored}/")
        for ignored in IGNORED_FOLDERS
    )


def _is_secret_like(relative_path: str) -> bool:
    name = Path(relative_path).name.lower()
    lowered_path = relative_path.lower()
    return any(fnmatch(name, pattern) or fnmatch(lowered_path, pattern) for pattern in SECRET_PATTERNS)


def _file_type(extension: str) -> str:
    if not extension:
        return "unknown"
    return extension.lstrip(".").lower()


def repo_file_lookup(query: str, repo_root: str | Path = "/workspace", max_results: int = DEFAULT_MAX_RESULTS) -> dict:
    """Search repository file paths by filename or partial path.

    Returns path metadata only and never reads file contents.
    """
    safe_query = (query or "").strip()
    if not safe_query:
        return {
            "status": "error",
            "query": query,
            "matches": [],
            "result_count": 0,
            "truncated": False,
            "ignored_folders": sorted(IGNORED_FOLDERS),
            "warnings": ["query is required"],
            "sources": [],
        }

    root = Path(repo_root).resolve()
    if not root.exists() or not root.is_dir():
        return {
            "status": "error",
            "query": safe_query,
            "matches": [],
            "result_count": 0,
            "truncated": False,
            "ignored_folders": sorted(IGNORED_FOLDERS),
            "warnings": [f"repo root does not exist: {root}"],
            "sources": [str(root)],
        }

    limit = max(1, min(int(max_results or DEFAULT_MAX_RESULTS), HARD_MAX_RESULTS))
    lowered_query = safe_query.replace("\\", "/").lower()
    matches = []
    truncated = False

    for current_root, dir_names, file_names in os.walk(root, followlinks=False):
        current_path = Path(current_root)
        relative_current = _to_posix_relative(current_path, root) if current_path != root else ""
        if _is_ignored_folder(relative_current):
            dir_names[:] = []
            continue

        dir_names[:] = [
            name for name in dir_names
            if not _is_ignored_folder(f"{relative_current}/{name}".strip("/"))
        ]

        for file_name in file_names:
            path = current_path / file_name
            relative_path = _to_posix_relative(path, root)
            if _is_ignored_folder(relative_path) or _is_secret_like(relative_path):
                continue

            if lowered_query not in relative_path.lower() and lowered_query not in file_name.lower():
                continue

            extension = path.suffix.lower()
            matches.append(
                {
                    "relative_path": relative_path,
                    "extension": extension,
                    "file_type": _file_type(extension),
                }
            )

            if len(matches) >= limit:
                truncated = True
                break

        if truncated:
            break

    return {
        "status": "success" if matches else "no_match",
        "query": safe_query,
        "matches": matches,
        "result_count": len(matches),
        "truncated": truncated,
        "ignored_folders": sorted(IGNORED_FOLDERS),
        "warnings": [],
        "sources": [root.as_posix()],
    }
