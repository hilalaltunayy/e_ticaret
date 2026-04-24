# 03 Security & Filter Audit

## Purpose

This document analyzes authentication, authorization, and filter-level security enforcement in the application.

## Scope

This file reviews static route and filter configuration for security-sensitive access behavior. It does not execute the application and does not change filters, routes, controllers, or other application code.

## Source of Truth

- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `ai/domain-kb/00_repo_inventory.md`
- `ai/domain-kb/01_domain_index.md`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/06_route_baseline.md`

## Key Claims

- Claim: CSRF is registered as an alias but is not enabled in global filters in the reviewed configuration.
  - Source: `app/Config/Filters.php`
  - Confidence: High
  - Domain: Auth / Admin Panel / Order / Product / Catalog / Page Builder / Dashboard Builder
  - Related files: `app/Config/Routes.php`, `ai/domain-kb/02_route_permission_matrix.md`

- Claim: Secure headers are registered as an alias but are not globally enabled in the reviewed configuration.
  - Source: `app/Config/Filters.php`
  - Confidence: High
  - Domain: Security / All web domains
  - Related files: `ai/domain-kb/04_kb_quality_audit.md`

- Claim: `/logout` is a public GET route with no explicit route-level `auth` filter.
  - Source: `app/Config/Routes.php`
  - Confidence: High
  - Domain: Auth
  - Related files: `app/Controllers/Logout.php`, `ai/domain-kb/06_route_baseline.md`

- Claim: Admin/secretary operational routes are generally protected by role and permission filters.
  - Source: `app/Config/Routes.php`, `app/Config/Filters.php`
  - Confidence: High
  - Domain: Admin Panel / Secretary Access / RBAC
  - Related files: `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/06_route_baseline.md`

- Claim: Permission enforcement is not fully centralized.
  - Source: `app/Config/Routes.php`, `app/Config/Filters.php`, `ai/domain-kb/00_repo_inventory.md`
  - Confidence: Medium
  - Domain: RBAC / Campaign / Coupon / Order / Product / Catalog
  - Related files: `app/Filters/RoleFilter.php`, `app/Filters/CampaignAccessFilter.php`, `app/Controllers/Admin/Orders.php`, `app/Controllers/Admin/StockMove.php`

## Related Files

- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `ai/domain-kb/00_repo_inventory.md`
- `ai/domain-kb/01_domain_index.md`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/04_kb_quality_audit.md`
- `ai/domain-kb/05_kb_manifest_and_schema_plan.md`
- `ai/domain-kb/06_route_baseline.md`

No application code was changed.

## Global Security Configuration

### Filters configuration

`app/Config/Filters.php` defines the following relevant aliases:

- `auth` -> `App\Filters\AuthFilter`
- `role` -> `App\Filters\RoleFilter`
- `perm` -> `App\Filters\PermissionFilter`
- `campaign_access` -> `App\Filters\CampaignAccessFilter`
- Framework filters such as `csrf`, `toolbar`, `honeypot`, `invalidchars`, `secureheaders`, `cors`, `forcehttps`, `pagecache`, and `performance`

Findings:

- The `auth` alias appears twice in the aliases array.
- `csrf` is registered as an alias but is not enabled globally in `globals.before`.
- `secureheaders` is registered as an alias but is not enabled globally in `globals.after`.
- `honeypot` and `invalidchars` are present as aliases but commented out globally.
- `forcehttps` appears in the `required.before` list.
- `pagecache`, `performance`, and `toolbar` appear in required filters.
- There is an import typo-like namespace in `use CodeIgniter\Roter\RouteCollection;`; it is not used in the filter class, but the presence is noted as configuration noise.

### Global filters

Configured global filters:

- `globals.before`: no active entries; `honeypot`, `csrf`, and `invalidchars` are commented out.
- `globals.after`: no active entries; `honeypot` and `secureheaders` are commented out.

Required filters:

- Before: `forcehttps`, `pagecache`
- After: `pagecache`, `performance`, `toolbar`

### CSRF status

CSRF protection appears not globally enabled. Many POST routes exist in `Routes.php`, including login/register, admin writes, product writes, order updates, shipping actions, page builder writes, dashboard builder writes, campaign/coupon writes, and notification tests/templates.

Because this is a static documentation audit only, runtime environment behavior was not verified. Assumption: unless enabled elsewhere outside the reviewed files, CSRF is not enforced globally.

