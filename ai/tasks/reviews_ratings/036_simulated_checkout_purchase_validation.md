# Sprint 3.6 — Simulated Checkout Completion and Purchase Eligibility Validation

## Added files
- `C:\code\e_ticaret\ai\tasks\reviews_ratings\036_simulated_checkout_purchase_validation.md`

## Modified files
- `C:\code\e_ticaret\app\Config\Routes.php`
- `C:\code\e_ticaret\app\Controllers\Checkout.php`
- `C:\code\e_ticaret\app\Services\CheckoutService.php`
- `C:\code\e_ticaret\app\Views\site\checkout\index.php`

## Routes added/changed
- Added: `POST /yardim/odeme/tamamla` -> `Checkout::complete`
- Existing route preserved: `GET /yardim/odeme` -> `Checkout::index`

## Checkout Flow
- Customer enters checkout from `GET /yardim/odeme`.
- `Checkout::index()` still builds readonly checkout data through `CheckoutService::buildCheckoutViewModel()`.
- New submit path: `Checkout::complete()`
  - reads the logged-in customer id from the same session pattern used by checkout/orders/reviews
  - calls `CheckoutService::completeSimulatedCheckout()`
- `CheckoutService::completeSimulatedCheckout()`:
  - loads the customer’s active cart
  - validates non-empty cart items
  - validates each product still exists
  - reserves stock only for printed items through existing `ProductsModel::reserveStockForOrder()`
  - creates one customer order row
  - creates matching `order_items` rows for every cart item
  - marks the cart status as `ORDERED`
  - redirects customer to `/yardim/siparislerim/{orderNo}`

## User Ownership Fix
- `orders.user_id` is now written from the real logged-in customer session id during checkout completion.
- Ownership is no longer inferred from `updated_by` or any admin/actor-style id in this customer checkout path.
- `updated_by` still stores the same customer id for audit consistency in this flow.

## Simulated Payment Completion
- Simulated checkout completion now sets:
  - `payment_status = paid`
  - `order_status = preparing`
  - `status = reserved`
  - `fulfillment_status = PREPARING`
  - `shipping_status = not_shipped`
  - `paid_at = now`
- This keeps the customer order in an active/preparing state for storefront UX while still making review eligibility true through `payment_status = paid`.
- No real payment provider, card processing, order confirmation webhook, or payment record creation was added.

## Stock / Admin Metrics
- Stock behavior:
  - printed products: reservation now happens during simulated checkout completion using the existing reservation method
  - digital products: no stock reservation is attempted
  - shipped/sold deduction behavior remains unchanged and still depends on the existing shipping flow
- Admin metrics:
  - existing order reporting should still see the order because the main `orders` row is created with normal totals and non-cancelled status
  - legacy product/category aggregate reports still depend on `orders.product_id` + `orders.quantity`, so mixed-item customer checkout orders remain only partially representable in those older aggregates

## Review Eligibility Result
- `ReviewEligibilityService::canUserReviewProduct($customerUserId, $productId)` should now return `true` after simulated completion because:
  - `orders.user_id` now stores the real customer id
  - `order_items.product_id` is created for every cart item using the product UUID
  - `orders.payment_status` is now `paid`
- No bypass or review-specific exception was added.

## Validation Notes
- Syntax checks performed:
  - `php -l app/Services/CheckoutService.php`
  - `php -l app/Controllers/Checkout.php`
  - `php -l app/Views/site/checkout/index.php`
  - `php -l app/Config/Routes.php`
- Manual checks to run:
  1. Create/login as customer.
  2. Add product to cart.
  3. Open `/yardim/odeme`.
  4. Submit simulated checkout completion.
  5. Confirm redirect to `Siparişlerim` / order detail.
  6. Confirm `orders.user_id` equals the logged-in customer id.
  7. Confirm `orders.payment_status = paid`.
  8. Confirm `order_items.product_id` matches the product detail UUID.
  9. Open the matching product detail page.
  10. Confirm the review form appears.
  11. Submit a review.
  12. Confirm `product_reviews.status = pending`.
  13. Confirm pending review does not render publicly.
  14. Check admin dashboard/order reports for expected unchanged behavior.
  15. Check stock reservation behavior for printed products.

## Blockers / Follow-ups
- Mixed-item customer orders are now correct for review eligibility and customer ownership, but older admin sales aggregates that rely on a single `orders.product_id` cannot represent multi-item orders perfectly.
- No payment row is created in `payments`; this sprint intentionally treats `orders.payment_status` as the simulated payment source of truth.
- No shipping/timeline auto-creation was added for checkout-created orders.

## Final Verdict
- Paid customer checkout orders are now review-eligible through the real order data path, without changing review eligibility rules.
