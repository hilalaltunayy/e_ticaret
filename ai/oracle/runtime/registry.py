"""Safe tool registry for the local Oracle MCP runtime."""

INITIAL_TOOLS = [
    {
        "name": "repo_file_lookup",
        "status": "implemented",
        "access": "read_only_repo",
        "handler": "tools.repo_file_lookup:repo_file_lookup",
    },
    {
        "name": "route_lookup",
        "status": "implemented",
        "access": "read_only_repo",
        "handler": "tools.route_lookup:route_lookup",
    },
    {
        "name": "model_lookup",
        "status": "implemented",
        "access": "read_only_repo",
        "handler": "tools.model_lookup:model_lookup",
    },
    {
        "name": "controller_lookup",
        "status": "implemented",
        "access": "read_only_repo",
        "handler": "tools.controller_lookup:controller_lookup",
    },
    {
        "name": "permission_lookup",
        "status": "implemented",
        "access": "read_only_repo",
        "handler": "tools.permission_lookup:permission_lookup",
    },
    {
        "name": "filter_lookup",
        "status": "implemented",
        "access": "read_only_repo",
        "handler": "tools.filter_lookup:filter_lookup",
    },
]


def get_registered_tools():
    """Return safe tool metadata."""
    return list(INITIAL_TOOLS)
