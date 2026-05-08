<?php
$preview = is_array($cartPreview ?? null) ? $cartPreview : [];
$config = is_array($preview['config'] ?? null) ? $preview['config'] : [];
$sections = is_array($preview['sections'] ?? null) ? $preview['sections'] : [];
$items = is_array($preview['sampleItems'] ?? null) ? $preview['sampleItems'] : [];
$optionalBlocks = is_array($preview['optionalBlocks'] ?? null) ? $preview['optionalBlocks'] : [];
$hasVisibleSections = ! empty($preview['hasVisibleSections']);
$visibleSectionCount = (int) ($preview['visibleSectionCount'] ?? 0);

$sectionMap = [];
foreach ($sections as $section) {
    if (! is_array($section) || empty($section['key'])) {
        continue;
    }

    $sectionMap[(string) $section['key']] = $section;
}

$topSection = $sectionMap['sayfa_ust_alani'] ?? ['active' => true, 'order' => 1];
$itemsSection = $sectionMap['sepet_urunleri_alani'] ?? ['active' => true, 'order' => 2];
$summarySection = $sectionMap['sepet_ozeti_cta_alani'] ?? ['active' => true, 'order' => 6];
$emptySection = $sectionMap['bos_sepet_alani'] ?? ['active' => true, 'order' => 7];

$pageTitle = trim((string) ($config['sayfa_basligi'] ?? 'Sepetim'));
$pageSubtitle = trim((string) ($config['sayfa_alt_basligi'] ?? ''));
$shortDescription = trim((string) ($config['kisa_aciklama'] ?? ''));
$itemsTitle = trim((string) ($config['sepet_urunleri_baslik'] ?? 'Sepetinizdeki Urunler'));
$itemsDescription = trim((string) ($config['sepet_urunleri_aciklama'] ?? ''));
$summaryTitle = trim((string) ($config['sepet_ozeti_baslik'] ?? 'Sepet Ozeti'));
$securePaymentNote = trim((string) ($config['guvenli_odeme_kisa_notu'] ?? ''));
$emptyTitle = trim((string) ($config['bos_sepet_baslik'] ?? 'Sepetiniz Su Anda Bos'));
$emptyDescription = trim((string) ($config['bos_sepet_aciklama'] ?? ''));
$emptyButtonText = trim((string) ($config['alisverise_basla_buton_metni'] ?? 'Alisverise Basla'));
?>

