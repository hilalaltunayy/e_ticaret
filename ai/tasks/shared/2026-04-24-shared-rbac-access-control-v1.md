# Shared RBAC Access Control v1

## Goal

Standardize authentication, role checks, permission checks, and ownership checks across admin, secretary, and user areas.

## Scope

This task covers shared access control behavior used by multiple modules.

## Related Skills

- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project has three main roles:

- admin
- secretary
- user

Admin has full management access.

Secretary must only access the modules explicitly allowed by admin permissions.

User must only access storefront and personal account features.

## Requirements

1. Review current route groups for admin, secretary, and user.
2. Review current filters and permission checks.
3. Ensure protected routes require login.
4. Ensure admin-only routes cannot be accessed by secretary or user.
5. Ensure secretary routes are permission-based.
6. Ensure user-only pages are protected from admin/secretary misuse where needed.
7. Ensure direct URL access is blocked when permission is missing.
8. Ensure user-owned resources check ownership.
9. Keep permission code names consistent.
10. Do not rely only on hiding UI buttons.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Filters/
- app/Services/AuthService.php
- app/Services/PermissionService.php
- app/Models/UserModel.php
- app/Models/RoleModel.php
- app/Models/PermissionModel.php
- app/Models/UserPermissionModel.php
- app/Models/RolePermissionModel.php

## Manual Test Steps

1. Login as admin and open admin dashboard.
2. Login as secretary with no extra permissions and try admin dashboard.
3. Login as secretary with order permission and try order page.
4. Login as secretary without order permission and try order page manually from URL.
5. Login as user and try admin or secretary URL.
6. Logout and try protected URLs.
7. Check that user can only view their own account/orders.

## Out of Scope

- Creating full admin dashboard UI
- Creating full secretary dashboard UI
- Creating new business modules
- Rewriting authentication from scratch
- Removing existing RBAC structure

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
