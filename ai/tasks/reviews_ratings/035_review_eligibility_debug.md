# Sprint 3.5 — Review Eligibility Debug

## Final Verdict
En olası neden review formunun bloklanması: **wrong order status/payment status**.

Örnek sipariş `DMO-260408-0008` için gerçek kayıt:
- `orders.user_id` dolu
- `order_items.product_id` ürün detay UUID’si ile eşleşiyor
- ama sipariş statüleri:
  - `status = reserved`
  - `order_status = pending`
  - `payment_status = unpaid`
  - `shipping_status = not_shipped`
  - `fulfillment_status = PENDING`

Bu değerler mevcut `ReviewEligibilityService` tarafından **reviewable** kabul edilmiyor.

## Files Inspected
- `C:\code\e_ticaret\app\Services\ReviewEligibilityService.php`
- `C:\code\e_ticaret\app\Controllers\ProductController.php`
- `C:\code\e_ticaret\app\Controllers\CustomerOrders.php`
- `C:\code\e_ticaret\app\Services\CustomerOrderService.php`
- `C:\code\e_ticaret\app\Models\OrderModel.php`
- `C:\code\e_ticaret\app\Models\OrderItemModel.php`
- `C:\code\e_ticaret\app\Services\OrdersService.php`
- `C:\code\e_ticaret\app\Services\OrderCreationService.php`
- `C:\code\e_ticaret\app\Config\Routes.php`
- `C:\code\e_ticaret\app\Views\site\orders\show.php`
- `C:\code\e_ticaret\app\Views\site\products\product_detail.php`
- `C:\code\e_ticaret\app\Database\Seeds\OrdersShippingDemoSeeder.php`

## Eligibility Flow
- Product detail controller current user id’yi şu sırayla çözüyor:
  - `session('user')['id']`
  - fallback `session('user_id')`
- `ProductController::detail($id)` içinde:
  - `$productId` ürün detay route parametresinden geliyor
  - `ReviewService::canUserReviewProduct($currentUserId, $productId)` çağrılıyor
  - sonuç `reviewUiState['canSubmitReview']` olarak view’a gidiyor
- `ReviewEligibilityService::canUserReviewProduct()` query akışı:
  - `orders.user_id = current user id`
  - `order_items.order_id = orders.id`
  - `order_items.product_id = product detail product id`
  - ayrıca şu statülerden biri gerekli:
    - `orders.order_status IN ('shipped', 'delivered', 'return_in_progress', 'return_done')`
    - veya `orders.payment_status = 'paid'`
    - veya legacy `orders.status IN ('paid', 'shipped', 'completed', 'returned')`

## Order Detail Flow
- Route:
  - `GET /yardim/siparislerim/(:segment)` -> `CustomerOrders::show/$1`
- `CustomerOrders::show()` current user id’yi yine:
  - `session('user')['id']`
  - fallback `session('user_id')`
  ile çözüyor
- `CustomerOrderService::getOrderDetailForUser()` order’ı şu filtreyle yüklüyor:
  - `orders.user_id = current user id`
  - `orders.id = identifier OR orders.order_no = identifier`
- Sipariş detayındaki ürün linki `CustomerOrderService::buildOrderItems()` içinde kuruluyor:
  - `detail_url = base_url('products/detail/' . $item['product_id'])`
- Yani sipariş detayı ile ürün detay linki aynı `order_items.product_id` üzerinden bağlı.

## Product Detail Flow
- Route:
  - `GET /products/detail/(:segment)` -> `ProductController::detail/$1`
- `ProductController::detail()`:
  - ürün DTO’sunu yükler
  - current user id ve role çözer
  - permission check yapar:
    - `create_review`
    - `rate_product`
  - duplicate review check yapar
  - permission + duplicate geçerse eligibility check yapar
- View tarafında form görünürlüğü:
  - `!isLoggedIn` -> login mesajı
  - `hasSubmittedReview` -> duplicate mesajı
  - `!canSubmitReview` -> “Yorum yapabilmek için ürünü satın almış olmanız gerekir.”

Önemli not:
- Bu mesaj sadece “hiç satın almamış” durumunu değil,
- **satın almış ama henüz reviewable statüye gelmemiş** siparişi de kapsıyor.

## Likely Blocker
- **wrong order status/payment status**

Gerekçe:
- Aynı current user id her iki sayfada da kullanılıyor.
- Somut order kaydında `orders.user_id` dolu:
  - `57f01727-71c9-4921-96c0-1a55eeeabed3`
- Somut order item product id, ürün detay UUID’si ile birebir eşleşiyor:
  - `0af610e7-e96b-43d9-9215-3fcbc5a6f44d`
- Ama sipariş statüleri review eligibility whitelist’ine girmiyor:
  - `reserved`
  - `pending`
  - `unpaid`
- Bu yüzden query doğal olarak `false` dönüyor.

Ek bulgu:
- Bu örnek siparişin sahibi `admin@site.com / admin` kullanıcısı görünüyor.
- Yani issue customer/session mismatch değil; kayıt gerçekten bu oturuma ait.

## Recommended Fix
En küçük güvenli düzeltme:

1. **Önce iş kuralını netleştirin**
- Review yalnızca:
  - `paid`
  - `shipped`
  - `delivered`
  gibi siparişlerde mi açılmalı?
- Yoksa `pending/unpaid` sipariş de yeterli mi?

2. **Mesajı eligibility sebebine göre ayırın**
- Bugünkü metin yanıltıcı.
- `canUserReviewProduct()` false olduğunda en az iki durum ayrılmalı:
  - ürün hiç satın alınmamış
  - ürün satın alınmış ama sipariş henüz yorum için uygun statüde değil

3. **Eğer business kuralı izin veriyorsa whitelist’i genişletin**
- Örneğin review, sipariş görünür olur olmaz açılacaksa:
  - `orders.order_status = pending`
  - veya `orders.status = reserved`
  gibi durumlar ayrıca kabul edilebilir
- Ama bu karar net olmadan doğrudan kod değişikliği yapılmamalı.

4. **İsteğe bağlı ek debug iyileştirmesi**
- `ReviewService` veya controller tarafında failure reason ayrıştırılırsa,
  storefront doğru Türkçe mesaj gösterebilir.
