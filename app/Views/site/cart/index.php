<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$cartView = is_array($cartView ?? null) ? $cartView : [];
$cartItems = is_array($cartView['items'] ?? null) ? $cartView['items'] : [];
$suggestedProducts = is_array($suggestedProducts ?? null) ? $suggestedProducts : [];
?>
<style>
    .cart-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 1.25rem 1rem 2.5rem;
    }
    .cart-header {
        margin-bottom: 1.5rem;
    }
    .cart-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.45rem, 2vw, 1.95rem);
        font-weight: 800;
    }
    .cart-subtitle {
        margin: 0.45rem 0 0;
        color: #64748b;
        max-width: 760px;
        line-height: 1.6;
    }
    .cart-alert-stack {
        display: grid;
        gap: 0.75rem;
        margin-bottom: 1.15rem;
    }
    .cart-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(300px, 360px);
        gap: 1.35rem;
        align-items: start;
    }
    .cart-empty-card,
    .cart-summary-card,
    .cart-item-card,
    .cart-suggested-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }
    .cart-empty-card {
        padding: 2.75rem 1.5rem;
        text-align: center;
    }
    .cart-empty-title {
        margin: 0 0 0.5rem;
        color: #0f172a;
        font-size: 1.15rem;
        font-weight: 800;
    }
    .cart-empty-text {
        margin: 0 0 1.15rem;
        color: #64748b;
        line-height: 1.65;
    }
    .cart-items-column {
        display: grid;
        gap: 1rem;
    }
    .cart-item-card {
        padding: 1rem;
    }
    .cart-item-grid {
        display: grid;
        grid-template-columns: 112px minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }
    .cart-item-image {
        width: 112px;
        height: 136px;
        border-radius: 16px;
        object-fit: cover;
        background: #f8fafc;
        display: block;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
    }
    .cart-item-main {
        min-width: 0;
    }
    .cart-item-top {
        display: flex;
        justify-content: space-between;
        gap: 0.9rem;
        align-items: start;
    }
    .cart-item-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-bottom: 0.65rem;
    }
    .cart-item-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.7rem;
        border-radius: 999px;
        font-size: 0.79rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .cart-item-badge.type {
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        border-color: rgba(37, 99, 235, 0.16);
    }
    .cart-item-badge.stock-ok {
        background: rgba(22, 163, 74, 0.1);
        color: #166534;
        border-color: rgba(22, 163, 74, 0.16);
    }
    .cart-item-badge.stock-warn {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
        border-color: rgba(245, 158, 11, 0.2);
    }
    .cart-item-name {
        margin: 0;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.4;
    }
    .cart-item-author {
        margin: 0.35rem 0 0;
        color: #64748b;
        font-size: 0.92rem;
    }
    .cart-item-unit-price {
        min-width: 132px;
        text-align: right;
    }
    .cart-item-old-price {
        color: #dc2626;
        font-size: 0.86rem;
        text-decoration: line-through;
    }
    .cart-item-current-price {
        color: #0f172a;
        font-weight: 800;
        font-size: 1.08rem;
        line-height: 1.2;
    }
    .cart-item-saving {
        margin-top: 0.2rem;
        color: #16a34a;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .cart-item-bottom {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 1rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }
    .cart-qty-stack {
        display: grid;
        gap: 0.55rem;
    }
    .cart-qty-controls {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.3rem;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: #f8fafc;
    }
    .cart-qty-controls form {
        margin: 0;
    }
    .cart-qty-btn {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        box-shadow: 0 6px 12px rgba(15, 23, 42, 0.05);
    }
    .cart-qty-btn[disabled] {
        opacity: 0.45;
        cursor: not-allowed;
        box-shadow: none;
    }
    .cart-qty-value {
        min-width: 32px;
        text-align: center;
        font-weight: 800;
        color: #0f172a;
    }
    .cart-qty-note {
        color: #64748b;
        font-size: 0.84rem;
    }
    .cart-item-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        justify-content: flex-end;
    }
    .cart-item-actions a,
    .cart-item-actions button {
        min-height: 40px;
        border-radius: 12px;
        font-weight: 700;
    }
    .cart-item-actions form {
        margin: 0;
    }
    .cart-item-total {
        color: #0f172a;
        font-weight: 800;
        font-size: 1.08rem;
    }
    .cart-summary-card {
        padding: 1.2rem;
        position: sticky;
        top: 110px;
    }
    .cart-summary-title {
        margin: 0 0 1rem;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 800;
    }
    .cart-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        color: #475569;
        font-size: 0.94rem;
        margin-bottom: 0.8rem;
    }
    .cart-summary-row strong {
        color: #0f172a;
    }
    .cart-summary-row.savings strong {
        color: #16a34a;
    }
    .cart-summary-divider {
        height: 1px;
        background: rgba(148, 163, 184, 0.2);
        margin: 1rem 0;
    }
    .cart-summary-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
    }
    .cart-summary-note {
        margin-top: 0.9rem;
        color: #64748b;
        font-size: 0.88rem;
        line-height: 1.6;
    }
    .cart-summary-actions {
        display: grid;
        gap: 0.7rem;
        margin-top: 1.1rem;
    }
    .cart-summary-actions .btn {
        min-height: 46px;
        border-radius: 14px;
        font-weight: 700;
    }
    .cart-summary-disabled {
        background: #e2e8f0;
        color: #475569;
        border: 1px solid rgba(148, 163, 184, 0.22);
        cursor: not-allowed;
    }
    .cart-suggested {
        margin-top: 2rem;
    }
    .cart-suggested-header {
        margin-bottom: 1rem;
    }
    .cart-suggested-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.2rem;
        font-weight: 800;
    }
    .cart-suggested-subtitle {
        margin: 0.35rem 0 0;
        color: #64748b;
    }
    .cart-suggested-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
    }
    .cart-suggested-card {
        overflow: hidden;
    }
    .cart-suggested-image {
        width: 100%;
        height: 190px;
        object-fit: cover;
        display: block;
        background: #f8fafc;
    }
    .cart-suggested-body {
        padding: 1rem;
        display: grid;
        gap: 0.65rem;
    }
    .cart-suggested-name {
        margin: 0;
        color: #0f172a;
        font-size: 0.98rem;
        font-weight: 800;
        line-height: 1.45;
    }
    .cart-suggested-price {
        color: #0f172a;
        font-weight: 800;
    }
    .cart-suggested-actions {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
    }
    .cart-suggested-actions form {
        margin: 0;
    }
    .cart-suggested-actions .btn {
        min-height: 40px;
        border-radius: 12px;
        font-weight: 700;
    }
    @media (max-width: 991.98px) {
        .cart-layout {
            grid-template-columns: 1fr;
        }
        .cart-summary-card {
            position: static;
        }
    }
    @media (max-width: 767.98px) {
        .cart-page {
            padding-inline: 0.85rem;
        }
        .cart-item-grid {
            grid-template-columns: 1fr;
        }
        .cart-item-image {
            width: 100%;
            height: 220px;
        }
        .cart-item-top,
        .cart-item-bottom {
            flex-direction: column;
            align-items: stretch;
        }
        .cart-item-unit-price {
            text-align: left;
            min-width: 0;
        }
        .cart-item-actions {
            justify-content: stretch;
        }
        .cart-item-actions a,
        .cart-item-actions form,
        .cart-item-actions button {
            width: 100%;
        }
    }
