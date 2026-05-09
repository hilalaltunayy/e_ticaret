# Sprint 1 — Reviews/Ratings Discovery

## Final Verdict
- Reviews existing: NO
- Ratings existing: PARTIAL
- Product detail review UI existing: PARTIAL
- Purchased-user validation possible now: PARTIAL
- Ready for implementation sprint: NO, with blockers

## Findings
- `C:\code\e_ticaret\app\Database\Migrations\2026-02-07-104831_CreateInitialSchemaUuid.php`
  - `products`, `orders`, `users`, `permissions` temel tabloları var; `reviews` veya `ratings` tablosu yok.
  - Schema başlangıcı review/rating domain’i için hiç foundation vermiyor.

- `C:\code\e_ticaret\app\Database\Migrations\2026-02-25-191500_AddOrderManagementSchema.php`
  - `orders.user_id` sonradan eklenmiş, ayrıca `order_items` tablosu oluşturulmuş.
  - “Satın alan kullanıcı bu ürünü aldı mı?” kontrolü için gerekli ilişki parçaları burada başlıyor.

- `C:\code\e_ticaret\app\Database\Migrations\2026-05-05-180000_EnsurePermissionCoverageAndDescriptions.php`
  - Yalnızca `manage_reviews` izni ekleniyor.
  - Moderasyon tarafı düşünülmüş, ama müşteri review/rating aksiyon izinleri henüz yok.

- `C:\code\e_ticaret\app\Models\OrderModel.php`
  - `user_id`, `product_id`, `order_status`, `payment_status`, `fulfillment_status` alanları tanımlı; `getOrderItems()` mevcut.
  - Sipariş sahipliği ve ürün sahipliği teorik olarak sorgulanabilir.

- `C:\code\e_ticaret\app\Models\OrderItemModel.php`
  - `order_id`, `product_id`, `item_status` ve snapshot alanları mevcut.
  - Ürün bazlı satın alma doğrulaması için en doğru kaynak burası olur.

- `C:\code\e_ticaret\app\Models\ProductsModel.php`
  - `products.id` UUID (`CHAR(36)`) ve model `BaseUuidModel` kullanıyor.
  - Product flow tarafında int yerine UUID/string bekleniyor.

- `C:\code\e_ticaret\app\Models`
  - `ReviewModel` veya `RatingModel` yok.
  - Domain persistence katmanı eksik.

- `C:\code\e_ticaret\app\Controllers\Admin\Reviews.php`
  - Sadece admin index sayfası var; özetler hardcoded `0`, liste boş.
  - Gerçek moderasyon CRUD akışı henüz yok.

- `C:\code\e_ticaret\app\Controllers\ProductController.php`
  - Ürün detay sayfası `detail($id)` ile render ediliyor; published builder text binding var.
  - Review/rating verisi controller seviyesinde yüklenmiyor.

- `C:\code\e_ticaret\app\Controllers\OrderController.php`
  - Sipariş oluşturma akışı `OrdersService::createReservedOrder()` çağırıyor.
  - Review için sipariş sahipliği önemliyken bu akış müşteri siparişini review-ready hale getirmiyor.

- `C:\code\e_ticaret\app\Services\OrdersService.php`
  - Sipariş oluşturma ve statü güncelleme mantığı var; review/rating servisi yok.
  - Sipariş verisi mevcut ama “yorum yapabilir mi?” kontrolü için hazır method yok.

- `C:\code\e_ticaret\app\Models\OrderModel.php`
  - `createOrderReserved()` sipariş insert ederken `user_id` set etmiyor.
  - En kritik blokerlerden biri bu: customer purchase ownership verisi her siparişte güvenilir olmayabilir.

- `C:\code\e_ticaret\app\Services\OrderCreationService.php`
  - `order_items` snapshot üretiyor, ama sipariş kaydı yine legacy `orders.product_id` + `quantity` akışına bağlı.
  - Review eligibility kontrolü yazılırken `orders` ve `order_items` birlikte değerlendirilmeli.

- `C:\code\e_ticaret\app\Config\Routes.php`
  - Admin route var: `GET admin/reviews` (`perm:manage_reviews`).
  - Customer/public review create/list/rate/approve/hide/delete route’ları yok.

- `C:\code\e_ticaret\app\Views\site\products\product_detail.php`
  - Review tabı, yıldız görünümü ve placeholder metinler var; gerçek review listesi yok.
  - UI “hazırmış gibi” görünüyor ama veri ve aksiyon bağlantısı yok.

- `C:\code\e_ticaret\app\Services\ProductDetailPageBuilderService.php`
  - Builder sadece review section presentation config saklıyor: başlık, özet, callout, rating/review count visibility.
  - Bu gerçek review/rating verisi değil, yalnızca sunum metni.

