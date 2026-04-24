# Task: Homepage User Polish (v1)

## Epic
user-frontend

## Status
planned

## Goal
Polish the user-facing homepage so it feels consistent, modern, and aligned with the storefront design.

---

## Scope

- homepage visual polish
- hero section refinement
- featured product/card sections
- banner/discount sections if needed
- navigation/footer consistency check
- spacing and responsive behavior improvements
- empty/fallback content checks

---

## Out of Scope

- full homepage rebuild
- admin page builder redesign
- AI-powered homepage personalization
- major database changes
- new product recommendation engine

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
- controller/service only if existing data flow requires small adjustment

---

## UI / Template

- primary source: Flowbite
- primary path: C:\code\e_ticaret\frontend_template\flowbite
- template files:
  - hero_section.html
  - discount_banner.html
  - product-cards.html
  - navbars.html
  - mega_footers.html
  - image_description.html

- usage rules:
  - use these files to polish homepage sections
  - preserve existing storefront identity
  - do not redesign the whole homepage unless explicitly requested
  - reuse existing product card structure
  - keep mobile behavior intact
  - avoid admin-style BeAble Pro visual language unless only used as tiny utility reference

---

## Constraints / Rules

- do not break page builder/published home behavior if connected
- do not change unrelated pages
- do not rewrite the entire homepage
- keep changes small and traceable
- preserve existing approved storefront decisions
- no business logic in views

---

## Acceptance Criteria

- homepage looks more consistent
- hero/banner/product sections align visually
- mobile layout is not broken
- navigation/footer remain consistent
- no admin panel regression
- no unrelated redesign

---

## Manual Test Steps

1. open homepage desktop
2. check hero section
3. check product sections
4. check banner/discount sections if present
5. test mobile/narrow width
6. verify navigation and footer
7. compare with product list/detail consistency

---

## Risks / Notes

- homepage has page builder implications
- avoid repeating the previous issue of accidentally removing the wrong UI block
- visual polish must stay controlled

---

## Progress Log

- 2026-04-23: task created