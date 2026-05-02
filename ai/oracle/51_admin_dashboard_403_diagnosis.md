# 51 Admin Dashboard 403 Diagnosis

## Purpose

Diagnose why `admin@site.com` still receives `403` on `/admin/dashboard` after:

- `manage_dashboard` was added to `InitialAuthSeeder`.
- `manage_dashboard` was granted to admin role only.
- `php spark db:seed InitialAuthSeeder` was run successfully.

This diagnosis is read-only. It does not modify code, database state, routes, filters, sessions, `.env`, Docker files, or runtime tools.

## Likely Cause

The most likely cause is that the current logged-in session user is not carrying role `admin`, or the existing `admin@site.com` database row does not have role `admin`.

Why this is most likely:

- `/admin/dashboard` uses `role:admin,secretary|perm:manage_dashboard`.
- `RoleFilter` allows role `admin` to bypass permission checks.
- Therefore, if the session role is exactly `admin`, missing or newly added `manage_dashboard` should not cause a 403 for admin.
- `InitialAuthSeeder` only inserts `admin@site.com` when the user does not already exist. It does not update an existing admin user row.
- An old session can still contain stale or wrong role/user data until logout/login or session clear.

Secondary possible cause:

- If the current user is actually `secretary`, `manage_dashboard` was intentionally not granted to secretary by default, so `admin/dashboard` can still return 403 for secretary.

## Question Review

| Question | Result | Evidence | Notes |
|----------|--------|----------|-------|
| 1. Is `/admin/dashboard` protected by `perm:manage_dashboard` or another permission? | PASS | `app/Config/Routes.php:123` uses `role:admin,secretary|perm:manage_dashboard`; `app/Config/Routes.php:124` maps `dashboard` to `Admin\DashboardController::index`. | It is protected by `manage_dashboard` plus role filter. |
| 2. Is `manage_dashboard` actually inserted with the same code expected by the route? | PASS | `app/Database/Seeds/InitialAuthSeeder.php:26` creates `manage_dashboard`. | Code matches route permission name exactly. |
| 3. Is `admin@site.com` assigned to the admin role in seed data? | PARTIAL | `app/Database/Seeds/InitialAuthSeeder.php:40-48` inserts `admin@site.com` with role `admin` only if the user does not already exist. | Existing `admin@site.com` rows are not updated by this seeder. DB row must be checked manually. |
| 4. Does the login/session code load role permissions correctly? | PASS WITH NOTE | `app/Controllers/Login.php:37-54` loads user id, lowercased role, effective permissions, and stores session `user`, `user_id`, `role`, and `permissions`. `app/Services/AuthService.php:26-35` returns the user row from `UserModel::findByEmail()`. | Login loads role from current DB user row. Existing wrong DB role would become wrong session role. |
| 5. Does `PermissionFilter` read permissions from session, DB, or both? | DB VIA ROLEFILTER | `app/Filters/PermissionFilter.php:13-16` delegates to `RoleFilter`. `app/Filters/RoleFilter.php:65-66` creates `UserPermissionModel` and calls `isAllowed()`. `app/Models/UserPermissionModel.php:111-112` evaluates effective DB permissions. | Permission checks use DB-backed permission logic, not session permissions directly. Admin bypass happens before DB check. |
| 6. Is there a naming mismatch like `admin` vs `administrator`, `manage_dashboard` vs `view_dashboard`? | PARTIAL | `manage_dashboard` route and seed match exactly. `admin` seed insert uses role `admin`. `RoleFilter.php:21` lowercases session role and compares to allowed roles. | No code mismatch for `manage_dashboard`; possible DB data mismatch remains for existing admin role value. |
| 7. Is old session still possible? | PASS | `Login.php:45-54` sets session data only during login. `RoleFilter.php:15` reads session `user`. | If the user stayed logged in before seed/data changes, stale session role/user data is possible. Logout/login or clearing session is a safe first validation. |
| 8. What is the most likely cause of 403? | LIKELY DATA/SESSION | `RoleFilter.php:56-58` returns 403 when role is not in allowed roles; `RoleFilter.php:61-63` lets exact `admin` bypass permission checks. | Most likely: session role is not `admin`, existing admin DB row role is not `admin`, or user is actually secretary without `manage_dashboard`. |

## Evidence Details

### Route Evidence

- `app/Config/Routes.php:123`: `$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_dashboard'], function ($routes) {`
- `app/Config/Routes.php:124`: `$routes->get('dashboard', 'Admin\DashboardController::index');`

### Seed Evidence

