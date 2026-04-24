# Admin Orders Management v1

## Goal

Create or standardize admin order management flow for viewing orders and updating order statuses.

## Scope

This task focuses on admin-side order management only.

## Related Skills

- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/AUDIT_LOG_RULES.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project supports user checkout and order tracking.

Admin should be able to manage orders, but order changes must be controlled and traceable.

## Requirements

1. Ensure only admin can access full order management.
2. Protect order routes with permission checks.
3. Use controller-service separation.
4. Allow admin to list orders.
5. Allow admin to view order detail.
6. Allow admin to update order status.
7. Prevent invalid order status transitions.
8. Do not delete orders permanently.
9. Log order status changes if audit log exists.
10. Keep user-facing order history consistent.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Controllers/Admin/
- app/Services/OrderService.php
- app/Models/OrderModel.php
- app/Views/admin/
- app/Database/Migrations/

## Manual Test Steps

1. Login as admin and open order management page.
2. View order list.
3. Open order detail.
4. Update order status with a valid transition.
5. Try invalid status transition.
6. Confirm user can see updated order status.
7. Confirm secretary/user cannot access full admin order management.
8. Confirm audit log is created for status changes if audit exists.

## Out of Scope

- Payment provider integration
- Refund automation
- Shipment tracking integration
- Advanced order analytics
- Full warehouse workflow

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
