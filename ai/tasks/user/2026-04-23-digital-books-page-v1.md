# Task: Digital Books Page (v1)

## Epic
user-frontend

## Status
planned

## Goal
Create the user-facing digital books page where users can view and access their purchased digital books.

---

## Scope

- digital books library page
- list purchased digital products
- access/read button
- ownership validation
- empty state
- link from account page
- basic integration point for digital reader

---

## Out of Scope

- full reader implementation
- PDF.js / EPUB rendering
- advanced watermarking
- device/session limits
- AI book discovery
- direct file download

---

## Relevant Context

- ai/project.md
- ai/rules.md
- ai/architecture.md
- ai/current_state.md
- ai/backlog.md
- ai/decisions.md
- ai/epics/user-frontend.md
- ai/epics/digital-reader.md

---

## Expected Layers / Files

- routes
- digital books controller
- DTO/ViewModel if needed
- digital access service
- order/order item models
- digital access model if available
- views

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template files:
  - product-cards.html
  - account overview.html

- secondary source: BeAble Pro
- secondary path: C:\code\e_ticaret\app\dist

- usage rules:
  - use product-cards.html for digital book library cards
  - use account overview if integrated into account dashboard
  - BeAble Pro may only be used for utility cards or empty-state panels
  - reader UI can be custom if no suitable template exists
  - do not expose direct file download links
  - keep the page consistent with storefront/account design

---

## Constraints / Rules

- only authenticated users can access
- user can only see purchased digital books
- ownership validation is mandatory
- direct file path must not be exposed
- access should go through reader/token flow later
- controller must remain thin
- service must handle access rules

---

## Acceptance Criteria

- user can view purchased digital books
- user without digital purchases sees empty state
- user cannot see another user's digital books
- read/access button routes safely
- no direct file download is exposed
- page is ready for reader integration

---

## Manual Test Steps

1. log in as user with digital purchases
2. open digital books page
3. verify digital book cards
4. click read/access button
5. verify safe route behavior
6. log in as user without digital purchases
7. verify empty state
8. try accessing another user's digital access if possible

---

## Risks / Notes

- digital access is security-sensitive
- full reader belongs to digital-reader epic/task
- this task is library/access surface, not full DRM

---

## Progress Log

- 2026-04-23: task created
