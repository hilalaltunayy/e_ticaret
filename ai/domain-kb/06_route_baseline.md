# 06 Route Baseline

## Purpose

Define the current known route baseline for future KB drift checks.

## Scope

This file records the current static route structure from `app/Config/Routes.php` and the relevant filter aliases from `app/Config/Filters.php`. It is documentation-only and does not change application routes or filters.

## Source of Truth

- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/kb-manifest.yaml`

## Key Claims

- Claim: Public storefront and auth form routes are explicitly defined outside route-level auth filters.
  - Source: `app/Config/Routes.php`
  - Confidence: High
  - Domain: Frontend Storefront / Auth / Product / Catalog
  - Related files: `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/03_security_filter_audit.md`

- Claim: Basic user routes are protected by the `auth` route group.
  - Source: `app/Config/Routes.php`, `app/Config/Filters.php`
  - Confidence: High
  - Domain: Auth / Frontend Storefront / Order / Product / Catalog
  - Related files: `app/Filters/AuthFilter.php`

- Claim: Admin builder and configuration routes are protected by `role:admin`.
  - Source: `app/Config/Routes.php`, `app/Config/Filters.php`
  - Confidence: High
  - Domain: Admin Panel / Page Builder / Dashboard Builder / Theme / Media
  - Related files: `app/Filters/RoleFilter.php`

- Claim: Main admin/secretary operational routes are protected by `role:admin,secretary|perm:*`.
  - Source: `app/Config/Routes.php`, `app/Config/Filters.php`
  - Confidence: High
  - Domain: Secretary Access / RBAC / Order / Product / Catalog / Shipping
  - Related files: `app/Filters/RoleFilter.php`, `app/Models/UserPermissionModel.php`

- Claim: Runtime cart, checkout, favorites, account, and review routes are missing or unclear in the current route baseline.
  - Source: `app/Config/Routes.php`
  - Confidence: High
  - Domain: Cart / Favorites / Wishlist / Review / Frontend Storefront
  - Related files: `ai/domain-kb/01_domain_index.md`, `ai/domain-kb/03_security_filter_audit.md`

## Route Baseline Table

### Route Group Baseline

Rows containing `*` or grouped controller method notation are route group baseline rows. They are useful for human review and policy coverage, but they are not exact drift automation records.

Required note for every grouped row:

`This row is not suitable for exact drift automation until route extraction is automated.`

### Exact Route Baseline

Rows without wildcard notation represent currently documented exact routes from `app/Config/Routes.php`. These rows can support basic manual drift checks, but generated route extraction is still recommended before strict automation.

### Needs Extraction

The following route areas must be expanded into one-route-per-row records before strict KB drift automation:

- `admin/pages/drafts/*`
- `admin/pages/builder/*`
- `admin/dashboard/blocks/*`
- `admin/pricing*`
- `admin/settings/permissions*`
- `admin/stock*`
- `admin/notifications*`
- `admin/products*`
- `admin/authors*`
- `admin/categories*`
- `admin/shipping*`
- `admin/campaigns*`
- `admin/coupons*`
- `admin/orders*`
- Missing runtime route checks for cart, checkout, favorites/wishlist, account/profile, review/rating, and payment.

| Route / URI | Method | Controller@Method | Route Group | Filter | Permission | Domain | Source | Confidence | Notes |
|------------|--------|-------------------|-------------|--------|------------|--------|--------|------------|------|
| `/` | GET | `StorefrontController@home` | Public | None | None | Frontend Storefront | `app/Config/Routes.php` | High | Public home route |
| `/yardim/(:segment)` | GET | `StorefrontController@placeholder` | Public | None | None | Frontend Storefront | `app/Config/Routes.php` | High | Public help/placeholder page |
| `/login` | GET | `Login@index` | Public | None | None | Auth | `app/Config/Routes.php` | High | Login form |
| `/login/auth` | POST | `Login@auth` | Public | None | None | Auth | `app/Config/Routes.php`, `app/Config/Filters.php` | High | Public by design; CSRF is not globally enabled in reviewed config |
| `/register` | GET | `Register@index` | Public | None | None | Auth | `app/Config/Routes.php` | High | Register form |
| `/register/save` | POST | `Register@save` | Public | None | None | Auth | `app/Config/Routes.php`, `app/Config/Filters.php` | High | Public by design; CSRF is not globally enabled in reviewed config |
| `/logout` | GET | `Logout@index` | Public | None | None | Auth | `app/Config/Routes.php` | High | Public GET logout; security review remains relevant |
| `/dashboard_anasayfa` | GET | `Home@index` | Authenticated user | `auth` | Login required | Auth / Frontend Storefront | `app/Config/Routes.php` | High | Normal user dashboard after login |
| `/products` | GET | `ProductController@index` | Authenticated user | `auth` | Login required | Product / Catalog | `app/Config/Routes.php` | High | Authenticated product route; public product routes also exist |
| `/orders` | GET | `OrderController@index` | Authenticated user | `auth` | Login required | Order | `app/Config/Routes.php` | High | Redirects to admin orders according to existing KB review |
| `/orders/create` | POST | `OrderController@create` | Authenticated user | `auth` | Login required | Order | `app/Config/Routes.php` | High | User-side reserved order creation |
| `/products/detail/(:segment)` | GET | `ProductController@detail` | Public | None | None | Product / Catalog / Storefront | `app/Config/Routes.php` | High | Public product detail |
| `/products/list/(:any)/(:any)` | GET | `ProductController@listByCategory` | Public | None | None | Product / Catalog / Storefront | `app/Config/Routes.php` | High | Public category listing |
| `/products/list/(:any)` | GET | `ProductController@listByType` | Public | None | None | Product / Catalog / Storefront | `app/Config/Routes.php` | High | Public type listing |
| `/products/selection` | GET | `ProductController@selection` | Public | None | None | Product / Catalog / Storefront | `app/Config/Routes.php` | High | Delegates to product index according to current KB |
| `/admin/banners` | GET | `Admin\Banners@index` | Admin | `role:admin` | Admin role | Theme / Media | `app/Config/Routes.php` | High | Admin-only banner index |
| `/admin/banners/save` | POST | `Admin\Banners@save` | Admin | `role:admin` | Admin role | Theme / Media | `app/Config/Routes.php` | High | State-changing route; CSRF review applies |
| `/admin/banners/toggle/(:segment)` | POST | `Admin\Banners@toggle` | Admin | `role:admin` | Admin role | Theme / Media | `app/Config/Routes.php` | High | State-changing route; CSRF review applies |
| `/admin/dashboard-builder` | GET | `Admin\DashboardBuilder@index` | Admin | `role:admin` | Admin role | Dashboard Builder | `app/Config/Routes.php` | High | Admin-only builder |
| `/admin/dashboard-builder/reorder` | POST | `Admin\DashboardBuilder@reorder` | Admin | `role:admin` | Admin role | Dashboard Builder | `app/Config/Routes.php` | High | Admin-only builder write |
| `/admin/dashboard-builder/resize` | POST | `Admin\DashboardBuilder@resize` | Admin | `role:admin` | Admin role | Dashboard Builder | `app/Config/Routes.php` | High | Admin-only builder write |
| `/admin/pages` | GET | `Admin\PageController@index` | Admin | `role:admin` | Admin role | Page Builder | `app/Config/Routes.php` | High | Admin-only page list |
| `/admin/pages/(:segment)/builder` | GET | `Admin\PageController@builder` | Admin | `role:admin` | Admin role | Page Builder | `app/Config/Routes.php` | High | Admin-only page builder |
| `/admin/pages/product-list-builder/update` | POST | `Admin\PageController@updateProductListBuilder` | Admin | `role:admin` | Admin role | Page Builder | `app/Config/Routes.php` | High | Specialized builder update |
| `/admin/pages/product-detail-builder/update` | POST | `Admin\PageController@updateProductDetailBuilder` | Admin | `role:admin` | Admin role | Page Builder | `app/Config/Routes.php` | High | Specialized builder update |
| `/admin/pages/checkout-builder/update` | POST | `Admin\PageController@updateCheckoutBuilder` | Admin | `role:admin` | Admin role | Page Builder / Cart | `app/Config/Routes.php` | High | Builder route only; not runtime checkout |
| `/admin/pages/cart-builder/update` | POST | `Admin\PageController@updateCartBuilder` | Admin | `role:admin` | Admin role | Page Builder / Cart | `app/Config/Routes.php` | High | Builder route only; not runtime cart |
| `/admin/pages/drafts/*` | POST | `Admin\PageController@draft methods` | Admin | `role:admin` | Admin role | Page Builder | `app/Config/Routes.php` | High | Draft create/duplicate/start/archive/unpublish. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/pages/builder/*` | POST | `Admin\PageController@builder block methods` | Admin | `role:admin` | Admin role | Page Builder | `app/Config/Routes.php` | High | Draft update/publish/schedule/block operations. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/pages/(:segment)/drafts` | GET | `Admin\PageController@drafts` | Admin | `role:admin` | Admin role | Page Builder | `app/Config/Routes.php` | High | Admin-only drafts |
| `/admin/pages/(:segment)` | GET | `Admin\PageController@show` | Admin | `role:admin` | Admin role | Page Builder | `app/Config/Routes.php` | High | Admin-only page detail |
| `/admin/page-versions/(:segment)` | GET | `Admin\PageController@show` | Admin | `role:admin` | Admin role | Page Builder | `app/Config/Routes.php` | Medium | Exact intent needs review because it reuses `show` |
| `/admin/dashboard/blocks/*` | GET/POST | `Admin\DashboardBlockController@*` | Admin | `role:admin` | Admin role | Dashboard Builder | `app/Config/Routes.php` | High | Admin-only block API. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/marketing` | GET | `Admin\Marketing@index` | Admin | `role:admin` | Admin role | Admin Panel / Campaign / Coupon | `app/Config/Routes.php` | High | Role-only; no granular permission visible |
| `/admin/pricing*` | GET/POST | `Admin\Pricing@*` | Admin | `role:admin` | Admin role | Product / Catalog | `app/Config/Routes.php` | High | Role-only; no granular pricing permission visible. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/automation` | GET | `Admin\Automation@index` | Admin | `role:admin` | Admin role | Admin Panel | `app/Config/Routes.php` | Medium | Domain scope remains unclear |
| `/admin/settings` | GET | `Admin\Settings@index` | Admin | `role:admin` | Admin role | Admin Panel | `app/Config/Routes.php` | High | Admin-only settings |
| `/admin/settings/permissions*` | GET/POST | `Admin\SettingsPermissionsController@*` | Admin | `role:admin` | Admin role | Secretary Access / RBAC | `app/Config/Routes.php` | High | Secretary permission management. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/dashboard` | GET | `Admin\DashboardController@index` | Admin + Secretary | `role:admin,secretary|perm:manage_dashboard` | `manage_dashboard` | Dashboard Builder / Admin Panel | `app/Config/Routes.php` | High | Secretary requires permission |
| `/admin/stock*` | GET/POST | `Admin\Stock@*`, `Admin\StockMove@*` | Admin + Secretary | `role:admin,secretary|perm:manage_stock` | `manage_stock` | Product / Catalog | `app/Config/Routes.php` | High | Stock movement has extra controller-level admin-only rule for manual correction. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/notifications*` | GET/POST | `Admin\Notifications@*` | Admin + Secretary | `role:admin,secretary|perm:manage_notifications` | `manage_notifications` | Admin Panel | `app/Config/Routes.php` | High | Notification template and test routes. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/customers` | GET | `Admin\Customers@index` | Admin + Secretary | `role:admin,secretary|perm:manage_customers` | `manage_customers` | User / Admin Panel | `app/Config/Routes.php` | High | Customer index |
| `/admin/products*` | GET/POST | `Admin\Products@*` | Admin + Secretary | `role:admin,secretary|perm:manage_products` | `manage_products` | Product / Catalog | `app/Config/Routes.php` | High | Product CRUD and API. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/authors*` | GET/POST | `Admin\Products@createAuthor/storeAuthor` | Admin + Secretary | `role:admin,secretary|perm:manage_products` | `manage_products` | Product / Catalog | `app/Config/Routes.php` | High | Author creation under product permission. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/categories*` | GET/POST | `Admin\Products@createCategory/storeCategory` | Admin + Secretary | `role:admin,secretary|perm:manage_products` | `manage_products` | Category | `app/Config/Routes.php` | High | Category creation under product permission. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/shipping*` | GET/POST | `Admin\Shipping@*`, `Admin\ShippingCompanies@*`, `Admin\ShippingAutomationController@*` | Admin + Secretary | `role:admin,secretary|perm:manage_shipping` | `manage_shipping` | Shipping / Order | `app/Config/Routes.php` | High | Shipping, companies, API, automation. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/campaigns*` | GET/POST | `Admin\Campaigns@*` | Campaign Access | `campaign_access` | `manage_campaigns` or `manage_campaigns_engine` | Campaign / Coupon | `app/Config/Routes.php` | High | Filter requires admin role according to current KB. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/coupons*` | GET/POST | `Admin\Coupons@*` | Campaign Access | `campaign_access` | `manage_campaigns` or `manage_campaigns_engine` | Campaign / Coupon / Cart | `app/Config/Routes.php` | High | Filter requires admin role according to current KB. This row is not suitable for exact drift automation until route extraction is automated. |
| `/admin/orders*` | GET/POST | `Admin\Orders@*`, `Admin\OrderStatuses@index` | Admin + Secretary | `role:admin,secretary|perm:manage_orders` | `manage_orders` | Order | `app/Config/Routes.php` | High | Orders, analytics, status, packing, invoice, shipping, cancel, return. This row is not suitable for exact drift automation until route extraction is automated. |
| `/cart` | Any | Not visible | Missing or unclear | Unknown | Unknown | Cart | `app/Config/Routes.php` | High | Runtime cart route not visible |
| `/checkout` | Any | Not visible | Missing or unclear | Unknown | Unknown | Cart / Order / Payment | `app/Config/Routes.php` | High | Runtime checkout route not visible |
| `/favorites` or `/wishlist` | Any | Not visible | Missing or unclear | Unknown | Unknown | Favorites / Wishlist | `app/Config/Routes.php` | High | Backend route not visible; product detail UI fragments require review |
| `/account` or `/profile` | Any | Not visible | Missing or unclear | Unknown | Unknown | User / Frontend Storefront | `app/Config/Routes.php` | High | Account/profile route not visible |
| `/reviews` or `/ratings` | Any | Not visible | Missing or unclear | Unknown | Unknown | Review | `app/Config/Routes.php` | High | Backend route not visible; product detail rating UI requires review |

## Public Routes

- `/`
- `/yardim/(:segment)`
- `/login`
- `/login/auth`
- `/register`
- `/register/save`
- `/logout`
- `/products/detail/(:segment)`
- `/products/list/(:any)/(:any)`
- `/products/list/(:any)`
- `/products/selection`

## Authenticated Routes

- `/dashboard_anasayfa`
- `/products`
- `/orders`
- `/orders/create`

These routes are inside the root route group with `filter => auth`.

## Admin Routes

Admin-only route groups use `role:admin`.

- `/admin/banners*`
- `/admin/dashboard-builder*`
- `/admin/pages*`
- `/admin/page-versions/*`
- `/admin/dashboard/blocks*`
- `/admin/marketing`
- `/admin/pricing*`
- `/admin/automation`
- `/admin/settings`
- `/admin/settings/permissions*`

Security-critical admin groups:

- Page builder routes because they alter storefront page configuration.
- Dashboard builder routes because they alter admin dashboard configuration.
- Settings and secretary permission routes because they affect access policy.
- Pricing, banners, marketing, campaigns, and coupons because they affect public commerce behavior.

## Secretary Routes

Secretary-accessible route groups use `role:admin,secretary|perm:*`.

- `/admin/dashboard` -> `manage_dashboard`
- `/admin/stock*` -> `manage_stock`
- `/admin/notifications*` -> `manage_notifications`
- `/admin/customers` -> `manage_customers`
- `/admin/products*`, `/admin/authors*`, `/admin/categories*` -> `manage_products`
- `/admin/shipping*` -> `manage_shipping`
- `/admin/orders*` -> `manage_orders`

Campaign/coupon routes use `campaign_access` and are tracked separately because the current KB indicates that this filter requires admin role.

## User Runtime Routes

Visible user runtime routes:

- `/dashboard_anasayfa` with `auth`
- `/products` with `auth`
- `/orders` with `auth`
- `/orders/create` with `auth`

Public user-facing routes:

- Home, help, public product detail/list/selection routes.

Missing or unclear runtime routes:

- Runtime cart route
- Runtime checkout route
- Account/profile route
- Favorites/wishlist route
- Review/rating submit or moderation route
- Payment route
- User order history route separate from admin redirect

## Missing or Unclear Routes

- Cart: Missing or unclear as a runtime route. Builder routes exist under `admin/pages/cart-builder/update`.
- Checkout: Missing or unclear as a runtime route. Builder routes exist under `admin/pages/checkout-builder/update`.
- Favorites / Wishlist: Missing or unclear backend route. Product detail UI fragments exist according to prior KB audit.
- Account / Profile: Missing or unclear route.
- Review / Rating: Missing or unclear backend route. Product detail rating UI fragments exist according to prior KB audit.
- Payment: Missing or unclear standalone route.
- `Admin\Dashboard.php`: No active route link visible.
- `Admin\OrderController.php`: No active route link visible.
- `Admin\ShippingAutomation.php`: Active routes use `ShippingAutomationController`.

## Drift-Relevant Route Rules

- If `app/Config/Routes.php` changes, update:
  - `ai/domain-kb/01_domain_index.md`
  - `ai/domain-kb/02_route_permission_matrix.md`
  - `ai/domain-kb/03_security_filter_audit.md`
  - `ai/domain-kb/06_route_baseline.md`
  - `ai/domain-kb/kb-manifest.yaml`

- If `app/Config/Filters.php` changes, update:
  - `ai/domain-kb/02_route_permission_matrix.md`
  - `ai/domain-kb/03_security_filter_audit.md`
  - `ai/domain-kb/06_route_baseline.md`
  - `ai/domain-kb/kb-manifest.yaml`

- If any `/admin/*` route changes, update the owning domain in:
  - `ai/domain-kb/01_domain_index.md`
  - `ai/domain-kb/02_route_permission_matrix.md`
  - `ai/domain-kb/06_route_baseline.md`

- If any route changes filter from `role:admin`, `role:admin,secretary|perm:*`, `auth`, or `campaign_access`, update:
  - `ai/domain-kb/03_security_filter_audit.md`
  - `ai/domain-kb/06_route_baseline.md`

- If cart, checkout, favorites, account, review, or payment routes are added, update:
  - `ai/domain-kb/00_repo_inventory.md`
  - `ai/domain-kb/01_domain_index.md`
  - `ai/domain-kb/02_route_permission_matrix.md`
  - `ai/domain-kb/03_security_filter_audit.md`
  - `ai/domain-kb/06_route_baseline.md`
  - `ai/domain-kb/kb-manifest.yaml`

- If a controller referenced by a route is renamed or removed, update:
  - `ai/domain-kb/00_repo_inventory.md`
  - `ai/domain-kb/01_domain_index.md`
  - `ai/domain-kb/06_route_baseline.md`

## Assumptions

- Assumption: Route names are not listed because named route aliases are not visible in the reviewed `Routes.php`.
- Assumption: Public storefront routes are intentionally public.
- Assumption: Missing cart/checkout/favorites/account/review/payment routes mean these runtime flows are not visible at route level in the reviewed repository state.
- Assumption: Some grouped rows in this baseline summarize multiple concrete routes to keep the baseline maintainable until an automated route extractor exists.

## Risks

- POST routes are authorization-protected in many areas but CSRF is not globally enabled in the reviewed `Filters.php`.
- `/logout` is public GET.
- Permission enforcement is split between route filters and selected controller-level checks.
- Missing runtime user routes can create drift between product expectations, UI fragments, and backend implementation.

## Last Normalization Notes

- Created on 2026-04-24 as part of Domain KB normalization.
- Built from `app/Config/Routes.php`, `app/Config/Filters.php`, and existing KB files.
- This file is the current route drift reference until a generated route baseline is introduced.
