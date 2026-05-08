# CART-BUILDER-BINDING-DISCOVERY

## Executive summary
- Admin tarafında `cart` için gerçek bir builder, draft ve publish akışı var.
- Builder ayarları `page_versions` + `block_instances` içinde saklanıyor; cart için özel config, `cart_layout` zone’unda tek bir layout bloğuna JSON olarak yazılıyor.
- Gerçek storefront cart sayfası `/yardim/sepetim` şu anda bu published builder config’ini okumuyor.
- Admin preview ile gerçek web cart sayfası farklı çünkü preview, `CartPreviewRenderer` içindeki sample/mock item’lar ve yapı bloklarıyla çalışıyor; web cart ise `CartService::getCartViewModel()` ve gerçek iş kurallarıyla render ediliyor.
- En güvenli sonraki sprint: accepted web cart UI’yi kaynak gerçeklik kabul edip, yalnızca güvenli metin/başlık/opsiyonel görünürlük katmanlarını published config’ten okumak.

## Cart storefront route/view flow
- Route:
  - `app/Config/Routes.php`
  - `GET /yardim/sepetim` -> `Cart::index`
- Controller:
  - `app/Controllers/Cart.php`
  - login kontrolü yapıyor
  - `CartService::getCartViewModel($userId)` çağırıyor
  - `CartService::getSuggestedProducts($userId, 4)` çağırıyor
  - `site/cart/index` view’ını render ediyor
- View:
  - `app/Views/site/cart/index.php`
  - gerçek yapı:
    - page header
    - flash / stock / price alerts
    - empty cart veya item listesi
    - summary card
    - suggested products
- Builder bağı:
  - yok; view’a cart builder config’i geçilmiyor.

## Admin cart builder route/save/publish flow
- Admin builder routes:
  - `app/Config/Routes.php`
  - `GET admin/pages/cart/builder`
  - `POST admin/pages/cart-builder/update`
  - ortak draft lifecycle endpoint’leri:
    - `POST admin/pages/drafts/create`
    - `POST admin/pages/drafts/duplicate`
    - `POST admin/pages/drafts/archive`
    - `POST admin/pages/drafts/unpublish`
    - `POST admin/pages/builder/draft/update`
    - `POST admin/pages/builder/draft/publish`
    - `POST admin/pages/builder/draft/schedule`
    - `POST admin/pages/builder/draft/unschedule`
- Controller flow:
  - `app/Controllers/Admin/PageController.php`
  - `builder($pageCode)` cart için:
    - `CartPageBuilderService::getBuilderState()`
    - `CartPreviewRenderer::build()` veya `buildFromFormInput()`
    - `admin/pages/cart_builder` view
  - `updateCartBuilder()`
    - `CartPageBuilderService::updateConfig()`
    - save sonrası tekrar builder ekranına döner
- Publish logic:
  - cart’e özel değil; ortak page builder publish motoru kullanılıyor
  - published version seçimi:
    - `app/Models/PageVersionModel.php::findPublishedByPageId()`
  - publish/unpublish/schedule akışı:
    - `app/Services/PageBuilderService.php`

## Saved cart config keys
- Kaydın fiziksel yeri:
  - `block_instances.config_json`
  - `zone = cart_layout`
  - `_template = cart_layout`
- Config’i üreten servis:
  - `app/Services/CartPageBuilderService.php::buildConfigPayload()`
- Başlıca key grupları:
  - Üst alan:
    - `sayfa_basligi`
    - `sayfa_alt_basligi`
    - `breadcrumb_goster`
    - `kisa_aciklama`
  - Sepet ürünleri alanı:
    - `sepet_urunleri_baslik`
    - `sepet_urunleri_aciklama`
    - `urun_gorseli_goster`
    - `format_etiketi_goster`
    - `adet_kontrolu_goster`
    - `kaldir_buton_metni`
  - Fiyat uyarı alanı:
    - `fiyat_uyari_baslik`
    - `fiyat_uyari_aciklama`
    - `fiyat_farki_bilgi_kutusu_goster`
    - `eski_fiyat_etiketi`
    - `guncel_fiyat_etiketi`
    - `toplam_guncelleme_notu`
  - Stok/uygunluk alanı:
    - `stok_uyari_baslik`
    - `stok_uyari_aciklama`
    - `dusuk_stok_uyarisi_goster`
    - `tukenme_mesaji_goster`
    - `dusuk_stok_mesaj_sablonu`
    - `son_urun_mesaj_sablonu`
  - Kupon/kampanya alanı:
    - `kupon_kampanya_baslik`
    - `kupon_kampanya_aciklama`
    - `kupon_alani_goster`
    - `kampanya_bilgi_notu`
    - `ucretsiz_kargo_bilgi_notu`
  - Özet/CTA alanı:
    - `sepet_ozeti_baslik`
    - `ara_toplam_goster`
    - `indirim_goster`
    - `kargo_goster`
    - `genel_toplam_basligi`
    - `odeme_sayfasina_git_buton_metni`
    - `guvenli_odeme_kisa_notu`
  - Boş sepet alanı:
    - `bos_sepet_baslik`
    - `bos_sepet_aciklama`
    - `alisverise_basla_buton_metni`
  - Section ordering/visibility:
    - `sections.sayfa_ust_alani`
    - `sections.sepet_urunleri_alani`
    - `sections.fiyat_guncelleme_uyari_alani`
    - `sections.stok_uygunluk_uyari_alani`
    - `sections.kupon_kampanya_alani`
    - `sections.sepet_ozeti_cta_alani`
    - `sections.bos_sepet_alani`

