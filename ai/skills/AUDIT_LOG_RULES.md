# Audit Log Rules Skill

This skill defines how to track important system actions.

## Purpose

Ensure traceability of critical actions for security and debugging.

## Actions That Should Be Logged

- Permission changes
- Secretary role updates
- Product create/update/delete
- Price changes
- Stock updates
- Order status changes
- Shipment status updates
- Review moderation (approve/hide/delete)
- Page builder publish
- Dashboard publish
- Digital access grant/revoke
- Campaign or coupon changes

## Minimum Fields

- actor_user_id
- action_code
- entity_type
- entity_id
- before_json
- after_json
- ip
- user_agent
- created_at

## Rules

- Do not log sensitive data (passwords, tokens, payment data)
- Keep logs concise
- Log only meaningful actions
- Unauthorized access attempts can be logged separately
- Audit logs are not user-facing messages
- Use centralized audit logic (service/helper)
- Do not implement inconsistent logging across controllers

## Priority for MVP

1. Permission changes
2. Product price/stock changes
3. Order status changes
4. Review moderation
5. Page/Dashboard publish
