E-Ticaret Platformu
## Demo Preview

[![Demo 1](./demo1.gif)](./demo1.mp4)
[![Demo 2](./demo2.gif)](./demo2.mp4)
[![Demo 3](./demo3.gif)](./demo3.mp4)
[![Demo 4](./demo4.gif)](./demo4.mp4)
[![Demo 5](./demo5.gif)](./demo5.mp4)



- [Demo 1](./demo1.mp4)
- [Demo 2](./demo2.mp4)
- [Demo 3](./demo3.mp4)
- [Demo 4](./demo4.mp4)
- [Demo 5](./demo5.mp4)



Rol Tabanlı, Modüler Yönetim Panelli, Dijital & Basılı Kitap Satış Sistemi

Modern, güvenli ve modüler bir e-ticaret platformu.
Dijital (PDF/EPUB) ve basılı kitap satışı destekler.
Admin tarafından dinamik olarak tasarlanabilen Dashboard ve Page Builder içerir.
RBAC (Role Based Access Control) mimarisi ile yetki kontrollü yönetim sunar.

 Proje Amacı

Bu platformun amacı:

Admin / Sekreter / Kullanıcı rollerinin net ayrıştırılması

Deny-by-default RBAC mimarisi

Dijital içerik güvenliği (token + watermark + canvas rendering)

Dinamik Dashboard & Page Builder

Katmanlı (Controller → Service → Model) mimari

Güvenli ödeme, sipariş ve stok yönetimi

Audit log ile izlenebilir sistem

 Sistem Mimarisi
Genel Mimari
HTTP Request
   ↓
Filter (Auth + RBAC + CSRF + SecureHeaders)
   ↓
Controller (Thin)
   ↓
Service (Business Logic)
   ↓
Repository / Model
   ↓
Database
Katmanlar
Katman	Sorumluluk
Filter	Auth + Permission enforcement (PEP)
Controller	Request/Response orchestration
Service	İş kuralları
DTO	Tip güvenli veri taşıma
Model	DB erişimi
Migration	Şema versiyonlama

RBAC (Role-Based Access Control)
Roller

ADMIN

SECRETARY

CUSTOMER

Yetki Yapısı
roles
permissions
role_permissions
user_roles
user_permissions (override)
Kritik Özellik

Sekreter minimum rol ile başlar

Admin UI üzerinden özel permission override verebilir

Route bazlı filter ile zorunlu kontrol

Service katmanında ikinci güvenlik kontrolü

Deny by default prensibi uygulanır.

 Dashboard Builder

Admin kendi dashboard’unu blok bazlı oluşturabilir.

Block Türleri

Stat Card

Chart (Bar / Pie / Line)

Slider

Quote

Calendar

Veri Kaynakları

sales_by_category

top_authors

visitors

favorites_count

Versioning

Draft

Published

Archived

Dashboard her reload’da hesaplanmaz → Cache uygulanır.

 Page Builder

Admin aşağıdaki sayfaları bloklarla oluşturabilir:

Ana Sayfa

Kategori Liste

Ürün Detay

Cart

Slot Tabanlı Ürün Kartı

Tam serbest HTML yerine:

cover
title
author
price
favorite
add_to_cart
rating

Admin sadece yerleşim ve görünürlük kontrol eder.

Bu yaklaşım:

XSS riskini azaltır

Bakımı kolaylaştırır

UI standardizasyonu sağlar

 Domain Model (Core)
Work → SKU Ayrımı

Work: İçerik kimliği
SKU: Satılabilir varyant (PRINT / DIGITAL / BUNDLE)

Snapshot Politikası

OrderItem içinde:

unit_price_snapshot

product_name_snapshot

tax_snapshot

discount_snapshot

Fiyat sonradan değişse bile geçmiş bozulmaz.

 Dijital İçerik Güvenliği
Amaç

Tam DRM değil, caydırıcılık + izlenebilirlik.

Uygulama

