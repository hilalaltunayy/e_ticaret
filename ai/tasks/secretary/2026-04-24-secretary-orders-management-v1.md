# Secretary Orders Management v1

## Goal

Allow secretary users to view and manage orders only if admin has granted the required permission.

## Scope

This task focuses on secretary-side order management with limited permissions.

## Related Skills

- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/AUDIT_LOG_RULES.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project includes a secretary role whose permissions are controlled by admin.

Secretary order access must be more limited than full admin order management.

## Requirements

1. Ensure secretary order routes require authentication.
2. Ensure secretary order access depends on assigned permission.
3. Allow secretary to view order list if permitted.
4. Allow secretary to view order detail if permitted.
5. Allow secretary to update allowed order statuses if permitted.
6. Prevent invalid order status transitions.
7. Do not allow secretary to delete orders.
8. Do not allow secretary to access admin-only order actions.
9. Log order status changes if audit log exists.
10. Block direct URL access without permission.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Controllers/Secretary/
- app/Services/OrderService.php
- app/Models/OrderModel.php
- app/Views/secretary/
- app/Services/PermissionService.php

## Manual Test Steps

1. Login as secretary without order permission.
2. Try opening secretary order page.
3. Login as secretary with order permission.
4. Open order list.
5. Open order detail.
6. Update an allowed order status.
7. Try an invalid order status transition.
8. Try direct URL access without permission.
9. Confirm user-facing order status updates correctly.
10. Confirm audit log is created for status change if audit exists.

## Out of Scope

- Full admin order management
- Payment refunds
- Shipment carrier integrations
- Deleting orders
- Advanced order analytics

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
