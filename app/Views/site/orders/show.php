<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$order = is_array($order ?? null) ? $order : [];
$items = is_array($order['items'] ?? null) ? $order['items'] : [];
$timeline = is_array($order['timeline'] ?? null) ? $order['timeline'] : [];
$returnState = is_array($order['return_state'] ?? null) ? $order['return_state'] : [];
?>
<style>
    .order-detail-page {
        max-width: 1240px;
        margin: 0 auto;
        padding: 1.25rem 1rem 2.5rem;
    }
    .order-detail-back {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 1rem;
        color: #2563eb;
        font-weight: 700;
        text-decoration: none;
    }
    .order-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(300px, 360px);
        gap: 1.25rem;
        align-items: start;
    }
    .order-detail-card,
    .order-detail-summary-card,
    .timeline-card,
    .return-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }
    .order-detail-card,
    .timeline-card,
    .return-card {
        padding: 1.05rem;
    }
    .order-detail-summary-card {
        padding: 1.1rem;
        position: sticky;
        top: 110px;
    }
    .order-detail-header {
        display: flex;
        align-items: start;
        justify-content: space-between;
        gap: 0.85rem;
        flex-wrap: wrap;
    }
    .order-detail-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.3rem, 2vw, 1.8rem);
        font-weight: 800;
    }
    .order-detail-date {
        margin: 0.35rem 0 0;
        color: #64748b;
        line-height: 1.55;
    }
    .order-detail-status {
        display: inline-flex;
        align-items: center;
        min-height: 36px;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        font-size: 0.83rem;
        font-weight: 800;
    }
    .order-detail-status.success { background: rgba(22, 163, 74, 0.1); color: #166534; }
    .order-detail-status.info { background: rgba(37, 99, 235, 0.1); color: #1d4ed8; }
    .order-detail-status.warning { background: rgba(245, 158, 11, 0.14); color: #92400e; }
    .order-detail-status.danger { background: rgba(220, 38, 38, 0.1); color: #b91c1c; }
    .order-detail-status.secondary { background: rgba(148, 163, 184, 0.16); color: #475569; }
    .order-overview-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin-top: 1rem;
    }
    .order-overview-box {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 16px;
        background: #f8fafc;
        padding: 0.9rem;
    }
    .order-overview-label {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .order-overview-value {
        color: #0f172a;
        font-weight: 800;
        line-height: 1.45;
    }
    .timeline-title,
    .order-items-title,
    .summary-title,
    .return-title {
        margin: 0 0 0.9rem;
        color: #0f172a;
        font-size: 1.02rem;
        font-weight: 800;
    }
    .timeline-list {
        display: grid;
        gap: 0.85rem;
    }
    .timeline-item {
        display: grid;
        grid-template-columns: 24px minmax(0, 1fr);
        gap: 0.9rem;
        align-items: start;
    }
    .timeline-marker {
        width: 24px;
        height: 24px;
        border-radius: 999px;
        border: 2px solid rgba(148, 163, 184, 0.35);
        background: #fff;
        position: relative;
        margin-top: 0.1rem;
    }
    .timeline-marker::after {
        content: '';
        position: absolute;
        left: 50%;
        top: calc(100% + 4px);
        width: 2px;
        height: 30px;
        transform: translateX(-50%);
        background: rgba(148, 163, 184, 0.22);
    }
    .timeline-item:last-child .timeline-marker::after {
        display: none;
    }
    .timeline-item.completed .timeline-marker {
        background: #2563eb;
        border-color: #2563eb;
    }
    .timeline-item.current .timeline-marker {
        background: rgba(37, 99, 235, 0.14);
        border-color: #2563eb;
        box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.08);
    }
    .timeline-item-content {
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 16px;
        padding: 0.85rem 0.95rem;
        background: #fff;
    }
    .timeline-item-title {
        margin: 0;
        color: #0f172a;
        font-weight: 800;
    }
    .timeline-item-meta {
        margin: 0.35rem 0 0;
        color: #64748b;
        line-height: 1.6;
        font-size: 0.9rem;
    }
    .order-items-list {
        display: grid;
        gap: 0.85rem;
    }
    .order-item-row {
        display: grid;
        grid-template-columns: 92px minmax(0, 1fr) auto;
        gap: 0.9rem;
        align-items: start;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 16px;
        padding: 0.85rem;
        background: #fff;
    }
    .order-item-image {
        width: 92px;
        height: 112px;
        border-radius: 14px;
        object-fit: cover;
        display: block;
        background: #f8fafc;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }
    .order-item-name {
        margin: 0;
        color: #0f172a;
        font-weight: 800;
        line-height: 1.45;
    }
    .order-item-author,
    .order-item-meta {
        margin: 0.35rem 0 0;
        color: #64748b;
        line-height: 1.55;
        font-size: 0.9rem;
    }
    .order-item-status {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        margin-top: 0.6rem;
    }
    .order-item-status.success { background: rgba(22, 163, 74, 0.1); color: #166534; }
    .order-item-status.info { background: rgba(37, 99, 235, 0.1); color: #1d4ed8; }
    .order-item-status.warning { background: rgba(245, 158, 11, 0.14); color: #92400e; }
    .order-item-status.danger { background: rgba(220, 38, 38, 0.1); color: #b91c1c; }
    .order-item-status.secondary { background: rgba(148, 163, 184, 0.16); color: #475569; }
    .order-item-price {
        min-width: 135px;
        text-align: right;
        color: #0f172a;
        font-weight: 800;
    }
    .summary-row,
    .summary-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.8rem;
        color: #475569;
    }
    .summary-row strong,
    .summary-total strong {
        color: #0f172a;
    }
    .summary-divider {
        height: 1px;
        background: rgba(148, 163, 184, 0.18);
        margin: 1rem 0;
    }
    .summary-total {
        font-size: 1.02rem;
        font-weight: 800;
        margin-bottom: 0;
    }
    .return-card p {
        margin: 0;
        color: #64748b;
        line-height: 1.65;
    }
    .return-card .btn {
        min-height: 42px;
        border-radius: 12px;
        font-weight: 700;
        margin-top: 0.9rem;
    }
    @media (max-width: 991.98px) {
        .order-detail-grid {
            grid-template-columns: 1fr;
        }
        .order-detail-summary-card {
            position: static;
        }
    }
    @media (max-width: 767.98px) {
        .order-detail-page {
            padding-inline: 0.85rem;
        }
        .order-overview-grid,
        .order-item-row {
            grid-template-columns: 1fr;
        }
        .order-item-image {
            width: 100%;
            height: 220px;
        }
        .order-item-price {
            text-align: left;
            min-width: 0;
        }
    }
</style>

<section class="order-detail-page">
    <a href="<?= base_url('yardim/siparislerim') ?>" class="order-detail-back">
        <i class="ti ti-arrow-left"></i>
        Siparişlerime Dön
    </a>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success mb-3"><?= esc((string) session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mb-3"><?= esc((string) session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="order-detail-grid">
        <div class="d-grid gap-3">
            <article class="order-detail-card">
                <div class="order-detail-header">
                    <div>
                        <h1 class="order-detail-title">Sipariş No: <?= esc((string) ($order['order_number'] ?? '')) ?></h1>
                        <p class="order-detail-date">Sipariş tarihi: <?= esc((string) ($order['order_date'] ?? '')) ?></p>
                    </div>
                    <span class="order-detail-status <?= esc((string) ($order['status_badge_class'] ?? 'secondary')) ?>">
                        <?= esc((string) ($order['status_label'] ?? 'Sipariş Alındı')) ?>
                    </span>
                </div>

                <div class="order-overview-grid">
                    <div class="order-overview-box">
                        <div class="order-overview-label">Ödeme Durumu</div>
                        <div class="order-overview-value"><?= esc((string) ($order['payment_status_label'] ?? '-')) ?></div>
                    </div>
                    <div class="order-overview-box">
                        <div class="order-overview-label">Teslimat Durumu</div>
                        <div class="order-overview-value"><?= esc((string) ($order['fulfillment_status_label'] ?? '-')) ?></div>
                    </div>
                    <div class="order-overview-box">
                        <div class="order-overview-label">Tahmini Teslimat</div>
                        <div class="order-overview-value"><?= esc(trim((string) ($order['estimated_delivery_at'] ?? '')) !== '' ? (string) $order['estimated_delivery_at'] : 'Hazırlanıyor') ?></div>
                    </div>
                    <div class="order-overview-box">
                        <div class="order-overview-label">Kargo / Takip No</div>
                        <div class="order-overview-value">
                            <?= esc(trim((string) ($order['carrier'] ?? '')) !== '' ? (string) $order['carrier'] : 'Belirlenmedi') ?>
                            <?php if (trim((string) ($order['tracking_number'] ?? '')) !== ''): ?>
                                <br><?= esc((string) $order['tracking_number']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>

            <article class="timeline-card">
                <h2 class="timeline-title">Kargo Takip Zaman Çizelgesi</h2>
                <div class="timeline-list">
                    <?php foreach ($timeline as $step): ?>
                        <div class="timeline-item <?= esc((string) ($step['state'] ?? 'pending')) ?>">
                            <div class="timeline-marker"></div>
                            <div class="timeline-item-content">
                                <h3 class="timeline-item-title"><?= esc((string) ($step['title'] ?? 'Adım')) ?></h3>
                                <?php if (trim((string) ($step['note'] ?? '')) !== '' || trim((string) ($step['location'] ?? '')) !== '' || trim((string) ($step['time'] ?? '')) !== ''): ?>
                                    <p class="timeline-item-meta">
                                        <?php if (trim((string) ($step['note'] ?? '')) !== ''): ?>
                                            <?= esc((string) $step['note']) ?>
                                        <?php endif; ?>
                                        <?php if (trim((string) ($step['location'] ?? '')) !== ''): ?>
                                            <br><?= esc((string) $step['location']) ?>
                                        <?php endif; ?>
                                        <?php if (trim((string) ($step['time'] ?? '')) !== ''): ?>
                                            <br><?= esc((string) $step['time']) ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="order-detail-card">
                <h2 class="order-items-title">Sipariş İçeriği</h2>
                <div class="order-items-list">
                    <?php foreach ($items as $item): ?>
                        <div class="order-item-row">
                            <img src="<?= esc((string) ($item['image_url'] ?? '')) ?>" alt="<?= esc((string) ($item['product_name'] ?? 'Ürün')) ?>" class="order-item-image">
                            <div>
                                <h3 class="order-item-name"><?= esc((string) ($item['product_name'] ?? '')) ?></h3>
                                <?php if (trim((string) ($item['author'] ?? '')) !== ''): ?>
                                    <p class="order-item-author">Yazar: <?= esc((string) $item['author']) ?></p>
                                <?php endif; ?>
                                <p class="order-item-meta">
                                    Adet: <?= esc((string) ($item['quantity'] ?? 1)) ?>
                                    <?php if (trim((string) ($item['product_type'] ?? '')) !== ''): ?>
                                        <br>Tür: <?= esc((string) $item['product_type']) ?>
                                    <?php endif; ?>
                                </p>
                                <span class="order-item-status <?= esc((string) ($item['item_status_badge_class'] ?? 'secondary')) ?>">
                                    <?= esc((string) ($item['item_status_label'] ?? 'Hazırlanıyor')) ?>
                                </span>
                            </div>
                            <div class="order-item-price">
                                <div><?= number_format((float) ($item['unit_price'] ?? 0), 2, ',', '.') ?> TL</div>
                                <div class="order-item-meta">Satır toplam: <?= number_format((float) ($item['line_total'] ?? 0), 2, ',', '.') ?> TL</div>
                                <?php if (! empty($item['detail_url'])): ?>
                                    <a href="<?= esc((string) $item['detail_url']) ?>" class="btn btn-sm btn-outline-primary mt-2">Ürüne Git</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="return-card">
                <h2 class="return-title"><?= esc((string) ($returnState['label'] ?? 'İade')) ?></h2>
                <p><?= esc((string) ($returnState['message'] ?? 'İade bilgisi bulunamadı.')) ?></p>
                <?php if (! empty($returnState['status_label'])): ?>
                    <div class="mt-3">
                        <span class="order-detail-status warning"><?= esc((string) $returnState['status_label']) ?></span>
                    </div>
                    <?php if (trim((string) ($returnState['requested_at'] ?? '')) !== ''): ?>
                        <p class="mt-3 mb-0">Talep tarihi: <?= esc((string) $returnState['requested_at']) ?></p>
                    <?php endif; ?>
                    <?php if (trim((string) ($returnState['reason'] ?? '')) !== ''): ?>
                        <p class="mt-2 mb-0">Neden: <?= esc((string) $returnState['reason']) ?></p>
                    <?php endif; ?>
                    <?php if (trim((string) ($returnState['note'] ?? '')) !== ''): ?>
                        <p class="mt-2 mb-0">Not: <?= esc((string) $returnState['note']) ?></p>
                    <?php endif; ?>
                <?php elseif (! empty($returnState['can_request'])): ?>
                    <form action="<?= base_url('yardim/siparislerim/' . urlencode((string) ($order['order_number'] ?? '')) . '/iade-talebi') ?>" method="post" class="mt-3">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="return-reason" class="form-label">İade Nedeni</label>
                            <input type="text" name="reason" id="return-reason" class="form-control" maxlength="255" placeholder="Örn. Ürün beklentimi karşılamadı">
                        </div>
                        <div class="mb-0">
                            <label for="return-note" class="form-label">Not</label>
                            <textarea name="note" id="return-note" class="form-control" rows="3" placeholder="İsterseniz kısa bir açıklama ekleyebilirsiniz."></textarea>
                        </div>
                        <button type="submit" class="btn btn-outline-primary"><?= esc((string) ($returnState['button_label'] ?? 'İade Talebi Oluştur')) ?></button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-outline-secondary" disabled><?= esc((string) ($returnState['button_label'] ?? 'İade Talebi')) ?></button>
                <?php endif; ?>
            </article>
        </div>

        <aside class="order-detail-summary-card">
            <h2 class="summary-title">Sipariş Özeti</h2>
            <div class="summary-row">
                <span>Ara toplam</span>
                <strong><?= number_format((float) ($order['subtotal_amount'] ?? 0), 2, ',', '.') ?> TL</strong>
            </div>
            <div class="summary-row">
                <span>Kargo</span>
                <strong><?= number_format((float) ($order['shipping_amount'] ?? 0), 2, ',', '.') ?> TL</strong>
            </div>
            <div class="summary-row">
                <span>İndirim</span>
                <strong><?= number_format((float) ($order['discount_amount'] ?? 0), 2, ',', '.') ?> TL</strong>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-total">
                <span>Genel toplam</span>
                <strong><?= number_format((float) ($order['total_amount'] ?? 0), 2, ',', '.') ?> TL</strong>
            </div>
        </aside>
    </div>
</section>
<?= $this->endSection() ?>
