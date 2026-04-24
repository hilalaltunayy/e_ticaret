# Domain KB - Repository Inventory

## Purpose

Provide a repo-first inventory of the current CodeIgniter 4 e-commerce application and map important files to Domain KB ownership.

Source: `ai/domain-cube/00_repo_inventory.md`. This document does not copy the source inventory verbatim; it converts it into a shorter, trackable, domain-oriented Knowledge Base summary. The content is based only on static repository evidence.

## Scope

- Application code was not changed.
- Route, controller, model, service, view, migration, and seeder files were reviewed only as documentation input.
- Uncertain areas are marked as `needs_review` or `Assumption`.

## Source of Truth

- `ai/domain-cube/00_repo_inventory.md`
- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `app/Controllers/**`
- `app/Models/**`
- `app/Services/**`
- `app/Database/Migrations/**`
- `app/Database/Seeds/**`
- `app/Views/**`

## Key Claims

- Claim: The repository has clear Auth, RBAC, Admin Panel, Product / Catalog, Order, Page Builder, Dashboard Builder, Theme / Media, and Frontend Storefront surfaces.
  - Source: `app/Controllers/**`, `app/Models/**`, `app/Services/**`, `app/Views/**`
  - Confidence: High
  - Domain: All tracked domains
  - Related files: `app/Config/Routes.php`, `app/Config/Filters.php`, `ai/domain-kb/01_domain_index.md`

- Claim: Cart and checkout are visible mainly through page builder and preview files, not through a clear public runtime cart/checkout controller flow.
  - Source: `app/Config/Routes.php`, `app/Services/*PageBuilderService.php`, `app/Services/*PreviewRenderer.php`, `app/Views/admin/pages/**`
  - Confidence: High
  - Domain: Cart / Page Builder / Frontend Storefront
  - Related files: `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/06_route_baseline.md`

- Claim: Favorites / Wishlist and Review do not have clear backend route/model/service/migration coverage in the current static inventory.
  - Source: `app/Config/Routes.php`, `app/Controllers/**`, `app/Models/**`, `app/Services/**`, `app/Database/Migrations/**`
  - Confidence: Medium
  - Domain: Favorites / Wishlist / Review
  - Related files: `app/Views/site/products/product_detail.php`, `ai/domain-kb/04_kb_quality_audit.md`

- Claim: `RoleModel.php` and `RoleModels.php` require review because they appear to represent the same role/table intent.
  - Source: `app/Models/RoleModel.php`, `app/Models/RoleModels.php`
  - Confidence: High
  - Domain: User / Role / Permission
  - Related files: `ai/domain-kb/01_domain_index.md`, `ai/domain-kb/04_kb_quality_audit.md`

- Claim: Route/filter security findings must be tracked outside this inventory in the route and security KB files.
  - Source: `app/Config/Routes.php`, `app/Config/Filters.php`
  - Confidence: High
  - Domain: Auth / RBAC / Admin Panel / Secretary Access
  - Related files: `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/03_security_filter_audit.md`

## Related Files