### Secure headers

`secureheaders` exists as an alias, but it is not enabled in global filters. No route-level `secureheaders` filter usage is visible in `Routes.php`.

## Authentication Flow

- `GET /login` renders the login form through `Login@index`.
- `POST /login/auth` submits credentials to `Login@auth`.
- `Login@auth` calls `AuthService::attemptLogin`.
- On successful login, the controller stores:
  - `isLoggedIn`
  - `user`
  - `user_id`
  - `role`
  - `permissions`
- The session `user` array includes id, role, email, and name.
- Admin and secretary users are redirected to `admin/dashboard`.
- Normal users are redirected to `dashboard_anasayfa`.
- Failed login attempts are tracked in session with temporary wait-time behavior.

User identity is retrieved mainly from session:

- `session()->get('user')`
- `session()->get('user_id')`
- `session()->get('role')`
- `session()->get('permissions')`

Routes requiring authentication:

- The root auth group in `Routes.php` uses `filter => auth`.
- Protected routes in that group:
  - `/dashboard_anasayfa`
  - `/products`
  - `/orders`
  - `/orders/create`
- Admin route groups use `role:*`, `perm:*`, or `campaign_access`; these filters also rely on session user state and redirect to login when needed.

## Authorization & RBAC Enforcement

Permission checks happen in multiple layers:

- Route-level filters:
  - `AuthFilter` checks `isLoggedIn`.
  - `RoleFilter` checks role and optional `perm:*`.
  - `PermissionFilter` delegates to `RoleFilter`.
  - `CampaignAccessFilter` performs separate campaign/coupon permission checks.
- Controller-level checks:
  - `Admin\Orders` uses internal checks such as `canManageOrders()` in some methods.
  - `StockMove@store` includes an additional role check for `manuel_duzeltme`.
- Service/model-level behavior:
  - `UserPermissionModel` provides effective permission and allow/deny checks.

RBAC is not fully centralized.

- Most admin+secretary route permissions are centralized through `RoleFilter`.
- Campaign/coupon access is enforced separately through `CampaignAccessFilter`.
- Some sensitive controller actions add internal checks.

Deny-by-default status:

- Public routes are explicitly defined without filters.
- Admin routes are grouped behind role/permission filters.
- No global deny-by-default filter is visible.
- Assumption: Deny-by-default is applied by route grouping discipline, not by a global security policy.

## Route Protection Audit

### Admin routes

Admin routes are generally protected.

- Admin-only routes use `role:admin`.
- Admin+secretary routes use `role:admin,secretary|perm:*`.
- Campaign/coupon routes use `campaign_access`.

Risk:

- Some admin-only areas lack granular operation permissions, such as marketing, pricing, automation, settings, page builder, dashboard builder, and banners.

### Secretary routes

Secretary routes are permission-based for the main operational areas:

- Dashboard: `manage_dashboard`
- Stock: `manage_stock`
- Notifications: `manage_notifications`
- Customers: `manage_customers`
- Products/categories/authors: `manage_products`
- Shipping and shipping automation: `manage_shipping`
- Orders: `manage_orders`

Risk:

- Campaign/coupon routes are not secretary-permission based; `CampaignAccessFilter` requires admin role.

### User account routes

Visible user-authenticated routes:

- `/dashboard_anasayfa`
- `/products`
- `/orders`
- `/orders/create`

Missing or not visible:

- Account/profile routes
- User order history route separate from admin redirect
- Runtime cart route
- Checkout route
- Favorites / Wishlist route
- Review route

### Public routes

Public routes are clearly separated:

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

Risk:

- `/logout` is public GET.
- Login/register POST routes are public by design, but CSRF does not appear globally enabled.

## Filter Coverage Matrix

