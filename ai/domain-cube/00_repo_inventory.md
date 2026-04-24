# Domain Cube Base - İlk Repository Envanteri

Bu doküman yalnızca mevcut repository dosyalarına ve statik incelemeye dayanır. Kod çalıştırma, refactor veya uygulama dosyası değişikliği yapılmamıştır. Emin olunmayan noktalar "varsayım" veya "incelenmeli" olarak işaretlenmiştir.

## 1. Proje yapısı genel bakış

### app/ altındaki ana klasörler

- `app/Config`: CI4 yapılandırmaları, route/filter/security/session/autoload ayarları.
- `app/Controllers`: açık alan controller'ları ve `Admin/` namespace altındaki yönetim controller'ları.
- `app/Database/Migrations`: auth, ürün, sipariş, stok, kargo, kampanya, dashboard builder, page builder, bildirim ve banner şemaları.
- `app/Database/Seeds`: başlangıç auth verisi, ürün/kategori, sipariş demo, dashboard/page builder, fiyat ve marketing seed'leri.
- `app/DTO`: user/product/admin/marketing/page/shipping veri taşıyıcıları.
- `app/Filters`: auth, role, permission ve campaign erişim filtreleri.
- `app/Helpers`: dashboard tarih/seri ve ürün medya helper'ları.
- `app/Models`: users/roles/permissions, products/categories/authors/types, orders/order_items/order_logs, dashboard/page builder, campaign/coupon, notification, shipping ve admin yardımcı modelleri.
- `app/Presenters`: order datatable presenter.
- `app/Repositories`: shipping automation rule repository.
- `app/Services`: auth, products, orders, shipping, dashboard, page builder, notification, marketing/campaign/coupon ve admin dashboard/permissions servisleri.
- `app/Views`: auth, site/storefront, admin panel, page builder, dashboard builder, layout/partial ve error view'ları.

### Routing/auth/security ile ilgili config dosyaları

- `app/Config/Routes.php`: açık storefront, login/register/logout, auth korumalı klasik dashboard/products/orders, admin ve admin+secretary permission grupları.
- `app/Config/Filters.php`: `auth`, `role`, `perm`, `campaign_access`, CI4 güvenlik/toolbar/cors alias'ları. `auth` alias'ı aynı array içinde iki kez tanımlı.
- `app/Config/Security.php`: framework security config.
- `app/Config/Session.php`: session config; login akışı session anahtarlarına dayanıyor.
- `app/Config/App.php`, `app/Config/Routing.php`, `app/Config/Autoload.php`, `app/Config/Constants.php`: uygulama, routing ve autoload temeli.

### Controller'lar

- Public/auth: `StorefrontController`, `Login`, `Register`, `Logout`, `Home`, `ProductController`, `OrderController`.
- Admin: `Automation`, `Banners`, `Campaigns`, `Coupons`, `Customers`, `Dashboard`, `DashboardBlockController`, `DashboardBuilder`, `DashboardController`, `Marketing`, `Notifications`, `OrderController`, `Orders`, `OrderStatuses`, `PageController`, `Pricing`, `Products`, `Settings`, `SettingsPermissionsController`, `Shipping`, `ShippingAutomation`, `ShippingAutomationController`, `ShippingCompanies`, `Stock`, `StockMove`.
- Admin trait: `Admin/Traits/OrderPackingActions`.

### Model'ler

- Auth/RBAC: `UserModel`, `RoleModel`, `RoleModels`, `PermissionModel`, `RolePermissionModel`, `UserPermissionModel`.
- Catalog: `ProductsModel`, `CategoryModel`, `AuthorModel`, `TypeModel`, `PriceRuleModel`, `BannerModel`.
- Order/stock/shipping: `OrderModel`, `OrderItemModel`, `OrderLogModel`, `PackingSessionModel`, `InvoiceModel`, `ShippingModel`, `ShippingAutomationRuleModel`.
- Marketing: `CampaignModel`, `CampaignTargetModel`, `CouponModel`, `CouponTargetModel`, `CouponRedemptionModel`.
- Dashboard/admin: `DashboardModel`, `DashboardBlockTypeModel`, `DashboardBlockModel`, `DashboardBlockInstanceModel`, `AdminNoteModel`, `AdminSettingModel`, `AuditLogModel`, `VisitModel`.
- Page builder: `PageModel`, `PageVersionModel`, `BlockTypeModel`, `BlockInstanceModel`.
- Notification: `NotificationEmailTemplateModel`, `NotificationDeliveryLogModel`.
- Base: `BaseUuidModel`.

### Service'ler

- Auth/catalog/storefront: `AuthService`, `ProductsService`, `StorefrontHomeService`, `PriceService`, `BannerService`.
- Order/fulfillment: `OrdersService`, `OrderCreationService`, `OrderShippingService`, `OrderNoteService`, `OrdersReportingService`, `PackingService`, `InvoiceService`.
- Shipping: `ShippingAutomationService`, `ShippingSimulationService`, `TwilioSmsProvider`.
- Marketing: `MarketingService`, `CampaignService`, `CouponService`.
- Dashboard builder: `DashboardService`, `DashboardBuilderService`, `DashboardBlockService`, `DashboardDataSourceService`, `Admin/DashboardService`.
- Page builder: `PageService`, `PageVersionService`, `PageBuilderService`, `ProductDetailPageBuilderService`, `ProductDetailPreviewRenderer`, `ProductListPreviewRenderer`, `CartPageBuilderService`, `CartPreviewRenderer`, `CheckoutPageBuilderService`, `CheckoutPreviewRenderer`.
- Notification: `NotificationEmailService`, `NotificationSmsService`, `NotificationSmsProviderInterface`, `NotificationLogService`, `NotificationTemplateService`.
- Admin access: `Admin/SettingsPermissionsService`.

### DTO'lar

