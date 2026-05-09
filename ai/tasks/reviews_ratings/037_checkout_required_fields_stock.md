# Sprint 3.7 — Checkout Required Fields and Stock Deduction

## Added files
- `C:\code\e_ticaret\ai\tasks\reviews_ratings\037_checkout_required_fields_stock.md`

## Modified files
- `C:\code\e_ticaret\app\Models\ProductsModel.php`
- `C:\code\e_ticaret\app\Services\CheckoutService.php`
- `C:\code\e_ticaret\app\Views\site\checkout\index.php`
- `C:\code\e_ticaret\app\Controllers\Checkout.php`

## Validation Changes
- Simulated checkout now validates required customer/shipping fields server-side before any order is created.
- Required fields:
  - `delivery_name` / customer full name
  - `delivery_phone` or `contact_phone`
  - `delivery_city`
  - `delivery_town`
  - `delivery_address`
- Failure behavior:
  - no order is created
  - no payment status is written
  - no stock is reserved/deducted
  - cart stays `ACTIVE`
  - customer is redirected back to `/yardim/odeme` with Turkish error text and old input preserved
- Matching HTML `required` attributes were also added to the checkout form, but server-side validation is the source of truth.

## Stored Checkout Data
- Persisted to `orders` where schema already supports it:
  - `customer_name`
  - `shipping_address_line1`
  - `shipping_address_line2` (used for address title)
  - `shipping_city`
  - `shipping_district`
  - `shipping_country`
- Not persisted because the current `orders` schema does not expose a dedicated phone field:
  - contact phone / delivery phone
- No migration was added for phone storage in this sprint.

## Stock Deduction
- Printed/physical products now use a two-step existing-stock-compatible flow during simulated paid checkout:
  1. reserve stock with existing `reserveStockForOrder()`
  2. finalize that reservation with new `finalizeReservedForPaidOrder()`
- `finalizeReservedForPaidOrder()`:
  - decreases `products.stock_count`
  - decreases `products.reserved_count`
  - logs stock movement with reason `order_paid`
- Digital products are skipped and do not affect stock.
- Duplicate deduction is avoided because checkout only works against the user’s `ACTIVE` cart; after success the cart becomes `ORDERED`, so the same cart cannot complete again through the normal path.

## Insufficient Stock Handling
- Before order creation, printed items are checked against available stock.
- If stock is insufficient:
  - order is not created
  - payment is not marked paid
  - cart remains active
  - customer gets a Turkish error message
- If stock finalization fails during the transaction, checkout is rolled back and the order is not completed.

## Admin Metrics / Stock Visibility
- Paid checkout orders should continue to appear in existing admin order flows because:
  - `orders` row is still created
  - `payment_status = paid`
  - `order_status = preparing`
- Admin stock visibility:
  - product stock changes should be visible anywhere admin uses `products.stock_count`
  - stock log/history should show the `order_paid` movement on the related product
- Legacy sales aggregates that rely on a single `orders.product_id` still have the same mixed-cart limitation from Sprint 3.6.

## Manual Validation
1. Submit checkout with empty required fields.
2. Confirm no order is created/paid.
3. Fill required fields.
4. Complete checkout.
5. Confirm order is paid.
6. Confirm cart is no longer active or duplicate completion is blocked.
7. Confirm physical product stock decreased.
8. Confirm digital product stock is not incorrectly decreased.
9. Confirm review form appears after paid checkout.
10. Confirm `product_reviews` pending insert still works.

## Blockers / Follow-ups
- Phone is validated but not persisted because current order schema has no dedicated phone column.
- Shipping completion and shipment timeline creation are still separate flows.
- Legacy admin aggregates based on `orders.product_id` remain only partially representative for multi-item customer orders.

## Final Verdict
- Checkout is now validation-safe and stock-aware for the simulated paid flow, while keeping review eligibility natural and leaving real payment/order-domain integration for later sprints.
