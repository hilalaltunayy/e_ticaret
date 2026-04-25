# Domain KB - Domain Index

## Purpose

Provide the canonical domain list for the current repository and summarize each domain's purpose, files, routes, tables, permissions, status, risks, and update triggers.

## Scope

This file covers the Domain KB domains currently tracked for the CodeIgniter 4 e-commerce application. It does not fix application behavior and does not replace detailed route, security, schema, or drift baseline files.

## Source of Truth

- `ai/domain-kb/00_repo_inventory.md`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/04_kb_quality_audit.md`
- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `app/Controllers/**`
- `app/Models/**`
- `app/Services/**`
- `app/Database/Migrations/**`
- `app/Database/Seeds/**`
- `app/Views/**`

## Key Claims

- Claim: Auth, RBAC, Admin Panel, Secretary Access, Product / Catalog, Category, Order, Page Builder, Dashboard Builder, Theme / Media, and Frontend Storefront are active or partially active domains in the repository.
  - Source: `app/Config/Routes.php`, `app/Controllers/**`, `app/Models/**`, `app/Services/**`, `app/Views/**`
  - Confidence: High
  - Domain: All tracked domains
  - Related files: `ai/domain-kb/00_repo_inventory.md`, `ai/domain-kb/kb-manifest.yaml`

- Claim: Cart, checkout, Favorites / Wishlist, Review, account, and payment runtime flows are missing or unclear at route level.
  - Source: `app/Config/Routes.php`, `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/06_route_baseline.md`
  - Confidence: High
  - Domain: Cart / Favorites / Wishlist / Review / Frontend Storefront / Order
  - Related files: `ai/domain-kb/03_security_filter_audit.md`, `ai/domain-kb/04_kb_quality_audit.md`

- Claim: Campaign/Coupon is security-relevant and should be treated as a first-class or explicitly owned subdomain before automation.
  - Source: `app/Config/Routes.php`, `app/Config/Filters.php`, `ai/domain-kb/04_kb_quality_audit.md`
  - Confidence: Medium
  - Domain: Campaign / Coupon / Cart / Admin Panel
  - Related files: `ai/domain-kb/kb-manifest.yaml`

- Claim: The domain index should be updated whenever route filters, domain controllers, domain models, or domain services change.
  - Source: `ai/domain-kb/05_kb_manifest_and_schema_plan.md`, `ai/domain-kb/kb-manifest.yaml`
  - Confidence: High
  - Domain: All tracked domains
  - Related files: `ai/domain-kb/06_route_baseline.md`

## Related Files

- `ai/domain-kb/00_repo_inventory.md`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/04_kb_quality_audit.md`
- `ai/domain-kb/05_kb_manifest_and_schema_plan.md`
- `ai/domain-kb/06_route_baseline.md`
- `ai/domain-kb/kb-manifest.yaml`

This file is the domain-oriented working guide. Route, table, permission, and file lists are based on static repository review.

## Domain: Auth

- Purpose: Manage user login, logout, registration, and session initialization.
- Related files: `app/Controllers/Login.php`, `app/Controllers/Register.php`, `app/Controllers/Logout.php`, `app/Services/AuthService.php`, `app/Models/UserModel.php`, `app/DTO/UserDTO.php`, `app/Views/auth/login.php`, `app/Views/auth/register.php`, `app/Filters/AuthFilter.php`, `app/Config/Routes.php`, `app/Config/Session.php`.
- Related routes: `GET /login`, `POST /login/auth`, `GET /register`, `POST /register/save`, `GET /logout`, auth-filtered `dashboard_anasayfa`, `products`, and `orders`.
- Related tables: `users`; also `user_permissions`, `roles`, `permissions`, and `role_permissions` for permission loading after login.
- Related permissions: No direct permission is required for login; role/permission-based redirect is used after login.
- Current status: Login writes `isLoggedIn`, `user`, `user_id`, `role`, and `permissions` to the session. Admin/secretary users go to `admin/dashboard`; normal users go to `dashboard_anasayfa`.
- Missing: Password reset, email verification, and account activation flows are not visible in the static inventory.
- Risks: `Filters.php` duplicates the `auth` alias; CSRF appears globally disabled.
- KB update triggers: Auth controller/service/model, session config, filter alias, login/register views, or auth route changes.

## Domain: User / Role / Permission

- Purpose: Manage user roles, role permissions, and user-level permission overrides.
- Related files: `app/Models/UserModel.php`, `app/Models/RoleModel.php`, `app/Models/RoleModels.php`, `app/Models/PermissionModel.php`, `app/Models/RolePermissionModel.php`, `app/Models/UserPermissionModel.php`, `app/Filters/RoleFilter.php`, `app/Filters/PermissionFilter.php`, `app/Filters/CampaignAccessFilter.php`, `app/Controllers/Admin/SettingsPermissionsController.php`, `app/Services/Admin/SettingsPermissionsService.php`, `app/Views/admin/settings/permissions.php`, `app/Database/Seeds/InitialAuthSeeder.php`.
- Related routes: `admin/settings/permissions`, `admin/settings/permissions/update`, `admin/settings/permissions/secretaries/create`, and all admin routes using `role:*` or `perm:*` filters.
- Related tables: `users`, `roles`, `permissions`, `role_permissions`, `user_permissions`.
- Related permissions: `manage_products`, `manage_orders`, `manage_shipping`, `manage_campaigns`, `manage_campaigns_engine`, plus operation permissions added by `2026-04-17-120000_EnsureSecretaryOperationPermissions.php`.
- Current status: Route-level RBAC exists. Admin users bypass permission checks; secretary users are checked through effective permissions.
- Missing: General `Users` and `Roles` admin controllers are not active; only commented route traces exist.
- Risks: Possible class/model conflict between `RoleModel.php` and `RoleModels.php`. `CampaignAccessFilter` accepts admin only.
- KB update triggers: RBAC migrations/seeders, filters, permissions settings UI/service, or route filter argument changes.

## Domain: Admin Panel

- Purpose: Group the management interface, shared layout, admin dashboard, and domain management screens.
- Related files: `app/Controllers/Admin/*`, `app/Views/admin/layouts/main.php`, `app/Views/admin/partials/*`, `app/Views/partials/sidebar.php`, `app/Services/Admin/DashboardService.php`, admin DTOs.
- Related routes: `admin/dashboard`, `admin/products`, `admin/orders`, `admin/stock`, `admin/shipping`, `admin/notifications`, `admin/settings`, `admin/marketing`, `admin/campaigns`, `admin/coupons`, `admin/pricing`, `admin/banners`, `admin/automation`, `admin/customers`.
- Related tables: Spread across domains; admin dashboard uses `admin_notes`, `admin_settings`, `audit_logs`, `visits`, `orders`, and `users`.
- Related permissions: Admin-only areas use `role:admin`; shared areas use `manage_dashboard`, `manage_stock`, `manage_notifications`, `manage_customers`, `manage_products`, `manage_shipping`, and `manage_orders`.
- Current status: Admin layout and many management screens exist.
- Missing: Customer and automation backend depth is unclear.
- Risks: Legacy/general layouts coexist with the newer admin layout. Some admin controllers appear route-less.
- KB update triggers: `app/Controllers/Admin/**`, `app/Views/admin/**`, admin sidebar/layout, or admin route changes.

## Domain: Secretary Access

- Purpose: Provide controlled secretary access to admin screens.
- Related files: `Login.php`, `RoleFilter.php`, `UserPermissionModel.php`, `SettingsPermissionsController.php`, `SettingsPermissionsService.php`, `admin/settings/permissions.php`, `2026-04-17-120000_EnsureSecretaryOperationPermissions.php`.
- Related routes: `admin/dashboard`, `admin/stock`, `admin/notifications`, `admin/customers`, `admin/products`, `admin/shipping`, `admin/shipping/automation`, `admin/orders`, and `admin/settings/permissions` management routes.
- Related tables: `users`, `permissions`, `user_permissions`, `roles`, `role_permissions`.
- Related permissions: `manage_dashboard`, `manage_stock`, `manage_notifications`, `manage_customers`, `manage_products`, `manage_shipping`, `manage_orders`.
- Current status: Secretary users are redirected to `admin/dashboard` after login. There is no separate UI namespace; admin screens are shared through permissions.
- Missing: Campaign/coupon access does not appear open to secretary users.
- Risks: `CampaignAccessFilter` requires role `admin`. A permission route matrix is needed for policy confirmation.
- KB update triggers: Secretary permission migration, settings permissions UI/service, route filters, or `RoleFilter` changes.

## Domain: Product / Catalog

- Purpose: Manage products, stock, author, type, price rules, and public product list/detail flows.
- Related files: `ProductController.php`, `Admin/Products.php`, `Admin/Stock.php`, `Admin/StockMove.php`, `Admin/Pricing.php`, `ProductsService.php`, `PriceService.php`, `ProductsModel.php`, `AuthorModel.php`, `TypeModel.php`, `PriceRuleModel.php`, `ProductDTO.php`, `app/Views/site/products/*`, `app/Views/admin/products/*`, `app/Views/admin/pricing/*`, `app/Views/admin/stock/*`.
- Related routes: `products/detail/{segment}`, `products/list/{any}`, `products/list/{any}/{any}`, `products/selection`, `admin/products`, `admin/api/products`, `admin/products/create`, `admin/products/store`, `admin/products/edit/{id}`, `admin/products/update/{id}`, `admin/stock`, `admin/pricing`.
- Related tables: `products`, `authors`, `types`, `categories`, `price_rules`, `product_stock_logs`.
- Related permissions: `manage_products`, `manage_stock`; pricing routes are admin-only.
- Current status: Public listing/detail and admin product CRUD exist. Stock and pricing subflows exist.
- Missing: A standalone product selection view exists, but `products/selection` delegates to the product index.
- Risks: Stock move routing and `PriceService` usage points should be tracked.
- KB update triggers: Product controller/service/model, product views, stock/pricing routes, or product migrations.
- Controlled update note: REAL-TEST-001 marks `app/Models/ProductsModel.php` as high impact for Product / Catalog and `app/Views/site/products/list.php` as medium impact for Product / Catalog through the optimized manifest. Needs Review: no application diff was provided, so schema, validation, or behavior changes are not confirmed.

## Domain: Category

- Purpose: Provide product categories for catalog and marketing targets.
- Related files: `CategoryModel.php`, `Admin/Products.php`, `ProductsService.php`, `CampaignService.php`, `CouponService.php`, `app/Views/admin/categories/create.php`, `CategorySeeder.php`.
- Related routes: `admin/categories/create`, `admin/categories/store`.
- Related tables: `categories`.
- Related permissions: Category creation is under the `manage_products` admin/secretary route group.
- Current status: Category creation and select/meta usage exist inside product admin.
- Missing: Independent category index/edit/delete flow is not visible.
- Risks: Category is embedded under Product / Catalog; ownership boundary is weak.
- KB update triggers: Category model, product admin category methods, category seeder, or campaign/coupon target usage changes.
- Controlled update note: REAL-TEST-001 marks `app/Models/ProductsModel.php` as medium impact for Category because product-category relations may be affected by product model changes. Needs Review: no category behavior change is confirmed without an application diff.

## Domain: Order

- Purpose: Handle order creation, management, status, shipping, invoice, packing, and reporting flows.
- Related files: `OrderController.php`, `Admin/Orders.php`, `Admin/OrderStatuses.php`, `Admin/Traits/OrderPackingActions.php`, `OrdersService.php`, `OrderCreationService.php`, `OrderShippingService.php`, `OrderNoteService.php`, `OrdersReportingService.php`, `PackingService.php`, `InvoiceService.php`, `OrderModel.php`, `OrderItemModel.php`, `OrderLogModel.php`, `PackingSessionModel.php`, `InvoiceModel.php`, `app/Views/admin/orders/*`.
- Related routes: `orders`, `orders/create`, `admin/orders`, `admin/api/orders`, `admin/api/orders/analytics`, `admin/orders/{id}`, `admin/orders/update-status`, `admin/orders/*/packing/*`, `admin/orders/invoice/*`, cancel/return/ship/note/shipping routes.
- Related tables: `orders`, `order_items`, `order_logs`, `packing_sessions`, `invoices`, `products`.
- Related permissions: `manage_orders`.
- Current status: Admin/secretary order management is extensive. User-side order creation with reserved stock exists.
- Missing: User account order history or checkout-originated order flow is not visible.
- Risks: `Admin/OrderController.php` appears route-less; `Admin/Orders.php` is the main controller.
- KB update triggers: Order controllers/services/models/views, order/shipping/invoice migrations, or `manage_orders` routes.

## Domain: Cart

- Purpose: Represent cart and checkout experience; currently mostly visible as page builder preview/config.
- Related files: `CartPageBuilderService.php`, `CartPreviewRenderer.php`, `CheckoutPageBuilderService.php`, `CheckoutPreviewRenderer.php`, `app/Views/admin/pages/cart_builder.php`, `app/Views/admin/pages/checkout_builder.php`, `app/Views/admin/pages/partials/cart_preview.php`, `app/Views/admin/pages/partials/checkout_preview.php`, `CouponService.php`, `CouponModel.php`, `CouponTargetModel.php`, `CouponRedemptionModel.php`.
- Related routes: `admin/pages/cart-builder/update`, `admin/pages/checkout-builder/update`, page builder draft/block routes. Public cart/checkout routes are not visible in the static inventory.
- Related tables: `coupons`, `coupon_targets`, `coupon_redemptions`, plus `pages`, `page_versions`, `block_instances` for page builder.
- Related permissions: Page builder routes are admin-only through `role:admin`.
- Current status: Cart/checkout builder UI and preview renderers exist.
- Missing: Runtime cart model/controller/session, checkout submission, and payment flow are not visible.
- Risks: UI exists but backend commerce flow may be absent or located elsewhere. Assumption: Cart domain is currently represented at builder level.
- KB update triggers: Cart/checkout controller, model, route, payment, coupon redemption, or builder service changes.

## Domain: Campaign / Coupon

- Purpose: Manage campaign and coupon administration, campaign/coupon targets, coupon redemption data, and campaign-specific access control.
- Source of Truth: `app/Config/Routes.php`, `app/Config/Filters.php`, `app/Filters/CampaignAccessFilter.php`, `app/Controllers/Admin/Campaigns.php`, `app/Controllers/Admin/Coupons.php`, `app/Services/CampaignService.php`, `app/Services/CouponService.php`, `app/Models/CampaignModel.php`, `app/Models/CampaignTargetModel.php`, `app/Models/CouponModel.php`, `app/Models/CouponTargetModel.php`, `app/Models/CouponRedemptionModel.php`, `app/Database/Migrations/2026-03-06-120000_CreateCouponsModuleTables.php`, `app/Database/Migrations/2026-03-06-140000_CreateCampaignsModuleTables.php`, `app/Database/Migrations/2026-03-06-130000_EnsureManageCampaignsPermission.php`, `app/Database/Migrations/2026-03-06-141000_EnsureManageCampaignsEnginePermission.php`, `app/Database/Seeds/MarketingCouponsSeeder.php`.
- Related files: `app/Controllers/Admin/Campaigns.php`, `app/Controllers/Admin/Coupons.php`, `app/Services/CampaignService.php`, `app/Services/CouponService.php`, `app/Models/CampaignModel.php`, `app/Models/CampaignTargetModel.php`, `app/Models/CouponModel.php`, `app/Models/CouponTargetModel.php`, `app/Models/CouponRedemptionModel.php`, `app/Filters/CampaignAccessFilter.php`, `app/Views/admin/campaigns/**`, `app/Views/admin/coupons/**`.
- Related routes: `admin/campaigns`, `admin/campaigns/create`, `admin/campaigns`, `admin/campaigns/edit/{id}`, `admin/campaigns/update/{id}`, `admin/campaigns/toggle/{id}`, `admin/campaigns/delete/{id}`, `admin/coupons`, `admin/coupons/create`, `admin/coupons`, `admin/coupons/edit/{id}`, `admin/coupons/update/{id}`, `admin/coupons/toggle/{id}`, `admin/coupons/delete/{id}`.
- Related tables: `campaigns`, `campaign_targets`, `coupons`, `coupon_targets`, `coupon_redemptions`, plus `permissions` and `role_permissions` for campaign access permissions.
- Related permissions: `manage_campaigns`, `manage_campaigns_engine`; route protection uses `campaign_access`.
- Current status: Campaign and coupon admin route groups exist and are protected by `campaign_access`. Campaign and coupon models, services, migrations, and admin controllers are visible. Coupon redemption data has a model/table, but runtime redemption flow is still tied to the broader Cart/Checkout uncertainty.
- Missing parts: Runtime coupon redemption path is not clearly mapped to a public checkout flow. Secretary campaign/coupon access appears blocked by the `campaign_access` policy according to current KB analysis.
- Risks: Campaign/Coupon uses a distinct access model instead of the shared `role:admin,secretary|perm:*` pattern. This can be correct policy, but it must remain explicit in route and security KB files.
- Key Claims:
  - Claim: Campaign/Coupon is a first-class KB domain for automation because it has distinct routes, models, services, migrations, permissions, and filter behavior.
    - Source: `app/Config/Routes.php`, `app/Config/Filters.php`, `app/Filters/CampaignAccessFilter.php`, `ai/domain-kb/kb-manifest.yaml`
    - Confidence: High
    - Domain: Campaign / Coupon
    - Related files: `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/03_security_filter_audit.md`, `ai/domain-kb/06_route_baseline.md`
  - Claim: Coupon redemption exists as data structure, but runtime checkout/cart redemption flow remains unclear.
    - Source: `app/Models/CouponRedemptionModel.php`, `app/Database/Migrations/2026-03-06-120000_CreateCouponsModuleTables.php`, `app/Config/Routes.php`
    - Confidence: Medium
    - Domain: Campaign / Coupon / Cart
    - Related files: `ai/domain-kb/06_route_baseline.md`, `ai/domain-kb/10_schema_model_matrix.md`
- Assumptions:
  - Assumption: `campaign_access` intentionally keeps campaign/coupon routes separate from the secretary permission route groups unless a future policy says otherwise.
  - Assumption: Coupon redemption is not a complete runtime checkout flow until a public cart/checkout route is mapped.
- KB update triggers: Campaign/coupon routes, `CampaignAccessFilter`, campaign/coupon services, campaign/coupon models, campaign/coupon migrations, marketing coupon seed data, or campaign permission migrations.

## Domain: Favorites / Wishlist

- Purpose: Manage user favorite/wishlist products.
- Related files: No clear application files found.
- Related routes: No clear route found.
- Related tables: No clear migration/table found.
- Related permissions: None.
- Current status: Missing domain.
- Missing: Controller, model, migration, service, and view.
- Risks: User task documents may mention this need, but application code does not show an implementation.
- KB update triggers: New route/model/service/view/migration files named `favorite`, `wishlist`, `saved`, or `like`.

## Domain: Review

- Purpose: Manage product review/rating flow.
- Related files: No clear application files found.
- Related routes: No clear route found.
- Related tables: No clear migration/table found.
- Related permissions: None.
- Current status: Missing domain.
- Missing: Review model, migration, moderation controller, storefront submit view, and admin review screen.
- Risks: Secretary review moderation tasks may exist in planning documents, but no application code counterpart is visible.
- KB update triggers: New file/route/table names containing `review`, `rating`, `comment`, or `moderation`.

## Domain: Page Builder

- Purpose: Manage storefront/page experience through versions, drafts, blocks, and specialized builder screens.
- Related files: `Admin/PageController.php`, `PageService.php`, `PageVersionService.php`, `PageBuilderService.php`, `ProductDetailPageBuilderService.php`, `ProductListPreviewRenderer.php`, `ProductDetailPreviewRenderer.php`, `CartPageBuilderService.php`, `CartPreviewRenderer.php`, `CheckoutPageBuilderService.php`, `CheckoutPreviewRenderer.php`, `PageModel.php`, `PageVersionModel.php`, `BlockTypeModel.php`, `BlockInstanceModel.php`, `app/Views/admin/pages/**`.
- Related routes: `admin/pages`, `admin/pages/{code}`, `admin/pages/{code}/builder`, `admin/pages/{code}/drafts`, `admin/page-versions/{id}`, `admin/pages/*builder/update`, `admin/pages/drafts/*`, `admin/pages/builder/*`.
- Related tables: `pages`, `page_versions`, `block_types`, `block_instances`.
- Related permissions: Admin-only `role:admin`.
- Current status: Draft, publish, schedule, block CRUD, and specialized page builders exist.
- Missing: Public rendering scope is only clear through `StorefrontHomeService`; all page types need a runtime render matrix.
- Risks: Cart/checkout builder can be confused with real cart/checkout runtime flow.
- KB update triggers: `PageController`, page services/models/migrations/views, or page seed changes.

## Domain: Dashboard Builder

- Purpose: Manage the admin/secretary dashboard screen through blocks and data sources.
- Related files: `Admin/DashboardController.php`, `Admin/DashboardBuilder.php`, `Admin/DashboardBlockController.php`, `DashboardService.php`, `DashboardBuilderService.php`, `DashboardBlockService.php`, `DashboardDataSourceService.php`, `Admin/DashboardService.php`, `DashboardModel.php`, `DashboardBlockTypeModel.php`, `DashboardBlockModel.php`, `DashboardBlockInstanceModel.php`, `app/Views/admin/dashboard/**`, `app/Views/admin/dashboard_builder/**`.
- Related routes: `admin/dashboard`, `admin/dashboard-builder`, `admin/dashboard-builder/reorder`, `admin/dashboard-builder/resize`, `admin/dashboard/blocks/*`.
- Related tables: `dashboards`, `dashboard_block_types`, `dashboard_blocks`, `dashboard_block_instances`, plus `orders` for dashboard data.
- Related permissions: `manage_dashboard` for dashboard viewing; builder route is admin-only.
- Current status: Active dashboard controller and builder/block APIs exist.
- Missing: Old/new dashboard block table distinction is unclear.
- Risks: `Admin/Dashboard.php` appears to be a route-less legacy controller. `DashboardBlockModel` table intent needs_review.
- KB update triggers: Dashboard controllers/services/models/views, dashboard migrations/seeds, or `manage_dashboard` filter changes.

## Domain: Theme / Media

- Purpose: Manage banners, theme layout, and media assets.
- Related files: `Admin/Banners.php`, `BannerService.php`, `BannerModel.php`, `CreateBannersTable` migration, `app/Views/admin/banners/index.php`, `app/Views/admin/theme/*`, `public/uploads/products/**`, `app/Helpers/product_media_helper.php`.
- Related routes: `admin/banners`, `admin/banners/save`, `admin/banners/toggle/{id}`.
- Related tables: `banners`; product media data is stored through `products` fields.
- Related permissions: Banner routes are admin-only through `role:admin`.
- Current status: Banner CRUD/toggle exists. Product uploads exist.
- Missing: Active usage of theme layout files is unclear.
- Risks: `admin/theme/*` and `admin/layouts/main.php` may be parallel layout sets.
- KB update triggers: Banner controller/service/model/view, product media helper, theme/layout, or upload path usage changes.

## Domain: Frontend Storefront

- Purpose: Provide public home page, product list/detail, and post-login user dashboard experience.
- Related files: `StorefrontController.php`, `ProductController.php`, `Home.php`, `StorefrontHomeService.php`, `ProductsService.php`, `app/Views/site/storefront/*`, `app/Views/site/products/*`, `app/Views/site/home/index.php`, `app/Views/site/layouts/*`, `app/Views/site/partials/*`.
- Related routes: `/`, `yardim/{slug}`, `products/detail/{segment}`, `products/list/{type}`, `products/list/{type}/{category}`, `products/selection`, `dashboard_anasayfa`.
- Related tables: `products`, `categories`, `authors`, `types`, plus `pages`, `page_versions`, `block_instances`, and `block_types` for page builder.
- Related permissions: Public storefront routes have no permission; `dashboard_anasayfa` is under the auth filter.
- Current status: Public home and product list/detail flow exist; user dashboard view exists.
- Missing: Account pages, favorites, reviews, and cart/checkout runtime flows are not visible.
- Risks: `site/products/product_selection.php` is not linked by the active selection route. Page builder public render scope must be mapped separately.
- KB update triggers: Storefront/Product/Home controller, storefront service, site views, page builder public rendering, or public route changes.
- Controlled update note: REAL-TEST-001 marks `app/Views/site/products/list.php` as high impact for Frontend Storefront and `app/Models/ProductsModel.php` as low impact for Frontend Storefront. With the available input, this is treated as a storefront view impact only; backend-flow impact is not confirmed. Needs Review if the view change adds form actions, route targets, cart/favorite/review behavior, or other runtime backend implications.

## Assumptions

- Assumption: Domain status is based on static repository evidence and the current KB files, not runtime execution.
- Assumption: Campaign/Coupon currently belongs near Cart/Admin Panel until a first-class domain decision is made.
- Assumption: Missing runtime routes for cart, checkout, favorites, account, and review mean those flows are not currently visible at route level.

## Risks

- Domains with UI-only evidence can be mistaken for complete backend runtime flows.
- Campaign/Coupon ownership is not fully normalized across all KB files.
- Future automation may miss drift if domain IDs and manifest watched paths are not kept synchronized.

## Last Normalization Notes

- Normalized to the KB claim schema on 2026-04-24.
- Added Purpose, Scope, Source of Truth, Key Claims, Related Files, Assumptions, Risks, and normalization notes.
- Detailed route evidence is now expected to live in `06_route_baseline.md`.