- Genel: `UserDTO`, `ProductDTO`.
- Admin dashboard: `Admin/AdminNoteDTO`, `Admin/AuditLogItemDTO`, `Admin/ChartPointDTO`, `Admin/DashboardDTO`, `Admin/MetricCardDTO`, `Admin/OrderListItemDTO`, `Admin/PieSliceDTO`, `Admin/RevenueRowDTO`, `Admin/RevenueTableDTO`.
- Marketing: `Marketing/CampaignDTO`, `Marketing/CouponDTO`, `Marketing/MarketingPageSummaryDTO`.
- Page management: `PageManagement/PageDraftListItemDTO`, `PageManagement/PageListItemDTO`.
- Shipping: `Shipping/AutomationRuleDTO`, `Shipping/ShippingSimulationRequestDTO`.

### Migration'lar

- İlk şema/auth/catalog/order/RBAC: `2026-02-07-104831_CreateInitialSchemaUuid.php`.
- Admin dashboard/log/settings: `2026-02-13-*`.
- Stok/order flow/order management/invoice/shipping/packing: `2026-02-21-*` ile `2026-03-04-*`.
- Marketing/coupon/campaign permissions: `2026-03-06-*`.
- Pricing/dashboard builder/page builder: `2026-03-31-*`, `2026-04-03-*`, `2026-04-04-*`, `2026-04-08-*`, `2026-04-09-*`.
- Notification/banner/secretary permissions: `2026-04-10-*`, `2026-04-17-*`.
- `_2026-03-04-090000_AddShippedAtToOrdersIfMissing.php`: underscore ile başlayan migration dosyası; çalıştırılma durumu incelenmeli.

### Seed'ler

- Auth/RBAC: `InitialAuthSeeder`.
- Catalog: `CategorySeeder`, `ProductsFullSeeder`, `PriceRuleSeeder`.
- Orders/shipping demos: `OrdersTestSeeder`, `OrdersShippingDemoSeeder`, `SyncShippingCompaniesSeeder`.
- Builder/demo: `DashboardBuilderSeeder`, `PageManagementSeeder`, `AnalyticsDemoSeeder`.
- Marketing: `MarketingCouponsSeeder`.

### View'lar

- Auth: `app/Views/auth/login.php`, `app/Views/auth/register.php`.
- Kullanıcı/storefront: `site/storefront/home.php`, `site/storefront/fallback_page.php`, `site/products/index.php`, `site/products/product_detail.php`, `site/products/product_selection.php`, `site/home/index.php`, `site/layouts/*`, `site/partials/*`.
- Admin panel: `admin/dashboard`, `admin/products`, `admin/orders`, `admin/stock`, `admin/shipping`, `admin/notifications`, `admin/automation`, `admin/marketing`, `admin/campaigns`, `admin/coupons`, `admin/pricing`, `admin/customers`, `admin/banners`, `admin/settings`.
- Sekreter erişimi: ayrı sekreter view klasörü yok; mevcut admin view'ları role/permission filtresiyle paylaşılıyor.
- Dashboard builder: `admin/dashboard_builder/index.php`, `_reorder_script.php`, `admin/dashboard/blocks/*`.
- Page builder: `admin/pages/index.php`, `show.php`, `version.php`, `drafts.php`, `builder.php`, `product_list_builder.php`, `product_detail_builder.php`, `cart_builder.php`, `checkout_builder.php`, `partials/*`.
- Layout/partials: `admin/layouts/main.php`, `admin/partials/*`, ayrıca eski/genel görünümlü `layouts/*`, `partials/sidebar.php`, `admin/theme/*`.

## 2. Dosya envanter tablosu

