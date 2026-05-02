# 52 Revert RBAC CSRF Gap Fix

## Purpose

Document the minimal revert of changes made in `50_rbac_csrf_gap_fix_implementation.md`.

This revert restores the application files to the pre-fix state for the RBAC/CSRF changes that preceded the reported `admin@site.com` 403 issue.

## Files Changed

- `app/Database/Seeds/InitialAuthSeeder.php`
- `app/Config/Filters.php`

## Exact Lines/Blocks Reverted

### `app/Database/Seeds/InitialAuthSeeder.php`

Removed the permission creation line added by the previous fix:

```php
$permManageDashboardId = $this->firstOrCreatePerm($perms, 'manage_dashboard', 'Manage dashboard');
```

Removed the admin role permission grant added by the previous fix:

```php
$this->firstOrCreateRolePerm($rp, $roleAdminId, $permManageDashboardId);
```

### `app/Config/Filters.php`

Restored global CSRF setting to the previous commented state:

```php
// 'csrf',
```

## Scope Confirmation

No changes were made to:

- `app/Config/Routes.php`
- Filter classes
- Controllers
- Services
- Models
- Migrations
- Docker files
- Oracle runtime tools
- `.env` or secrets

## Validation Commands Only

Recommended manual validation commands:

```text
php -l app/Database/Seeds/InitialAuthSeeder.php
php -l app/Config/Filters.php
```

Recommended manual application check:

- Log out and log back in as `admin@site.com`.
- Re-test `/admin/dashboard`.

No DB command, Docker command, or application run was executed by this revert.

## Final Decision

Ready for manual restore validation? YES
