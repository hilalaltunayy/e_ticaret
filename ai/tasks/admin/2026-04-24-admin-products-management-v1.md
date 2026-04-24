# Admin Products Management v1

## Goal

Create or standardize admin product management flow for adding, editing, listing, and disabling products.

## Scope

This task focuses on admin-side product management only.

## Related Skills

- ai/skills/CI4_CONTROLLER_SERVICE_PATTERN.md
- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/SAFE_MIGRATION_AND_SEEDER.md
- ai/skills/AUDIT_LOG_RULES.md
- ai/skills/CODEX_OUTPUT_FORMAT.md

## Current Context

The project supports physical and digital book sales.

Products may include:

- Printed books
- Digital books
- Product images
- Categories
- Authors
- Stock-related fields
- Price-related fields

## Requirements

1. Ensure only admin can manage products.
2. Protect product management routes with permission checks.
3. Use controller-service separation.
4. Allow admin to list products.
5. Allow admin to create products.
6. Allow admin to edit products.
7. Allow admin to disable or archive products instead of hard deleting.
8. Validate all product inputs.
9. Support product image handling if current structure allows it.
10. Log important product changes such as price and stock updates.

## Expected Areas to Check

- app/Config/Routes.php
- app/Config/Filters.php
- app/Controllers/Admin/
- app/Services/ProductsService.php
- app/Models/ProductsModel.php
- app/Models/CategoryModel.php
- app/Models/AuthorModel.php
- app/Views/admin/
- app/Database/Migrations/
- app/Database/Seeds/

## Manual Test Steps

1. Login as admin and open product management page.
2. Create a product with valid data.
3. Try creating a product with invalid data.
4. Edit product name, price, stock, and category.
5. Disable or archive a product.
6. Confirm product changes appear on user product listing if applicable.
7. Confirm secretary/user cannot access product management.
8. Confirm audit log is created for price or stock changes if audit exists.

## Out of Scope

- Full inventory reservation system
- Payment integration
- Shipping logic
- Product recommendation system
- Advanced search engine

## Expected Codex Output

Codex must return output using:

- ai/skills/CODEX_OUTPUT_FORMAT.md