| Dosya | Tür | Mevcut amacı | Bağlı olduğu domain | Notlar |
|------|----|-------------|---------------------|--------|
| `app/Config/Routes.php` | Config | Tüm public/auth/admin route gruplarını tanımlar | Auth / Admin Panel / Frontend Storefront | `Admin\Users` ve `Admin\Roles` route'ları yorum satırı; admin+secretary izinleri route seviyesinde var |
| `app/Config/Filters.php` | Config | Filter alias'larını tanımlar | Auth / User / Role / Permission | `auth` alias'ı iki kez tanımlı; globals içinde CSRF kapalı görünüyor |
| `app/Filters/AuthFilter.php` | Filter | `isLoggedIn` session kontrolü | Auth | Auth korumalı grup tarafından kullanılıyor |
| `app/Filters/RoleFilter.php` | Filter | Role ve `perm:*` argümanlarını kontrol eder | User / Role / Permission | Admin için permission bypass var; secretary için `UserPermissionModel::isAllowed` kullanılıyor |
| `app/Filters/PermissionFilter.php` | Filter | Permission kontrolünü RoleFilter'a delege eder | User / Role / Permission | Route'larda alias olarak tanımlı |
| `app/Filters/CampaignAccessFilter.php` | Filter | Campaign/coupon alanında admin + permission kontrolü | Campaign / Coupon / Permission | Sadece admin rolünü kabul ediyor; secretary erişimi bu filtreden geçemiyor |
| `app/Controllers/Login.php` | Controller | Login formu ve session oluşturma | Auth | Başarılı admin/secretary login `admin/dashboard`, user login `dashboard_anasayfa` |
| `app/Controllers/Register.php` | Controller | Kullanıcı kayıt formu ve kayıt işlemi | Auth | `AuthService` ve `UserDTO` kullanıyor |
| `app/Controllers/Logout.php` | Controller | Session sonlandırır | Auth | Public logout route'u var |
| `app/Controllers/Home.php` | Controller | Login olan user için klasik dashboard | Frontend Storefront / Auth | `site/home/index` view'ına gider |
| `app/Controllers/StorefrontController.php` | Controller | Ana storefront ve placeholder sayfalar | Frontend Storefront / Page Builder | `StorefrontHomeService` kullanıyor |
| `app/Controllers/ProductController.php` | Controller | Public ürün liste/detay/filtreleme | Product / Catalog / Frontend Storefront | `selection()` route'u var ama view dönüşü incelenmeli |
| `app/Controllers/OrderController.php` | Controller | Auth kullanıcı sipariş oluşturma ve admin orders'a yönlendirme | Order | `orders` route'u auth grubunda; create reserved order yapıyor |
| `app/Controllers/Admin/Products.php` | Controller | Admin ürün CRUD, author/category oluşturma, datatable | Product / Catalog / Admin Panel | `ProductsService`, `ProductsModel`, `AuthorModel` kullanıyor |
| `app/Controllers/Admin/Orders.php` | Controller | Admin/sekreter sipariş listesi, detay, status, invoice, packing | Order / Admin Panel / Secretary Access | Çok sayıda order service kullanıyor |
| `app/Controllers/Admin/OrderStatuses.php` | Controller | Sipariş status ekranı | Order / Admin Panel | `admin/orders/statuses` view'ı |
| `app/Controllers/Admin/Stock.php` | Controller | Stok listesi ve hareketleri | Product / Catalog / Admin Panel / Secretary Access | `manage_stock` route filtresi altında |
| `app/Controllers/Admin/StockMove.php` | Controller | Ürün stok hareketi oluşturma | Product / Catalog / Admin Panel / Secretary Access | `admin/stock/move/*` route'ları |
| `app/Controllers/Admin/Shipping.php` | Controller | Kargo datatable, template, bulk labels/barcodes/manifest | Order / Shipping / Admin Panel / Secretary Access | `ShippingModel` CI Model değil, custom model sınıfı |
| `app/Controllers/Admin/ShippingCompanies.php` | Controller | Kargo firması oluşturma | Shipping / Admin Panel | `admin/shipping/companies_create` view'ı |
| `app/Controllers/Admin/ShippingAutomationController.php` | Controller | Kargo otomasyon UI/API | Shipping / Admin Panel / Secretary Access | Aktif route'lar bu controller'a bağlı |
| `app/Controllers/Admin/ShippingAutomation.php` | Controller | Alternatif/eski shipping automation controller | Shipping / Unknown / İncelenmeli | Aktif route bağlantısı görünmedi |
| `app/Controllers/Admin/DashboardController.php` | Controller | Builder tabanlı admin/secretary dashboard | Dashboard Builder / Admin Panel / Secretary Access | `admin/dashboard` route'u buna bağlı |
| `app/Controllers/Admin/Dashboard.php` | Controller | Legacy dashboard controller | Admin Panel / Unknown / İncelenmeli | Aktif route bağlantısı görünmedi |
| `app/Controllers/Admin/DashboardBuilder.php` | Controller | Dashboard builder ekranı ve reorder/resize | Dashboard Builder | Sadece admin role filtresi altında |
| `app/Controllers/Admin/DashboardBlockController.php` | Controller | Dashboard blok fetch/detail/store/update/delete API | Dashboard Builder | Dashboard builder route grubunda |
| `app/Controllers/Admin/PageController.php` | Controller | Page builder, draft, version, block ve özel builder ekranları | Page Builder | Dinamik view seçimiyle page code'a göre builder açıyor |
| `app/Controllers/Admin/SettingsPermissionsController.php` | Controller | Secretary kullanıcı ve permission ekranı | Secretary Access / User / Role / Permission | Sadece admin route grubunda |
| `app/Controllers/Admin/Settings.php` | Controller | Admin ayarlar ana ekranı | Admin Panel | `admin/settings/index` view'ı |
| `app/Controllers/Admin/Customers.php` | Controller | Müşteri yönetim ekranı | User / Admin Panel / Secretary Access | Route permission: `manage_customers` |
| `app/Controllers/Admin/Notifications.php` | Controller | Email/SMS testleri, template kaydı ve log listesi | Admin Panel / Notification | Route permission: `manage_notifications` |
| `app/Controllers/Admin/Marketing.php` | Controller | Marketing özet ekranı | Campaign / Coupon / Admin Panel | Admin-only route grubunda |
| `app/Controllers/Admin/Campaigns.php` | Controller | Campaign CRUD | Campaign | `campaign_access` filtresi altında |
| `app/Controllers/Admin/Coupons.php` | Controller | Coupon CRUD | Coupon | `campaign_access` filtresi altında |
| `app/Controllers/Admin/Pricing.php` | Controller | Fiyat kuralları CRUD | Product / Catalog | Admin-only route grubunda |
| `app/Controllers/Admin/Banners.php` | Controller | Banner yönetimi | Theme / Media / Frontend Storefront | Admin-only route grubunda |
| `app/Controllers/Admin/Automation.php` | Controller | Admin automation ekranı | Admin Panel / Unknown / İncelenmeli | Basit index ekranı |
| `app/Models/UserModel.php` | Model | `users` tablosu | Auth / User | Role bilgisi users üzerinde de tutuluyor |
| `app/Models/RoleModel.php` | Model | `roles` tablosu | User / Role / Permission | Aktif RBAC modeli |
| `app/Models/RoleModels.php` | Model | `roles` tablosu için ikinci dosya | User / Role / Permission | Aynı işi yapan model adayı; class adı `RoleModel` görünüyor, incelenmeli |
| `app/Models/PermissionModel.php` | Model | `permissions` tablosu | User / Role / Permission | Permission code yönetimi |
| `app/Models/RolePermissionModel.php` | Model | `role_permissions` pivot | User / Role / Permission | Role-permission bağlantısı |
| `app/Models/UserPermissionModel.php` | Model | `user_permissions` override | User / Role / Permission / Secretary Access | `getEffectivePermissions` ve `isAllowed` akışında kullanılıyor |
| `app/Models/ProductsModel.php` | Model | `products` tablosu | Product / Catalog | Storefront, admin, order ve pricing servislerinde kullanılıyor |
| `app/Models/CategoryModel.php` | Model | `categories` tablosu | Category | Admin/product/marketing hedeflerinde kullanılıyor |
| `app/Models/AuthorModel.php` | Model | `authors` tablosu | Product / Catalog | Ürün author ilişkisi |
| `app/Models/TypeModel.php` | Model | `types` tablosu | Product / Catalog | Ürün tipi/listesi |
| `app/Models/PriceRuleModel.php` | Model | `price_rules` tablosu | Product / Catalog | Pricing controller/service ile bağlı |
| `app/Models/OrderModel.php` | Model | `orders` tablosu | Order | Sipariş yönetiminin ana modeli |
| `app/Models/OrderItemModel.php` | Model | `order_items` tablosu | Order | OrderCreationService ile bağlı |
| `app/Models/OrderLogModel.php` | Model | `order_logs` tablosu | Order / Audit | OrdersService/Admin Orders içinde kullanılıyor |
| `app/Models/PackingSessionModel.php` | Model | `packing_sessions` tablosu | Order / Shipping | PackingService ile bağlı |
| `app/Models/InvoiceModel.php` | Model | `invoices` tablosu | Order | InvoiceService/OrdersReportingService kullanıyor |
| `app/Models/ShippingModel.php` | Model | Kargo datatable için `orders` üzerinden query | Shipping / Order | CI `Model` extend etmiyor; custom data access |
| `app/Models/ShippingAutomationRuleModel.php` | Model | `shipping_automation_rules` tablosu | Shipping | Automation service/repository ile bağlı |
| `app/Models/CampaignModel.php` | Model | `campaigns` tablosu | Campaign | CampaignService/MarketingService |
| `app/Models/CampaignTargetModel.php` | Model | `campaign_targets` tablosu | Campaign | Campaign hedefleri |
| `app/Models/CouponModel.php` | Model | `coupons` tablosu | Coupon | CouponService |
| `app/Models/CouponTargetModel.php` | Model | `coupon_targets` tablosu | Coupon | Coupon hedefleri |
| `app/Models/CouponRedemptionModel.php` | Model | `coupon_redemptions` tablosu | Coupon / Order | Redemption akışı incelenmeli |
| `app/Models/DashboardModel.php` | Model | `dashboards` tablosu | Dashboard Builder | User/global dashboard |
| `app/Models/DashboardBlockTypeModel.php` | Model | `dashboard_block_types` tablosu | Dashboard Builder | Aktif blok tipleri |
| `app/Models/DashboardBlockModel.php` | Model | Dashboard block type modelinden türemiş | Dashboard Builder / Unknown / İncelenmeli | `dashboard_blocks` yerine type tablosunu kullanıyor gibi görünüyor |
| `app/Models/DashboardBlockInstanceModel.php` | Model | `dashboard_block_instances` tablosu | Dashboard Builder | Yeni builder instance modeli |
| `app/Models/PageModel.php` | Model | `pages` tablosu | Page Builder | PageService/PageVersionService |
| `app/Models/PageVersionModel.php` | Model | `page_versions` tablosu | Page Builder | Draft/publish/schedule akışı |
| `app/Models/BlockTypeModel.php` | Model | `block_types` tablosu | Page Builder | Page builder blok tipleri |
| `app/Models/BlockInstanceModel.php` | Model | `block_instances` tablosu | Page Builder | Page version blokları |
| `app/Models/BannerModel.php` | Model | `banners` tablosu | Theme / Media | BannerService |
| `app/Models/NotificationEmailTemplateModel.php` | Model | `notification_email_templates` tablosu | Admin Panel / Notification | NotificationTemplateService |
| `app/Models/NotificationDeliveryLogModel.php` | Model | `notification_delivery_logs` tablosu | Admin Panel / Notification | NotificationLogService |
| `app/Models/AdminNoteModel.php` | Model | `admin_notes` tablosu | Admin Panel | Admin dashboard service |
| `app/Models/AdminSettingModel.php` | Model | `admin_settings` tablosu | Admin Panel | Admin dashboard/settings |
| `app/Models/AuditLogModel.php` | Model | `audit_logs` tablosu | Admin Panel / Audit | Admin dashboard |
| `app/Models/VisitModel.php` | Model | `visits` tablosu | Admin Panel / Analytics | `allowedFields` yalnızca `id`, `visited_at`; migration'da daha fazla alan var |
| `app/Services/AuthService.php` | Service | Register ve login domain mantığı | Auth | `UserModel` kullanıyor |
| `app/Services/ProductsService.php` | Service | Ürün listeleme, stok ve meta işlemleri | Product / Catalog | Admin/storefront/order tarafından kullanılıyor |
| `app/Services/StorefrontHomeService.php` | Service | Ana sayfa/page builder/storefront veri hazırlığı | Frontend Storefront / Page Builder | Page/block/product/category/author modelleriyle bağlı |
| `app/Services/OrdersService.php` | Service | Sipariş oluşturma, stok rezervasyon, status/log işlemleri | Order | Birden çok controller/service tarafından kullanılıyor |
| `app/Services/OrderCreationService.php` | Service | Admin sipariş oluşturma | Order | OrderItem ve ProductsModel kullanıyor |
| `app/Services/OrderShippingService.php` | Service | Sipariş kargo durumları | Order / Shipping | Admin Orders ile bağlı |
| `app/Services/OrderNoteService.php` | Service | Sipariş notları | Order | Admin Orders ile bağlı |
| `app/Services/OrdersReportingService.php` | Service | Sipariş analitik/rapor verisi | Order / Dashboard | Admin Orders API'leri |
| `app/Services/PackingService.php` | Service | Paketleme session ve doğrulama | Order / Shipping | Packing views ve bulk labels ile bağlı |
| `app/Services/InvoiceService.php` | Service | Fatura üretimi/görüntüleme | Order | `writable/uploads/invoices` çıktıları mevcut |
| `app/Services/ShippingAutomationService.php` | Service | Kargo otomasyon kuralları CRUD | Shipping | Controller ve eski controller tarafından referanslanıyor |
| `app/Services/ShippingSimulationService.php` | Service | Kargo otomasyon simülasyonu | Shipping | `ShippingAutomationController::simulate` |
| `app/Repositories/ShippingAutomationRuleRepository.php` | Repository | Aktif shipping automation rule erişimi | Shipping | Service dışında kullanımı ayrıca incelenmeli |
| `app/Services/DashboardService.php` | Service | Builder dashboard state/veri | Dashboard Builder | `Admin/DashboardController` |
| `app/Services/Admin/DashboardService.php` | Service | Legacy/admin dashboard metrikleri | Admin Panel | `Admin/DashboardController` alias ile kullanıyor |
| `app/Services/DashboardBuilderService.php` | Service | Dashboard oluşturma/bulma/reorder | Dashboard Builder | User/global dashboard ayrımı var |
| `app/Services/DashboardBlockService.php` | Service | Dashboard blok CRUD/config normalize | Dashboard Builder | Block controller ve builder |
| `app/Services/DashboardDataSourceService.php` | Service | Dashboard veri kaynakları | Dashboard Builder | OrderModel üzerinden veri |
| `app/Services/PageService.php` | Service | Page listesi/temel page işlemleri | Page Builder | PageController |
| `app/Services/PageVersionService.php` | Service | Version/draft/publish işlemleri | Page Builder | PageController |
| `app/Services/PageBuilderService.php` | Service | Generic page builder state/block işlemleri | Page Builder | PageController |
| `app/Services/ProductDetailPageBuilderService.php` | Service | Product detail özel builder config | Page Builder / Product | Özel builder ekranına bağlı |
| `app/Services/CartPageBuilderService.php` | Service | Cart özel builder config | Page Builder / Cart | Cart UI var, backend checkout/cart domain akışı belirsiz |
| `app/Services/CheckoutPageBuilderService.php` | Service | Checkout özel builder config | Page Builder / Cart | Checkout UI var, gerçek ödeme akışı belirsiz |
| `app/Services/ProductListPreviewRenderer.php` | Service | Product list builder preview | Page Builder / Product | Preview partial ile bağlı |
| `app/Services/ProductDetailPreviewRenderer.php` | Service | Product detail builder preview | Page Builder / Product | Preview partial ile bağlı |
| `app/Services/CartPreviewRenderer.php` | Service | Cart builder preview | Page Builder / Cart | UI preview odaklı |
| `app/Services/CheckoutPreviewRenderer.php` | Service | Checkout builder preview | Page Builder / Cart | UI preview odaklı |
| `app/Services/CampaignService.php` | Service | Campaign CRUD ve hedefler | Campaign | Campaigns controller |
| `app/Services/CouponService.php` | Service | Coupon CRUD/validation/discount | Coupon / Cart | Cart domain model/controller görünmedi; kullanım incelenmeli |
| `app/Services/MarketingService.php` | Service | Marketing özetleri | Campaign / Coupon | Admin marketing ekranı |
| `app/Services/BannerService.php` | Service | Banner yönetimi | Theme / Media | Storefront bağlantısı incelenmeli |
| `app/Services/PriceService.php` | Service | Fiyat kuralı uygulama | Product / Catalog | Kullanım noktaları incelenmeli |
| `app/Services/Notification*` | Service | Email/SMS template/log/test gönderimleri | Admin Panel / Notification | Notifications controller |
| `app/Services/Admin/SettingsPermissionsService.php` | Service | Secretary kullanıcı ve permission override yönetimi | Secretary Access / RBAC | Settings permissions controller |
| `app/DTO/UserDTO.php` | DTO | Register kullanıcı verisi | Auth | AuthService |
| `app/DTO/ProductDTO.php` | DTO | Ürün verisi | Product / Catalog | ProductsService/Admin Products |
| `app/DTO/Admin/*` | DTO | Admin dashboard typed output | Admin Panel / Dashboard Builder | Admin dashboard service |
| `app/DTO/Marketing/*` | DTO | Campaign/coupon/marketing data | Campaign / Coupon | Campaign/Coupon services |
| `app/DTO/PageManagement/*` | DTO | Page list/draft list item | Page Builder | Page services |
| `app/DTO/Shipping/*` | DTO | Shipping automation/simulation input | Shipping | Shipping services |
| `app/Database/Migrations/2026-02-07-104831_CreateInitialSchemaUuid.php` | Migration | users/auth/catalog/orders/RBAC ilk şema | Auth / Product / Order / RBAC | Temel tablo kaynağı |
| `app/Database/Migrations/2026-02-25-191500_AddOrderManagementSchema.php` | Migration | order management schema genişletmeleri | Order | `order_items`, `order_logs` dahil |
| `app/Database/Migrations/2026-03-31-110000_CreateDashboardBuilderTables.php` | Migration | dashboards/dashboard block tables | Dashboard Builder | Sonraki migrations instance yapısını genişletiyor |
| `app/Database/Migrations/2026-04-04-100000_CreatePagesTable.php` | Migration | pages tablosu | Page Builder | Page builder temeli |
| `app/Database/Migrations/2026-04-04-100100_CreatePageVersionsTable.php` | Migration | page_versions tablosu | Page Builder | Draft/version/publish |
| `app/Database/Migrations/2026-04-04-100200_CreateBlockTypesTable.php` | Migration | block_types tablosu | Page Builder | Generic block types |
| `app/Database/Migrations/2026-04-04-100300_CreateBlockInstancesTable.php` | Migration | block_instances tablosu | Page Builder | Version blokları |
| `app/Database/Migrations/2026-04-17-120000_EnsureSecretaryOperationPermissions.php` | Migration | secretary/admin operasyon permission'ları | Secretary Access / RBAC | Permission listesi route'larla karşılaştırılmalı |
| `app/Database/Seeds/InitialAuthSeeder.php` | Seeder | admin/secretary/user ve temel permissions | Auth / RBAC | Admin ve secretary başlangıç kullanıcıları |
| `app/Database/Seeds/PageManagementSeeder.php` | Seeder | Page builder başlangıç verisi | Page Builder | Storefront page kodları varsayımı |
| `app/Database/Seeds/DashboardBuilderSeeder.php` | Seeder | Dashboard builder başlangıç verisi | Dashboard Builder | Global dashboard seed'i |
| `app/Views/auth/login.php` | View | Login formu | Auth | Login controller bağlı |
| `app/Views/auth/register.php` | View | Register formu | Auth | Register controller bağlı |
| `app/Views/site/storefront/home.php` | View | Ana storefront | Frontend Storefront / Page Builder | StorefrontController bağlı |
| `app/Views/site/products/index.php` | View | Ürün listeleme | Product / Catalog / Frontend Storefront | ProductController bağlı |
| `app/Views/site/products/product_detail.php` | View | Ürün detay | Product / Catalog / Frontend Storefront | ProductController bağlı |
| `app/Views/site/products/product_selection.php` | View | Ürün seçim ekranı | Product / Catalog / Unknown / İncelenmeli | Statik taramada controller view dönüşü bulunmadı |
| `app/Views/site/home/index.php` | View | Login sonrası user dashboard | Frontend Storefront | Home controller bağlı |
| `app/Views/admin/layouts/main.php` | View/Layout | Admin layout | Admin Panel | Admin view'ların çoğu extend ediyor |
| `app/Views/admin/partials/*` | View/Partial | Admin layout partial'ları | Admin Panel | Sidebar ortak partial'a delegasyon yapıyor |
| `app/Views/partials/sidebar.php` | View/Partial | Ortak sidebar | Admin Panel / Unknown / İncelenmeli | Hem admin partial hem eski layout tarafından kullanılıyor |
| `app/Views/layouts/*` | View/Layout | Eski/genel layout seti | Unknown / İncelenmeli | Bazı eski site/admin view'ları extend ediyor |
| `app/Views/admin/theme/*` | View/Layout | Alternatif admin theme layout/topbar/sidebar | Theme / Media / Unknown / İncelenmeli | Aktif controller view bağlantısı görünmedi |
| `app/Views/admin/products/*` | View | Ürün liste/create/edit | Product / Catalog / Admin Panel | Admin Products bağlı |
| `app/Views/admin/orders/*` | View | Sipariş liste/detay/status/packing | Order / Admin Panel / Secretary Access | Admin Orders ve trait bağlı |
| `app/Views/admin/stock/*` | View | Stok liste/move ekranları | Product / Catalog / Secretary Access | `stock/move.php` view bağlantısı incelenmeli |
| `app/Views/admin/shipping/*` | View | Kargo ekranları ve bulk çıktı | Shipping / Order | Shipping ve automation controller bağlı |
| `app/Views/admin/dashboard/index.php` | View | Admin/secretary dashboard | Dashboard Builder / Admin Panel | DashboardController ve legacy Dashboard kullanıyor |
| `app/Views/admin/dashboard_builder/*` | View | Dashboard builder | Dashboard Builder | Admin-only |
| `app/Views/admin/dashboard/blocks/*` | View/Partial | Dashboard blok render partial'ları | Dashboard Builder | Data source ile bağlı |
| `app/Views/admin/pages/*` | View | Page builder, drafts, versions, özel builders | Page Builder | PageController bağlı/dinamik |
| `app/Views/admin/pages/partials/*` | View/Partial | Builder preview partial'ları | Page Builder | Preview renderer servisleriyle ilişkili |
| `app/Views/admin/settings/permissions.php` | View | Secretary permission UI | Secretary Access / RBAC | Admin-only route |
| `app/Views/admin/campaigns/*` | View | Campaign CRUD UI | Campaign | CampaignAccessFilter altında |
| `app/Views/admin/coupons/*` | View | Coupon CRUD UI | Coupon | CampaignAccessFilter altında |
| `app/Views/admin/pricing/*` | View | Pricing rule UI | Product / Catalog | Admin-only |
| `app/Views/admin/banners/index.php` | View | Banner yönetimi | Theme / Media | Admin-only |
| `app/Views/admin/customers/index.php` | View | Customer yönetimi | User / Admin Panel | Permission route var; backend derinliği incelenmeli |
| `app/Views/admin/automation/index.php` | View | Automation ekranı | Admin Panel / Unknown / İncelenmeli | Domain kapsamı belirsiz |
| `app/Views/admin/notifications/index.php` | View | Notification ekranı | Admin Panel / Notification | Email/SMS servisleri bağlı |