- `C:\code\e_ticaret\app\Services\ProductDetailPreviewRenderer.php`
  - Admin preview `rating = 4.8`, `review_count = 126` gibi mock sample data kullanıyor.
  - Preview ile gerçek storefront farkının nedeni gerçek veri yerine örnek verinin kullanılması.

- `C:\code\e_ticaret\app\Services\ProductDetailStorefrontBindingService.php`
  - Storefront yalnızca güvenli review section text alanlarını consume ediyor.
  - Yorum/rating domain’i hâlâ builder presentation seviyesinde.

## Product Detail Flow
- Route: `GET /products/detail/(:segment)` -> `ProductController::detail/$1`
- Controller: `C:\code\e_ticaret\app\Controllers\ProductController.php`
- Service, if any: `ProductsService` ürün verisini yüklüyor; `ProductDetailStorefrontBindingService` sadece builder text binding yapıyor.
- View: `C:\code\e_ticaret\app\Views\site\products\product_detail.php`
- Review/rating UI status:
  - Yıldız görünümü var
  - Review sekmesi var
  - Placeholder review empty-state metinleri var
  - Gerçek review listesi yok
  - Review formu yok
  - Rating submit/input akışı yok

## Purchase Ownership Flow
- Current order tables/models:
  - `orders` / `OrderModel`
  - `order_items` / `OrderItemModel`
- Current user relation:
  - `orders.user_id` alanı mevcut
  - Customer order ekranı `orders.user_id` üzerinden çalışıyor
- Current product relation:
  - Legacy: `orders.product_id`
  - Daha doğru yeni ilişki: `order_items.product_id`
- Can verify purchase? Explain briefly.
  - PARTIAL.
  - Şema düzeyinde `orders.user_id + order_items.product_id` ile doğrulama mümkün.
  - Ancak `OrderModel::createOrderReserved()` akışında `user_id` insert edilmediği için mevcut veri kalitesi/tutarlılığı garanti değil.
  - Ayrıca purchase eligibility için hangi sipariş statülerinin kabul edileceği (`paid`, `shipped`, `delivered`) henüz tanımlı değil.

## Permissions / RBAC
- Existing review/rating permissions:
  - `manage_reviews`
- Missing permissions:
  - `create_review`
  - `rate_product`
  - `delete_reviews`
  - Gerekirse ayrıca `approve_reviews` / `hide_reviews` veya tek başına `moderate_reviews`

## Recommended Next Sprint Scope
- `reviews` tablosu foundation schema
  - `id`, `product_id`, `user_id`, `order_id` (veya `order_item_id`), `rating`, `title`, `body`, `status`, timestamps, soft delete
- Permission groundwork
  - `create_review`, `rate_product`, `delete_reviews`
  - Moderation için `manage_reviews` altında net davranış
- Purchase-eligibility groundwork
  - “Satın alan kullanıcı” doğrulama query/service kuralı
  - Geçerli sipariş statülerinin net tanımı
- Demo seed groundwork
  - Yalnızca sabit test user/product/order anahtarlarıyla, non-destructive review seed planı
- Moderation groundwork
  - İlk sprintten itibaren status alanı eklenmeli

## Risks / Blockers
- `C:\code\e_ticaret\app\Models\OrderModel.php`
  - `createOrderReserved()` içinde `user_id` set edilmiyor.
  - “Only purchased users can review” kuralı için ana veri güvenilirliği riski.

- `C:\code\e_ticaret\app\Database\Migrations\2026-02-07-104831_CreateInitialSchemaUuid.php`
  - Legacy `orders.product_id` tek ürünlü sipariş yapısı hâlâ mevcut.
  - `order_items` ile birlikte çift kaynaklı order-product ilişkisi var.

- `C:\code\e_ticaret\app\Views\site\products\product_detail.php`
  - Placeholder UI gerçek feature varmış izlenimi veriyor.
  - Implementation sprintinde gerçek data bağlanana kadar UX dikkatli yönetilmeli.

- UUID/int mismatch
  - İncelenen product flow’da aktif int kimlik kullanımı bulunmadı.
  - `products.id`, `orders.id`, `order_items.id`, `users.id` UUID/string tabanlı.
  - Risk int/UUID mismatch değil; legacy `orders.product_id` ile yeni `order_items.product_id` arasında mantıksal kaynak ayrışması.

- Moderation requirement
  - YES, first implementation’dan itibaren gerekli.
  - Önerilen başlangıç statüleri:
    - `pending`
    - `approved`
    - `hidden`
    - opsiyonel: `rejected`
