# 45 Admin Secretary Access Review

## Purpose

Review admin and secretary access evidence before moving to controller-level analysis.

This review is read-only. It does not modify application code, routes, filters, seeders, services, models, controllers, Docker files, or runtime tools.

## Sources Reviewed

- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `app/Database/Seeds/InitialAuthSeeder.php`
- `app/Services/AuthService.php`
- Existing Oracle runtime `permission_lookup` output

## Question Review

| Question | Result | Evidence | Notes |
|----------|--------|----------|-------|
| 1. Can admin access `admin/dashboard`? | UNCLEAR | `app/Config/Routes.php:123` uses `role:admin,secretary|perm:manage_dashboard`; `app/Config/Routes.php:124` maps `admin/dashboard` to `Admin\DashboardController::index`. | The route includes `admin`, but `manage_dashboard` was not found in `InitialAuthSeeder.php`. Actual result depends on filter semantics. |
| 2. Is `admin/dashboard` protected by the correct auth/filter/permission? | UNCLEAR | `app/Config/Routes.php:123` applies `role:admin,secretary|perm:manage_dashboard`; `app/Config/Filters.php:34-37` defines `auth`, `role`, `perm`, and `campaign_access` aliases. | Route-level RBAC is present, but there is no explicit `auth` filter on this route group and `manage_dashboard` seed evidence is missing. |
| 3. Can secretary access `admin/dashboard`? | UNCLEAR | `app/Config/Routes.php:123` includes `secretary` and `perm:manage_dashboard`; no `manage_dashboard` seed or secretary assignment was found in `InitialAuthSeeder.php`. | Secretary is named in the route filter, but default permission evidence is missing. |
| 4. Can secretary access `admin/orders`? | PASS | `app/Config/Routes.php:202` applies `role:admin,secretary|perm:manage_orders`; `app/Config/Routes.php:204` maps `admin/orders`; `app/Database/Seeds/InitialAuthSeeder.php:21` creates `secretary`; `app/Database/Seeds/InitialAuthSeeder.php:36` assigns `manage_orders` to secretary role. | Based on route and seed evidence, secretary has default role permission for order management. |
| 5. Which permission protects `admin/orders`? | PASS | `app/Config/Routes.php:202` uses `perm:manage_orders`. | `manage_orders` protects the admin order route group. |
| 6. Does `manage_orders` exist in seed data? | PASS | `app/Database/Seeds/InitialAuthSeeder.php:25` creates `manage_orders`. | Permission exists in seed data. |
| 7. Is secretary granted `manage_orders` by default, or only via user-specific permission? | PASS | `app/Database/Seeds/InitialAuthSeeder.php:21` creates secretary role; `app/Database/Seeds/InitialAuthSeeder.php:36` assigns `manage_orders` to `$roleSecId`. | Evidence shows default role-level assignment, not only user-specific permission. |
| 8. Are there obvious RBAC gaps before moving to controllers? | PASS WITH RISKS | `manage_dashboard` is referenced in `app/Config/Routes.php:123` but was not found in seed evidence; `app/Config/Filters.php:86-91` has no global `auth` or `csrf` enabled in `before`. | There are obvious items to review before claiming full RBAC correctness. |

## Evidence Details

### Route Evidence

- `app/Config/Routes.php:123`: `$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_dashboard'], function ($routes) {`
- `app/Config/Routes.php:124`: `$routes->get('dashboard', 'Admin\DashboardController::index');`
- `app/Config/Routes.php:202`: `$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_orders'], function ($routes) {`
- `app/Config/Routes.php:204`: `$routes->get('orders', 'Admin\Orders::index');`

### Filter Alias Evidence

- `app/Config/Filters.php:34`: `auth` alias maps to `\App\Filters\AuthFilter::class`.
- `app/Config/Filters.php:35`: `role` alias maps to `\App\Filters\RoleFilter::class`.
- `app/Config/Filters.php:36`: `perm` alias maps to `\App\Filters\PermissionFilter::class`.
- `app/Config/Filters.php:86-91`: global `before` filters do not show enabled `auth`, `csrf`, or `invalidchars`.

### Seed Evidence

- `app/Database/Seeds/InitialAuthSeeder.php:20`: creates `admin` role.
- `app/Database/Seeds/InitialAuthSeeder.php:21`: creates `secretary` role.
- `app/Database/Seeds/InitialAuthSeeder.php:25`: creates `manage_orders`.
- `app/Database/Seeds/InitialAuthSeeder.php:31`: assigns `manage_orders` to admin role.
- `app/Database/Seeds/InitialAuthSeeder.php:36`: assigns `manage_orders` to secretary role.

No seed evidence was found for:

- `manage_dashboard`
- `view_dashboard`

## Risks

- `manage_dashboard` is referenced by the `admin/dashboard` route group but was not found in seed data reviewed here.
- `admin/dashboard` and `admin/orders` route filters use combined syntax such as `role:admin,secretary|perm:manage_orders`; actual enforcement depends on filter implementation details that were not reviewed in this controller-prep step.
- No explicit route-level `auth` alias is shown for the reviewed admin dashboard/order route groups. Authentication may be enforced inside role/permission filters, but that requires filter class review.
- Global `auth` and `csrf` are not enabled in `app/Config/Filters.php:86-91`.
- `manage_reviews` and `view_dashboard` were not found in the reviewed seed evidence, so review/dashboard permission coverage may be incomplete or named differently.

## Recommended Next Action

- Move to filter/controller-level review before making access guarantees.
- Review `app/Filters/RoleFilter.php` and `app/Filters/PermissionFilter.php` next to confirm how combined `role:...|perm:...` syntax is enforced.
- Then review `Admin\DashboardController` and `Admin\Orders` controller-level checks for additional RBAC or assumptions.

## Final Decision

Ready to move to controllers? NO

Move to filter implementation review first, then controllers.
