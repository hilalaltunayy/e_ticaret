# Epic: Payment System

## Goal
Build a secure, extendable, and controlled payment infrastructure.

---

## Scope

- payment initiation
- payment attempt structure
- callback / webhook handling
- success / failure handling
- refund process

---

## Rules

- payments must be idempotent
- callbacks must be secure
- payment data must not be manipulated directly
- orders and payments must be separated

---

## Technical Notes

- start with mock provider
- integrate real provider later
- webhook validation is mandatory

---

## Success Criteria

- user can initiate payment
- payment result is processed correctly
- order-payment relation is consistent
