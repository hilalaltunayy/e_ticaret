# Admin Page Builder Management v1

## Goal

Create or standardize admin page builder management for editable user-facing pages.

## Scope

This task focuses on admin-side page builder management only.

## Related Skills

- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/SAFE_MIGRATION_AND_SEEDER.md
- ai/skills/AUDIT_LOG_RULES.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project includes admin-managed user-facing pages such as:

- Home page
- Product list page
- Product detail page
- Cart page
- Checkout page

The builder should allow admin to manage page sections safely.

## Requirements

1. Ensure only admin can access page builder management.
2. Protect builder routes with permission checks.
3. Use controller-service separation.
4. Support draft and published page versions.
5. Allow admin to edit section content.
6. Allow admin to enable or disable sections.
7. Allow admin to manage section ordering where supported.
8. Validate builder configuration before saving.
9. Prevent unsafe HTML or script injection.
10. Log publish actions if audit log exists.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Controllers/Admin/
- app/Services/PageBuilderService.php
- app/Views/admin/pages/
- app/Database/Migrations/
- app/Database/Seeds/
- page builder related models

## Manual Test Steps

1. Login as admin and open page builder management.
2. Open a user-facing page builder screen.
3. Edit a section title or text.
4. Enable or disable a section.
5. Save as draft.
6. Publish the page.
7. Verify the published version appears on the user-facing page.
8. Try entering unsafe script content and confirm it is blocked or escaped.
9. Confirm secretary/user cannot access builder routes.
10. Confirm audit log is created for publish action if audit exists.

## Out of Scope

- Full visual drag-and-drop builder
- Theme system redesign
- Advanced component marketplace
- AI-generated page design
- Multi-language page management

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