## 3. Mevcut akışların özeti

### Auth ve login akışı

- `GET /login` -> `Login::index` -> `auth/login`.
- `POST /login/auth` -> `Login::auth` -> `AuthService::attemptLogin`.
- Başarılı login session'a `isLoggedIn`, `user`, `user_id`, `role`, `permissions` yazar.
- Role `admin` veya `secretary` ise `admin/dashboard`; diğer user için `dashboard_anasayfa`.
- Hatalı denemelerde session bazlı bekleme süresi uygulanıyor.
- Register akışı `Register::save` -> `AuthService::registerUser`.

### Role / permission / RBAC yapısı

- `users.role` doğrudan role bilgisini tutuyor.
- `roles`, `permissions`, `role_permissions`, `user_permissions` tabloları migration ile oluşturuluyor.
- `InitialAuthSeeder` admin/secretary/user ve temel permission'ları ekliyor.
- `RoleFilter` route argümanını `role:admin,secretary|perm:manage_orders` gibi parse ediyor.
- Admin rolü permission kontrolünde bypass ediliyor; secretary için `UserPermissionModel::isAllowed` kullanılıyor.
- `SettingsPermissionsController` ve `SettingsPermissionsService` secretary kullanıcı oluşturma ve permission override yönetimi sağlıyor.
- `CampaignAccessFilter` campaign/coupon route'larında farklı bir kontrol yapıyor ve role `admin` değilse reddediyor.