## Current storefront binding status
- `pages`, `page_versions`, `block_instances` cart builder tarafında kullanılıyor.
- `/yardim/sepetim` tarafında şu an:
  - `PageService` yok
  - `PageVersionModel::findPublishedByPageId()` yok
  - `BlockInstanceModel` yok
  - `CartPageBuilderService` yok
  - builder config’i view’a geçilmiyor
- Sonuç:
  - storefront cart published cart config tüketmiyor.

## Preview mismatch causes
- Admin preview kaynağı:
  - `app/Services/CartPreviewRenderer.php`
  - sample item setleri:
    - defter seti
    - PDF paket
    - kalem
- Preview, cart iş kurallarını değil örnek yapı bloklarını temsil ediyor:
  - mock fiyat değişimi
  - mock stok mesajı
  - mock kupon alanı
  - mock toplam alanı
- Gerçek storefront ise:
  - `CartService` tarafından hazırlanmış gerçek kullanıcı sepetini render ediyor
  - accepted web UI’ye sahip
  - mevcut CSS ve layout farklı
- Bu yüzden preview, gerçek `site/cart/index` yapısına değil builder’ın section modeline daha yakın.

## Safe admin-controllable sections
- Accepted web cart UI korunarak admin’e açılabilecek güvenli alanlar:
  - page title / subtitle / breadcrumb görünürlüğü
  - empty cart title / description / CTA metni
  - summary card başlık/metinleri
  - info/help microcopy:
    - shipping note
    - secure payment note
    - price-change explainer text
    - stock-warning explainer text
  - optional section visibility, yalnızca mevcut accepted UI bunu zaten destekliyorsa
- Yani metin, açıklama, başlık ve bazı güvenli görünürlük alanları kontrollü şekilde bağlanabilir.

## Non-admin-controlled business logic sections
- Admin builder tarafından kontrol edilmemesi gereken alanlar:
  - cart item calculation
  - quantity increase/decrease behavior
  - remove item behavior
  - clear cart behavior
  - stock validation
  - price snapshot vs current price comparison logic
  - subtotal / savings / grand total hesapları
  - digital vs physical quantity rules
  - suggested products selection logic
  - authentication/ownership checks
- Bunlar `CartService` ve controller iş mantığının parçası; builder yalnızca sunum katmanına dokunmalı.

## Recommended implementation order
1. Yeni bir `CartStorefrontBindingService` ekle; yalnızca published `cart` config’ini okusun.
2. `Cart::index()` içinde bu binding verisini opsiyonel olarak view’a geçir.
3. `site/cart/index` içinde yalnızca güvenli metin/başlık alanlarını published config’ten oku:
   - page title/subtitle
   - empty state copy
   - summary labels
   - helper notes
4. Accepted web cart yapısını aynen koru; section order’ı veya item layout’u builder’a teslim etme.
5. Sonra admin preview’ı accepted real storefront cart yapısına yaklaştır:
   - mevcut `CartPreviewRenderer` ve `admin/pages/partials/cart_preview.php` gerçek cart page yapısına göre sadeleştirilsin
   - storefront preview’ye benzesin, storefront preview builder’a değil
6. En son, gerçekten gerekli olan birkaç görünürlük flag’i varsa dikkatli şekilde bağla.

## Files inspected
- `app/Config/Routes.php`
- `app/Controllers/Cart.php`
- `app/Controllers/Admin/PageController.php`
- `app/Views/site/cart/index.php`
- `app/Views/admin/pages/cart_builder.php`
- `app/Views/admin/pages/partials/cart_preview.php`
- `app/Services/CartPageBuilderService.php`
- `app/Services/CartPreviewRenderer.php`
- `app/Services/PageBuilderService.php`
- `app/Models/PageVersionModel.php`
- `app/Models/BlockInstanceModel.php`

Cart Builder Binding Discovery Complete: YES
