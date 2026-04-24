# Secretary Review Moderation v1

## Goal

Allow secretary users to moderate product reviews only if admin has granted the required permission.

## Scope

This task focuses on secretary-side review moderation.

## Related Skills

- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/AUDIT_LOG_RULES.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project supports user product reviews and ratings.

Secretary users may be allowed by admin to moderate reviews, but they should not receive unrestricted admin access.

## Requirements

1. Ensure review moderation routes require authentication.
2. Ensure secretary review access depends on assigned permission.
3. Allow secretary to view pending reviews if permitted.
4. Allow secretary to approve reviews if permitted.
5. Allow secretary to hide or reject reviews if permitted.
6. Prevent hard delete unless explicitly allowed.
7. Do not allow secretary to access unrelated admin actions.
8. Log moderation actions if audit log exists.
9. Block direct URL access without permission.
10. Keep review status transitions valid.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Controllers/Secretary/
- app/Services/ReviewModerationService.php
- app/Models/
- app/Views/secretary/
- app/Services/PermissionService.php

## Manual Test Steps

1. Login as secretary without review permission.
2. Try opening review moderation page.
3. Login as secretary with review permission.
4. View pending reviews.
5. Approve a review.
6. Hide or reject a review.
7. Try hard deleting a review.
8. Try direct URL access without permission.
9. Confirm user-facing review visibility changes correctly.
10. Confirm audit log is created for moderation action if audit exists.

## Out of Scope

- Full admin review management
- Review recommendation algorithms
- Spam detection automation
- Public review UI redesign

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