### Admin dashboard akışı

- `GET /admin/dashboard` -> `Admin\DashboardController::index`.
- Route filtresi: `role:admin,secretary|perm:manage_dashboard`.
- Controller `DashboardService`, `DashboardBlockService`, `DashboardDataSourceService` ve alias olarak `Admin\DashboardService` kullanıyor.
- View: `admin/dashboard/index`.
- Legacy `Admin\Dashboard` controller da aynı view'a dönebiliyor ama aktif route bağlantısı görünmedi.

### Sekreter erişim akışı

- Sekreter login sonrası `admin/dashboard` adresine yönleniyor.
- Route seviyesinde `role:admin,secretary|perm:*` grupları ile dashboard, stock, notifications, customers, products, shipping, orders gibi alanlar açılabiliyor.
- Permission yönetimi admin-only `admin/settings/permissions` altında.
- Ayrı `secretary` view/controller namespace'i yok; admin ekranları permission filtresiyle paylaşılıyor.
- Campaign/coupon alanında `campaign_access` filtresi role `admin` şartı koyduğu için secretary erişimi statik incelemede kapalı görünüyor.

### Product / katalog akışı

- Public ürün route'ları: `products/detail/{id}`, `products/list/{type}`, `products/list/{type}/{categoryId}`, `products/selection`.
- `ProductController` `ProductsService` ve `StorefrontHomeService` ile ürün listesi/detay verisini hazırlar.
- Admin ürün yönetimi: `Admin\Products` altında list/create/store/edit/update, author/category create/store.
- Kategori, author, type ve price rule modelleri catalog domain'e bağlı.
- Stok yönetimi `Admin\Stock` ve `Admin\StockMove` üzerinden product stock fonksiyonlarıyla ilişkili.

