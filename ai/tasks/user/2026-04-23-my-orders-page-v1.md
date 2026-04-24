# Task: My Orders Page (v1)

## Epic
user-frontend

## Status
planned

## Goal
Create the user-facing orders page where users can view their order history and basic order details.

---

## Scope

- my orders page
- order list
- order detail summary
- order status display
- link from account page
- authenticated user-only access

---

## Out of Scope

- admin order management
- shipment event editing
- refunds
- invoice generation
- advanced shipment tracking

---

## Relevant Context

- ai/project.md
- ai/rules.md
- ai/architecture.md
- ai/current_state.md
- ai/backlog.md
- ai/decisions.md
- ai/epics/user-frontend.md
- ai/epics/order-shipping.md

---

## Expected Layers / Files

- routes
- orders controller
- DTO/ViewModel if needed
- order service
- order model
- order item model
- views

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template files:
  - orders_overview.html
  - order_summary.html
  - order_confirmation.html

- usage rules:
  - use orders_overview.html for the order list
  - use order_summary.html for order detail summary
  - use order_confirmation.html only for completed order feedback if needed
  - keep visual style consistent with the account area
  - do not create orders UI from scratch

---

## Constraints / Rules

- only authenticated users can access their orders
- users must only see their own orders
- ownership validation is mandatory
- order history must not be mutated from this page
- controller must remain thin
- service must handle query/business logic

---

## Acceptance Criteria

- user can view own order list
- user can open order detail/summary
- user cannot see another user's orders
- order status is displayed clearly
- empty state exists when user has no orders

---

## Manual Test Steps

1. log in as user with orders
2. open my orders page
3. verify order list
4. open an order detail
5. verify items and totals
6. log in as user without orders
7. verify empty state
8. try accessing another user's order URL if possible

---

## Risks / Notes

- ownership leak risk
- order snapshot values must be displayed, not recalculated historical values
- future shipment tracking can be added later

---

## Progress Log

- 2026-04-23: task created
