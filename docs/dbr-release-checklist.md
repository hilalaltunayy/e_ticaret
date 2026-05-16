# DBR Release Validation Checklist (TICKET-DBR-012)

## 1) DBR Release Kapsam Özeti
Bu dokuman, DBR-002..DBR-011 kapsamindaki dijital kutuphane/reader/highlight/admin/seed akisinin release oncesi dogrulama sonucunu icerir.

Kapsam:
- E2E kritik akislar
- Guvenlik smoke testleri
- Regresyon notlari
- Bilinen limitasyonlar

Kapsam disi:
- Yeni feature
- Refactor
- Yeni migration/model
- UI redesign

## 2) Test Ortami Bilgisi
- Uygulama: CodeIgniter 4
- Lokal URL: `http://localhost:8080`
- Tarih: 2026-05-16
- Dogrulama tipi: CLI + DB smoke + kisitli HTTP smoke + manuel browser gerektiren adimlar

## 3) Demo Kullanici Bilgisi
- Email: `demo.reader@example.test`
- Password: `DemoReader123!`
- Role: `user`

## 4) Kritik E2E Senaryolari
| Senaryo | Beklenen | Sonuc | Not |
| --- | --- | --- | --- |
| DBR-002: Auth user `/yardim/dijital-kitaplarim` acilisi | Sayfa acilir | MANUAL | Browser login/session gerekir |
| DBR-002: Guest library erisimi | Login redirect/engelleme | PASS | Route `auth` filterli, guest endpoint 302 login |
| DBR-003: Owned dijital list sadece dogru urunler | Owned dijital urunler listelenir | PASS | SQL smoke ile demo user icin 3 owned dijital product dogrulandi |
| DBR-003: Duplicate satin alma tek urun | Tekilleme | MANUAL | UI listesinde tekilleme gorsel dogrulama gerekli |
| DBR-004: Header `Dijital Kitaplarim` linki | Gorunur + dogru route | MANUAL | Browser/header kontrolu gerekli |
| DBR-005: Reader acilis + prev/next | Owned kitap acilir, navigasyon calisir | MANUAL | Browser reader etkileşimi gerekli |
| DBR-006: Guest reader/API erisimi deny | Icerik alamaz | PASS | Guest reader ve page API -> `302 /login` |
| DBR-006: Non-owned API 403 | Forbidden | MANUAL | Ayrik auth session ile negatif test gerekli |
| DBR-006: Non-digital deny | Engellenir | MANUAL | Ayrik auth senaryosu gerekli |
| DBR-007: Casual copy prevention | Reader content copy/selection baskilanir | MANUAL | Klavye/mouse browser testi gerekli |
| DBR-008: Highlight create/load/delete | Persist + silme + popover | MANUAL | Browser etkileşimli test gerekli |
| DBR-009: Admin helper text + create/edit regression | Bozulmama | MANUAL | Admin panel browser testi gerekli |
| DBR-010: secure content fetch + fallback | `digital_book_contents` once, yoksa description fallback | PARTIAL | Kod/DB ile dogrulandi, full E2E manuel |
| DBR-011: Demo seeder idempotency | 2. kosuda duplicate yok | PASS | Seeder iki kez calistirildi, update/reuse ciktilari alindi |

## 5) Guvenlik Smoke Checklist
| Kontrol | Sonuc | Kanit |
| --- | --- | --- |
| Reader/API route'lari auth filterli mi | PASS | `php spark routes` ciktisinda tum `dijital-kitaplarim/digital-books/highlights` route'lari `auth` |
| Guest reader URL icerik alabiliyor mu | PASS | `curl -i /yardim/dijital-kitaplarim/{id}/oku` -> `302 Location: /login` |
| Guest page API icerik alabiliyor mu | PASS | `curl -i /api/digital-books/{id}/pages/1` -> `302 Location: /login` |
| API response public URL/path donduruyor mu | PARTIAL | `DigitalBooks::page` response shape sadece `productId,currentIndex,total,hasPrev,hasNext,content,isEmpty` |
| Non-owned / non-digital deny | MANUAL | Ayrik auth kullanici + product varyanti ile canli test gerekli |

