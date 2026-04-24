# Secretary Access Control v1

## Goal

Ensure secretary users can only access modules explicitly allowed by admin permissions.

## Scope

This task focuses on secretary authentication, permission checks, and route protection.

## Related Skills

- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project includes a secretary role with dynamic permissions controlled by admin.

Secretary access must be limited and permission-based.

## Requirements

1. Ensure secretary routes require authentication.
2. Ensure secretary users cannot access admin-only pages.
3. Ensure secretary access is based on assigned permissions.
4. Use route/filter checks before controller execution.
5. Re-check critical permissions in service layer.
6. Do not hard-code secretary permissions.
7. Support user_permissions override behavior.
8. Block direct URL access without permission.
9. Return proper unauthorized response or redirect.
10. Keep permission code names consistent.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Filters/
- app/Controllers/Secretary/
- app/Services/AuthService.php
- app/Services/PermissionService.php
- app/Models/UserPermissionModel.php
- app/Models/RolePermissionModel.php

## Manual Test Steps

1. Login as secretary with no permissions.
2. Try opening secretary routes.
3. Login as secretary with order permission.
4. Open allowed order page.
5. Try opening unauthorized secretary page.
6. Try opening admin dashboard.
7. Try direct URL access to restricted pages.
8. Confirm admin can update secretary permissions.

## Out of Scope

- Full secretary dashboard UI
- Admin permission management UI
- New business modules
- Rewriting the auth system

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