<style>
    .cart-builder-preview {
        display: grid;
        gap: 1rem;
    }
    .cart-builder-preview-shell,
    .cart-builder-preview-empty,
    .cart-builder-preview-muted {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }
    .cart-builder-preview-shell {
        padding: 1.1rem;
    }
    .cart-builder-preview-breadcrumb {
        color: #64748b;
        font-size: 0.85rem;
        margin-bottom: 0.75rem;
    }
    .cart-builder-preview-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 800;
    }
    .cart-builder-preview-subtitle,
    .cart-builder-preview-copy {
        color: #64748b;
        line-height: 1.6;
    }
    .cart-builder-preview-subtitle {
        margin: 0.45rem 0 0;
    }
    .cart-builder-preview-copy {
        margin: 0.7rem 0 0;
    }
    .cart-builder-preview-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        align-items: start;
        margin-top: 1rem;
    }
    .cart-builder-preview-items {
        display: grid;
        gap: 0.85rem;
    }
    .cart-builder-preview-section-label {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.75rem;
        padding: 0.32rem 0.68rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.79rem;
        font-weight: 700;
    }
    .cart-builder-preview-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 18px;
        background: #fff;
        padding: 0.95rem;
        min-width: 0;
    }
    .cart-builder-preview-item {
        display: grid;
        grid-template-columns: 78px minmax(0, 1fr);
        gap: 0.9rem;
        align-items: start;
    }
    .cart-builder-preview-item-content {
        display: grid;
        gap: 0.75rem;
        min-width: 0;
    }
    .cart-builder-preview-thumb {
        width: 78px;
        height: 98px;
        border-radius: 14px;
        background: #f8fafc;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .cart-builder-preview-item-top,
    .cart-builder-preview-item-bottom,
    .cart-builder-preview-summary-row,
    .cart-builder-preview-summary-total {
        display: flex;
        gap: 0.75rem;
        min-width: 0;
    }
    .cart-builder-preview-item-top {
        flex-direction: column;
        align-items: stretch;
    }
    .cart-builder-preview-item-bottom {
        flex-direction: column;
        align-items: stretch;
    }
    .cart-builder-preview-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-bottom: 0.55rem;
    }
    .cart-builder-preview-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.58rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .cart-builder-preview-badge.type {
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
    }
    .cart-builder-preview-badge.stock {
        background: rgba(22, 163, 74, 0.1);
        color: #166534;
    }
    .cart-builder-preview-name {
        margin: 0;
        color: #0f172a;
        font-size: 0.98rem;
        font-weight: 800;
        line-height: 1.45;
        word-break: break-word;
    }
    .cart-builder-preview-author {
        margin: 0.3rem 0 0;
        color: #64748b;
        font-size: 0.84rem;
    }
    .cart-builder-preview-price {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0.25rem 0.7rem;
        min-width: 0;
        text-align: left;
    }
    .cart-builder-preview-old-price {
        color: #dc2626;
        font-size: 0.8rem;
        text-decoration: line-through;
    }
    .cart-builder-preview-current-price,
    .cart-builder-preview-total-amount {
        color: #0f172a;
        font-weight: 800;
    }
    .cart-builder-preview-saving {
        color: #16a34a;
        font-size: 0.76rem;
        font-weight: 700;
    }
    .cart-builder-preview-controls-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .cart-builder-preview-total-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .cart-builder-preview-qty {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.26rem;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 999px;
        background: #f8fafc;
    }
    .cart-builder-preview-qty-btn {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.22);
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
    }
    .cart-builder-preview-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-start;
    }
    .cart-builder-preview-link,
    .cart-builder-preview-danger {
        min-height: 34px;
        padding: 0.45rem 0.85rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .cart-builder-preview-link {
        border: 1px solid rgba(37, 99, 235, 0.2);
        color: #1d4ed8;
        background: rgba(37, 99, 235, 0.04);
    }
    .cart-builder-preview-danger {
        border: 1px solid rgba(220, 38, 38, 0.15);
        color: #b91c1c;
        background: rgba(220, 38, 38, 0.04);
    }
    .cart-builder-preview-summary-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 18px;
        background: #fff;
        padding: 1rem;
        min-width: 0;
    }
    .cart-builder-preview-summary-title {
        margin: 0 0 0.95rem;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
    }
    .cart-builder-preview-summary-row {
        color: #475569;
        font-size: 0.88rem;
        margin-bottom: 0.75rem;
    }
    .cart-builder-preview-summary-row strong,
    .cart-builder-preview-summary-total {
        color: #0f172a;
    }
    .cart-builder-preview-summary-row.savings strong {
        color: #16a34a;
    }
    .cart-builder-preview-summary-divider {
        height: 1px;
        background: rgba(148, 163, 184, 0.2);
        margin: 0.95rem 0;
    }
    .cart-builder-preview-summary-total {
        align-items: center;
        font-size: 0.96rem;
        font-weight: 800;
    }
    .cart-builder-preview-summary-note {
        margin: 0.8rem 0 0;
        color: #64748b;
        font-size: 0.82rem;
        line-height: 1.55;
    }
    .cart-builder-preview-cta {
        width: 100%;
        margin-top: 0.9rem;
        min-height: 40px;
        border: 0;
        border-radius: 14px;
        background: #e2e8f0;
        color: #475569;
        font-weight: 700;
    }
    .cart-builder-preview-empty {
        padding: 1.35rem;
        text-align: center;
    }
    .cart-builder-preview-empty-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 0.9rem;
        border-radius: 999px;
        background: #f8fafc;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .cart-builder-preview-empty-title {
        margin: 0 0 0.45rem;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
    }
    .cart-builder-preview-empty-text {
        margin: 0 0 0.95rem;
        color: #64748b;
        font-size: 0.88rem;
        line-height: 1.6;
    }
    .cart-builder-preview-muted {
        padding: 1rem;
    }
    .cart-builder-preview-muted-title {
        margin: 0 0 0.65rem;
        color: #0f172a;
        font-size: 0.94rem;
        font-weight: 800;
    }
    .cart-builder-preview-muted-grid {
        display: grid;
        gap: 0.7rem;
    }
    .cart-builder-preview-muted-item {
        border: 1px dashed rgba(148, 163, 184, 0.35);
        border-radius: 14px;
        background: #f8fafc;
        padding: 0.8rem;
    }
    .cart-builder-preview-muted-label {
        color: #334155;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .cart-builder-preview-muted-copy {
        margin-top: 0.28rem;
        color: #64748b;
        font-size: 0.8rem;
        line-height: 1.5;
    }
    .cart-builder-preview-placeholder {
        margin-top: 0.35rem;
        color: #94a3b8;
        font-size: 0.78rem;
    }
    @media (min-width: 1500px) {
        .cart-builder-preview-layout {
            grid-template-columns: minmax(0, 1.45fr) minmax(250px, 320px);
        }
    }
    @media (max-width: 767.98px) {
        .cart-builder-preview-shell,
        .cart-builder-preview-empty,
        .cart-builder-preview-muted {
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }
        .cart-builder-preview-item {
            grid-template-columns: 1fr;
        }
        .cart-builder-preview-thumb {
            width: 100%;
            height: 90px;
        }
        .cart-builder-preview-price {
            text-align: left;
        }
        .cart-builder-preview-actions {
            width: 100%;
            justify-content: stretch;
        }
        .cart-builder-preview-link,
        .cart-builder-preview-danger {
            width: 100%;
        }
        .cart-builder-preview-summary-row,
        .cart-builder-preview-summary-total {
            align-items: start;
            flex-wrap: wrap;
        }
    }
