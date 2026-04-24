# Admin Dashboard Access v1

## Goal

Ensure that only admin users can access the admin dashboard and that access control is correctly enforced.

## Scope

This task focuses on authentication, role verification, and route protection for admin dashboard access.

## Related Skills

- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project includes multiple roles:

- admin
- secretary
- user

The admin dashboard is a protected area and must not be accessible by secretary or user roles.

## Requirements

1. Ensure admin dashboard routes require authentication.
2. Ensure only users with admin role can access admin dashboard.
3. Prevent secretary and user roles from accessing admin routes.
4. Prevent access via direct URL manipulation.
5. Use filters for route-level protection.
6. Ensure session-based role data is correctly checked.
7. Do not rely only on frontend visibility.
8. Return proper response or redirect on unauthorized access.
9. Keep logic consistent with RBAC system.
10. Avoid duplicating access control logic in multiple places.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Filters/
- app/Controllers/Admin/
- app/Services/AuthService.php
- session handling

## Manual Test Steps

1. Login as admin and access /admin/dashboard.
2. Login as secretary and try /admin/dashboard.
3. Login as user and try /admin/dashboard.
4. Logout and try accessing /admin/dashboard.
5. Try accessing admin routes via direct URL.

## Out of Scope

- Building full dashboard UI
- Adding analytics widgets
- Implementing business logic inside dashboard
- Creating new admin modules

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
