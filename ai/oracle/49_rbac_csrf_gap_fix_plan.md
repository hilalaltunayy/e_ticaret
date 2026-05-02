# 49 RBAC CSRF Gap Fix Plan

## Purpose

Plan minimal fixes for the blockers found in `48_filter_behavior_review.md`.

This is a planning document only. It does not modify application code, routes, filters, seeders, models, services, controllers, Docker files, `.env`, or secrets.

## Blocker 1: `manage_dashboard` Missing From Seed Evidence

### Exact Suspected Cause

`admin/dashboard` is protected by a route-level permission filter that references `manage_dashboard`, but the reviewed seed data does not create or assign `manage_dashboard`.

Evidence:

- `app/Config/Routes.php:123`: `role:admin,secretary|perm:manage_dashboard`
- `app/Config/Routes.php:124`: `admin/dashboard` maps to `Admin\DashboardController::index`
- `app/Database/Seeds/InitialAuthSeeder.php:24-28`: creates `manage_products`, `manage_orders`, `manage_shipping`, `manage_campaigns`, and `manage_campaigns_engine`
- `app/Database/Seeds/InitialAuthSeeder.php:30-36`: assigns existing permissions to admin and secretary roles
- Search evidence found `manage_orders` but not `manage_dashboard` or `view_dashboard`

Filter behavior impact:

- `app/Filters/RoleFilter.php:60-69` enforces required permissions for non-admin roles.
- `app/Filters/RoleFilter.php:61-63` bypasses permission checks for admin.
- `app/Filters/PermissionFilter.php:13-16` delegates permission checks to `RoleFilter`.

Result:

- Admin may still pass because RoleFilter bypasses permissions for role `admin`.
- Secretary may fail `admin/dashboard` unless `UserPermissionModel::isAllowed()` grants `manage_dashboard` through another path not reviewed here.

### Exact Files That Would Need Modification Later

Minimal likely file:

- `app/Database/Seeds/InitialAuthSeeder.php`

Possible alternative file, only if policy decides to change route permission naming:

- `app/Config/Routes.php`

### Minimal Safe Fix Option

Preferred minimal fix:

- Add `manage_dashboard` to `InitialAuthSeeder`.
- Assign `manage_dashboard` to the admin role.
- Decide whether secretary should receive `manage_dashboard` by default:
  - If secretary should access `admin/dashboard`, assign `manage_dashboard` to secretary role.
  - If secretary should not access dashboard by default, remove `secretary` from the route filter or keep secretary excluded through missing permission and document the policy.

Conservative option:

- Keep route permission name unchanged and seed the missing permission.
- Avoid changing route behavior unless access policy requires it.

### Risk Of Not Fixing

- Secretary dashboard access may fail despite the route filter naming secretary as an allowed role.
- Documentation and runtime behavior remain inconsistent.
- Controller review may misinterpret dashboard access because the route suggests permission-based access that seed data does not support.
- Future KB/GitNexus automation may repeatedly flag `manage_dashboard` as a drift/gap item.

### Test Steps After Fix

After implementation in a later task:

1. Inspect `InitialAuthSeeder.php` and confirm `manage_dashboard` is created.
2. Confirm `manage_dashboard` is assigned to the intended roles.
3. Run the Oracle runtime `permission_lookup` query:
   - `manage_dashboard`
   - `admin/dashboard`
   - `secretary`
4. Confirm route-level evidence still shows `app/Config/Routes.php:123`.
5. Confirm filter behavior still parses `role:admin,secretary|perm:manage_dashboard`.
6. If an application-level manual test is approved later, verify dashboard access as admin and secretary according to the intended policy.

### Required Before Controller Review?

YES.

Controller review should wait until dashboard permission policy is either fixed or explicitly accepted as intentional.

## Blocker 2: CSRF Global Protection Disabled

### Exact Suspected Cause

CSRF alias exists, but the global `before` CSRF filter is commented out.

Evidence:

- `app/Config/Filters.php:9`: imports `CodeIgniter\Filters\CSRF`
- `app/Config/Filters.php:38`: defines `csrf` alias
- `app/Config/Filters.php:86-91`: global `before` filters show `// 'csrf',` commented out at line 89

Result:

- CSRF is not globally enforced through the reviewed `Filters.php` config.
- State-changing POST routes may rely on another protection layer or may be unprotected.

### Exact Files That Would Need Modification Later

Minimal likely file:

- `app/Config/Filters.php`

Optional follow-up files if global CSRF breaks known flows:

- Views/forms that perform POST requests, only if missing CSRF tokens are discovered in a later review.

### Minimal Safe Fix Option

Preferred minimal fix:

- Enable `csrf` in the global `before` filters in `app/Config/Filters.php`.
- Keep any required exceptions explicit and narrow if some endpoints must bypass CSRF.

Conservative staged option:

- First review POST forms and API-like POST endpoints for CSRF token readiness.
- Then enable CSRF globally with documented exceptions.

### Risk Of Not Fixing

- POST routes such as admin page builder, order updates, campaign/coupon operations, product changes, and auth-related actions may be exposed to CSRF risk unless protected elsewhere.
- Security review remains incomplete.
- Future controller review may identify many POST actions but cannot safely reason about request-level protection.

### Test Steps After Fix

After implementation in a later task:

1. Inspect `app/Config/Filters.php` and confirm `csrf` is enabled in global `before`.
2. Run Oracle runtime `filter_lookup` query:
   - `csrf`
   - `before`
   - `globals`
3. Confirm `csrf` is no longer only a commented global entry.
4. Manually test login, logout, admin POST actions, and storefront POST actions if approved.
5. Verify any CSRF exceptions are explicit and narrow.

### Required Before Controller Review?

YES.

Controller review can identify POST actions, but security conclusions should wait until CSRF policy is fixed or explicitly accepted as intentionally disabled.

## Combined Minimal Fix Sequence

Recommended order for a later implementation task:

1. Fix or explicitly decide dashboard permission policy.
2. Fix or explicitly decide CSRF global protection policy.
3. Re-run read-only Oracle lookup checks:
   - `permission_lookup("manage_dashboard")`
   - `permission_lookup("admin/dashboard")`
   - `filter_lookup("csrf")`
   - `filter_lookup("before")`
4. Create a post-fix validation report.
5. Only then proceed to controller review.

## Files To Avoid Modifying Unless Separately Approved

- `app/Filters/RoleFilter.php`
- `app/Filters/PermissionFilter.php`
- Controllers
- Models
- Services
- Views
- Routes, unless permission naming policy is intentionally changed

The current blockers appear fixable with seed/config changes only.

## Final Decision

Ready for RBAC/CSRF fix implementation? YES
