# TICKET-DBR-001A — Ownership ve Status Erişim Kuralı

## Amaç
DBR erişimini yalnızca gerçekten satın almış kullanıcıya açmak; `unpaid/cancelled/refunded` siparişleri erişim dışı bırakmak.

## Tek Ownership Sorgu Yaklaşımı (Öneri)
- Kaynak: `orders` + `order_items` + `products`
- Eşleşme:
  - `orders.user_id = currentUserId`
  - `order_items.product_id = targetProductId`
  - `products.type IN ('dijital', 'digital', 'ebook', 'e-book')`
  - `orders.deleted_at IS NULL` ve `order_items.deleted_at IS NULL`
- Sonuç: Bu sorgu yalnızca DBR okuyucu erişimi için kullanılmalı (tek kaynak kuralı).

## Allow / Deny Matrisi
- **ALLOW**
  - owned + `payment_status = paid`
  - owned + `order_status = delivered`
  - owned + legacy `status IN ('completed', 'delivered', 'paid')`
- **DENY**
  - owned + `payment_status IN ('unpaid', 'failed', 'refunded', 'partial_refund')`
  - owned + `order_status IN ('pending', 'cancelled', 'return_in_progress', 'return_done', 'returned', 'refunded')`
  - owned + legacy `status IN ('pending', 'reserved', 'cancelled', 'returned', 'refunded')`
  - non-owned
  - `orders.user_id` boş veya eşleşmiyor

## orders.user_id Veri Kalitesi Riski
- Bazı eski/legacy sipariş oluşturma akışlarında `orders.user_id` set edilmemiş kayıt riski bulunuyor.
- Etki: Kullanıcı gerçekte satın almış olsa bile ownership doğrulaması false dönebilir.
- Bu ticket kapsamında yaklaşım: güvenlik öncelikli deny (`orders.user_id` yoksa erişim yok).

## Bu Ticket Kapsamında Eklenen İzole Metot
- `App\Services\DigitalBookOwnershipService::userCanReadPurchasedDigitalBook(string $userId, string $productId): bool`
- Amaç: Sonraki ticket’larda tekrar kullanılabilir, akıştan bağımsız DBR erişim kontrolü.
- Not: Mevcut checkout/order davranışını değiştirmez.

