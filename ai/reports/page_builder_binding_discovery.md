# PAGE-BUILDER-BINDING-DISCOVERY

## Executive summary
- Admin tarafında gerçek bir page builder altyapısı var: `pages`, `page_versions`, `block_types`, `block_instances`.
- Yönetilebilir sayfa kodları mevcut: `home`, `product_list`, `product_detail`, `cart`, `checkout`.
- Draft / publish / schedule / archive akışı uygulanmış durumda.
- Storefront tarafında yayınlanmış builder verisini gerçekten tüketen tek sayfa `home`.
- `product_list`, `product_detail` ve `cart` için admin builder var, ancak gerçek storefront hâlâ kendi sabit controller/view akışını kullanıyor.
- `checkout` için admin builder ve preview var, fakat gerçek storefront route/controller/view bağlantısı görünmüyor.
- Admin preview ile gerçek storefront arasındaki farkın ana nedeni: preview katmanı mock/sample veri ve ayrı preview renderer’ları kullanıyor; storefront ise canlı şablonları doğrudan render ediyor.

## Admin builder route map
- `app/Config/Routes.php`
  - `GET admin/pages`
  - `GET admin/pages/(:segment)`
  - `GET admin/pages/(:segment)/drafts`
  - `GET admin/pages/(:segment)/builder`
  - `GET admin/page-versions/(:segment)`
  - `POST admin/pages/drafts/create`
  - `POST admin/pages/drafts/duplicate`
  - `POST admin/pages/drafts/start-editing`
  - `POST admin/pages/drafts/archive`
  - `POST admin/pages/drafts/unpublish`
  - `POST admin/pages/builder/draft/update`
  - `POST admin/pages/builder/draft/publish`
  - `POST admin/pages/builder/draft/schedule`
  - `POST admin/pages/builder/draft/unschedule`
  - `POST admin/pages/builder/blocks`
  - `POST admin/pages/builder/blocks/update`
  - `POST admin/pages/builder/blocks/delete`
  - `POST admin/pages/builder/blocks/reorder`
  - Sayfa-özel update endpoint’leri:
    - `POST admin/pages/product-list-builder/update`
    - `POST admin/pages/product-detail-builder/update`
    - `POST admin/pages/cart-builder/update`
    - `POST admin/pages/checkout-builder/update`

## Admin builder save/publish flow
- Giriş servisleri:
  - `app/Services/PageService.php`
  - `app/Services/PageVersionService.php`
  - `app/Services/PageBuilderService.php`
- Builder giriş noktası:
  - `app/Controllers/Admin/PageController.php::builder()`
- Kayıt yeri:
  - Sayfa meta: `pages`
  - Versiyon meta: `page_versions`
  - Blok kayıtları ve config JSON: `block_instances`
  - Blok tanımları: `block_types`
- Draft / publish mantığı:
  - `PageBuilderService::createDraftIfNotExists()`
  - `createDraft()`
  - `publishDraft()`
  - `scheduleDraft()`
  - `unpublishVersion()`
  - `archiveDraft()`
- Sayfa-özel builder kaydı:
  - `product_list` -> `product_list_layout` bloğu
  - `product_detail` -> `notice` tipli blok içinde `product_detail_layout` config
  - `cart` -> `notice` tipli blok içinde `cart_layout` config
  - `checkout` -> `notice` tipli blok içinde `checkout_layout` config

## Storefront route map
- `app/Config/Routes.php`
  - `GET /` -> `StorefrontController::home`
  - `GET /products/list/(:any)` -> `ProductController::listByType`
  - `GET /products/list/(:any)/(:any)` -> `ProductController::listByCategory`
  - `GET /products/detail/(:segment)` -> `ProductController::detail`
  - `GET /yardim/sepetim` -> `Cart::index`
- Checkout / payment:
  - Storefront için net bir `checkout` veya `payment` route bulunmadı.

## Page-by-page binding status table
| Page code | Admin manageable | Storefront route/view present | Published builder data consumed | Notes |
|---|---|---|---|---|
| `home` | Yes | Yes | Yes | `StorefrontHomeService` published version ve block instances okuyor. |
| `product_list` | Yes | Yes | No | `ProductController` doğrudan `site/products/index` render ediyor. |
| `product_detail` | Yes | Yes | No | `ProductController` doğrudan `site/products/product_detail` render ediyor. |
| `cart` | Yes | Yes | No | `Cart` controller doğrudan `site/cart/index` render ediyor. |
| `checkout` | Yes | No clear storefront page | No | Admin builder hazır, storefront tarafı görünmüyor. |

## Preview mismatch notes
- `app/Controllers/Admin/PageController.php`
  - Builder ekranı sayfa koduna göre ayrı admin preview view’ları açıyor.