| Area | Route Group | Applied Filter | Expected Protection | Status | Notes |
|------|-------------|----------------|---------------------|--------|-------|
| Public storefront | `/`, `/yardim/*`, public product routes | None | Public access | OK | Public product browsing appears intentional |
| Auth forms | `/login`, `/register` | None | Public access | OK | Forms must be public |
| Auth POST | `/login/auth`, `/register/save` | None | Public access + CSRF | Needs Review | CSRF appears globally disabled |
| Logout | `/logout` | None | Authenticated session action | Weak | Public GET route destroys session |
| User dashboard | `/dashboard_anasayfa` | `auth` | Logged-in user | OK | Uses `AuthFilter` |
| User product/order auth group | `/products`, `/orders`, `/orders/create` | `auth` | Logged-in user | OK / Needs Review | `/orders` redirects to admin orders |
| Admin-only tools | `/admin/pages*`, `/admin/dashboard-builder*`, `/admin/settings*`, `/admin/banners*` | `role:admin` | Admin only | OK | No secretary access |
| Admin dashboard | `/admin/dashboard` | `role:admin,secretary|perm:manage_dashboard` | Admin or secretary with permission | OK | Main admin/secretary dashboard |
| Secretary operations | `/admin/products*`, `/admin/orders*`, `/admin/shipping*`, `/admin/stock*`, `/admin/notifications*`, `/admin/customers` | `role:admin,secretary|perm:*` | Admin or secretary with matching permission | OK | Main RBAC path |
| Campaign/coupon | `/admin/campaigns*`, `/admin/coupons*` | `campaign_access` | Admin with campaign permission | OK / Needs Review | Different RBAC model; secretary blocked |
| Pricing | `/admin/pricing*` | `role:admin` | Admin only | Missing Permission | No granular pricing permission visible |
| Marketing overview | `/admin/marketing` | `role:admin` | Admin only | Missing Permission | No granular marketing permission visible |
| Automation page | `/admin/automation` | `role:admin` | Admin only | Needs Review | Domain and sensitivity unclear |
| Admin write APIs | Multiple POST routes under `/admin/*` | Role/permission filters | Auth + authorization + CSRF | Weak | Authorization exists; CSRF not globally visible |
| Secure headers | All routes | Not globally active | Security headers | Weak | `secureheaders` alias exists but is not enabled globally |

## Critical Security Checks

1. Is CSRF protection enabled globally?
   - No active global CSRF filter is visible in `app/Config/Filters.php`.

2. Are there routes that bypass CSRF?
   - Assumption: Yes. All POST routes bypass CSRF unless CSRF is enabled elsewhere outside the reviewed files.

3. Is `/logout` protected properly?
   - Needs Review. It is a public GET route with no `auth` filter.

4. Are admin routes protected by authentication AND permission?
   - Mostly yes. `role:*` and `perm:*` filters check session user state and role/permission.
   - Some admin-only routes use role-only protection without granular permission.

5. Can secretary access unauthorized areas?
   - Route-level filters should block secretary users from `role:admin` areas.
   - Secretary can access shared operational routes only with matching `manage_*` permissions.
   - Campaign/coupon routes block secretary by role through `campaign_access`.

6. Are there routes missing filters?
   - Public storefront/auth form routes intentionally have no filters.
   - `/logout` is the main auth-related public route that needs review.
   - Runtime cart/account/favorites/review routes are not visible, so protection cannot be assessed.

7. Are there duplicate or conflicting filters?
   - The `auth` alias is duplicated in `Filters.php`.
   - `PermissionFilter` delegates to `RoleFilter`.
   - `CampaignAccessFilter` implements a separate RBAC path, which is functionally different from `RoleFilter`.

8. Is permission enforcement done in a single layer?
   - No. Enforcement is scattered across route filters and controller-internal checks.

9. Are sensitive actions protected (order, payment, user management)?
   - Order actions are route-protected by `manage_orders`, with some internal checks.
   - User/secretary permission management is admin-only.
   - Payment routes are not visible.
   - Cart/checkout runtime routes are not visible.

10. Are builder/admin tools exposed unintentionally?
   - Page builder and dashboard builder routes are admin-only.
   - Dashboard viewing is shared with secretary through `manage_dashboard`.
   - No public builder route is visible.

## Detected Vulnerabilities

- Issue: Global CSRF protection is not enabled in the reviewed filter config.
  - Affected area: All POST routes, including auth, admin writes, orders, products, shipping, page builder, dashboard builder, campaigns, coupons, and notifications.
  - Cause: `csrf` is only registered as an alias and is commented out in global filters.
  - Risk level: High
  - Suggested next analysis: CSRF route-by-route impact audit.

- Issue: `/logout` is a public GET route.
  - Affected area: Auth.
  - Cause: Route is defined outside the `auth` group and has no filter.
  - Risk level: Medium
  - Suggested next analysis: Session lifecycle and logout behavior audit.

