# Oracle MCP Runtime

This folder contains the minimal local Oracle MCP runtime placeholder.

The runtime is intentionally small and safe. It does not implement the real MCP protocol yet.

## Current Files

- `Dockerfile`: local placeholder image definition.
- `compose.yaml`: local Compose definition with read-only repository mount.
- `server.py`: minimal placeholder entrypoint.
- `registry.py`: safe tool registry.
- `config.py`: non-secret read-only path configuration.
- `tools/__init__.py`: empty package marker for future tools.
- `tools/repo_file_lookup.py`: first read-only path lookup tool.
- `tools/route_lookup.py`: read-only CodeIgniter route lookup tool.
- `tools/model_lookup.py`: read-only model, migration, and seeder inspection tool.
- `tools/permission_lookup.py`: read-only RBAC source inspection tool.
- `tools/filter_lookup.py`: read-only CodeIgniter filter inspection tool.
- `tools/controller_lookup.py`: read-only controller and route handler inspection tool.
- `output/.gitkeep`: placeholder for the only approved writable runtime output directory.

## Safe Boundaries

- No secrets belong here.
- Do not place real `.env`, wallet files, credentials, API keys, private keys, passwords, certificates, or tokens in this folder.
- `app/` remains read-only.
- The repository mount must remain read-only.
- `output/` is the only approved writable runtime directory.
- No ports are exposed.
- No database access is implemented.
- No Oracle connection logic is implemented.
- No real MCP protocol server is implemented yet.
- `repo_file_lookup` returns path metadata only and does not read file contents.
- `route_lookup` reads `app/Config/Routes.php` as text only and does not execute PHP.
- `model_lookup` reads approved model, migration, and seeder source files as text only and does not execute PHP or access the database.
- `permission_lookup` reads approved RBAC source files as text only and does not execute PHP or access the database.
- `filter_lookup` reads approved filter and route source files as text only and does not execute PHP.
- `controller_lookup` reads controller and route source files as text only and does not execute PHP.

## Run Command

Use the controlled local runtime command:

```text
docker compose run --rm oracle-mcp-runtime
```

Current expected placeholder output:

```text
Oracle MCP runtime placeholder
Registered tools:
- repo_file_lookup (implemented)
- route_lookup (implemented)
- model_lookup (implemented)
- controller_lookup (implemented)
- permission_lookup (implemented)
- filter_lookup (implemented)
Sample repo_file_lookup:
- status: success
- result_count: 3
- app/Config/Routes.php
- system/Commands/Utilities/Routes.php
- system/Debug/Toolbar/Collectors/Routes.php
Sample route_lookup:
- status: success
- result_count: 5
- GET admin/dashboard -> Admin\DashboardController::index (app/Config/Routes.php:124)
- GET admin/dashboard/blocks/fetch/(:segment) -> Admin\DashboardBlockController::fetch/$1 (app/Config/Routes.php:99)
- GET admin/dashboard/blocks/detail -> Admin\DashboardBlockController::detail (app/Config/Routes.php:100)
- POST admin/dashboard/blocks/store -> Admin\DashboardBlockController::store (app/Config/Routes.php:101)
- POST admin/dashboard/blocks/update/(:segment) -> Admin\DashboardBlockController::update/$1 (app/Config/Routes.php:102)
Sample model_lookup:
- query: UserModel
  status: success
- query: RolePermissionModel
  status: success
- query: allowedFields
  status: success
Sample permission_lookup:
- query: manage_orders
  status: success
  result_count: 3
  - permission manage_orders (app/Database/Seeds/InitialAuthSeeder.php:25)
  - route admin/orders (app/Config/Routes.php:204)
  - route admin/api/orders (app/Config/Routes.php:205)
- query: admin/dashboard
  status: success
  result_count: 3
  - route admin/dashboard (app/Config/Routes.php:124)
  - route admin/dashboard/blocks/fetch/(:segment) (app/Config/Routes.php:99)
  - route admin/dashboard/blocks/detail (app/Config/Routes.php:100)
- query: secretary
  status: success
  result_count: 3
  - permission secretary (app/Database/Seeds/InitialAuthSeeder.php:21)
  - permission secretary (app/Database/Seeds/InitialAuthSeeder.php:49)
  - permission secretary (app/Database/Seeds/InitialAuthSeeder.php:52)
Sample filter_lookup:
- query: role:admin,secretary
  status: success
- query: perm:manage_orders
  status: success
- query: csrf
  status: success
Sample controller_lookup:
- query: admin/dashboard
  status: success
- query: Admin\Orders
  status: success
- query: Login
  status: success
```
