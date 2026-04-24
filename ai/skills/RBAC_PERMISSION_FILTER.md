# RBAC Permission Filter Skill

This skill defines how to enforce role-based access control securely.

## Purpose

Prevent unauthorized access to routes, actions, and data.

## Core Principle

Deny by default.

If a permission is not explicitly granted, access must be denied.

## Layers

### 1. Route / Filter Level
- Apply authentication and permission checks before controller execution
- Separate admin, secretary, and user routes

### 2. Service Level
- Re-check permissions for critical operations
- Protect create, update, delete, publish, approve, cancel, refund actions

### 3. View Level
- Hide buttons and UI elements based on permissions
- NEVER rely on view alone for security

## Rules

- UI hiding is not sufficient for security
- Do not hard-code secretary permissions
- Use user_permissions override system
- Admin has full access
- User can only access their own data
- Always check ownership where required
- Keep permission codes consistent
- Update seeder and filters when adding new permissions

## Manual Test Requirements

- Admin can access page
- Secretary can access if permission exists
- Secretary is blocked if no permission
- User cannot access admin/secretary pages
- Guest cannot access protected pages
- Direct URL access is also blocked

## Example Permissions

- view_dashboard
- manage_dashboard
- manage_pages
- manage_products
- manage_orders
- manage_shipping
- manage_reviews
- delete_reviews
- manage_users
- manage_secretary
- manage_favorites
- manage_own_orders
- manage_account
