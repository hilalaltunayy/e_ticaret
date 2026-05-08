# Executive summary

- Gerçek storefront checkout sayfası artık `GET /yardim/odeme` üzerinden çalışıyor ve `Checkout::index -> CheckoutService -> site/checkout/index.php` akışıyla render ediliyor.
- Admin checkout builder daha önce zaten vardı; config verisi `block_instances.config_json` içinde, `zone = checkout_layout` ve `_template = checkout_layout` ile saklanıyor.
- Draft / published yaşam döngüsü checkout için hazır.
- Ancak gerçek `/yardim/odeme` şu an published checkout builder config okumuyor.
- En güvenli bağlama yaklaşımı, cart/product_detail binding pattern’ine benzer küçük bir read-only storefront binding service eklemek ve yalnızca güvenli metin / görünürlük alanlarını consume etmek.
- Mevcut admin preview, accepted real checkout UI’dan ayrışıyor çünkü preview hâlâ form-benzeri bir builder maketi; gerçek storefront ise readonly, kart-temelli ödeme shell’i.

# Storefront checkout route/view flow

- Route:
  - `app/Config/Routes.php`
  - `GET /yardim/odeme -> Checkout::index` (`auth`)
- Controller:
  - `app/Controllers/Checkout.php`
  - login kontrolü yapıyor
  - boş sepeti `/yardim/sepetim` sayfasına geri yönlendiriyor
  - `CheckoutService::buildCheckoutViewModel()` çağırıyor
- Service:
  - `app/Services/CheckoutService.php`
  - mevcut `CartService::getCartViewModel()` çıktısını readonly checkout view model’e sarıyor
  - contact / delivery / billing / payment / security note kurgusu burada
- View:
  - `app/Views/site/checkout/index.php`
  - mevcut storefront theme’e uygun kartlı shell
  - gerçek ödeme işlemi yapmıyor

# Admin checkout builder route/save/publish flow

- Builder açılışı:
  - `GET /admin/pages/checkout/builder`
  - `Admin\PageController::builder()`
  - checkout sayfası için `admin/pages/checkout_builder` view’ını seçiyor
- Builder update endpoint:
  - `POST /admin/pages/checkout-builder/update`
  - `Admin\PageController::updateCheckoutBuilder()`
  - `CheckoutPageBuilderService::updateConfig()` çağırıyor
- Draft lifecycle:
  - `POST /admin/pages/drafts/create`
  - `POST /admin/pages/drafts/duplicate`
  - `POST /admin/pages/drafts/archive`
  - `POST /admin/pages/drafts/unpublish`
  - `POST /admin/pages/builder/draft/update`
  - `POST /admin/pages/builder/draft/publish`
  - `POST /admin/pages/builder/draft/schedule`
  - `POST /admin/pages/builder/draft/unschedule`
- Preview:
  - `CheckoutPreviewRenderer`
  - `admin/pages/partials/checkout_preview`

# Saved checkout config keys

Builder şu alanları kaydediyor:

- Üst alan:
  - `sayfa_basligi`
  - `sayfa_alt_basligi`
  - `breadcrumb_goster`
  - `guven_kisa_notu`
- Adım çubuğu:
  - `adim_cubugu_aciklama`
  - `adim_cubugu_gorunur`
- Teslimat / fatura:
  - `teslimat_baslik`
  - `teslimat_aciklama`
  - `ayni_adres_notu`
  - `zorunlu_alan_bilgi_metni`
- Ödeme yöntemi:
  - `odeme_baslik`
  - `odeme_aciklama`
  - `guvenli_odeme_notu`
  - `kart_logo_goster`
  - `guven_rozeti_goster`
- Sipariş özeti:
  - `ozet_baslik`
  - `kupon_alani_goster`
  - `indirim_satiri_goster`
  - `kargo_satiri_goster`
  - `siparis_tipi_notu`
- Bilgilendirme / CTA:
  - `bilgi_kutusu_baslik`
  - `bilgi_kutusu_aciklama`
  - `guven_mesaji`
  - `tamamla_buton_metni`
  - `alt_yardim_metni`
- Section order/visibility:
  - `sections.sayfa_ust_alani`
  - `sections.adim_cubugu`
  - `sections.teslimat_fatura_alani`
  - `sections.odeme_yontemi_alani`
  - `sections.siparis_ozeti_alani`
  - `sections.bilgilendirme_guven_cta_alani`

# Current storefront binding status

- `/yardim/odeme` şu an published checkout builder config **okumuyor**.
- `Checkout.php` sadece `CheckoutService` çıktısını view’a iletiyor.
- `site/checkout/index.php` builder presenter veya page builder binding verisi almıyor.
- Yani checkout builder ile storefront checkout arasında henüz binding katmanı yok.

# Safe admin-controllable fields

