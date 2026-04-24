# Task: Product Questions (v1)

## Epic
user-frontend

## Status
planned

## Goal
Allow users to ask and view product-related questions on product detail pages.

---

## Scope

- product question section
- ask question form
- list product questions
- basic question status
- authenticated submission
- product detail integration

---

## Out of Scope

- seller/admin answer management
- AI-generated answers
- advanced Q&A moderation
- notification system
- public voting on questions

---

## Relevant Context

- ai/project.md
- ai/rules.md
- ai/architecture.md
- ai/current_state.md
- ai/backlog.md
- ai/decisions.md
- ai/epics/user-frontend.md
- ai/epics/admin-completion.md

---

## Expected Layers / Files

- routes
- product question controller
- DTO
- question service
- question model
- product detail view

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template files:
  - customer_service.html
  - product_description.html
  - product_overview.html

- usage rules:
  - use customer_service.html style for question/help blocks
  - adapt product_description/product_overview if question section is placed inside product detail
  - keep UI consistent with product detail page
  - do not create a visually unrelated Q&A layout
  - if no suitable exact component exists, create a small custom section consistent with storefront

---

## Constraints / Rules

- only authenticated users can submit questions
- unauthenticated users may view public approved questions if intended
- controller must remain thin
- service must handle ownership and product validation
- question status/moderation readiness should be considered
- admin management will be a separate task

---

## Acceptance Criteria

- product detail page can show product questions
- authenticated user can submit a product question
- invalid product question is rejected
- empty state exists
- backend structure supports future moderation/answers

---

## Manual Test Steps

1. open product detail page
2. view question section
3. log in as user
4. submit a question
5. verify question is saved/displayed according to status
6. try submitting without login
7. test on multiple products

---

## Risks / Notes

- future admin answer/moderation must be considered
- avoid mixing product questions with seller questions unless intentionally designed
- question status should not be ignored

---

## Progress Log

- 2026-04-23: task created
