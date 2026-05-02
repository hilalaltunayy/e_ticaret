# 48 Filter Behavior Review

## Purpose

Review whether the current filter setup correctly enforces authentication, role checks, permission checks, and CSRF protection before moving to controller review.

This review is read-only. It does not modify application code, runtime code, routes, filters, Docker files, `.env`, or secrets.

## Sources Reviewed

- `app/Config/Filters.php`
- `app/Config/Routes.php`
- `app/Filters/RoleFilter.php`
- `app/Filters/PermissionFilter.php`
- `app/Filters/AuthFilter.php`
- `app/Database/Seeds/InitialAuthSeeder.php`

## Question Review

| Question | Result | Evidence | Notes |
|----------|--------|----------|-------|
| 1. Does role filter actually block users without required role? | PASS | `app/Filters/RoleFilter.php:14-18` checks session user and redirects to login when missing. `app/Filters/RoleFilter.php:21-26` requires user id and role. `app/Filters/RoleFilter.php:56-58` denies when current role is not in allowed roles. `app/Filters/RoleFilter.php:79-96` returns 403 response for denied access. | RoleFilter enforces login presence and role membership before allowing access. |
| 2. Does permission filter actually block users without required permission? | PASS | `app/Filters/PermissionFilter.php:13-16` forwards `perm:<permission>` into `RoleFilter`. `app/Filters/RoleFilter.php:60-69` checks required permission and denies if `UserPermissionModel::isAllowed()` returns false. | PermissionFilter delegates to RoleFilter permission logic. Admin bypass exists in `RoleFilter.php:61-63`. |
| 3. Does combined route syntax like `role:admin,secretary|perm:manage_orders` work as expected? | PASS | `app/Filters/RoleFilter.php:31-44` flattens arguments, splits by `|`, parses `role:` and `perm:` sections. `app/Filters/RoleFilter.php:47-52` splits comma-separated roles. | Static code supports the combined syntax used by route groups. |
| 4. Is CSRF globally enabled? | FAIL | `app/Config/Filters.php:38` defines the `csrf` alias. `app/Config/Filters.php:86-91` shows global `before` filters with `// 'csrf',` commented out at line 89. | CSRF alias exists but is not globally enabled in reviewed config. |
| 5. Are `admin/orders` routes protected correctly? | PASS | `app/Config/Routes.php:202` applies `role:admin,secretary|perm:manage_orders`. `app/Config/Routes.php:204-205` maps order routes. `app/Database/Seeds/InitialAuthSeeder.php:25` creates `manage_orders`; lines 31 and 36 assign it to admin and secretary roles. | Based on route filter, RoleFilter parsing, and seed evidence, admin/orders is protected by role and `manage_orders`. |
| 6. Is `admin/dashboard` protected correctly, considering `manage_dashboard` may be missing in seed data? | FAIL | `app/Config/Routes.php:123` applies `role:admin,secretary|perm:manage_dashboard`. `app/Config/Routes.php:124` maps `admin/dashboard`. Seed search found only `manage_orders` among dashboard/order terms; no `manage_dashboard` or `view_dashboard` seed entry was found. | Route has a permission filter, but the referenced permission appears missing from initial seed data, so non-admin secretary access may fail by default. |
| 7. Are there blockers before controller review? | FAIL | CSRF global protection is disabled in `app/Config/Filters.php:86-91`; `manage_dashboard` seed evidence is missing while route requires it at `app/Config/Routes.php:123`. | Filter behavior is now understood enough to identify blockers. Resolve or explicitly accept these before controller review. |

## Evidence Highlights

### Authentication Requirement

- `app/Filters/AuthFilter.php:17-19` redirects to login when `isLoggedIn` session is missing.
- `app/Filters/RoleFilter.php:14-18` redirects to login when session `user` is missing.
- `app/Filters/RoleFilter.php:24-26` redirects to login when user id or role is missing.

### Role And Permission Enforcement

- `app/Filters/RoleFilter.php:31-44` parses combined role and permission arguments.
- `app/Filters/RoleFilter.php:56-58` denies users outside allowed roles.
- `app/Filters/RoleFilter.php:60-69` checks required permission for non-admin roles.
- `app/Filters/PermissionFilter.php:13-16` delegates permission checks to RoleFilter.

### Route Protection

- `app/Config/Routes.php:123`: `admin/dashboard` group uses `role:admin,secretary|perm:manage_dashboard`.
- `app/Config/Routes.php:124`: `admin/dashboard` maps to `Admin\DashboardController::index`.
- `app/Config/Routes.php:202`: `admin/orders` group uses `role:admin,secretary|perm:manage_orders`.
- `app/Config/Routes.php:204`: `admin/orders` maps to `Admin\Orders::index`.

### Seed Evidence

- `app/Database/Seeds/InitialAuthSeeder.php:25`: creates `manage_orders`.
- `app/Database/Seeds/InitialAuthSeeder.php:31`: assigns `manage_orders` to admin role.
- `app/Database/Seeds/InitialAuthSeeder.php:36`: assigns `manage_orders` to secretary role.
- No reviewed seed evidence was found for `manage_dashboard` or `view_dashboard`.

### CSRF Evidence

- `app/Config/Filters.php:38`: `csrf` alias exists.
- `app/Config/Filters.php:89`: global `before` CSRF entry is commented out.

## Risks

- `manage_dashboard` is required by route filter but appears absent from seed data, which can block non-admin dashboard access by default.
- Admin bypasses permission checks in `RoleFilter.php:61-63`, so missing permission seed data may not affect admin but can affect secretary.
- CSRF is not globally enabled, increasing risk for state-changing POST routes unless CSRF is enforced elsewhere.
- Permission enforcement relies on `UserPermissionModel::isAllowed()`, so role/user permission merge behavior should be reviewed next if access anomalies appear.
- Combined route syntax works statically, but controller-level assumptions may still introduce gaps.

## Recommended Fixes If Needed

- Add or confirm seed coverage for `manage_dashboard` or adjust dashboard route permission naming to match seeded permissions.
- Decide whether CSRF should be globally enabled or intentionally route-specific.
- Document the admin permission bypass in RoleFilter as an explicit policy decision.
- Confirm `UserPermissionModel::isAllowed()` behavior before relying on user-specific overrides.

## Recommended Next Action

- Do not move directly to controllers yet.
- First review `UserPermissionModel::isAllowed()` and permission seed coverage for dashboard-related permissions.
- After permission data behavior is clear, review `Admin\DashboardController` and `Admin\Orders` controllers.

## Final Decision

Ready to move to controllers? NO