</style>

<div class="cart-builder-preview">
    <?php if (! $hasVisibleSections): ?>
        <div class="alert alert-light border mb-0">
            <div class="fw-semibold mb-1">On izleme hazir</div>
            <div class="small text-muted">Tum bolumler pasif olsa bile sepet akisinin bos kalmamasi icin guvenli fallback durum gosterilir.</div>
        </div>
    <?php endif; ?>

    <div class="cart-builder-preview-shell">
        <?php if (! empty($config['breadcrumb_goster']) && ! empty($topSection['active'])): ?>
            <div class="cart-builder-preview-breadcrumb">Ana Sayfa / Sepet / <?= esc($pageTitle) ?></div>
        <?php endif; ?>

        <?php if (! empty($topSection['active'])): ?>
            <div class="cart-builder-preview-section-label">Sayfa basligi alani</div>
            <h3 class="cart-builder-preview-title"><?= esc($pageTitle) ?></h3>
            <?php if ($pageSubtitle !== ''): ?>
                <p class="cart-builder-preview-subtitle"><?= esc($pageSubtitle) ?></p>
            <?php endif; ?>
            <?php if ($shortDescription !== ''): ?>
                <p class="cart-builder-preview-copy"><?= esc($shortDescription) ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <div class="cart-builder-preview-layout">
            <div class="cart-builder-preview-items">
                <?php if (! empty($itemsSection['active'])): ?>
                    <div>
                        <div class="cart-builder-preview-section-label">Sepet urunleri alani</div>
                        <div class="cart-builder-preview-card">
                            <h4 class="cart-builder-preview-summary-title"><?= esc($itemsTitle) ?></h4>
                            <?php if ($itemsDescription !== ''): ?>
                                <p class="cart-builder-preview-copy mt-0 mb-3"><?= esc($itemsDescription) ?></p>
                            <?php endif; ?>

                            <?php foreach ($items as $item): ?>
                                <div class="cart-builder-preview-card mb-2">
                                    <div class="cart-builder-preview-item">
                                        <?php if (! empty($config['urun_gorseli_goster'])): ?>
                                            <div class="cart-builder-preview-thumb"><?= esc((string) ($item['image_label'] ?? 'URUN')) ?></div>
                                        <?php endif; ?>

                                        <div class="cart-builder-preview-item-content">
                                            <div class="cart-builder-preview-item-top">
                                                <div>
                                                    <div class="cart-builder-preview-meta">
                                                        <?php if (! empty($config['format_etiketi_goster'])): ?>
                                                            <span class="cart-builder-preview-badge type"><?= esc((string) ($item['type'] ?? 'Urun')) ?></span>
                                                        <?php endif; ?>
                                                        <span class="cart-builder-preview-badge stock"><?= esc((string) ($item['stock_state'] ?? 'Durum')) ?></span>
                                                    </div>
                                                    <h5 class="cart-builder-preview-name"><?= esc((string) ($item['name'] ?? 'Urun')) ?></h5>
                                                    <p class="cart-builder-preview-author">Ornek yazar bilgisi</p>
                                                </div>

                                                <div class="cart-builder-preview-price">
                                                    <?php if (! empty($item['price_decreased'])): ?>
                                                        <div class="cart-builder-preview-old-price"><?= esc(number_format((float) ($item['snapshot_price'] ?? 0), 2, ',', '.')) ?> TL</div>
                                                    <?php endif; ?>
                                                    <div class="cart-builder-preview-current-price"><?= esc(number_format((float) ($item['current_price'] ?? 0), 2, ',', '.')) ?> TL</div>
                                                    <?php if (! empty($item['price_decreased'])): ?>
                                                        <div class="cart-builder-preview-saving">Kazanciniz: <?= esc(number_format(((float) ($item['snapshot_price'] ?? 0) - (float) ($item['current_price'] ?? 0)) * (int) ($item['quantity'] ?? 1), 2, ',', '.')) ?> TL</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="cart-builder-preview-item-bottom">
                                                <div class="cart-builder-preview-controls-row">
                                                    <div>
                                                        <?php if (! empty($config['adet_kontrolu_goster']) && mb_strtolower((string) ($item['type'] ?? ''), 'UTF-8') !== 'dijital'): ?>
                                                            <div class="cart-builder-preview-qty">
                                                                <span class="cart-builder-preview-qty-btn">-</span>
                                                                <strong><?= esc((string) ($item['quantity'] ?? 1)) ?></strong>
                                                                <span class="cart-builder-preview-qty-btn">+</span>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="cart-builder-preview-placeholder">Dijital urun adet 1 olarak tutulur.</div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="cart-builder-preview-actions">
                                                        <span class="cart-builder-preview-link">Urune Git</span>
                                                        <span class="cart-builder-preview-danger"><?= esc(trim((string) ($config['kaldir_buton_metni'] ?? 'Kaldir')) ?: 'Kaldir') ?></span>
                                                    </div>
                                                </div>
                                                <div class="cart-builder-preview-total-row">
                                                    <span class="cart-builder-preview-placeholder">Ornek satir toplami</span>
                                                    <div class="cart-builder-preview-total-amount">Toplam: <?= esc(number_format((float) ($item['current_price'] ?? 0) * (int) ($item['quantity'] ?? 1), 2, ',', '.')) ?> TL</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (! empty($summarySection['active'])): ?>
                <aside>
                    <div class="cart-builder-preview-section-label">Sepet ozeti alani</div>
                    <div class="cart-builder-preview-summary-card">
                        <h4 class="cart-builder-preview-summary-title"><?= esc($summaryTitle) ?></h4>
                        <?php if (! empty($config['ara_toplam_goster'])): ?>
                            <div class="cart-builder-preview-summary-row">
                                <span>Urun adedi</span>
                                <strong>3</strong>
                            </div>
                            <div class="cart-builder-preview-summary-row">
                                <span>Urunler toplami</span>
                                <strong>529,70 TL</strong>
                            </div>
                        <?php endif; ?>
                        <?php if (! empty($config['indirim_goster'])): ?>
                            <div class="cart-builder-preview-summary-row savings">
                                <span>Toplam kazanciniz</span>
                                <strong>40,00 TL</strong>
                            </div>
                        <?php endif; ?>
                        <?php if (! empty($config['kargo_goster'])): ?>
                            <div class="cart-builder-preview-summary-row">
                                <span>Kargo</span>
                                <strong>Odeme adiminda</strong>
                            </div>
                        <?php endif; ?>
                        <div class="cart-builder-preview-summary-divider"></div>
                        <div class="cart-builder-preview-summary-total">
                            <span><?= esc(trim((string) ($config['genel_toplam_basligi'] ?? 'Genel Toplam')) ?: 'Genel Toplam') ?></span>
                            <span>529,70 TL</span>
                        </div>
                        <p class="cart-builder-preview-summary-note">Kargo ucreti odeme adiminda hesaplanacak.</p>
                        <?php if ($securePaymentNote !== ''): ?>
                            <p class="cart-builder-preview-summary-note"><?= esc($securePaymentNote) ?></p>
                        <?php endif; ?>
                        <button type="button" class="cart-builder-preview-cta" disabled><?= esc(trim((string) ($config['odeme_sayfasina_git_buton_metni'] ?? 'Odeme Sayfasina Git')) ?: 'Odeme Sayfasina Git') ?></button>
                    </div>
                </aside>
            <?php endif; ?>
        </div>
    </div>

    <?php if (! empty($emptySection['active'])): ?>
        <div class="cart-builder-preview-empty">
            <div class="cart-builder-preview-section-label">Bos sepet alani</div>
            <div class="cart-builder-preview-empty-icon"><i class="ti ti-shopping-cart-off"></i></div>
            <h4 class="cart-builder-preview-empty-title"><?= esc($emptyTitle) ?></h4>
            <?php if ($emptyDescription !== ''): ?>
                <p class="cart-builder-preview-empty-text"><?= esc($emptyDescription) ?></p>
            <?php endif; ?>
            <span class="cart-builder-preview-link"><?= esc($emptyButtonText !== '' ? $emptyButtonText : 'Alisverise Basla') ?></span>
        </div>
    <?php endif; ?>

    <?php
    $hasOptionalMutedBlocks = false;
    foreach ($optionalBlocks as $block) {
        if (! empty($block['active'])) {
            $hasOptionalMutedBlocks = true;
            break;
        }
    }
    ?>
    <?php if ($hasOptionalMutedBlocks): ?>
        <div class="cart-builder-preview-muted">
            <h4 class="cart-builder-preview-muted-title">Opsiyonel bilgilendirme alanlari</h4>
            <div class="cart-builder-preview-muted-grid">
                <?php foreach ($optionalBlocks as $block): ?>
                    <?php if (empty($block['active'])) {
                        continue;
                    } ?>
                    <div class="cart-builder-preview-muted-item">
                        <div class="cart-builder-preview-muted-label"><?= esc((string) ($block['title'] ?? 'Opsiyonel alan')) ?></div>
                        <?php if (trim((string) ($block['description'] ?? '')) !== ''): ?>
                            <div class="cart-builder-preview-muted-copy"><?= esc((string) $block['description']) ?></div>
                        <?php else: ?>
                            <div class="cart-builder-preview-placeholder">Aciklama verilmemis. Bu alan on izlemede gizli yardimci metin olmadan gosterilir.</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
