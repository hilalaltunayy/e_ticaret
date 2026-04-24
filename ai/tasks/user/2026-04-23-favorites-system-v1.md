# Task: Favorites System (v1)

## Epic
user-frontend

## Status
planned

## Goal
Allow users to add, remove, and view favorite products.

This feature enables personal tracking and is a core part of user experience.

---

## Scope

- add product to favorites
- remove product from favorites
- favorites page
- user-specific data storage
- prevent duplicate entries

---

## Out of Scope

- recommendation system based on favorites
- AI-based suggestions
- sharing favorites

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
- controller
- DTO
- service
- model (favorites table)
- view

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- secondary source: BeAble Pro
- secondary path: C:\code\e_ticaret\app\dist
- usage rules:
  - storefront-facing favorites layout must primarily follow existing Flowbite-based user UI patterns
  - existing product card structure and grid behavior should be reused
  - BeAble Pro templates may be used only as a secondary reference for reusable card, table, badge, or utility-style components if needed
  - do not redesign the page from scratch
  - do not mix unrelated template styles in a visually inconsistent way
  - final UI must remain consistent with the existing user storefront
- notes:
  - reuse existing product card component
  - maintain grid layout
  - do not create UI from scratch
  - keep consistency with storefront design

---

## Constraints / Rules

- only authenticated users can perform actions
- prevent duplicate entries
- ownership validation required
- no business logic in controllers
- service layer required

---

## Acceptance Criteria

- user can add product
- user can remove product
- user can view favorites
- no duplicate records
- unauthorized users cannot act

---

## Manual Test Steps

1. login as user
2. add product
3. try adding again (blocked)
4. go to favorites page
5. verify product
6. remove product
7. verify removal

---

## Progress Log

- 2026-04-23: task created
