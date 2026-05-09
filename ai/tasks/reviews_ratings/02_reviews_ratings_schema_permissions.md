# Sprint 2 — Reviews/Ratings Schema + Permissions

## Added files
- `C:\code\e_ticaret\app\Database\Migrations\2026-05-08-120000_CreateProductReviewsTable.php`
- `C:\code\e_ticaret\app\Database\Migrations\2026-05-08-121000_EnsureReviewRatingPermissions.php`
- `C:\code\e_ticaret\app\Models\ProductReviewModel.php`
- `C:\code\e_ticaret\app\Services\ReviewEligibilityService.php`

## Modified files
- None

## Database changes
- New additive table: `product_reviews`
- Fields:
  - `id` `CHAR(36)` primary key
  - `product_id` `CHAR(36)` not null
  - `user_id` `CHAR(36)` not null
  - `order_id` `CHAR(36)` nullable
  - `order_item_id` `CHAR(36)` nullable
  - `rating` `TINYINT` not null
  - `title` `VARCHAR(255)` nullable
  - `comment` `TEXT` nullable
  - `status` `VARCHAR(30)` default `pending`
  - `created_at`, `updated_at`, `deleted_at`
- Indexes:
  - `product_id`
  - `user_id`
  - `status`
  - `(product_id, status)`
  - `(user_id, product_id)`
- Safe optional FKs attempted:
  - `product_id -> products.id`
  - `user_id -> users.id`
  - `order_id -> orders.id`
  - `order_item_id -> order_items.id`

## Permission changes
- Added idempotent permissions:
  - `create_review`
  - `rate_product`
  - `delete_reviews`
- Preserved existing permission:
  - `manage_reviews`
- Role assignments:
  - `user` role: `create_review`, `rate_product`
  - `admin` role: `delete_reviews`
- `secretary` role was not granted `delete_reviews` by default.

## Purchase Eligibility
- Service location:
  - `C:\code\e_ticaret\app\Services\ReviewEligibilityService.php`
- Method:
  - `canUserReviewProduct(string $userId, string $productId): bool`
- Data source:
  - primary source is `orders.user_id + order_items.product_id`
- Conservative accepted ownership signals:
  - `orders.order_status IN ('shipped', 'delivered', 'return_in_progress', 'return_done')`
  - or `orders.payment_status = 'paid'`
  - or legacy `orders.status IN ('paid', 'shipped', 'completed', 'returned')`
- IDs are handled as UUID/string values.

## Order Ownership Fix
- `OrderModel::createOrderReserved()` was **not** changed.
- Reason:
  - current callers pass `actorUserId`, not a reliable customer `user_id`
  - writing `user_id = actorUserId` would risk assigning customer ownership to admin/secretary-created orders
- Safe follow-up needed later:
  - introduce a distinct customer user id parameter in the order creation path before persisting ownership.

## Seed Data
- Review seed data was **not** added in this sprint.
- Reason:
  - foundation sprint scope is complete without demo review inserts
  - deterministic seeding should later target stable known test keys such as existing test users/products/orders and remain non-destructive

## Validation Notes
- Run:
  - `php spark migrate`
  - `php -l app/Database/Migrations/2026-05-08-120000_CreateProductReviewsTable.php`
  - `php -l app/Database/Migrations/2026-05-08-121000_EnsureReviewRatingPermissions.php`
  - `php -l app/Models/ProductReviewModel.php`
  - `php -l app/Services/ReviewEligibilityService.php`
- Manual checks:
  - confirm `product_reviews` table exists after migration
  - confirm `permissions` contains `create_review`, `rate_product`, `delete_reviews`
  - confirm `manage_reviews` still exists
  - confirm routes/storefront/admin pages continue to load unchanged

## Final Verdict
- Sprint 2 foundation is complete for schema, model, permission, and purchase-eligibility groundwork.
- Storefront submission UI, review rendering, and admin moderation CRUD remain intentionally out of scope for the next sprint.