Dosyalar webroot dışında

Token bazlı erişim /reader/{token}

PDF.js → Canvas rendering

Text selection disabled

Print disabled

Watermark (email + order no + date)

Rate limiting

Access log

Token İçeriği
user_id
product_id
expire_time
hash
🛒 Sepet & Stok Yönetimi
Sepet

cart

cart_items

cart_promotions

Checkout sırasında reprice yapılır.

Stok

MVP:

Stok ödeme sonrası düşer

Ödeme öncesi tekrar kontrol edilir

Transaction ile korunur

 Kargo & Shipment Event Model
shipments
shipment_events

Status:

PENDING → PREPARING → SHIPPED → DELIVERED

 Ödeme Akışı

State machine:

INITIATED → PENDING → SUCCESS / FAILED → REFUNDED

Webhook idempotency:

provider_txn_id unique

webhook_events tablosu

 State Machines
Order

PENDING_PAYMENT
→ PAID
→ FULFILLMENT_PENDING
→ SHIPPED
→ DELIVERED

İade süreci ayrı yönetilir.

DigitalAccess

ACTIVE
→ EXPIRED
→ REVOKED

 Güvenlik Tasarımı
✔ Access Control

Route-level filter

Service-level guard

Ownership check

✔ CSRF

CI4 filter

✔ XSS

Output encoding

Builder schema validation

Slot-based design

✔ Secure Headers

HSTS

X-Content-Type-Options

CSP

✔ Session Security

HttpOnly

Secure

SameSite

✔ File Upload Security

MIME whitelist

Hash doğrulama

Webroot dışı depolama

✔ Secrets

Hardcoded anahtar yok

Environment config

 Audit Log
audit_logs
- actor_user_id
- action_code
- entity_type
- entity_id
- before_json
- after_json
- ip

Loglanan olaylar:

Permission değişikliği

Ürün fiyat/stok değişimi

Layout publish

Yorum silme

Dijital revoke

Shipment status update

 Edge Case Düşünülmüş Senaryolar
Senaryo	Çözüm
Fiyat değişimi	Checkout reprice
Stok yarışı	Transaction + tekrar kontrol
Webhook tekrarı	Idempotent handler
Sekreter URL hack	Route guard + deny default
Builder hatalı config	JSON schema validation
Dijital kopyalama	Watermark + rate limit
SQL performans	Index + cache

graph TD

User -->|HTTP| Filter
Filter --> Controller
Controller --> Service
Service --> Repository
Repository --> DB

Service --> PolicyCheck
PolicyCheck --> RBAC

Admin --> DashboardBuilder
DashboardBuilder --> BlockRenderer
BlockRenderer --> DataSource


Test Coverage Yaklaşımı

Service katmanı unit test

Policy testleri

State machine testleri

Permission matrix testleri

Order snapshot testleri

Idempotency testleri


 Migration Stratejisi

Versioned migrations

Seeder ile default roles & permissions

Production’da controlled migrate

Rollback planı mevcut


 Neden Bu Tasarım?

RBAC en baştan tasarlandı

Business logic Service katmanında

Snapshot modeli ile veri bütünlüğü

Slot-based UI ile XSS risk azaltımı

Audit log ile izlenebilir sistem

Builder versioning ile rollback imkanı


Token bazlı dijital erişim ile minimum DRM yaklaşımı

Bu proje, sadece çalışan bir e-ticaret sistemi değil;
güvenlik, izlenebilirlik ve sürdürülebilirlik düşünülerek tasarlanmış bir mimari çalışmadır.


 Kapsam Dışı (Şimdilik)

Native mobile app

AI öneri sistemi

Marketplace entegrasyonu

Çoklu para birimi


Sonuç

Bu proje:

✔ Rol bazlı
✔ Modüler
✔ Güvenli
✔ Ölçeklenebilir
✔ Audit destekli
✔ Builder destekli

bir e-ticaret altyapısıdır.