### Order akışı

- Auth kullanıcı route'u: `GET /orders` -> `OrderController::index` -> `admin/orders` yönlendirme.
- Auth kullanıcı sipariş oluşturma: `POST /orders/create` -> `OrderController::create` -> `OrdersService::createReservedOrder`.
- Admin/sekreter sipariş yönetimi: `Admin\Orders` route grubu `role:admin,secretary|perm:manage_orders`.
- Admin orders list/detail/status/shipping/note/ship/cancel/return/invoice/packing işlemlerini kapsıyor.
- `OrderCreationService`, `OrderShippingService`, `OrderNoteService`, `OrdersReportingService`, `PackingService`, `InvoiceService` bu akışı parçalamış.

### Kullanıcı storefront akışı

- `/` -> `StorefrontController::home` -> `StorefrontHomeService` -> `site/storefront/home`.
- `yardim/{slug}` placeholder sayfaya gider.
- Ürün liste/detay akışı `ProductController` üzerinden `site/products/*` view'larını render eder.
- Login sonrası user dashboard `dashboard_anasayfa` -> `Home::index` -> `site/home/index`.
- Cart/favorites/review için task dokümanları ve builder/preview sınıfları var; repo kodunda tam runtime controller/model akışı bu envanterde görülmedi.

### Page builder akışı

