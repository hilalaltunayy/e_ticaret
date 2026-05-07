<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$ordersView = is_array($ordersView ?? null) ? $ordersView : [];
$orders = is_array($ordersView['orders'] ?? null) ? $ordersView['orders'] : [];
$scope = (string) ($ordersView['scope'] ?? 'all');
$search = (string) ($ordersView['search'] ?? '');
$scopeLabels = [
    'all' => 'Tümü',
    'active' => 'Devam Edenler',
    'delivered' => 'Teslim Edilenler',
    'cancelled_return' => 'İptal / İade',
];
?>
<style>
    .orders-page {
        max-width: 1240px;
        margin: 0 auto;
        padding: 1.25rem 1rem 2.5rem;
    }
    .orders-header {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .orders-title {
        margin: 0;
        color: #0f172a;
        font-weight: 800;
        font-size: clamp(1.4rem, 2vw, 1.9rem);
    }
    .orders-subtitle {
        margin: 0.45rem 0 0;
        color: #64748b;
        line-height: 1.65;
        max-width: 760px;
    }
    .orders-header-cta {
        min-height: 42px;
        border-radius: 12px;
        font-weight: 700;
    }
    .orders-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .orders-scopes {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
    }
    .orders-scope-chip {
        display: inline-flex;
        align-items: center;
        min-height: 40px;
        padding: 0.55rem 0.95rem;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: #fff;
        color: #475569;
        font-weight: 700;
        text-decoration: none;
    }
    .orders-scope-chip.is-active {
        background: rgba(37, 99, 235, 0.1);
        border-color: rgba(37, 99, 235, 0.18);
        color: #1d4ed8;
    }
    .orders-search-form {
        display: flex;
        gap: 0.6rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .orders-search-input {
        min-width: min(100%, 280px);
        border-radius: 12px;
    }
    .orders-list {
        display: grid;
        gap: 1rem;
    }
    .orders-empty,
    .order-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }
    .orders-empty {
        padding: 2.75rem 1.5rem;
        text-align: center;
    }
    .orders-empty-title {
        margin: 0 0 0.5rem;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 800;
    }
    .orders-empty-text {
        margin: 0 0 1rem;
        color: #64748b;
        line-height: 1.65;
    }
    .order-card {
        padding: 1rem 1rem 1.05rem;
    }
    .order-card-top {
        display: flex;
        align-items: start;
        justify-content: space-between;
        gap: 0.85rem;
        flex-wrap: wrap;
        margin-bottom: 0.9rem;
    }
    .order-card-number {
        margin: 0;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
    }
    .order-card-date {
        margin: 0.25rem 0 0;
        color: #64748b;
        font-size: 0.9rem;
    }
    .order-card-status {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0.35rem 0.8rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 800;
    }
    .order-card-status.success { background: rgba(22, 163, 74, 0.1); color: #166534; }
    .order-card-status.info { background: rgba(37, 99, 235, 0.1); color: #1d4ed8; }
    .order-card-status.warning { background: rgba(245, 158, 11, 0.14); color: #92400e; }
    .order-card-status.danger { background: rgba(220, 38, 38, 0.1); color: #b91c1c; }
    .order-card-status.secondary { background: rgba(148, 163, 184, 0.16); color: #475569; }
    .order-card-meta {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
        margin-bottom: 0.9rem;
    }
    .order-card-meta span {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 700;
        border: 1px solid rgba(148, 163, 184, 0.16);
    }
    .order-card-body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: start;
    }
    .order-card-items {
        display: grid;
        gap: 0.8rem;
    }
    .order-card-thumbs {
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
    }
    .order-card-thumbs img {
        width: 48px;
        height: 58px;
        border-radius: 12px;
        object-fit: cover;
        background: #f8fafc;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
    }
    .order-card-item-names {
        color: #475569;
        line-height: 1.6;
        font-size: 0.92rem;
    }
    .order-card-summary {
        min-width: 240px;
        display: grid;
        gap: 0.55rem;
        justify-items: end;
    }
    .order-card-total {
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 800;
    }
    .order-card-delivery {
        color: #64748b;
        font-size: 0.88rem;
        text-align: right;
        line-height: 1.55;
    }
    .order-card-actions {
        margin-top: 0.35rem;
    }
    .order-card-actions .btn {
        min-height: 40px;
        border-radius: 12px;
        font-weight: 700;
    }
    @media (max-width: 767.98px) {
        .orders-page {
            padding-inline: 0.85rem;
        }
        .orders-toolbar,
        .order-card-body {
            grid-template-columns: 1fr;
        }
        .order-card-summary {
            justify-items: start;
            min-width: 0;
        }
        .order-card-delivery {
            text-align: left;
        }
        .orders-search-form {
            width: 100%;
        }
        .orders-search-input {
            min-width: 0;
            width: 100%;
        }
    }
</style>

<section class="orders-page">
    <header class="orders-header">
        <div>
            <h1 class="orders-title">Siparişlerim</h1>
            <p class="orders-subtitle">Geçmiş ve devam eden siparişlerinizi buradan takip edebilirsiniz.</p>
        </div>
        <a href="<?= base_url('products/selection') ?>" class="btn btn-outline-primary orders-header-cta">Ürünleri Keşfet</a>
    </header>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success mb-3"><?= esc((string) session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mb-3"><?= esc((string) session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="orders-toolbar">
        <div class="orders-scopes">
            <?php foreach ($scopeLabels as $scopeKey => $scopeLabel): ?>
                <a href="<?= base_url('yardim/siparislerim?' . http_build_query(array_filter(['scope' => $scopeKey, 'q' => $search !== '' ? $search : null]))) ?>" class="orders-scope-chip<?= $scope === $scopeKey ? ' is-active' : '' ?>">
                    <?= esc($scopeLabel) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form action="<?= base_url('yardim/siparislerim') ?>" method="get" class="orders-search-form">
            <?php if ($scope !== 'all'): ?>
                <input type="hidden" name="scope" value="<?= esc($scope) ?>">
            <?php endif; ?>
            <input type="search" name="q" value="<?= esc($search) ?>" class="form-control orders-search-input" placeholder="Sipariş numarası ara">
            <button type="submit" class="btn btn-outline-primary">Ara</button>
        </form>
    </div>

    <?php if ($orders === []): ?>
        <div class="orders-empty">
            <h2 class="orders-empty-title">Henüz siparişiniz yok.</h2>
            <p class="orders-empty-text">İlk siparişinizi verdiğinizde burada durumunu, teslimat bilgilerini ve geçmiş kayıtlarınızı göreceksiniz.</p>
            <a href="<?= base_url('products/selection') ?>" class="btn btn-primary px-4">Ürünleri Keşfet</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <article class="order-card">
                    <div class="order-card-top">
                        <div>
                            <h2 class="order-card-number">Sipariş No: <?= esc((string) ($order['order_number'] ?? '')) ?></h2>
                            <p class="order-card-date"><?= esc((string) ($order['order_date'] ?? '')) ?></p>
                        </div>
                        <span class="order-card-status <?= esc((string) ($order['status_badge_class'] ?? 'secondary')) ?>">
                            <?= esc((string) ($order['status_label'] ?? 'Sipariş Alındı')) ?>
                        </span>
                    </div>

                    <div class="order-card-meta">
                        <span>Ödeme: <?= esc((string) ($order['payment_status_label'] ?? '-')) ?></span>
                        <span>Teslimat: <?= esc((string) ($order['fulfillment_status_label'] ?? '-')) ?></span>
                        <span><?= esc((string) ($order['item_count'] ?? 0)) ?> ürün</span>
                        <?php if (! empty($order['has_return_request'])): ?>
                            <span>İade Talebi Alındı</span>
                        <?php endif; ?>
                    </div>

                    <div class="order-card-body">
                        <div class="order-card-items">
                            <?php if (! empty($order['thumbnails'])): ?>
                                <div class="order-card-thumbs">
                                    <?php foreach ($order['thumbnails'] as $thumb): ?>
                                        <img src="<?= esc((string) $thumb) ?>" alt="Sipariş ürünü">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="order-card-item-names">
                                <?= esc(implode(' • ', (array) ($order['item_names'] ?? []))) ?>
                            </div>
                        </div>

                        <div class="order-card-summary">
                            <div class="order-card-total"><?= number_format((float) ($order['total_amount'] ?? 0), 2, ',', '.') ?> TL</div>
                            <div class="order-card-delivery">
                                <?php if (trim((string) ($order['delivered_at'] ?? '')) !== ''): ?>
                                    Teslim tarihi: <?= esc((string) $order['delivered_at']) ?>
                                <?php elseif (trim((string) ($order['estimated_delivery_at'] ?? '')) !== ''): ?>
                                    Tahmini teslimat: <?= esc((string) $order['estimated_delivery_at']) ?>
                                <?php else: ?>
                                    Teslimat bilgisi hazırlanıyor
                                <?php endif; ?>
                                <?php if (trim((string) ($order['tracking_number'] ?? '')) !== ''): ?>
                                    <br>Takip No: <?= esc((string) $order['tracking_number']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="order-card-actions">
                                <a href="<?= esc((string) ($order['detail_url'] ?? '#')) ?>" class="btn btn-outline-primary">Detayları Gör</a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