- `ai/domain-kb/01_domain_index.md`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/04_kb_quality_audit.md`
- `ai/domain-kb/05_kb_manifest_and_schema_plan.md`
- `ai/domain-kb/06_route_baseline.md`
- `ai/domain-kb/kb-manifest.yaml`

## Main Application Folders

| Folder | KB meaning |
|---|---|
| `app/Config` | Route, filter, security, session, autoload, and framework configuration surface |
| `app/Controllers` | Public auth/storefront controllers and `Admin/` management controllers |
| `app/Database/Migrations` | Schema history for Auth, RBAC, catalog, order, shipping, marketing, dashboard builder, and page builder |
| `app/Database/Seeds` | Initial Auth/RBAC, demo order/shipping, product/catalog, and builder seed data |
| `app/DTO` | Data transfer objects for Auth, product, admin dashboard, marketing, page management, and shipping |
| `app/Filters` | Auth, role, permission, and campaign access enforcement layer |
| `app/Models` | Table-oriented data models and some custom data access classes |
| `app/Services` | Domain business logic for auth, product, order, shipping, marketing, builders, and notifications |
| `app/Repositories` | Repository layer currently limited to shipping automation rules |
| `app/Views` | Auth, storefront, admin panel, dashboard builder, page builder, layout/partial, and error screens |

## Current Module Map

| Module | Main files | Status |
|---|---|---|
| Auth | `Login`, `Register`, `Logout`, `AuthService`, `UserModel`, `UserDTO`, `auth/*` views | Basic login/register/logout flow exists |
| User / Role / Permission | `RoleFilter`, `PermissionFilter`, `CampaignAccessFilter`, RBAC models, `SettingsPermissions*` | Route-level RBAC and secretary override flow exist |
| Admin Panel | `app/Controllers/Admin/*`, `admin/layouts`, `admin/partials`, `Admin/DashboardService` | Many domain screens are grouped under the admin layout |
| Secretary Access | `Login`, `RoleFilter`, `UserPermissionModel`, `SettingsPermissionsController`, admin route groups | No separate secretary namespace; admin screens are shared through permissions |
| Product / Catalog | `ProductController`, `Admin/Products`, `ProductsService`, catalog models, product views | Public and admin product flows exist |
| Category | `CategoryModel`, `Admin/Products::createCategory/storeCategory`, `admin/categories/create` | Lives under Product / Catalog |
| Order | `OrderController`, `Admin/Orders`, order services/models/views | Admin/secretary order management is strong; user order creation is limited |
| Cart | Cart/checkout builder services and views | Runtime cart controller/model not visible; builder/preview level exists |
| Favorites / Wishlist | No clear files found | Missing |
| Review | No clear files found | Missing |
| Page Builder | `Admin/PageController`, page services/models/migrations, `admin/pages/*` | Draft/version/block and specialized builder flow exists |
| Dashboard Builder | `DashboardController`, `DashboardBuilder`, `DashboardBlockController`, dashboard services/models/views | Active dashboard plus builder structure exists |
| Theme / Media | `BannerService`, `BannerModel`, `Admin/Banners`, `admin/theme/*`, uploads | Banner management exists; theme layout links are unclear |
| Frontend Storefront | `StorefrontController`, `ProductController`, `StorefrontHomeService`, `site/*` views | Public home and product list/detail flow exists |

## File Inventory

| File | Type | KB domain | Current purpose | Note |
|---|---|---|---|---|
| `app/Config/Routes.php` | Config | All domains | Defines public, auth, admin, and secretary route groups | Must be watched for all domains in the KB manifest |
| `app/Config/Filters.php` | Config | Auth / RBAC | Defines filter aliases | `auth` alias is duplicated; CSRF appears globally disabled |
| `app/Filters/AuthFilter.php` | Filter | Auth | Checks `isLoggedIn` session state | Used by the auth-protected group |
| `app/Filters/RoleFilter.php` | Filter | User / Role / Permission | Checks role and `perm:*` arguments | Admin bypasses permission checks; secretary uses user permission checks |
| `app/Filters/PermissionFilter.php` | Filter | User / Role / Permission | Delegates permission checks to `RoleFilter` | Route alias exists |
| `app/Filters/CampaignAccessFilter.php` | Filter | Campaign / Coupon / RBAC | Enforces campaign/coupon access | Requires role `admin`, so secretary is excluded |
| `app/Controllers/Login.php` | Controller | Auth | Login form, session creation, role redirect | Admin/secretary -> `admin/dashboard`, user -> `dashboard_anasayfa` |
| `app/Controllers/Register.php` | Controller | Auth | Register form and account creation | Uses `AuthService` and `UserDTO` |
| `app/Controllers/Logout.php` | Controller | Auth | Destroys session | Public route exists |
| `app/Controllers/Home.php` | Controller | Frontend Storefront | User dashboard after login | Renders `site/home/index` |
| `app/Controllers/StorefrontController.php` | Controller | Frontend Storefront | Home and placeholder pages | Uses `StorefrontHomeService` |
| `app/Controllers/ProductController.php` | Controller | Product / Catalog | Public product list/detail | `selection()` delegates to `index()` |
| `app/Controllers/OrderController.php` | Controller | Order | Auth user order creation and redirect to admin orders | Creates reserved orders |
| `app/Controllers/Admin/Products.php` | Controller | Product / Catalog | Admin product CRUD, author/category creation | Main product admin surface |
| `app/Controllers/Admin/Orders.php` | Controller | Order | Admin/secretary order operations | Status, shipping, notes, invoice, packing |
| `app/Controllers/Admin/Stock.php` | Controller | Product / Catalog | Stock list and movements | Protected by `manage_stock` |
| `app/Controllers/Admin/StockMove.php` | Controller | Product / Catalog | Single-product stock movement | GET route redirects to stock moves |
| `app/Controllers/Admin/Shipping.php` | Controller | Order / Shipping | Shipping datatable and bulk outputs | `ShippingModel` is custom data access, not a CI Model subclass |
| `app/Controllers/Admin/ShippingAutomationController.php` | Controller | Shipping | Active shipping automation controller | Routes point to this class |
| `app/Controllers/Admin/ShippingAutomation.php` | Controller | Shipping | Similar/older automation controller | needs_review: no active route found |
| `app/Controllers/Admin/DashboardController.php` | Controller | Dashboard Builder | Active admin/secretary dashboard | `admin/dashboard` route |
| `app/Controllers/Admin/Dashboard.php` | Controller | Admin Panel | Legacy dashboard candidate | needs_review: no active route found |
| `app/Controllers/Admin/DashboardBuilder.php` | Controller | Dashboard Builder | Builder index/reorder/resize | Admin-only |
| `app/Controllers/Admin/DashboardBlockController.php` | Controller | Dashboard Builder | Block API | Store/update/delete/fetch |
| `app/Controllers/Admin/PageController.php` | Controller | Page Builder | Pages, versions, drafts, blocks, specialized builders | Chooses builder view dynamically |
| `app/Controllers/Admin/SettingsPermissionsController.php` | Controller | Secretary Access / RBAC | Secretary user and permission screen | Admin-only |
| `app/Controllers/Admin/Campaigns.php` | Controller | Campaign | Campaign CRUD | Protected by `campaign_access` |
| `app/Controllers/Admin/Coupons.php` | Controller | Coupon | Coupon CRUD | Protected by `campaign_access` |
| `app/Controllers/Admin/Banners.php` | Controller | Theme / Media | Banner management | Admin-only |
| `app/Controllers/Admin/Notifications.php` | Controller | Admin Panel | Email/SMS templates and tests | Notification services |
| `app/Controllers/Admin/Customers.php` | Controller | User | Customer index | Detailed CRUD needs_review |
| `app/Models/UserModel.php` | Model | Auth / User | `users` table | Role is also stored directly on users |
| `app/Models/RoleModel.php` | Model | RBAC | `roles` table | Active model candidate |
| `app/Models/RoleModels.php` | Model | RBAC | Second file targeting `roles` table | needs_review: possible class/model conflict |
| `app/Models/PermissionModel.php` | Model | RBAC | `permissions` table | Permission code source |
| `app/Models/RolePermissionModel.php` | Model | RBAC | `role_permissions` pivot | Role-permission relationship |
| `app/Models/UserPermissionModel.php` | Model | RBAC / Secretary Access | `user_permissions` override | Effective permission and `isAllowed` checks |
| `app/Models/ProductsModel.php` | Model | Product / Catalog | `products` table | Used by admin, storefront, order, pricing |
| `app/Models/CategoryModel.php` | Model | Category | `categories` table | Product and campaign/coupon targets |
| `app/Models/AuthorModel.php` | Model | Product / Catalog | `authors` table | Product metadata |
| `app/Models/TypeModel.php` | Model | Product / Catalog | `types` table | Product list/type |
| `app/Models/PriceRuleModel.php` | Model | Product / Catalog | `price_rules` table | Pricing |
| `app/Models/OrderModel.php` | Model | Order | `orders` table | Main order model |
| `app/Models/OrderItemModel.php` | Model | Order | `order_items` table | Order creation |
| `app/Models/OrderLogModel.php` | Model | Order | `order_logs` table | Audit/status log |
| `app/Models/PackingSessionModel.php` | Model | Order / Shipping | `packing_sessions` table | Packing flow |
| `app/Models/InvoiceModel.php` | Model | Order | `invoices` table | Invoice flow |
| `app/Models/ShippingModel.php` | Model | Shipping / Order | Shipping datatable over orders | Does not extend CI Model |
| `app/Models/ShippingAutomationRuleModel.php` | Model | Shipping | `shipping_automation_rules` table | Automation rule |
| `app/Models/CampaignModel.php` | Model | Campaign | `campaigns` table | `CampaignService` |
| `app/Models/CampaignTargetModel.php` | Model | Campaign | `campaign_targets` | Campaign targets |
| `app/Models/CouponModel.php` | Model | Cart / Coupon | `coupons` table | `CouponService` |
| `app/Models/CouponTargetModel.php` | Model | Cart / Coupon | `coupon_targets` | Coupon targets |
| `app/Models/CouponRedemptionModel.php` | Model | Cart / Coupon / Order | `coupon_redemptions` | Runtime usage needs_review |
| `app/Models/PageModel.php` | Model | Page Builder | `pages` table | Page registry |
| `app/Models/PageVersionModel.php` | Model | Page Builder | `page_versions` table | Draft/version/publish |
| `app/Models/BlockTypeModel.php` | Model | Page Builder | `block_types` table | Generic blocks |
| `app/Models/BlockInstanceModel.php` | Model | Page Builder | `block_instances` table | Version blocks |
| `app/Models/DashboardModel.php` | Model | Dashboard Builder | `dashboards` table | User/global dashboard |
| `app/Models/DashboardBlockTypeModel.php` | Model | Dashboard Builder | `dashboard_block_types` | Block type |
| `app/Models/DashboardBlockModel.php` | Model | Dashboard Builder | Derived from dashboard block type model | needs_review: table/model intent unclear |
| `app/Models/DashboardBlockInstanceModel.php` | Model | Dashboard Builder | `dashboard_block_instances` | New instance structure |
| `app/Models/BannerModel.php` | Model | Theme / Media | `banners` table | `BannerService` |
| `app/Models/VisitModel.php` | Model | Admin Panel | `visits` table | needs_review: migration fields appear broader than `allowedFields` |
| `app/Services/AuthService.php` | Service | Auth | Login/register logic | Uses `UserModel` |
| `app/Services/ProductsService.php` | Service | Product / Catalog | Product list, stock, metadata | Shared by public/admin/order |
| `app/Services/StorefrontHomeService.php` | Service | Frontend Storefront | Home/page-builder storefront data | Uses page and product models |
| `app/Services/OrdersService.php` | Service | Order | Order, stock reservation, status/log | Main order service |
| `app/Services/OrderCreationService.php` | Service | Order | Admin order creation | OrderItem/Product |
| `app/Services/OrderShippingService.php` | Service | Order / Shipping | Shipping status update | Admin Orders |
| `app/Services/OrderNoteService.php` | Service | Order | Order note update | Admin Orders |
| `app/Services/OrdersReportingService.php` | Service | Order | Analytics/reporting | Admin Orders APIs |
| `app/Services/PackingService.php` | Service | Order / Shipping | Packing verification | Packing views |
| `app/Services/InvoiceService.php` | Service | Order | Invoice create/view/download | Writable invoice outputs |
| `app/Services/PageService.php` | Service | Page Builder | Page list/core operations | `PageController` |
| `app/Services/PageVersionService.php` | Service | Page Builder | Version/draft/publish | `PageController` |
| `app/Services/PageBuilderService.php` | Service | Page Builder | Generic block builder | `PageController` |
| `app/Services/*PageBuilderService.php` | Service | Page Builder / Cart | Product detail/cart/checkout special configs | Cart/checkout runtime needs_review |
| `app/Services/*PreviewRenderer.php` | Service | Page Builder | Admin builder preview data | Product/cart/checkout previews |
| `app/Services/DashboardService.php` | Service | Dashboard Builder | Dashboard builder state | `DashboardController` |
| `app/Services/DashboardBuilderService.php` | Service | Dashboard Builder | Dashboard creation/reorder | Builder controller |
| `app/Services/DashboardBlockService.php` | Service | Dashboard Builder | Block CRUD/config | Block controller |
| `app/Services/DashboardDataSourceService.php` | Service | Dashboard Builder | Dashboard datasets | Based on `OrderModel` |
| `app/Services/Admin/SettingsPermissionsService.php` | Service | Secretary Access / RBAC | Secretary permission management | Admin-only |
| `app/Services/BannerService.php` | Service | Theme / Media | Banner CRUD/toggle | Storefront usage needs_review |
| `app/Database/Seeds/InitialAuthSeeder.php` | Seeder | Auth / RBAC | Initial roles, users, permissions | admin/secretary/user |
| `app/Database/Seeds/PageManagementSeeder.php` | Seeder | Page Builder | Initial page builder data | Storefront page code Assumption |
| `app/Database/Seeds/DashboardBuilderSeeder.php` | Seeder | Dashboard Builder | Initial dashboard builder data | Global dashboard |
| `app/Views/auth/*` | View | Auth | Login/register screens | Auth controllers |
| `app/Views/site/storefront/*` | View | Frontend Storefront | Home/fallback pages | `StorefrontController` |
| `app/Views/site/products/*` | View | Product / Catalog | Product list/detail/selection | `product_selection` not route-bound |
| `app/Views/admin/products/*` | View | Product / Catalog | Admin product UI | Admin Products |
| `app/Views/admin/orders/*` | View | Order | Admin order and packing UI | Admin Orders |
| `app/Views/admin/pages/*` | View | Page Builder | Builder, drafts, versions, previews | `PageController` |
| `app/Views/admin/dashboard*` | View | Dashboard Builder | Dashboard and builder UI | Dashboard controllers |
| `app/Views/admin/settings/permissions.php` | View | Secretary Access / RBAC | Permission UI | `SettingsPermissionsController` |
| `app/Views/admin/theme/*` | View | Theme / Media | Alternative theme layout | needs_review: active link not clear |
| `app/Views/layouts/*` | View | Unknown | Legacy/general layouts | needs_review |

## Current Flow Summary

### Auth

`/login` and `/register` public routes go to `Login` and `Register` controllers. Successful login writes user, role, and permission data to the session. Admin and secretary users are redirected to `admin/dashboard`; normal users are redirected to `dashboard_anasayfa`.

### RBAC and Secretary

Route filters use the `role:...|perm:...` pattern. Admin users bypass permission checks in `RoleFilter`. Secretary users are checked through `UserPermissionModel`. Secretary management is under the admin-only settings permission screen.

### Admin Panel

The admin layout contains dashboard, products, orders, stock, shipping, notifications, settings, marketing, campaigns, coupons, pricing, customers, banners, and automation screens. Some areas are admin-only; others are shared with secretary users through permission filters.

### Product / Catalog

Public product listing/detail is handled by `ProductController`; admin product management is handled by `Admin/Products`. The product domain works with categories, authors, types, price rules, and stock flows.

### Order

Authenticated users can create reserved orders through `orders/create`. Admin/secretary order management is much broader: list, detail, analytics, status, shipping, notes, invoice, packing, cancel, and return operations exist.

### Page Builder

The admin-only page builder covers pages, versions, drafts, block CRUD, publish/schedule, and specialized product/cart/checkout builder screens. Cart/checkout currently appear as builder UI; the real runtime commerce flow needs_review.

### Dashboard Builder

`admin/dashboard` is the active dashboard screen. `admin/dashboard-builder` is the dashboard editing area. Dashboard block APIs provide fetch/detail/store/update/delete operations.

## Unclear / Needs Review Areas

| Area | Why it needs review |
|---|---|
| `app/Models/RoleModels.php` | Appears to target the same class/table intent as `RoleModel.php` |
| `app/Controllers/Admin/Dashboard.php` | No active route found; may be legacy |
| `app/Controllers/Admin/OrderController.php` | No active route found; `Admin/Orders.php` is the main controller |
| `app/Controllers/Admin/ShippingAutomation.php` | Active routes use `ShippingAutomationController` |
| `app/Views/site/products/product_selection.php` | File exists, but `products/selection` delegates to `ProductController@index` |
| `app/Views/admin/orders/stock_management_view.php` | No active controller view link found |
| `app/Views/admin/theme/*` | Alternative layout exists; active usage is unclear |
| `app/Views/layouts/*` and `app/Views/partials/sidebar.php` | Legacy/general layout set coexists with newer admin/site layouts |
| `VisitModel` | Model `allowedFields` appear narrower than migration fields |
| Cart/Checkout runtime | Builder/preview exists; controller/model/payment flow not visible |
| Favorites / Wishlist | No clear application files found |
| Review | No clear application files found |
| CSRF/filter config | `Filters.php` has globally disabled CSRF and duplicate `auth` alias |

## KB Maintenance Notes

- If `app/Config/Routes.php` changes, update `01_domain_index.md`, `02_route_permission_matrix.md`, and this file.
- If `app/Filters/*` or RBAC models change, update Auth, User / Role / Permission, and Secretary Access domains.
- If `app/Controllers/Admin/*` changes, update the related admin domain and check `kb-manifest.yaml` watched paths.
- If a new cart, wishlist, or review file is added, immediately update the status of the currently missing domains.

## Assumptions

- Assumption: Static repository evidence is sufficient for this inventory; runtime behavior was not executed.
- Assumption: Files without active route links may be legacy, alternate, or indirectly used until a deeper call-flow audit confirms usage.
- Assumption: Cart/checkout builder files do not prove a complete runtime commerce flow by themselves.

## Risks

- Route-level security and permission behavior can drift if `Routes.php` or filters change without updating the KB.
- UI files can exist without a backend flow, especially for cart, checkout, favorites, wishlist, and review.
- Broad admin areas can hide domain ownership unless the manifest keeps exact paths and review paths separate.

## Last Normalization Notes

- Normalized to the KB claim schema on 2026-04-24.
- Added explicit Purpose, Source of Truth, Key Claims, Related Files, Assumptions, Risks, and normalization notes.
- This file remains a high-level inventory; detailed route drift evidence belongs in `06_route_baseline.md`.
