"""Read-only model/schema lookup tool.

The tool inspects approved model, migration, and seeder source files as text
only. It does not execute PHP, instantiate models, run migrations/seeders,
read secrets, or access the database.
"""

from pathlib import Path
import os
import re


APPROVED_ROOTS = (
    "app/Models",
    "app/Database/Migrations",
    "app/Database/Seeds",
)

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

SECRET_NAME_PATTERN = re.compile(
    r"(^|/)(\.env|.*secret.*|.*credential.*|.*wallet.*|.*private.*|.*key.*)(\.|$|/)",
    re.IGNORECASE,
)
CLASS_PATTERN = re.compile(r"\bclass\s+([A-Za-z_][A-Za-z0-9_]*)")
TABLE_PATTERN = re.compile(r"\$table\s*=\s*['\"]([^'\"]+)['\"]")
CREATE_TABLE_PATTERN = re.compile(r"createTable\(\s*['\"]([^'\"]+)['\"]", re.IGNORECASE)
QUOTED_TOKEN_PATTERN = re.compile(r"['\"]([A-Za-z_][A-Za-z0-9_]*)['\"]")
PRIMARY_KEY_PATTERN = re.compile(r"\$primaryKey\s*=\s*['\"]([^'\"]+)['\"]")
RETURN_TYPE_PATTERN = re.compile(r"\$returnType\s*=\s*['\"]?([^'\";]+)['\"]?")


def _to_posix_relative(path: Path, root: Path) -> str:
    return path.relative_to(root).as_posix()


def _is_ignored(relative_path: str) -> bool:
    if not relative_path:
        return False
    if SECRET_NAME_PATTERN.search(relative_path):
        return True
    return any(
        relative_path == ignored or relative_path.startswith(f"{ignored}/")
        for ignored in IGNORED_FOLDERS
    )


def _source_files(repo_root: Path) -> list[Path]:
    files: list[Path] = []
    for source_root in APPROVED_ROOTS:
        root = repo_root / source_root
        if not root.exists() or not root.is_dir():
            continue

        for current_root, dir_names, file_names in os.walk(root, followlinks=False):
            current_path = Path(current_root)
            relative_current = _to_posix_relative(current_path, repo_root)
            if _is_ignored(relative_current):
                dir_names[:] = []
                continue

            dir_names[:] = [
                name for name in dir_names
                if not _is_ignored(f"{relative_current}/{name}".strip("/"))
            ]

            for file_name in file_names:
                path = current_path / file_name
                relative_file = _to_posix_relative(path, repo_root)
                if file_name.endswith(".php") and not _is_ignored(relative_file):
                    files.append(path)
    return sorted(files)


def _source_kind(source_file: str) -> str:
    if source_file.startswith("app/Models/"):
        return "model"
    if source_file.startswith("app/Database/Migrations/"):
        return "migration"
    if source_file.startswith("app/Database/Seeds/"):
        return "seeder"
    return "unknown"


def _fallback_model_class(source_file: str) -> str | None:
    if not source_file.startswith("app/Models/"):
        return None
    return Path(source_file).stem


def _extract_table(line: str) -> str | None:
    table_match = TABLE_PATTERN.search(line)
    if table_match:
        return table_match.group(1)

    create_match = CREATE_TABLE_PATTERN.search(line)
    if create_match:
        return create_match.group(1)

    return None


def _extract_field(line: str, context: str | None = None) -> str | None:
    if context in {"allowedFields", "validationRules"}:
        token = QUOTED_TOKEN_PATTERN.search(line)
        return token.group(1) if token else None

    primary_match = PRIMARY_KEY_PATTERN.search(line)
    if primary_match:
        return primary_match.group(1)

    if "addField" in line or "forge->addColumn" in line or "forge->addKey" in line:
        token = QUOTED_TOKEN_PATTERN.search(line)
        return token.group(1) if token else None

    if re.search(r"['\"][A-Za-z_][A-Za-z0-9_]*['\"]\s*=>", line):
        token = QUOTED_TOKEN_PATTERN.search(line)
        return token.group(1) if token else None

    return None


def _detect_type(source_file: str, line: str, context: str | None) -> str:
    kind = _source_kind(source_file)
    lowered = line.lower()

    if kind == "seeder":
        return "seeder_reference"

    if kind == "migration":
        return "migration_field"

    if CLASS_PATTERN.search(line):
        return "model_class"
    if TABLE_PATTERN.search(line):
        return "table_name"
    if context == "allowedFields" or "$allowedFields" in line:
        return "allowed_field"
    if context == "validationRules" or "$validationRules" in line:
        return "validation_rule"
    if "$returnType" in line:
        return "return_type"
    if (
        "$primaryKey" in line
        or "$useSoftDeletes" in line
        or "deleted_at" in lowered
        or "uuid" in lowered
        or re.search(r"['\"][a-z0-9_]+_id['\"]", lowered)
    ):
        return "relationship_hint"
    return "relationship_hint"


