# Task: Product List Polish (v1)

## Epic
user-frontend

## Status
planned

## Goal
Polish the product listing page to improve consistency, usability, and storefront quality without rebuilding it from scratch.

---

## Scope

- product card visual consistency
- list/grid layout refinement
- filter/sort bar polish
- result count / empty state polish
- responsive behavior check
- campaign/banner section only if already planned
- small UX improvements

---

## Out of Scope

- full product list rebuild
- advanced search engine
- AI recommendations
- semantic search
- major backend refactor
- new catalog domain redesign

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

- views
- partials
- CSS/JS only if needed
- controller/service only for minor data support if required

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template files:
  - product-cards.html
  - discount_banner.html
  - navbars.html

- usage rules:
  - use product-cards.html for listing cards
  - use discount_banner.html only if a campaign/banner section is needed
  - keep filtering/sorting consistent with existing product list
  - do not create product listing UI from scratch
  - do not introduce visually inconsistent template styles
  - final UI must remain consistent with storefront and product detail page

---

## Constraints / Rules

- do not break existing product list functionality
- preserve filters/sorting if already implemented
- do not introduce unnecessary queries
- keep mobile layout intact
- no broad refactor
- admin page builder/product list builder decisions must not be broken

---

## Acceptance Criteria

- product list looks consistent with storefront
- product cards are visually aligned
- filters/sorting remain usable
- empty state is acceptable
- mobile view works
- no regression in product detail navigation

---

## Manual Test Steps

1. open product list page
2. check product card layout
3. test filters if available
4. test sorting if available
5. open product detail from product card
6. test empty result state
7. test mobile/narrow view

---

## Risks / Notes

- product list may already be connected to page management/builder logic
- polish must not become redesign
- performance must be preserved

---

## Progress Log

- 2026-04-23: task created