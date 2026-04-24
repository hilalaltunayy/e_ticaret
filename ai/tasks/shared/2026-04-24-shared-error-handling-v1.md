# Shared Error Handling v1

## Goal

Standardize how errors are handled, logged, and shown to users across admin, secretary, and user areas.

## Scope

This task focuses on application-level error handling, user-friendly messages, and safe logging.

## Related Skills

- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/AUDIT_LOG_RULES.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project includes multiple user types and critical flows such as:

- Authentication
- Product management
- Orders and checkout
- Page builder
- Review moderation

Errors can occur in all these areas and must be handled consistently.

## Requirements

1. Do not expose raw error messages or stack traces to users.
2. Show user-friendly and generic error messages.
3. Log detailed technical errors internally.
4. Separate user-facing messages from system logs.
5. Handle validation errors clearly (form feedback).
6. Handle permission errors (403) properly.
7. Handle not found errors (404) properly.
8. Handle unexpected server errors (500) safely.
9. Use centralized error handling where possible.
10. Avoid breaking UI when an error occurs.

## Suggested Approach

- Controllers handle user response (redirect or view)
- Services throw or return structured error states
- Logging system records detailed error info
- Use flash messages or response messages for users

## Expected Areas to Check

- Controllers (try/catch usage, responses)
- Services (error throwing/handling)
- app/Config/ (error/log configuration)
- Views (error message display)
- Validation error handling

## Manual Test Steps

1. Trigger validation error and check user message.
2. Try accessing a forbidden page (expect 403).
3. Try accessing a non-existing page (expect 404).
4. Force a server error (if possible) and verify safe message.
5. Check logs for detailed error info.

## Out of Scope

- Advanced monitoring tools
- External logging services
- Full exception framework redesign

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
