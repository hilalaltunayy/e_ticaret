# Task: Seller Questions (v1)

## Epic
user-frontend

## Status
planned

## Goal
Create the first version of user-facing seller/general support questions, separated from product-specific questions.

---

## Scope

- seller/general question form
- seller questions list in account area
- basic question status
- authenticated submission
- link from account page

---

## Out of Scope

- admin answer panel
- secretary answer panel
- AI customer support
- live chat
- notification system

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
- seller questions controller
- DTO
- question/support service
- question model
- account-related views

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template files:
  - customer_service.html
  - account overview.html

- usage rules:
  - use customer_service.html for question form/list UI
  - use account overview structure if questions are shown inside the user account area
  - keep product questions and seller questions visually related but logically separate
  - do not create UI from scratch unless no suitable section exists

---

## Constraints / Rules

- only authenticated users can submit seller questions
- user can only see own submitted seller questions
- ownership validation is mandatory
- controller must remain thin
- service must handle submission and listing rules
- admin/secretary response management is separate

---

## Acceptance Criteria

- user can submit seller/general question
- user can view own seller questions
- unauthenticated user cannot submit
- user cannot view another user's questions
- account page links to this area

---

## Manual Test Steps

1. log in as user
2. open seller questions page
3. submit a question
4. verify it appears in the user's list
5. log out and try submitting
6. log in as another user and verify isolation
7. verify account page navigation

---

## Risks / Notes

- must not be confused with product-specific Q&A
- future admin/secretary answer flow should remain possible
- ownership validation is critical

---

## Progress Log

- 2026-04-23: task created
