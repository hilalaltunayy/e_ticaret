# Shared Layout and Partials v1

## Goal

Standardize layout structure and reusable view partials across admin, secretary, and user interfaces.

## Scope

This task focuses on shared layout structure, reusable components, and consistent rendering.

## Related Skills

- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project uses multiple UI areas:

- Admin panel
- Secretary panel (limited admin scope)
- User storefront

Each area may have repeated layout elements such as header, sidebar, footer, and scripts.

## Requirements

1. Identify existing layout files for admin and user.
2. Ensure a consistent layout structure for each area.
3. Extract repeated HTML into reusable partials.
4. Use partial includes instead of duplicating code.
5. Keep layouts clean and minimal.
6. Separate layout from page content.
7. Ensure scripts and styles are organized in partials.
8. Avoid inline scripts where possible.
9. Ensure layout does not contain business logic.
10. Ensure compatibility with page builder rendering.

## Suggested Structure

app/Views/

- layouts/
  - admin.php
  - secretary.php
  - site.php

- partials/
  - header.php
  - footer.php
  - sidebar.php
  - scripts.php
  - alerts.php

## Expected Areas to Check

- app/Views/admin/
- app/Views/site/
- app/Views/layouts/
- app/Views/partials/
- page builder related views

## Manual Test Steps

1. Open admin dashboard and verify layout consistency.
2. Open user homepage and verify layout consistency.
3. Check header, footer, and sidebar rendering.
4. Confirm no duplicated layout code across pages.
5. Verify that page content is rendered inside layout.
6. Test page builder pages with layout.

## Out of Scope

- Full UI redesign
- CSS framework migration
- Creating new design system
- Rewriting all frontend code

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