- `app/Database/Seeds/InitialAuthSeeder.php:26`: creates `manage_dashboard`.
- `app/Database/Seeds/InitialAuthSeeder.php:33`: grants `manage_dashboard` to admin role.
- `app/Database/Seeds/InitialAuthSeeder.php:40-48`: inserts `admin@site.com` with role `admin` only when no existing admin row is found.

### Filter Evidence

- `app/Filters/RoleFilter.php:15`: reads session `user`.
- `app/Filters/RoleFilter.php:21-22`: extracts role and user id from session user.
- `app/Filters/RoleFilter.php:56-58`: denies with 403 when role is outside allowed roles.
- `app/Filters/RoleFilter.php:60-69`: checks required permission for non-admin roles.
- `app/Filters/RoleFilter.php:61-63`: exact role `admin` bypasses permission check.
- `app/Filters/PermissionFilter.php:13-16`: delegates `perm:<permission>` to `RoleFilter`.

### Login/Session Evidence

- `app/Controllers/Login.php:37-39`: gets user id and lowercased role from DB user row.
- `app/Controllers/Login.php:41-43`: loads effective permissions when user id and role exist.
- `app/Controllers/Login.php:45-54`: writes `isLoggedIn`, `user`, `user_id`, `role`, and `permissions` into session.
- `app/Services/AuthService.php:26-35`: `attemptLogin()` returns the user row from `UserModel`.
- `app/Models/UserModel.php:21-24`: `findByEmail()` returns the first user row for the email.

### Permission Model Evidence

- `app/Models/UserPermissionModel.php:29-40`: admin effective permissions are all permissions.
- `app/Models/UserPermissionModel.php:107-109`: `isAllowed()` returns true for role `admin`.
- `app/Models/UserPermissionModel.php:111-112`: non-admin roles use DB-backed effective permissions.

### Controller Evidence

- `app/Controllers/Admin/DashboardController.php:25-36`: `index()` builds dashboard view and does not show a direct 403 deny path in reviewed lines.
- `app/Controllers/Admin/DashboardController.php:47-52`: reads actor role from session user or session role.

## Exact Safe Fix Recommendation

Do not change code first.

Recommended safe validation/fix sequence:

1. Log out completely and log in again as `admin@site.com`.
2. Clear the browser session/cookies if logout does not reset the session.
3. Verify the database row for `admin@site.com` has `role = 'admin'`, `status = 'active'`, and a non-empty `id`.
4. Verify there is only one active, non-deleted row for `admin@site.com`.
5. Verify the `admin` role row exists in `roles`.
6. Verify `manage_dashboard` exists in `permissions`.
7. Verify `role_permissions` links admin role id to `manage_dashboard` permission id.

If DB row is wrong:

- Minimal safe fix would be a targeted data correction for `admin@site.com` role/status, not a filter or route code change.

If session is stale:

- Minimal safe fix is logout/login or clearing session data.

If the user is actually secretary:

- 403 is expected with the current policy because `manage_dashboard` was not granted to secretary by default.

## Manual Validation Commands

Run these manually in the local environment. Do not paste secrets or `.env` contents.

```text
php spark db:seed InitialAuthSeeder
```

```text
php spark tinker
```

Suggested read-only DB checks inside an approved local DB shell or tinker session:

```php
db_connect()->table('users')->select('id,email,role,status,deleted_at')->where('email', 'admin@site.com')->get()->getResultArray();
```

```php
db_connect()->table('permissions')->select('id,code,deleted_at')->where('code', 'manage_dashboard')->get()->getResultArray();
```

```php
db_connect()->table('roles')->select('id,name,deleted_at')->where('name', 'admin')->get()->getResultArray();
```

```php
db_connect()->table('role_permissions rp')
    ->select('r.name AS role_name, p.code AS permission_code, rp.deleted_at')
    ->join('roles r', 'r.id = rp.role_id', 'inner')
    ->join('permissions p', 'p.id = rp.permission_id', 'inner')
    ->where('r.name', 'admin')
    ->where('p.code', 'manage_dashboard')
    ->get()
    ->getResultArray();
```

Session validation:

- Log out.
- Clear browser cookies/session for the local app if needed.
- Log in again as `admin@site.com`.
- Retry `/admin/dashboard`.

## Risks

- Re-running the seeder does not update existing `admin@site.com` rows because the seeder only inserts when missing.
- Multiple or soft-deleted user rows could make `findByEmail()` return an unexpected row.
- Existing browser session can keep stale role/user data after DB fixes.
- Admin permission bypass means a 403 for admin is a strong signal that current session role is not exact `admin` or user/session data is malformed.

## Final Decision

Ready for 403 fix implementation? YES
