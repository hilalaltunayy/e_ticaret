# Task: User Reviews Page (v1)

## Epic
user-frontend

## Status
planned

## Goal
Create a user-facing “My Reviews” page where users can view and manage their own product reviews.

---

## Scope

- my reviews page
- list user reviews
- review status display
- edit/delete or hide request if allowed
- link from account page
- authenticated user-only access

---

## Out of Scope

- admin review moderation
- secretary moderation
- AI review analysis
- public review listing redesign
- advanced moderation workflow

---

## Relevant Context

- ai/project.md
- ai/rules.md
- ai/architecture.md
- ai/current_state.md
- ai/backlog.md
- ai/decisions.md
- ai/epics/user-frontend.md
- ai/epics/secretary-panel.md

---

## Expected Layers / Files

- routes
- reviews controller
- DTO
- review service
- review model
- views

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template file:
  - product_reviews.html

- usage rules:
  - adapt product_reviews.html for the “My Reviews” user page
  - keep layout consistent with account area
  - show review status clearly
  - do not mix admin moderation UI into user page
  - do not create UI from scratch unless needed

---

## Constraints / Rules

- only authenticated users can access
- users can only view their own reviews
- ownership validation is mandatory
- moderation status must be respected
- controller must stay thin
- service must handle review ownership and allowed actions

---

## Acceptance Criteria

- user can view own reviews
- user cannot view another user's reviews
- review status is displayed
- empty state exists
- account page links to my reviews

---

## Manual Test Steps

1. log in as user with reviews
2. open my reviews page
3. verify reviews list
4. verify statuses
5. log in as user without reviews
6. verify empty state
7. try accessing another user's review if possible

---

## Risks / Notes

- ownership leaks are critical
- user actions must not bypass moderation
- admin/secretary moderation remains separate

---

## Progress Log

- 2026-04-23: task created