- Preview renderer’lar:
  - `app/Services/ProductListPreviewRenderer.php`
  - `app/Services/ProductDetailPreviewRenderer.php`
  - `app/Services/CartPreviewRenderer.php`
  - `app/Services/CheckoutPreviewRenderer.php`
- Fark nedeni:
  - Preview, örnek / mock veri üretip admin içindeki özel preview şablonlarını kullanıyor.
  - Gerçek storefront, mevcut production controller + service + live view zincirini kullanıyor.
  - Ortak bir “published builder config -> storefront renderer” katmanı henüz sadece `home` için var.
- Bu yüzden özellikle `product_detail` ve `cart` preview’leri, gerçek `site/products/product_detail` ve `site/cart/index` ile birebir eşleşmiyor.

## Checkout/payment existence check
- Admin tarafında mevcut:
  - `app/Views/admin/pages/checkout_builder.php`
  - `app/Services/CheckoutPageBuilderService.php`
  - `app/Services/CheckoutPreviewRenderer.php`
  - `POST admin/pages/checkout-builder/update`
- Storefront tarafında bulunamadı:
  - belirgin `checkout` route
  - müşteri checkout controller aksiyonu
  - `site/checkout/...` view akışı
- Sonuç:
  - Checkout builder, storefront implementasyonundan önce hazırlanmış görünüyor.

## Risks
- `product_list`, `product_detail` ve `cart` için builder config’i doğrudan mevcut view’lara bağlamak, mevcut kabul edilmiş UI’yi bozabilir.
- Preview ve storefront şu an iki ayrı render sistemi olduğu için “preview neyse canlı o” garantisi yok.
- `product_detail`, `cart`, `checkout` sayfalarında config’in `notice` tabanlı özel JSON yapıya gömülmesi, generic blok sistemine göre daha kırılgan bir bağlama noktası oluşturuyor.
- Checkout storefront’u olmadan checkout builder’ı canlıya bağlamak eksik ve yanıltıcı olur.

## Recommended next sprint order
1. `home` akışına dokunma; mevcut published binding referans alınsın.
2. `product_list` için published builder config’ini mevcut `site/products/index` içine güvenli, opsiyonel bir presenter katmanıyla bağla.
3. `product_detail` için aynı yaklaşımı uygula; mevcut canlı UI korunurken builder sadece düzen/section görünürlüğünü etkilesin.
4. `cart` için builder config’ini `site/cart/index` üstüne kademeli bağla.
5. En son gerçek storefront checkout route/controller/view oluştur; sonra `checkout` builder’ı bağla.
6. Preview ile storefront’u aynı render/presenter kurallarına yaklaştır; ayrı mock preview mantığını mümkün olduğunca azalt.

## Files inspected
- `app/Config/Routes.php`
- `app/Controllers/Admin/PageController.php`
- `app/Controllers/StorefrontController.php`
- `app/Controllers/ProductController.php`
- `app/Controllers/Cart.php`
- `app/Services/PageService.php`
- `app/Services/PageVersionService.php`
- `app/Services/PageBuilderService.php`
- `app/Services/StorefrontHomeService.php`
- `app/Services/ProductListPreviewRenderer.php`
- `app/Services/ProductDetailPageBuilderService.php`
- `app/Services/ProductDetailPreviewRenderer.php`
- `app/Services/CartPageBuilderService.php`
- `app/Services/CartPreviewRenderer.php`
- `app/Services/CheckoutPageBuilderService.php`
- `app/Services/CheckoutPreviewRenderer.php`
- `app/Models/PageModel.php`
- `app/Models/PageVersionModel.php`
- `app/Models/BlockTypeModel.php`
- `app/Models/BlockInstanceModel.php`
- `app/Database/Migrations/2026-04-04-100000_CreatePagesTable.php`
- `app/Database/Migrations/2026-04-04-100100_CreatePageVersionsTable.php`
- `app/Database/Migrations/2026-04-04-100200_CreateBlockTypesTable.php`
- `app/Database/Migrations/2026-04-04-100300_CreateBlockInstancesTable.php`
- `app/Database/Seeds/PageManagementSeeder.php`
- `app/Views/admin/pages/index.php`
- `app/Views/admin/pages/show.php`
- `app/Views/admin/pages/version.php`
- `app/Views/admin/pages/drafts.php`
- `app/Views/admin/pages/builder.php`
- `app/Views/admin/pages/product_list_builder.php`
- `app/Views/admin/pages/product_detail_builder.php`
- `app/Views/admin/pages/cart_builder.php`
- `app/Views/admin/pages/checkout_builder.php`
- `app/Views/site/storefront/home.php`
- `app/Views/site/products/index.php`
- `app/Views/site/products/product_detail.php`
- `app/Views/site/cart/index.php`

Page Builder Binding Discovery Complete: YES
