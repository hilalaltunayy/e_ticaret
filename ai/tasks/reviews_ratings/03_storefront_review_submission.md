# Sprint 3 — Storefront Review Submission

## Added files
- `C:\code\e_ticaret\app\Services\ReviewService.php`
- `C:\code\e_ticaret\ai\tasks\reviews_ratings\03_storefront_review_submission.md`

## Modified files
- `C:\code\e_ticaret\app\Config\Routes.php`
- `C:\code\e_ticaret\app\Controllers\ProductController.php`
- `C:\code\e_ticaret\app\Views\site\products\product_detail.php`

## Routes added
- `POST /products/detail/(:segment)/reviews` -> `ProductController::submitReview/$1`

## Service flow
- `getApprovedReviewsForProduct(string $productId): array`
  - approved review kayıtlarını kullanıcı adı ile birlikte döndürür
- `getReviewSummaryForProduct(string $productId): array`
  - approved review count ve average rating döndürür
- `createReview(string $userId, string $productId, array $payload): array`
  - `rating` 1-5 doğrular
  - `title` max 255 doğrular
  - `title` veya `comment` alanlarından en az biri dolu olmalıdır
  - purchase eligibility kontrolünü `ReviewEligibilityService` ile yapar
  - duplicate active review varsa engeller
  - kaydı `pending` statüsüyle oluşturur

## Product Detail Changes
- Review summary artık gerçek approved review summary verisini kullanır.
- Approved reviews varsa listelenir.
- Approved review yoksa empty-state görünür.
- Logged-in kullanıcı uygunsa review formu görünür.
- Logged-out kullanıcıya login mesajı gösterilir.
- Uygun olmayan kullanıcıya satın alma gerekliliği mesajı gösterilir.
- Daha önce review gönderen kullanıcıya duplicate-state mesajı gösterilir.
- Builder kaynaklı review section text alanları korunur.

## Eligibility and Duplicate Rules
- Purchase check:
  - `orders.user_id + order_items.product_id`
- Kabul edilen muhafazakar statüler:
  - `orders.order_status IN ('shipped', 'delivered', 'return_in_progress', 'return_done')`
  - veya `orders.payment_status = 'paid'`
  - veya legacy `orders.status IN ('paid', 'shipped', 'completed', 'returned')`
- Duplicate engeli:
  - aynı kullanıcı + ürün için `pending`, `approved`, `hidden` review varsa yeni kayıt engellenir

## Validation Notes
- Syntax checks:
  - `php -l app/Services/ReviewService.php`
  - `php -l app/Controllers/ProductController.php`
  - `php -l app/Views/site/products/product_detail.php`
  - `php -l app/Config/Routes.php`
- Manual checks:
  - product detail yükleniyor mu
  - approved reviews render oluyor mu
  - empty-state doğru mu
  - logged-out kullanıcı submit edemiyor mu
  - satın almayan kullanıcı submit edemiyor mu
  - eligible kullanıcı pending review oluşturabiliyor mu
  - pending review public görünmüyor mu
  - duplicate review bloklanıyor mu
  - `/admin/reviews` aynı şekilde açılıyor mu

## Blockers or Follow-ups
- Admin moderation CRUD hâlâ eksik
- Eski order verilerinde `orders.user_id` veri kalitesi purchase eligibility sonucunu etkileyebilir
- Review pagination/sorting/helpful votes henüz yok

## Final Verdict
- Storefront review submission ve approved review rendering temel akışı hazır.
