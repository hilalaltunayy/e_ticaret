# Shared Validation and Security v1

## Goal

Standardize input validation and basic security protections across admin, secretary, and user modules.

## Scope

This task focuses on request validation, form security, and common web vulnerabilities.

## Related Skills

- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/SAFE_MIGRATION_AND_SEEDER.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project includes forms for:

- Login / register
- Product creation and update
- Orders and checkout
- Review submission
- Page builder configuration

These areas require consistent validation and security handling.

## Requirements

1. Validate all incoming request data (POST, GET where needed).
2. Use CodeIgniter validation rules where possible.
3. Prevent empty, invalid, or malformed input.
4. Ensure CSRF protection is enabled for state-changing requests.
5. Prevent XSS by escaping output in views.
6. Sanitize or validate any HTML input (especially page builder).
7. Prevent direct trust of user input in services.
8. Validate IDs and ownership before performing actions.
9. Avoid exposing internal errors to users.
10. Ensure file uploads (if present) are validated (type, size, mime).

## Expected Areas to Check

- Controllers handling form input
- Services receiving request data
- app/Config/Filters.php (CSRF)
- Views (escaping output)
- Page builder inputs
- File upload logic (if exists)

## Manual Test Steps

1. Submit empty forms and verify validation errors.
2. Submit invalid data (wrong types, long strings).
3. Try injecting script tags in inputs (XSS test).
4. Submit forms without CSRF token (if possible).
5. Try accessing or modifying resources with invalid IDs.
6. Test file upload with invalid file types (if applicable).

## Out of Scope

- Advanced security systems (WAF, IDS, etc.)
- Full penetration testing
- Third-party security integrations

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
