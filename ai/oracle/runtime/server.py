"""Minimal entrypoint for the local Oracle MCP runtime.

This file does not implement the MCP protocol yet. It loads the safe registry
metadata, demonstrates one read-only lookup, and exits cleanly.
"""

from pathlib import Path

from registry import get_registered_tools
from tools.repo_file_lookup import repo_file_lookup
from tools.route_lookup import route_lookup
from tools.model_lookup import model_lookup
from tools.permission_lookup import permission_lookup
from tools.filter_lookup import filter_lookup
from tools.controller_lookup import controller_lookup


def main() -> int:
    tools = get_registered_tools()

    print("Oracle MCP runtime placeholder")
    print("Registered tools:")
    for tool in tools:
        print(f"- {tool['name']} ({tool['status']})")

    sample = repo_file_lookup("Routes.php", repo_root=Path.cwd(), max_results=5)
    print("Sample repo_file_lookup:")
    print(f"- status: {sample['status']}")
    print(f"- result_count: {sample['result_count']}")
    for match in sample["matches"]:
        print(f"- {match['relative_path']}")

    route_sample = route_lookup("admin/dashboard", repo_root=Path.cwd(), max_results=5)
    print("Sample route_lookup:")
    print(f"- status: {route_sample['status']}")
    print(f"- result_count: {route_sample['result_count']}")
    for match in route_sample["matches"]:
        print(
            f"- {match['http_method']} {match['route_path']} -> "
            f"{match['controller_target']} ({match['source_file']}:{match['line_number']})"
        )

    print("Sample model_lookup:")
    for lookup_query in ("UserModel", "RolePermissionModel", "allowedFields"):
        sample = model_lookup(lookup_query, repo_root=Path.cwd(), max_results=3)
        print(f"- query: {lookup_query}")
        print(f"  status: {sample['status']}")
        print(f"  result_count: {sample['result_count']}")
        for match in sample["matches"]:
            target = match["table_name"] or match["model_class"] or match["field"] or "text"
            print(
                f"  - {match['detected_type']} {target} "
                f"({match['source_file']}:{match['line_number']})"
            )

    print("Sample permission_lookup:")
    for lookup_query in ("manage_orders", "admin/dashboard", "secretary"):
        sample = permission_lookup(lookup_query, repo_root=Path.cwd(), max_results=3)
        print(f"- query: {lookup_query}")
        print(f"  status: {sample['status']}")
        print(f"  result_count: {sample['result_count']}")
        for match in sample["matches"]:
            target = match["route_path"] or match["permission_code"] or match["role"] or "text"
            print(
                f"  - {match['detected_type']} {target} "
                f"({match['source_file']}:{match['line_number']})"
            )

    print("Sample filter_lookup:")
    for lookup_query in ("role:admin,secretary", "perm:manage_orders", "csrf"):
        sample = filter_lookup(lookup_query, repo_root=Path.cwd(), max_results=3)
        print(f"- query: {lookup_query}")
        print(f"  status: {sample['status']}")
        print(f"  result_count: {sample['result_count']}")
        for match in sample["matches"]:
            target = match["related_route"] or match["related_alias"] or "text"
            print(
                f"  - {match['detected_type']} {target} "
                f"({match['source_file']}:{match['line_number']})"
            )

    print("Sample controller_lookup:")
    for lookup_query in ("admin/dashboard", "Admin\\Orders", "Login"):
        sample = controller_lookup(lookup_query, repo_root=Path.cwd(), max_results=3)
        print(f"- query: {lookup_query}")
        print(f"  status: {sample['status']}")
        print(f"  result_count: {sample['result_count']}")
        for match in sample["matches"]:
            target = match["related_route"] or match["controller_class"] or match["method"] or "text"
            print(
                f"  - {match['detected_type']} {target} "
                f"({match['source_file']}:{match['line_number']})"
            )

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