- Admin-only route grubu altında `admin/pages`, `admin/pages/{code}/builder`, drafts/version/block update/publish/schedule route'ları var.
- `PageController` page code'a göre generic builder veya özel builder view seçiyor: product list/detail, cart, checkout.
- `PageService`, `PageVersionService`, `PageBuilderService` generic page version/blok akışını yönetiyor.
- `ProductDetailPageBuilderService`, `CartPageBuilderService`, `CheckoutPageBuilderService` özel config builder servisleri.
- Preview renderer servisleri admin page partial'larını besliyor.

### Dashboard builder akışı

- Admin-only route: `admin/dashboard-builder`.
- `DashboardBuilder` controller dashboard state, reorder ve resize işlemlerini yürütüyor.
- `DashboardBlockController` blok fetch/detail/store/update/delete API'lerini sağlıyor.
- `DashboardBuilderService`, `DashboardBlockService`, `DashboardDataSourceService` domain mantığını taşıyor.
- `dashboards`, `dashboard_block_types`, `dashboard_blocks`, `dashboard_block_instances` migration/model seti var; eski/yeni blok tabloları ilişkisi incelenmeli.

## 4. Eksik veya belirsiz alanlar

- Route var ama controller yok:
  - Aktif route'larda controller dosyası eksik görünmedi.
  - `app/Config/Routes.php` içinde `Admin\Users::index` ve `Admin\Roles::index` yorum satırı olarak duruyor; controller dosyaları yok. Aktif route değil, backlog/plan izi olabilir.

- Model var ama service yok:
  - `RoleModel`, `PermissionModel`, `RolePermissionModel`, `UserPermissionModel` için ana service yalnızca admin permissions tarafında var; genel RBAC service yok.
  - `AdminSettingModel`, `AdminNoteModel`, `AuditLogModel`, `VisitModel` admin dashboard service içinde kullanılıyor; ayrı domain service yok.
  - `ShippingModel` custom query model olarak controller tarafından doğrudan kullanılıyor; service katmanı görünmedi.

