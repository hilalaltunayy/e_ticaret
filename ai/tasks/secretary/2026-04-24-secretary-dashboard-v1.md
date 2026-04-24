# Secretary Dashboard v1

## Goal

Create or standardize a limited secretary dashboard that only shows modules allowed by admin permissions.

## Scope

This task focuses on secretary dashboard visibility and navigation.

## Related Skills

- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project includes a secretary role with dynamic permissions.

Secretary users should not see full admin dashboard content. They should only see allowed modules such as orders or review moderation.

## Requirements

1. Ensure secretary dashboard requires authentication.
2. Ensure secretary dashboard is separate from full admin dashboard.
3. Show only modules allowed by assigned permissions.
4. Hide unauthorized navigation items.
5. Block unauthorized routes even if URL is typed manually.
6. Do not hard-code visible modules.
7. Use permission-based dashboard cards or menu items.
8. Show a friendly empty state if no permissions are assigned.
9. Keep dashboard layout simple.
10. Do not expose admin-only analytics unless explicitly permitted.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Controllers/Secretary/
- app/Services/PermissionService.php
- app/Views/secretary/
- app/Views/layouts/
- app/Views/partials/

## Manual Test Steps

1. Login as secretary with no permissions.
2. Open secretary dashboard.
3. Confirm empty or limited dashboard state.
4. Login as secretary with order permission.
5. Confirm order module appears.
6. Login as secretary with review permission.
7. Confirm review module appears.
8. Try direct URL access to hidden modules.
9. Confirm admin-only widgets are not visible.

## Out of Scope

- Full analytics dashboard
- Admin dashboard builder
- Advanced reporting
- New permission UI

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
