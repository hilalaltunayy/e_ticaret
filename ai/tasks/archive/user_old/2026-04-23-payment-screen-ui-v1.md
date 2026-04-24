# Task: Payment Screen UI (v1)

## Epic
user-frontend

## Status
planned

## Goal
Create the first version of the user-facing payment screen UI and prepare it for future payment provider integration.

---

## Scope

- payment form screen
- card/payment input UI
- payment summary area
- connection point from checkout
- basic frontend validation structure
- placeholder submit flow if real payment is not implemented yet

---

## Out of Scope

- real payment provider integration
- webhook/callback processing
- refund processing
- storing real card data
- advanced fraud checks

---

## Relevant Context

- ai/project.md
- ai/rules.md
- ai/architecture.md
- ai/current_state.md
- ai/backlog.md
- ai/decisions.md
- ai/epics/user-frontend.md
- ai/epics/payment-system.md

---

## Expected Layers / Files

- routes
- payment controller
- DTO
- payment service placeholder or mock service
- views
- order/payment models only if already planned in this sprint

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template files:
  - payment_forms.html
  - checkout.html

- usage rules:
  - use payment_forms.html as the main payment form reference
  - keep payment UI consistent with checkout
  - do not create a custom payment UI from scratch
  - real payment integration is separate from pure UI unless explicitly requested
  - never store real card data in this task

---

## Constraints / Rules

- payment UI must not imply real payment is complete unless backend supports it
- no sensitive card data should be stored
- controller must stay thin
- service/mock service should handle payment flow placeholder
- payment and order logic must stay separated
- future provider integration must remain possible

---

## Acceptance Criteria

- payment screen opens after checkout
- payment form is visually consistent
- order/payment summary is visible
- submit action is safely handled as mock or placeholder
- no real sensitive payment data is persisted
- payment integration can be added later without redesigning the UI

---

## Manual Test Steps

1. log in as user
2. add item to cart
3. go to checkout
4. proceed to payment screen
5. verify payment form layout
6. submit placeholder/mock payment if implemented
7. verify no real card data is stored
8. verify failed/empty input behavior

---

## Risks / Notes

- payment is high-risk and should not be rushed
- real provider integration requires separate research/task
- UI and provider logic must stay separated

---

## Progress Log

- 2026-04-23: task created
