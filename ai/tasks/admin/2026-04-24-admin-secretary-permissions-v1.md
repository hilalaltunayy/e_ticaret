# Admin Secretary Permissions v1

## Goal

Allow admin to assign, update, and revoke permissions for secretary users in a controlled and secure way.

## Scope

This task focuses on admin-side management of secretary permissions only.

## Related Skills

- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/AUDIT_LOG_RULES.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project includes a secretary role with dynamic permissions.

Admin must be able to control what each secretary can access.

Permissions are expected to be handled via:

- roles
- permissions
- role_permissions
- user_permissions (override system)

## Requirements

1. Ensure only admin can manage secretary permissions.
2. Protect routes with proper permission checks.
3. Allow admin to list secretary users.
4. Allow admin to assign permissions to a secretary.
5. Allow admin to remove permissions from a secretary.
6. Support user-level permission overrides.
7. Do not hard-code secretary permissions.
8. Keep permission codes consistent across system.
9. Log permission changes if audit log exists.
10. Prevent unauthorized users from modifying permissions.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Controllers/Admin/
- app/Services/PermissionService.php
- app/Models/UserModel.php
- app/Models/RoleModel.php
- app/Models/PermissionModel.php
- app/Models/UserPermissionModel.php
- app/Models/RolePermissionModel.php
- app/Views/admin/

## Manual Test Steps

1. Login as admin and open secretary management page.
2. View list of secretary users.
3. Assign a permission to a secretary.
4. Remove a permission from a secretary.
5. Login as secretary and verify access change.
6. Try accessing restricted route without permission.
7. Try modifying permissions as non-admin user (should fail).
8. Confirm audit log is created for permission changes.

## Out of Scope

- Full user management system redesign
- Role creation beyond existing roles
- External permission systems
- Advanced UI for permission matrix

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
