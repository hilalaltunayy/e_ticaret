# Task: Rating System (v1)

## Epic
user-frontend

## Status
planned

## Goal
Allow users to rate purchased products with a star-based rating system.

---

## Scope

- star rating UI
- rating submission
- rating update if allowed
- purchased-product validation
- display average/basic rating where needed
- integration with product detail page

---

## Out of Scope

- full review moderation
- AI sentiment analysis
- recommendation based on ratings
- complex rating analytics

---

## Relevant Context

- ai/project.md
- ai/rules.md
- ai/architecture.md
- ai/current_state.md
- ai/backlog.md
- ai/decisions.md
- ai/epics/user-frontend.md

---

## Expected Layers / Files

- routes
- rating controller
- DTO
- rating service
- rating/review model
- product/order ownership checks
- product detail view

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template files:
  - product_reviews.html
  - product_overview.html

- usage rules:
  - use product_reviews.html for rating and review UI
  - use product_overview.html only if rating summary appears near product info
  - rating UI must match existing product detail design
  - do not create rating UI from scratch
  - rating must be available only for purchased products

---

## Constraints / Rules

- only authenticated users can rate
- only purchased products can be rated
- duplicate rating rules must be defined
- controller must remain thin
- service must enforce ownership/purchase validation
- user cannot rate on behalf of another user

---

## Acceptance Criteria

- user can rate a purchased product
- user cannot rate a non-purchased product
- unauthenticated user cannot rate
- rating is saved correctly
- product rating display updates or is prepared for update
- duplicate rating behavior is handled

---

## Manual Test Steps

1. log in as user with purchased product
2. open product detail
3. submit rating
4. verify rating saved
5. try rating same product again
6. log in as user without purchase
7. verify rating is blocked
8. try without login

---

## Risks / Notes

- purchase validation is critical
- rating and review may share tables or models
- duplicate behavior must be consistent

---

## Progress Log

- 2026-04-23: task created
