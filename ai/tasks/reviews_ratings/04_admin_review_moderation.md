# Sprint 4 — Admin Review Moderation

## Added files
- `C:\code\e_ticaret\app\Services\Admin\AdminReviewModerationService.php`
- `C:\code\e_ticaret\ai\tasks\reviews_ratings\04_admin_review_moderation.md`

## Modified files
- `C:\code\e_ticaret\app\Controllers\Admin\Reviews.php`
- `C:\code\e_ticaret\app\Config\Routes.php`
- `C:\code\e_ticaret\app\Views\admin\reviews\index.php`

## Routes added/changed
- `GET /admin/reviews` (existing, now uses real data)
- `POST /admin/reviews/(:segment)/approve`
- `POST /admin/reviews/(:segment)/hide`
- `POST /admin/reviews/(:segment)/reject`
- `POST /admin/reviews/(:segment)/delete`

## Moderation Flow
- Admin reviews page now reads real `product_reviews` rows with joined product and user info.
- Listing is ordered latest first.
- Supported status transitions:
  - `pending -> approved`
  - `pending -> hidden`
  - `pending -> rejected`
  - `approved -> hidden`
  - `hidden -> approved`
  - `rejected -> approved`
- Delete action uses model soft delete, not hard delete.

## Permission Behavior
- Route access remains under existing `manage_reviews` admin route group.
- Approve / hide / reject require the same `manage_reviews` access already enforced by routes.
- Delete action adds an extra controller-level permission check for `delete_reviews`.
- Result:
  - admin can moderate and delete
  - secretary without `manage_reviews` cannot access
  - secretary with `manage_reviews` can moderate
  - delete still requires `delete_reviews`

## Admin View
- Admin reviews page now shows:
  - short review id
  - product name
  - customer name/email
  - rating
  - title/comment
  - Turkish status badge
  - created date
  - action buttons
- Added a small filter form for:
  - status
  - text search
- Added empty state message and flash success/error feedback.

## Public Visibility
- Storefront product detail already reads only `approved` reviews.
- Because moderation now changes only `product_reviews.status`:
  - `approved` reviews appear publicly
  - `hidden` reviews disappear
  - `rejected` reviews do not appear
  - soft-deleted reviews do not appear

## Validation Notes
- Syntax checks:
  - `php -l app/Services/Admin/AdminReviewModerationService.php`
  - `php -l app/Controllers/Admin/Reviews.php`
  - `php -l app/Views/admin/reviews/index.php`
  - `php -l app/Config/Routes.php`
- Manual checks:
  1. Open `/admin/reviews`
  2. Confirm pending review appears
  3. Approve and verify status becomes `approved`
  4. Reopen related product detail and verify review appears
  5. Hide and verify it disappears publicly
  6. Reject a pending review and verify it stays hidden publicly
  7. If delete is used, verify `deleted_at` is populated instead of hard delete
  8. Confirm unauthorized customer cannot access `/admin/reviews`
  9. Confirm secretary without `manage_reviews` cannot access

## Blockers / Follow-ups
- No moderation audit log table yet.
- No bulk actions.
- No review reply flow.
- No pagination/search API beyond the lightweight current page setup.

## Final Verdict
- Admin review moderation is ready for safe MVP usage: real reviews are listed, core status transitions work, public visibility updates naturally, and delete uses soft delete.