- Issue: Secure headers are not globally enabled.
  - Affected area: All web responses.
  - Cause: `secureheaders` alias exists but is not active in global `after` filters.
  - Risk level: Medium
  - Suggested next analysis: HTTP response hardening audit.

- Issue: Permission enforcement is scattered.
  - Affected area: RBAC, Campaign/Coupon, Orders, Stock.
  - Cause: Checks happen in `RoleFilter`, `CampaignAccessFilter`, and selected controller methods.
  - Risk level: Medium
  - Suggested next analysis: RBAC centralization and policy consistency audit.

- Issue: Some admin-only areas do not use granular permissions.
  - Affected area: Marketing, pricing, automation, settings, page builder, dashboard builder, banners.
  - Cause: Routes use `role:admin` only.
  - Risk level: Medium
  - Suggested next analysis: Admin permission granularity audit.

- Issue: Campaign/coupon access uses a different authorization model.
  - Affected area: Campaign / Coupon / Secretary Access.
  - Cause: `campaign_access` requires role `admin`, unlike shared `role:admin,secretary|perm:*` route groups.
  - Risk level: Medium
  - Suggested next analysis: Campaign/coupon access policy audit.

- Issue: Runtime cart, checkout, account, favorites, and review route protection cannot be assessed.
  - Affected area: Frontend Storefront / Cart / Favorites / Review.
  - Cause: Routes are not visible in `Routes.php`.
  - Risk level: Medium
  - Suggested next analysis: Frontend user flow gap analysis.

- Issue: `/orders` redirects authenticated users to admin orders.
  - Affected area: User / Order.
  - Cause: `OrderController@index` redirects to `admin/orders`.
  - Risk level: Low to Medium
  - Suggested next analysis: User order flow audit.

## Assumptions

- Assumption: CSRF is not enforced unless configured elsewhere outside the reviewed source files.
- Assumption: Public storefront routes are intentionally public.
- Assumption: Admin route groups rely on route-level filters as the primary access boundary.
- Assumption: `campaign_access` intentionally blocks secretary users unless a future policy says otherwise.
- Assumption: Missing cart/checkout/favorites/review runtime routes mean those flows are not yet implemented at route level.
- Assumption: JSON, redirect, file, and PDF responses do not require a related view.

## Risks

- Static filter review cannot prove runtime environment overrides.
- Missing global CSRF enforcement affects many POST routes if no external protection exists.
- Public GET logout can be triggered without an authenticated route guard.
- Role-only admin routes may be acceptable policy, but they are less granular than the secretary permission model.
- Missing runtime routes for cart, checkout, account, favorites, and review prevent complete user-flow security assessment.

## Summary

- Secure areas:
  - Admin and secretary operational route groups are generally protected by role/permission filters.
  - Page builder and dashboard builder tools are admin-only.
  - Public storefront routes are separated from authenticated user/admin routes.
  - Order management routes use `manage_orders` and some additional controller checks.

- Weakly protected areas:
  - POST routes due to missing visible global CSRF enforcement.
  - `/logout` because it is public GET.
  - Admin-only but permission-light areas such as pricing, marketing, automation, settings, banners, and builders.
  - Campaign/coupon authorization because it follows a separate filter policy.

- Missing protections:
  - Global CSRF filter is not active in the reviewed config.
  - Global secure headers filter is not active in the reviewed config.
  - Runtime protection for cart/checkout/account/favorites/review cannot be evaluated because routes are not visible.

- Highest risk vulnerabilities:
  - CSRF not globally enabled for many state-changing routes.
  - Public GET logout.
  - Scattered permission enforcement.
  - Missing visible route protection for future/expected frontend user flows.
  - Role-only admin areas without granular permissions.

- Immediate priorities (analysis only, no fixes):
  - Map every POST route against CSRF exposure.
  - Audit session lifecycle and logout behavior.
  - Build an RBAC policy consistency matrix for `RoleFilter` vs `CampaignAccessFilter` vs controller checks.
  - Audit admin-only areas for required granular permissions.
  - Map missing frontend runtime routes for cart, checkout, account, favorites, and reviews.

## Last Normalization Notes

- Normalized to the KB claim schema on 2026-04-24.
- Added Scope, Source of Truth, Key Claims, Related Files, Risks, and normalization notes.
- This file remains a security audit; route drift evidence now belongs in `06_route_baseline.md`.
