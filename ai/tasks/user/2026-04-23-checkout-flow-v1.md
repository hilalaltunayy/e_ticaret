# Task: Checkout Flow (v1)

## Epic
user-frontend

## Status
planned

## Goal
Build the first version of the checkout flow where users can review cart items, confirm order details, and proceed toward payment.

---

## Scope

- checkout page
- order summary section
- user information / address area
- cart item review
- basic checkout validation
- transition from cart to payment screen
- order confirmation preparation

---

## Out of Scope

- real payment provider integration
- advanced coupon system
- advanced shipment cost calculation
- multi-address support
- AI checkout assistance

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
- ai/epics/payment-system.md

---

## Expected Layers / Files

- routes
- checkout controller
- DTO
- checkout service
- cart service
- order service placeholder if needed
- cart/order models
- views

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template files:
  - checkout.html
  - order_summary.html
  - order_confirmation.html

- usage rules:
  - use checkout.html for the main checkout layout
  - use order_summary.html for right-side or step summary
  - use order_confirmation.html after successful order or as a prepared confirmation view
  - do not redesign checkout UI from scratch
  - keep checkout consistent with cart and payment screens

---

## Constraints / Rules

- only authenticated users can checkout
- checkout must validate cart ownership
- checkout must recheck prices and stock where applicable
- order item snapshot logic must be considered
- business logic must stay in service
- controller must remain thin
- payment integration is not part of this task unless explicitly requested

---

## Acceptance Criteria

- user can open checkout from cart
- checkout displays cart items correctly
- order summary totals are shown correctly
- unauthenticated user cannot checkout
- empty cart cannot proceed
- checkout can proceed toward payment screen or prepared next step

---

## Manual Test Steps

1. log in as user
2. add product to cart
3. open checkout page
4. verify cart items and totals
5. try checkout with empty cart
6. try checkout without login
7. proceed to payment/next step
8. verify UI consistency with cart

---

## Risks / Notes

- checkout is domain-critical
- price and stock mismatch must be considered
- payment should remain separated from checkout foundation

---

## Progress Log

- 2026-04-23: task created