- Service var ama kullanılmıyor:
  - Statik isim aramasında çoğu service referanslanıyor.
  - `ShippingAutomationService` hem aktif `ShippingAutomationController` hem route'suz görünen `ShippingAutomation` tarafından kullanılıyor.
  - `ShippingAutomationRuleRepository` için doğrudan controller/service referansı bu taramada netleşmedi; repository kullanım noktası incelenmeli.

- Permission var ama filter ile enforce edilmiyor:
  - `InitialAuthSeeder` `manage_campaigns` ve `manage_campaigns_engine` ekliyor; campaign/coupon route'larında özel `campaign_access` filtresi kullanılıyor ve yalnızca admin rolünü kabul ediyor.
  - `2026-04-17-120000_EnsureSecretaryOperationPermissions.php` operasyon permission'ları ekliyor; route filtresiyle birebir karşılaştırması ayrıca yapılmalı.
  - Admin-only route grubunda `banners`, `dashboard-builder`, `pages`, `marketing`, `pricing`, `automation`, `settings` gibi alanlar role:admin ile korunuyor; granular `perm:*` yok.

- View var ama route/controller bağlantısı yok:
  - `app/Views/site/products/product_selection.php` için `products/selection` route'u var ancak `ProductController::selection()` view dönüşü statik listede görünmedi; incelenmeli.
  - `app/Views/admin/orders/stock_management_view.php` aktif controller view listesinde görünmedi.
  - `app/Views/admin/theme/*` aktif controller view listesinde görünmedi.
  - `app/Views/admin/stock/move.php` route `StockMove::create/store` ile ilişkili olabilir; statik `return view` listesinde görünmediği için incelenmeli.
  - `app/Views/layouts/*` ve `app/Views/partials/sidebar.php` eski/genel layout olarak bazı view'larda kullanılıyor; admin yeni layout ile overlap var.

- Aynı işi yapan birden fazla model:
  - `RoleModel.php` ve `RoleModels.php` ikisi de `roles` tablosuna ve `RoleModel` class adına işaret ediyor gibi görünüyor; autoload/class çakışması riski incelenmeli.
  - `DashboardBlockTypeModel`, `DashboardBlockModel`, `DashboardBlockInstanceModel` ve migration'lardaki `dashboard_blocks` / `dashboard_block_instances` ayrımı netleştirilmeli.
  - `ShippingAutomation.php` ve `ShippingAutomationController.php` benzer controller sorumluluğu taşıyor.
  - `Admin\Dashboard.php` ve `Admin\DashboardController.php` aynı view'a giden iki dashboard controller adayı.

- Migration ile model uyumsuz:
  - `VisitModel` `allowedFields` sadece `id`, `visited_at`; `CreateVisitsTable` migration'ında `user_id` dahil daha fazla alan var.
  - `ShippingModel` CI Model değil; `shipping_companies` ve `orders` tablolarını custom query ile okuyor. Bu bilinçli olabilir, yine de standart model/migration eşleşmesi yok.
  - `_2026-03-04-090000_AddShippedAtToOrdersIfMissing.php` underscore ile başladığı için migration discovery/çalıştırma durumu incelenmeli.
  - Dashboard builder tarafında eski `dashboard_blocks` ile yeni `dashboard_block_instances` birlikte mevcut; model-service migration eşleşmesi detaylı kontrol edilmeli.

- UI var ama backend akışı yok:
  - Cart ve checkout builder UI/service/preview var; gerçek cart/checkout controller, model ve ödeme akışı bu statik taramada görünmedi.
  - Favorites / Wishlist domain için bu repoda belirgin controller/model/migration/view bulunmadı.
  - Review domain için belirgin controller/model/migration/view bulunmadı.
  - Customer admin view var; backend derinliği `Admin\Customers::index` seviyesinde görünüyor, detay CRUD akışı incelenmeli.
  - `admin/automation/index` view/controller var; domain kapsamı ve backend operasyonları belirsiz.

## İlk Bulgular

- Net çalışan yapılar:
  - Auth login/register/logout temel akışı.
  - Admin/secretary route-level RBAC ve permission filtresi.
  - Admin products, orders, shipping, stock, notifications, settings permissions ekran bağlantıları.
  - Storefront home ve product list/detail public akışı.
  - Page builder ve dashboard builder için controller-service-view-migration omurgası.

- Kısmen çalışanlar:
  - Secretary erişimi: route ve permission altyapısı var; campaign/coupon filtresi secretary'yi dışarıda bırakıyor.
  - Dashboard: yeni `DashboardController` aktif, legacy `Dashboard` dosyası duruyor.
  - Shipping automation: aktif `ShippingAutomationController` var, ayrıca benzer `ShippingAutomation` controller'ı route'suz görünüyor.
  - Cart/checkout: builder/preview UI var; gerçek alışveriş sepeti/ödeme runtime akışı net değil.

- Eksikler:
  - Favorites / Wishlist domain dosyaları bulunamadı.
  - Review domain dosyaları bulunamadı.
  - Aktif `Admin\Users` ve `Admin\Roles` controller/route akışı yok; sadece yorum satırı route izi var.
  - Cart/checkout için tam backend model/controller akışı görünmedi.

- Riskli / belirsiz alanlar:
  - `RoleModel.php` ve `RoleModels.php` olası class/model çakışması.
  - `Filters.php` içinde yinelenen `auth` alias'ı.
  - CSRF global olarak kapalı görünüyor; güvenlik davranışı ayrıca incelenmeli.
  - `VisitModel` migration alanlarıyla uyumsuz görünüyor.
  - `Admin\Dashboard`, `Admin\OrderController`, `Admin\ShippingAutomation` aktif route'suz olabilir.
  - Eski/genel layoutlar ile yeni admin/site layoutlarının birlikte yaşaması bağlantı karmaşası yaratabilir.

- Bir sonraki analiz dosyası önerisi:
  - `ai/domain-cube/01_rbac_and_route_matrix.md`: route -> filter -> controller -> permission -> view matrisi.
