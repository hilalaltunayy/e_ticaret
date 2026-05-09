# Sprint 1 - Digital Reader Discovery

## Final Verdict
- Digital product type existing: PARTIAL
- Digital file storage existing: NO
- Reader route existing: NO
- Purchase ownership validation possible: YES
- Ready for implementation sprint: NO, with blockers

## Findings
- `app/Database/Migrations/2026-02-07-104831_CreateInitialSchemaUuid.php`
  - `products` has `type_id`, `type`, `image`, `stock_count`; no digital file path or asset field exists.
  - Product type exists, but there is nowhere to bind an actual reader file.

- `app/Models/ProductsModel.php`
  - `allowedFields` includes `type`, `type_id`, `image`; no `pdf_path`, `epub_path`, `file_path`, `asset_id`.
  - Model-level digital file management does not exist yet.

- `app/Controllers/Admin/Products.php`
  - Admin create/update flow handles basic product fields and cover image only.
  - There is no digital file upload or reader access field.

- `app/Views/admin/products/product_create.php`
  - Type options are `basili`, `dijital`, `paket`.
  - When `dijital` is selected, stock UI is hidden. Digital type is recognized in admin.

- `app/Services/ProductsService.php`
  - Only cover image upload exists, saved under `uploads/products/`.
  - Existing media flow is for public images, not protected reader files.

- `app/Helpers/product_media_helper.php`
  - `product_upload_directory()` points to `FCPATH/uploads/products`.
  - Current upload pattern uses a public webroot directory.

- `app/Views/site/products/product_detail.php`
  - Storefront detail page distinguishes `basili` vs `dijital` and shows digital-specific copy like `Dijital teslimat`.
  - There is no `Oku`, `Indir`, iframe reader, PDF, or EPUB rendering area.

- `app/Services/ProductDetailStorefrontBindingService.php`
  - Builder supports text fields such as `dijital_erisim_kisa_notu`.
  - This is presentation copy only, not real digital access.

- `app/Config/Routes.php`
  - Routes exist for product detail, cart, checkout, and orders.
  - No storefront reader or download route exists.

- `app/Controllers/CustomerOrders.php` and `app/Views/site/orders/show.php`
  - Order detail links only to product detail via `Urune Git`.
  - There is no `Oku` or `Indir` action.

- `app/Services/CheckoutService.php`
  - Checkout can detect digital products and skips stock reservation for them.
  - The system can identify a purchased item as digital during checkout.

- `app/Models/OrderModel.php` and `app/Models/OrderItemModel.php`
  - `orders.user_id` exists, and `order_items.product_id` plus `order_items.product_type` are stored.
  - User-to-product purchase ownership can be verified at item level.

- `app/Database/Migrations/2026-05-07-120000_AddCustomerOrdersFields.php`
  - `order_items.product_type` was added.
  - This is useful later for reader access checks.

- `app/Database/Seeds/CustomerOrdersTestSeeder.php`
  - Seeded digital product exists: `Siparis Test E-Kitap`.
  - A digital-order scenario already exists for testing.

- `app/Database/Seeds/ProductsFullSeeder.php`
  - Some seed data uses `type = digital/physical`, while storefront code often expects `dijital/basili`.
  - Product type vocabulary is inconsistent and matters for any reader MVP.

## Product Type Flow
- Table/column:
  - `products.type_id`
  - `products.type`
  - `order_items.product_type`
- Model:
  - `app/Models/ProductsModel.php`
  - `app/Models/OrderItemModel.php`
- Admin create/edit:
  - `app/Controllers/Admin/Products.php`
  - `app/Views/admin/products/product_create.php`
  - Admin can mark a product as digital, but cannot attach a protected digital file.
- Storefront display:
  - `app/Controllers/ProductController.php`
  - `app/Views/site/products/product_detail.php`
  - Product detail shows digital label and digital delivery wording.
- Seed data:
  - `app/Database/Seeds/CustomerOrdersTestSeeder.php` -> `dijital`
  - `app/Database/Seeds/ProductsFullSeeder.php` -> `digital`

## Digital File / Asset Flow
- Storage location:
  - Only cover images are stored, under `FCPATH/uploads/products`
- DB fields:
  - No digital book file field was found
- Upload flow:
  - `app/Services/ProductsService.php::storeProductImage()`
  - Image upload only
- Public exposure risk:
  - Existing upload directory is public
  - Reusing it for PDF/EPUB files would expose direct URLs

## Purchase Access Flow
- Order tables/models:
  - `orders`, `order_items`
  - `app/Models/OrderModel.php`
  - `app/Models/OrderItemModel.php`
- User relation:
  - `orders.user_id`
- Product relation:
  - `order_items.product_id`
  - `order_items.product_type`
- Can verify purchase? Explain briefly.
  - Yes. After paid checkout, `orders.user_id + order_items.product_id` is enough to verify ownership for a specific digital product.

## Existing Reader / Download Flow
- Routes:
  - None
- Controllers:
  - None
- Services:
  - None
- Views:
  - None
- Current status:
  - Digital products can be sold and identified, but there is no reading or download access layer yet.

## Recommended Next Sprint Scope
- Add a private digital file storage strategy outside webroot
- Add the smallest additive schema to store a protected digital file reference
- Add an auth-protected storefront reader route
- Check access via `orders.user_id + order_items.product_id + paid/completed status`
- Stream file content through controlled access instead of a public URL
- Keep first MVP to controlled reading only, without download button

## Risks / Blockers
- No digital file field on `products`
- No digital asset / access / token table
- No reader route/controller/view
- Current upload pattern uses a public directory
- Product type vocabulary is inconsistent:
  - `basili/dijital`
  - `physical/digital`
- If type normalization is not handled, reader access and storefront labeling may diverge

Digital Reader Discovery Complete: YES
