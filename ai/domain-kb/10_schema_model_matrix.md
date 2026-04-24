# 10 Schema Model Matrix

## Purpose

Map database/migration structures to application models for future drift checks.

This is a baseline, not a full field-level schema diff. It documents model-to-table relationships and migration/seeder domain ownership using static repository evidence.

## Source of Truth

- `app/Models/**`
- `app/Database/Migrations/**`
- `app/Database/Seeds/**`
- `ai/domain-kb/00_repo_inventory.md`
- `ai/domain-kb/01_domain_index.md`
- `ai/domain-kb/08_post_normalization_validation.md`

## Model to Table Matrix

| Model | Expected Table | Evidence Source | Status | Notes |
|------|----------------|----------------|--------|------|
| `AdminNoteModel.php` | `admin_notes` | `app/Models/AdminNoteModel.php`, `2026-02-13-112617_CreateAdminNotesTable.php` | Verified | Admin dashboard/admin panel data. |
| `AdminSettingModel.php` | `admin_settings` | `app/Models/AdminSettingModel.php`, `2026-02-13-112910_CreateAdminSettingsTable.php` | Verified | Admin settings table. |
| `AuditLogModel.php` | `audit_logs` | `app/Models/AuditLogModel.php`, `2026-02-13-112256_CreateAuditLogsTable.php` | Verified | Admin/audit domain. |
| `AuthorModel.php` | `authors` | `app/Models/AuthorModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Verified | Product metadata. |
| `BannerModel.php` | `banners` | `app/Models/BannerModel.php`, `2026-04-10-160000_CreateBannersTable.php` | Verified | Theme / Media. |
| `BlockInstanceModel.php` | `block_instances` | `app/Models/BlockInstanceModel.php`, `2026-04-04-100300_CreateBlockInstancesTable.php` | Verified | Page Builder. |
| `BlockTypeModel.php` | `block_types` | `app/Models/BlockTypeModel.php`, `2026-04-04-100200_CreateBlockTypesTable.php` | Verified | Page Builder. |
| `CampaignModel.php` | `campaigns` | `app/Models/CampaignModel.php`, `2026-03-06-140000_CreateCampaignsModuleTables.php` | Verified | Campaign / Coupon. |
| `CampaignTargetModel.php` | `campaign_targets` | `app/Models/CampaignTargetModel.php`, `2026-03-06-140000_CreateCampaignsModuleTables.php` | Verified | Campaign target mapping. |
| `CategoryModel.php` | `categories` | `app/Models/CategoryModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Verified | Category/Product domain. |
| `CouponModel.php` | `coupons` | `app/Models/CouponModel.php`, `2026-03-06-120000_CreateCouponsModuleTables.php` | Verified | Campaign / Coupon / Cart. |
| `CouponTargetModel.php` | `coupon_targets` | `app/Models/CouponTargetModel.php`, `2026-03-06-120000_CreateCouponsModuleTables.php` | Verified | Coupon target mapping. |
| `CouponRedemptionModel.php` | `coupon_redemptions` | `app/Models/CouponRedemptionModel.php`, `2026-03-06-120000_CreateCouponsModuleTables.php` | Verified | Runtime redemption usage remains needs review. |
| `DashboardModel.php` | `dashboards` | `app/Models/DashboardModel.php`, `2026-03-31-110000_CreateDashboardBuilderTables.php` | Verified | Dashboard Builder. |
| `DashboardBlockTypeModel.php` | `dashboard_block_types` | `app/Models/DashboardBlockTypeModel.php`, `2026-03-31-110000_CreateDashboardBuilderTables.php` | Verified | Dashboard Builder. |
| `DashboardBlockModel.php` | Needs Review | `app/Models/DashboardBlockModel.php`, dashboard migrations | Needs Review | No direct `$table` match was found in static scan; table intent may be inherited or custom. |
| `DashboardBlockInstanceModel.php` | `dashboard_block_instances` | `app/Models/DashboardBlockInstanceModel.php`, `2026-04-08-183414_CreateDashboardBlockInstancesTable.php` | Verified | Dashboard Builder. |
| `InvoiceModel.php` | `invoices` | `app/Models/InvoiceModel.php`, `2026-03-02-100000_AddInvoicesTable.php` | Verified | Order / Invoice. |
| `NotificationDeliveryLogModel.php` | `notification_delivery_logs` | `app/Models/NotificationDeliveryLogModel.php`, `2026-04-10-140000_CreateNotificationDeliveryLogsTable.php` | Verified | Notification/Admin Panel. |
| `NotificationEmailTemplateModel.php` | `notification_email_templates` | `app/Models/NotificationEmailTemplateModel.php`, `2026-04-10-120000_CreateNotificationEmailTemplatesTable.php` | Verified | Notification/Admin Panel. |
| `OrderModel.php` | `orders` | `app/Models/OrderModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php`, order migrations | Verified | Order. |
| `OrderItemModel.php` | `order_items` | `app/Models/OrderItemModel.php`, `2026-02-25-191500_AddOrderManagementSchema.php` | Verified | Order. |
| `OrderLogModel.php` | `order_logs` | `app/Models/OrderLogModel.php`, `2026-02-25-191500_AddOrderManagementSchema.php` | Verified | Order audit/status log. |
| `PackingSessionModel.php` | `packing_sessions` | `app/Models/PackingSessionModel.php`, `2026-03-04-120000_CreatePackingSessionsTable.php` | Verified | Order / Shipping. |
| `PageModel.php` | `pages` | `app/Models/PageModel.php`, `2026-04-04-100000_CreatePagesTable.php` | Verified | Page Builder. |
| `PageVersionModel.php` | `page_versions` | `app/Models/PageVersionModel.php`, `2026-04-04-100100_CreatePageVersionsTable.php` | Verified | Page Builder. |
| `PermissionModel.php` | `permissions` | `app/Models/PermissionModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Verified | RBAC. |
| `PriceRuleModel.php` | `price_rules` | `app/Models/PriceRuleModel.php`, `2026-03-31-100000_CreatePriceRulesTable.php` | Verified | Product / Catalog / Pricing. |
| `ProductsModel.php` | `products` | `app/Models/ProductsModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Verified | Product / Catalog. |
| `RoleModel.php` | `roles` | `app/Models/RoleModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Verified | RBAC. |
| `RoleModels.php` | `roles` | `app/Models/RoleModels.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Needs Review | Duplicate/conflicting role model file risk remains. |
| `RolePermissionModel.php` | `role_permissions` | `app/Models/RolePermissionModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Verified | RBAC pivot. |
| `ShippingAutomationRuleModel.php` | `shipping_automation_rules` | `app/Models/ShippingAutomationRuleModel.php`, `2026-02-27-110000_CreateShippingAutomationRulesTable.php` | Verified | Shipping automation. |
| `ShippingModel.php` | Custom / Needs Review | `app/Models/ShippingModel.php` | Needs Review | Existing KB notes say this is custom data access and not a normal CI Model table mapping. |
| `TypeModel.php` | `types` | `app/Models/TypeModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Verified | Product type metadata. |
| `UserModel.php` | `users` | `app/Models/UserModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Verified | Auth/User. |
| `UserPermissionModel.php` | `user_permissions` | `app/Models/UserPermissionModel.php`, `2026-02-07-104831_CreateInitialSchemaUuid.php` | Verified | Secretary/user permission override. |
| `VisitModel.php` | `visits` | `app/Models/VisitModel.php`, `2026-02-13-111756_CreateVisitsTable.php` | Needs Review | Existing KB notes indicate allowed fields appear narrower than migration fields. |

## Migration to Domain Matrix

| Migration / Seeder | Related Domain | Tables / Seeds | Status | Notes |
|--------------------|----------------|----------------|--------|------|
| `2026-02-07-104831_CreateInitialSchemaUuid.php` | Auth / Product / Catalog / Order / RBAC | `users`, `authors`, `categories`, `types`, `products`, `orders`, `roles`, `permissions`, `role_permissions`, `user_permissions` | Verified | Initial broad schema. |
| `2026-02-13-111756_CreateVisitsTable.php` | Admin Panel | `visits` | Needs Review | Model field coverage needs deeper audit. |
| `2026-02-13-112256_CreateAuditLogsTable.php` | Admin Panel / Audit | `audit_logs` | Verified | Audit logging. |
| `2026-02-13-112617_CreateAdminNotesTable.php` | Admin Panel | `admin_notes` | Verified | Dashboard/admin notes. |
| `2026-02-13-112910_CreateAdminSettingsTable.php` | Admin Panel | `admin_settings` | Verified | Admin settings. |
| `2026-02-21-120000_CreateProductStockLogsTable.php` | Product / Catalog | `product_stock_logs` | Partial | Table exists; no dedicated model found in current model list. |
| `2026-02-25-191500_AddOrderManagementSchema.php` | Order | `order_items`, `order_logs`, order management columns | Verified | Order management expansion. |
| `2026-02-27-110000_CreateShippingAutomationRulesTable.php` | Shipping / Order | `shipping_automation_rules` | Verified | Shipping automation. |
| `2026-02-27-200000_AddSimulationColumnsToShippingAutomationRules.php` | Shipping / Order | shipping automation simulation columns | Verified | Extends automation rules. |
| `2026-03-02-100000_AddInvoicesTable.php` | Order | `invoices` | Verified | Invoice support. |
| `2026-03-03-090000_CreateShippingCompaniesTable.php` | Shipping / Order | `shipping_companies` | Partial | Seeder exists; model mapping not listed in current model scan. |
| `2026-03-04-120000_CreatePackingSessionsTable.php` | Order / Shipping | `packing_sessions` | Verified | Packing flow. |
| `2026-03-06-120000_CreateCouponsModuleTables.php` | Campaign / Coupon / Cart | `coupons`, `coupon_targets`, `coupon_redemptions` | Verified | Coupon schema. |
| `2026-03-06-130000_EnsureManageCampaignsPermission.php` | Campaign / Coupon / RBAC | `permissions`, `role_permissions` | Verified | Adds campaign permission. |
| `2026-03-06-140000_CreateCampaignsModuleTables.php` | Campaign / Coupon | `campaigns`, `campaign_targets` | Verified | Campaign schema. |
| `2026-03-06-141000_EnsureManageCampaignsEnginePermission.php` | Campaign / Coupon / RBAC | `permissions`, `role_permissions` | Verified | Adds campaign engine permission. |
| `2026-03-31-100000_CreatePriceRulesTable.php` | Product / Catalog | `price_rules` | Verified | Pricing rules. |
| `2026-03-31-110000_CreateDashboardBuilderTables.php` | Dashboard Builder | `dashboards`, `dashboard_block_types`, `dashboard_blocks` | Verified | Original dashboard builder tables. |
| `2026-04-03-120000_AlignDashboardBuilderSprint6A.php` | Dashboard Builder | Dashboard alignment changes | Needs Review | Requires field-level follow-up. |
| `2026-04-04-100000_CreatePagesTable.php` | Page Builder | `pages` | Verified | Page registry. |
| `2026-04-04-100100_CreatePageVersionsTable.php` | Page Builder | `page_versions` | Verified | Page versions. |
| `2026-04-04-100200_CreateBlockTypesTable.php` | Page Builder | `block_types` | Verified | Page block types. |
| `2026-04-04-100300_CreateBlockInstancesTable.php` | Page Builder | `block_instances` | Verified | Page block instances. |
| `2026-04-08-183414_CreateDashboardBlockInstancesTable.php` | Dashboard Builder | `dashboard_block_instances` | Verified | Newer dashboard block instance table. |
| `2026-04-08-183707_AddDeletedAtToDashboardTables.php` | Dashboard Builder | Dashboard soft-delete columns | Needs Review | Field-level mapping not audited. |
| `2026-04-08-212812_AddMissingColumnsToDashboardBlockInstances.php` | Dashboard Builder | Dashboard block instance columns | Needs Review | Field-level mapping not audited. |
| `2026-04-09-120000_AddBuilderColumnsToDashboardBlockInstances.php` | Dashboard Builder | Dashboard builder columns | Needs Review | Field-level mapping not audited. |
| `2026-04-10-120000_CreateNotificationEmailTemplatesTable.php` | Admin Panel / Notifications | `notification_email_templates` | Verified | Notification templates. |
| `2026-04-10-140000_CreateNotificationDeliveryLogsTable.php` | Admin Panel / Notifications | `notification_delivery_logs` | Verified | Notification delivery logs. |
| `2026-04-10-160000_CreateBannersTable.php` | Theme / Media | `banners` | Verified | Banner management. |
| `2026-04-17-120000_EnsureSecretaryOperationPermissions.php` | Secretary Access / RBAC | `permissions`, `role_permissions` | Verified | Secretary operation permissions. |
| `InitialAuthSeeder.php` | Auth / RBAC | roles, users, permissions | Verified | Initial auth/RBAC seed data. |
| `MarketingCouponsSeeder.php` | Campaign / Coupon | coupons and coupon targets | Verified | Marketing coupon seed data. |
| `PageManagementSeeder.php` | Page Builder | pages and block types | Verified | Page builder seed data. |
| `DashboardBuilderSeeder.php` | Dashboard Builder | dashboards and dashboard block types | Verified | Dashboard builder seed data. |
| `ProductsFullSeeder.php`, `CategorySeeder.php`, `PriceRuleSeeder.php` | Product / Catalog / Category | products, categories, authors, types, price rules | Verified | Product/catalog seed data. |
| `OrdersTestSeeder.php`, `OrdersShippingDemoSeeder.php`, `AnalyticsDemoSeeder.php` | Order / Shipping / Analytics | orders, order items, logs, products, users | Verified | Demo/test data. |
| `SyncShippingCompaniesSeeder.php` | Shipping / Order | shipping companies from order data | Partial | Seeder references `shipping_companies`; dedicated model mapping needs review. |

## Detected Risks

- Model exists but table unclear:
  - `DashboardBlockModel.php`
  - `ShippingModel.php`

- Table exists but model unclear:
  - `product_stock_logs`
  - `shipping_companies`

- Duplicate or conflicting model names:
  - `RoleModel.php` and `RoleModels.php` both map to `roles`.

- Seeder creates data not reflected in KB:
  - Shipping company sync and analytics/demo seeders may need deeper domain mapping.

- Migration/model field mismatch requires deeper audit:
  - `VisitModel.php` versus `CreateVisitsTable`.
  - Dashboard builder migrations after the initial table creation require field-level review.

## Known Gaps

- This is a baseline, not a full schema diff.
- Full field-level validation still requires a later extractor or manual schema audit.
- Model allowed fields were not exhaustively compared against every migration field.
- Runtime database state was not inspected.
- Soft-delete, timestamp, and custom data access behavior require a later detailed audit.

## Assumptions

- Assumption: Model `$table` declarations are the primary model-to-table evidence when present.
- Assumption: Migrations and seeders are enough for this baseline without querying a live database.
- Assumption: Missing dedicated models for some tables may be intentional if access is service-level or query-builder-based.