</style>

<section class="cart-page">
    <header class="cart-header">
        <h1 class="cart-title">Sepetim</h1>
        <p class="cart-subtitle">Sepetinizdeki urunleri kontrol edebilir, adetleri guncelleyebilirsiniz.</p>
    </header>

    <div class="cart-alert-stack">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success mb-0"><?= esc((string) session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger mb-0"><?= esc((string) session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if (! empty($cartView['has_price_drops'])): ?>
            <div class="alert alert-info mb-0">Sepetinizde fiyati dusen urunler var.</div>
        <?php endif; ?>
        <?php if (! empty($cartView['has_stock_warnings'])): ?>
            <div class="alert alert-warning mb-0">Sepetinizde stok durumu degisen urunler var.</div>
        <?php endif; ?>
    </div>

    <?php if ($cartItems === []): ?>
        <div class="cart-empty-card">
            <h2 class="cart-empty-title">Sepetinizde urun yok.</h2>
            <p class="cart-empty-text">Favori urunlerinizi veya begendiginiz kitaplari sepete ekleyerek alisverise devam edebilirsiniz.</p>
            <a href="<?= base_url('products/selection') ?>" class="btn btn-primary px-4">Urunleri Kesfet</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items-column">
                <?php foreach ($cartItems as $item): ?>
                    <article class="cart-item-card">
                        <div class="cart-item-grid">
                            <a href="<?= esc((string) ($item['detail_url'] ?? '#')) ?>">
                                <img src="<?= esc((string) ($item['image_url'] ?? '')) ?>" alt="<?= esc((string) ($item['product_name'] ?? 'Urun')) ?>" class="cart-item-image">
                            </a>

                            <div class="cart-item-main">
                                <div class="cart-item-top">
                                    <div>
                                        <div class="cart-item-meta">
                                            <span class="cart-item-badge type"><?= esc((string) ($item['type'] ?? 'urun')) ?></span>
                                            <span class="cart-item-badge <?= ! empty($item['has_stock_warning']) || empty($item['is_available']) ? 'stock-warn' : 'stock-ok' ?>">
                                                <?= esc((string) ($item['stock_message'] ?? 'Stok bilgisi')) ?>
                                            </span>
                                        </div>
                                        <h2 class="cart-item-name"><?= esc((string) ($item['product_name'] ?? '')) ?></h2>
                                        <?php if (trim((string) ($item['author'] ?? '')) !== ''): ?>
                                            <p class="cart-item-author">Yazar: <?= esc((string) $item['author']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="cart-item-unit-price">
                                        <?php if (! empty($item['is_price_dropped'])): ?>
                                            <div class="cart-item-old-price"><?= number_format((float) ($item['snapshot_price'] ?? 0), 2, ',', '.') ?> TL</div>
                                        <?php endif; ?>
                                        <div class="cart-item-current-price"><?= number_format((float) ($item['current_price'] ?? 0), 2, ',', '.') ?> TL</div>
                                        <?php if (! empty($item['is_price_dropped'])): ?>
                                            <div class="cart-item-saving">Kazanciniz: <?= number_format((float) ($item['saving_total'] ?? 0), 2, ',', '.') ?> TL</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="cart-item-bottom">
                                    <div class="cart-qty-stack">
                                        <?php if (! empty($item['is_digital'])): ?>
                                            <div class="cart-qty-note">Dijital urun adet 1 olarak tutulur.</div>
                                        <?php else: ?>
                                            <div class="cart-qty-controls">
                                                <form action="<?= base_url('cart/decrease') ?>" method="post">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="cart_item_id" value="<?= esc((string) ($item['cart_item_id'] ?? '')) ?>">
                                                    <button type="submit" class="cart-qty-btn" <?= empty($item['can_decrease']) ? 'disabled' : '' ?>>-</button>
                                                </form>
                                                <span class="cart-qty-value"><?= esc((string) ($item['quantity'] ?? 1)) ?></span>
                                                <form action="<?= base_url('cart/increase') ?>" method="post">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="cart_item_id" value="<?= esc((string) ($item['cart_item_id'] ?? '')) ?>">
                                                    <button type="submit" class="cart-qty-btn" <?= empty($item['can_increase']) ? 'disabled' : '' ?>>+</button>
                                                </form>
                                            </div>
                                            <?php if (! empty($item['has_stock_warning'])): ?>
                                                <div class="cart-qty-note">Stok miktari bu urun icin sinirlidir.</div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <div class="cart-item-total">Toplam: <?= number_format((float) ($item['line_total_current'] ?? 0), 2, ',', '.') ?> TL</div>
                                    </div>

                                    <div class="cart-item-actions">
                                        <a href="<?= esc((string) ($item['detail_url'] ?? '#')) ?>" class="btn btn-outline-primary">Urune Git</a>
                                        <form action="<?= base_url('cart/remove') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="cart_item_id" value="<?= esc((string) ($item['cart_item_id'] ?? '')) ?>">
                                            <button type="submit" class="btn btn-outline-danger">Sepetten Kaldir</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary-card">
                <h2 class="cart-summary-title">Siparis Ozeti</h2>
                <div class="cart-summary-row">
                    <span>Urun adedi</span>
                    <strong><?= esc((string) ($cartView['item_count'] ?? 0)) ?></strong>
                </div>
                <div class="cart-summary-row">
                    <span>Urunler toplami</span>
                    <strong><?= number_format((float) ($cartView['subtotal_current'] ?? 0), 2, ',', '.') ?> TL</strong>
                </div>
                <?php if ((float) ($cartView['total_savings'] ?? 0) > 0): ?>
                    <div class="cart-summary-row savings">
                        <span>Toplam kazanciniz</span>
                        <strong><?= number_format((float) ($cartView['total_savings'] ?? 0), 2, ',', '.') ?> TL</strong>
                    </div>
                <?php endif; ?>
                <div class="cart-summary-row">
                    <span>Kargo</span>
                    <strong>Odeme adiminda</strong>
                </div>

                <div class="cart-summary-divider"></div>

                <div class="cart-summary-total">
                    <span>Genel toplam</span>
                    <span><?= number_format((float) ($cartView['grand_total_current'] ?? 0), 2, ',', '.') ?> TL</span>
                </div>
                <p class="cart-summary-note"><?= esc((string) ($cartView['shipping_info'] ?? 'Kargo ucreti odeme adiminda hesaplanacak.')) ?></p>

                <div class="cart-summary-actions">
                    <button type="button" class="btn cart-summary-disabled" disabled>Odeme adimi sonraki sprintte eklenecek</button>
                    <a href="<?= base_url('products/selection') ?>" class="btn btn-outline-primary">Alisverise Devam Et</a>
                    <form action="<?= base_url('cart/clear') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger w-100">Sepeti Temizle</button>
                    </form>
                </div>
            </aside>
        </div>
    <?php endif; ?>

    <?php if ($suggestedProducts !== []): ?>
        <section class="cart-suggested">
            <div class="cart-suggested-header">
                <h2 class="cart-suggested-title">Sizin Icin Onerilenler</h2>
                <p class="cart-suggested-subtitle">Sepetinizdekilere benzer veya alisverisinizi tamamlayabilecek secimler.</p>
            </div>
            <div class="cart-suggested-grid">
                <?php foreach ($suggestedProducts as $product): ?>
                    <article class="cart-suggested-card">
                        <img src="<?= esc((string) ($product['image_url'] ?? '')) ?>" alt="<?= esc((string) ($product['title'] ?? 'Urun')) ?>" class="cart-suggested-image">
                        <div class="cart-suggested-body">
                            <h3 class="cart-suggested-name"><?= esc((string) ($product['title'] ?? '')) ?></h3>
                            <div class="cart-suggested-price"><?= number_format((float) ($product['price'] ?? 0), 2, ',', '.') ?> TL</div>
                            <div class="cart-suggested-actions">
                                <a href="<?= esc((string) ($product['detail_url'] ?? '#')) ?>" class="btn btn-outline-primary">Urune Git</a>
                                <?php if (! empty($product['can_add_to_cart'])): ?>
                                    <form action="<?= base_url('cart/add') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= esc((string) ($product['product_id'] ?? '')) ?>">
                                        <button type="submit" class="btn btn-primary">Sepete Ekle</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