## 6) Admin Regression Checklist
| Kontrol | Sonuc | Not |
| --- | --- | --- |
| Create form helper text | MANUAL | Browser admin create ekrani |
| Edit form helper text | MANUAL | Browser admin edit ekrani |
| Basili urun create/edit | MANUAL | Admin E2E gerekli |
| Dijital urun create/edit | MANUAL | Admin E2E gerekli |
| `author_id` bos FK kirilmasi yok | MANUAL | Admin create form submit testi gerekli |

## 7) Reader/Highlight Checklist
| Kontrol | Sonuc | Not |
| --- | --- | --- |
| Reader acilis (owned) | MANUAL | Browser session gerekli |
| Prev/next sinir davranisi | MANUAL | Browser etkileşimli |
| Copy prevention normal mod | MANUAL | Browser keyboard/mouse |
| Highlight mode selection | MANUAL | Browser |
| Highlight persist + refresh | MANUAL | Browser + refresh |
| Highlight delete popover | MANUAL | Browser |
| Duplicate highlight guard | MANUAL | Browser + API response kontrolu |

## 8) Seed Dogrulama Checklist
| Kontrol | Sonuc | Kanit |
| --- | --- | --- |
| `php spark db:seed DigitalBookDemoSeeder` | PASS | Basarili calisti |
| Ikinci kosu duplicate uretmiyor | PASS | `create` yerine `update/reuse` loglari |
| Demo user mevcut | PASS | `demo.reader@example.test` kaydi var |
| Demo products mevcut (`type=dijital`) | PASS | 3 demo urun DB'de |
| `digital_book_contents` kayitlari var | PASS | Her demo urun icin 1 satir, uzunluk ~12k |
| Demo order + order_items var | PASS | `DEMO-DBR-ORDER-001` + 3 item |

## 9) Migration / Route / Lint Sonuclari
- `php -l` kritik DBR dosyalari: PASS (tum hedef dosyalarda syntax hatasi yok)
- `php spark migrate:status`: PASS
  - `CreateDigitalBookHighlightsTable` applied (batch 16)
  - `CreateDigitalBookContentsTable` applied (batch 17)
- `php spark routes | rg "dijital-kitaplarim|digital-books|highlights"`: PASS
  - Reader/page/highlight endpointleri var ve `auth` filterli

## 10) Bilinen Limitasyonlar
- Casual copy prevention DRM degildir.
- Screenshot/devtools gibi yollari engellemez.
- Highlight offsetleri icerik metni degisirse kayabilir.
- Demo seed production gercek icerik migrasyonu degildir.
- `products.description` fallback gecis uyumlulugu icin korunur.
- Bazi E2E auth senaryolari manuel browser dogrulamasi gerektirir.

## 11) Release Oncesi Komutlar
```bash
php -l app/Controllers/DigitalBooks.php
php -l app/Controllers/Admin/Products.php
php -l app/Services/DigitalBookLibraryService.php
php -l app/Services/DigitalBookReaderService.php
php -l app/Services/DigitalBookHighlightService.php
php -l app/Services/DigitalBookContentService.php
php -l app/Models/DigitalBookHighlightModel.php
php -l app/Models/DigitalBookContentModel.php
php -l app/Database/Seeds/DigitalBookDemoSeeder.php
php -l app/Database/Migrations/2026-05-16-080000_CreateDigitalBookHighlightsTable.php
php -l app/Database/Migrations/2026-05-16-120000_CreateDigitalBookContentsTable.php
php spark migrate:status
php spark routes | rg "dijital-kitaplarim|digital-books|highlights"
php spark db:seed DigitalBookDemoSeeder
php spark db:seed DigitalBookDemoSeeder
```

## 12) Rollback/Temizlik Notlari (Dokumantasyon)
- Bu checklist destructive rollback icermez.
- Demo veriyi kaldirmak gerekirse otomatik `DELETE/TRUNCATE` scripti bu ticket kapsaminda verilmez.
- Olası temizlik adimi ayrik ve onayli operasyon ticket'i ile ele alinmalidir.