Gerçek checkout UI’yi bozmadan güvenle bağlanabilecek alanlar:

- page title / subtitle
  - `sayfa_basligi`
  - `sayfa_alt_basligi`
- breadcrumb visibility
  - `breadcrumb_goster`
- readonly section başlıkları
  - `teslimat_baslik`
  - `odeme_baslik`
  - `ozet_baslik`
- yardımcı açıklamalar
  - `teslimat_aciklama`
  - `odeme_aciklama`
  - `guven_kisa_notu`
  - `guvenli_odeme_notu`
  - `bilgi_kutusu_baslik`
  - `bilgi_kutusu_aciklama`
  - `guven_mesaji`
  - `alt_yardim_metni`
- placeholder CTA metni
  - `tamamla_buton_metni`
  - ama yalnızca disabled placeholder button label olarak
- güvenli görünürlük alanları
  - `kart_logo_goster`
  - `guven_rozeti_goster`
  - `adim_cubugu_gorunur`

# Non-admin-controlled payment/order fields

Admin builder’a bırakılmaması gereken alanlar:

- cart item calculation / line totals
- `subtotal_current`, `grand_total_current`, `total_savings`
- quantity behavior
- remove / clear cart behavior
- stock warnings
- gerçek payment method selection logic
- order creation
- payment creation / payment attempts
- stock reduction
- digital access
- coupon application logic
- shipping fee calculation logic
- empty-cart redirect behavior

Kısacası builder yalnızca metin, section label ve güvenli görünürlük kontrol etmeli; iş mantığı değil.

# Preview mismatch causes

- Admin preview form-benzeri bir checkout akışı çiziyor:
  - adres alanları mock input’lar
  - kredi kartı / havale badge’leri
  - kupon alanı
  - “Siparisi Tamamla” mock CTA
- Gerçek storefront checkout ise:
  - readonly summary shell
  - compact cart item cards
  - placeholder payment state
  - disabled “Odeme Altyapisi Hazirlaniyor” CTA
- Bu yüzden preview şu an gerçek sayfanın düzen mantığını değil, builder’daki section abstraction’ını gösteriyor.

# Default/helper copy notes

Şu an checkout builder tarafında birçok alan default explanatory copy ile prefilled:

- `Teslimat ve odeme adimlarini tamamlayarak siparisinizi olusturun.`
- `SSL korumasi aktif, bilgileriniz guvende islenir.`
- `Teslimat, odeme ve onay adimlarini sirasiyla tamamlayin.`
- `Adres ve iletisim bilgilerinizi eksiksiz girin.`
- `Kart bilgileriniz PCI uyumlu guvenli altyapida islenir.`
- `Adres, odeme ve siparis ozeti bilgilerinizi son kez kontrol edin.`
- `Bir sorun olursa destek ekibimiz yardim icin hazir.`

En güvenli sonraki adım:

- Bu alanları cart/product_detail’de yaptığımız gibi optional hale getirmek
- admin temizlerse boş kalmalarını sağlamak
- storefront’ta boşsa render etmemek
- ancak builder config hiç yoksa mevcut accepted checkout shell fallback metinleriyle çalışmaya devam etmek

# Recommended implementation order

1. `CheckoutStorefrontBindingService` ekle
   - page code `checkout`
   - published version
   - visible `checkout_layout` block
   - read-only presenter

2. `Checkout.php` içine optional binding ekle
   - mevcut `CheckoutService` flow’unu bozmadan view’a ek veri ver

3. `site/checkout/index.php` içinde yalnızca güvenli text bindings consume et
   - başlıklar
   - subtitle
   - breadcrumb visibility
   - readonly helper copy
   - disabled CTA label

4. Optional-copy cleanup yap
   - `CheckoutPageBuilderService`
   - `CheckoutPreviewRenderer`
   - eski default helper copy’nin admin temizlenince geri gelmesini engelle

5. Admin preview’ı gerçek storefront checkout yapısına yaklaştır
   - header
   - compact cart items
   - summary card
   - placeholder payment card
   - form-heavy mock alanları geri plana çek

# Files inspected

- `app/Config/Routes.php`
- `app/Controllers/Checkout.php`
- `app/Services/CheckoutService.php`
- `app/Views/site/checkout/index.php`
- `app/Views/admin/pages/checkout_builder.php`
- `app/Views/admin/pages/partials/checkout_preview.php`
- `app/Services/CheckoutPageBuilderService.php`
- `app/Services/CheckoutPreviewRenderer.php`
- `app/Controllers/Admin/PageController.php`
- `app/Models/PageModel.php`
- `app/Models/PageVersionModel.php`
- `app/Models/BlockInstanceModel.php`
- `app/Services/PageVersionService.php`

Checkout Builder Binding Discovery Complete: YES
