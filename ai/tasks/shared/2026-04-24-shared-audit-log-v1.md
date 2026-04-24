# Shared Audit Log v1

## Goal

Create or standardize a shared audit logging approach for critical admin, secretary, and system actions.

## Scope

This task covers audit logging rules and shared infrastructure only.

## Related Skills

- ai/skills/AUDIT_LOG_RULES.md
- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/SAFE_MIGRATION_AND_SEEDER.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project has sensitive actions such as permission changes, product updates, order status changes, review moderation, and page/dashboard publishing.

These actions should be traceable.

## Requirements

1. Review whether an audit log table already exists.
2. If missing, add a safe additive migration for audit_logs.
3. Create or standardize a shared AuditLogService.
4. Do not log passwords, tokens, card data, or sensitive payment data.
5. Store actor user id when available.
6. Store action code consistently.
7. Store entity type and entity id.
8. Store before/after data only when useful.
9. Store IP and user agent if available.
10. Keep audit logging centralized.

## Suggested Table Fields

- id
- actor_user_id
- action_code
- entity_type
- entity_id
- before_json
- after_json
- ip
- user_agent
- created_at

## Priority Actions to Log

1. Permission changes
2. Secretary permission changes
3. Product price and stock changes
4. Order status changes
5. Shipment status changes
6. Review moderation actions
7. Page builder publish
8. Dashboard builder publish
9. Digital access grant/revoke

## Expected Areas to Check

- app/Database/Migrations/
- app/Services/
- app/Models/
- app/Controllers/Admin/
- app/Controllers/Secretary/
- app/Config/Routes.php

## Manual Test Steps

1. Perform a permission change as admin.
2. Check audit_logs for the permission change.
3. Change a product price or stock value.
4. Check audit_logs for before/after values.
5. Change an order status.
6. Check audit_logs for actor, action code, and entity id.
7. Confirm no password/token/payment sensitive value is logged.

## Out of Scope

- Building a full audit log UI
- Real-time alerting
- External logging integrations
- Rewriting all existing services at once

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