def _extract_return_type(line: str) -> str | None:
    match = RETURN_TYPE_PATTERN.search(line)
    if not match:
        return None
    return match.group(1).strip()


def _risk_hint(detected_type: str, line: str, source_file: str) -> str | None:
    lowered = line.lower()
    if detected_type == "relationship_hint":
        return "Relationship or base-model behavior is inferred from text and requires manual review."
    if detected_type == "migration_field" and "createTable" not in line and "addField" not in line:
        return "Migration field evidence is text-based; field-level schema diff still requires deeper audit."
    if detected_type == "seeder_reference":
        return "Seeder evidence confirms source text only, not runtime database state."
    if "uuid" in lowered:
        return "UUID behavior may be inherited or framework-dependent; verify parent model behavior if needed."
    if source_file.endswith("RoleModels.php"):
        return "RoleModels.php name may overlap with RoleModel.php; verify intended ownership."
    return None


def _confidence(detected_type: str, line: str) -> str:
    if detected_type in {"model_class", "table_name", "allowed_field", "validation_rule", "return_type"}:
        return "high"
    if detected_type in {"migration_field", "seeder_reference"}:
        return "medium"
    return "low"


def _matches_query(query: str, *values: str | None) -> bool:
    lowered = query.lower()
    normalized = lowered.replace("\\", "/")
    candidates: list[str] = []
    for value in values:
        if value:
            candidate = value.lower()
            candidates.append(candidate)
            candidates.append(candidate.replace("\\", "/"))
    return any(lowered in candidate or normalized in candidate for candidate in candidates)


def _match_score(query: str, model_class: str | None, table_name: str | None, field: str | None, text: str) -> int:
    lowered = query.lower()
    if (model_class or "").lower() == lowered:
        return 0
    if (table_name or "").lower() == lowered:
        return 1
    if (field or "").lower() == lowered:
        return 2
    if lowered in (model_class or "").lower() or lowered in (table_name or "").lower():
        return 3
    if lowered in (field or "").lower():
        return 4
    if lowered in text.lower():
        return 5
    return 6


def _scan_file(repo_root: Path, path: Path, query: str) -> list[dict]:
    source_file = _to_posix_relative(path, repo_root)
    kind = _source_kind(source_file)
    fallback_model = _fallback_model_class(source_file)
    rows: list[dict] = []
    current_model = fallback_model
    current_table: str | None = None
    context: str | None = None

    lines = path.read_text(encoding="utf-8", errors="replace").splitlines()
    for line_number, raw_line in enumerate(lines, start=1):
        stripped = raw_line.strip()
        if not stripped or stripped.startswith("//"):
            continue

        if "$allowedFields" in stripped:
            context = "allowedFields"
        elif "$validationRules" in stripped:
            context = "validationRules"

        class_match = CLASS_PATTERN.search(stripped)
        if class_match:
            current_model = class_match.group(1)

        line_table = _extract_table(stripped)
        table_name = line_table or current_table
        if line_table:
            current_table = line_table

        field = _extract_field(stripped, context)
        return_type = _extract_return_type(stripped)
        detected_type = _detect_type(source_file, stripped, context)
        related_migration = source_file if kind == "migration" else None
        related_seeder = source_file if kind == "seeder" else None

        if _matches_query(
            query,
            stripped,
            class_match.group(1) if class_match else None,
            line_table,
            field,
            return_type,
            detected_type,
            context,
        ):
            rows.append(
                {
                    "_score": _match_score(query, current_model, table_name, field, stripped),
                    "status": "success",
                    "source_file": source_file,
                    "line_number": line_number,
                    "matched_text": stripped,
                    "detected_type": detected_type,
                    "model_class": current_model,
                    "table_name": table_name,
                    "field": field,
                    "related_migration": related_migration,
                    "related_seeder": related_seeder,
                    "risk_hint": _risk_hint(detected_type, stripped, source_file),
                    "confidence": _confidence(detected_type, stripped),
                }
            )

        if context and "];" in stripped:
            context = None

    return rows


def model_lookup(query: str, repo_root: str | Path = "/workspace", max_results: int = DEFAULT_MAX_RESULTS) -> dict:
    """Inspect approved model, migration, and seeder files as read-only text."""
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
            "sources": list(APPROVED_ROOTS),
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
            "sources": list(APPROVED_ROOTS),
        }

    limit = max(1, min(int(max_results or DEFAULT_MAX_RESULTS), HARD_MAX_RESULTS))
    all_matches: list[dict] = []
    for path in _source_files(root):
        all_matches.extend(_scan_file(root, path, safe_query))

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
        "sources": list(APPROVED_ROOTS),
    }
