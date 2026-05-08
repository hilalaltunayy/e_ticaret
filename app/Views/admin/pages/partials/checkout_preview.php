<?php
$preview = is_array($checkoutPreview ?? null) ? $checkoutPreview : [];
$config = is_array($preview['config'] ?? null) ? $preview['config'] : [];
$sections = is_array($preview['sections'] ?? null) ? $preview['sections'] : [];
$hasVisibleSections = ! empty($preview['hasVisibleSections']);
$visibleSectionCount = (int) ($preview['visibleSectionCount'] ?? 0);

$sectionMap = [];
foreach ($sections as $section) {
    if (! is_array($section) || empty($section['key'])) {
        continue;
    }

    $sectionMap[(string) $section['key']] = $section;
}

$topSection = $sectionMap['sayfa_ust_alani'] ?? ['active' => true];
$stepsSection = $sectionMap['adim_cubugu'] ?? ['active' => true];
$deliverySection = $sectionMap['teslimat_fatura_alani'] ?? ['active' => true];
$paymentSection = $sectionMap['odeme_yontemi_alani'] ?? ['active' => true];
$summarySection = $sectionMap['siparis_ozeti_alani'] ?? ['active' => true];
$infoSection = $sectionMap['bilgilendirme_guven_cta_alani'] ?? ['active' => true];

$pageTitle = trim((string) ($config['sayfa_basligi'] ?? 'Guvenli Checkout'));
$pageSubtitle = trim((string) ($config['sayfa_alt_basligi'] ?? ''));
$trustNote = trim((string) ($config['guven_kisa_notu'] ?? ''));
$stepsDescription = trim((string) ($config['adim_cubugu_aciklama'] ?? ''));
$deliveryTitle = trim((string) ($config['teslimat_baslik'] ?? 'Teslimat ve Fatura Bilgileri'));
$deliveryDescription = trim((string) ($config['teslimat_aciklama'] ?? ''));
$sameAddressNote = trim((string) ($config['ayni_adres_notu'] ?? ''));
$requiredFieldsNote = trim((string) ($config['zorunlu_alan_bilgi_metni'] ?? ''));
$paymentTitle = trim((string) ($config['odeme_baslik'] ?? 'Odeme Yontemi'));
$paymentDescription = trim((string) ($config['odeme_aciklama'] ?? ''));
$securePaymentNote = trim((string) ($config['guvenli_odeme_notu'] ?? ''));
$summaryTitle = trim((string) ($config['ozet_baslik'] ?? 'Siparis Ozeti'));
$orderTypeNote = trim((string) ($config['siparis_tipi_notu'] ?? ''));
$infoTitle = trim((string) ($config['bilgi_kutusu_baslik'] ?? ''));
$infoDescription = trim((string) ($config['bilgi_kutusu_aciklama'] ?? ''));
$trustMessage = trim((string) ($config['guven_mesaji'] ?? ''));
$completeButtonLabel = trim((string) ($config['tamamla_buton_metni'] ?? 'Siparisi Tamamla'));
$helpText = trim((string) ($config['alt_yardim_metni'] ?? ''));
?>

<style>
    .checkout-builder-preview {
        display: grid;
        gap: 1rem;
    }
    .checkout-builder-preview-shell,
    .checkout-builder-preview-card,
    .checkout-builder-preview-item {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }
    .checkout-builder-preview-shell {
        padding: 1rem;
    }
    .checkout-builder-preview-header {
        display: grid;
        gap: 0.35rem;
    }
    .checkout-builder-preview-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.32rem 0.72rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 700;
        width: fit-content;
    }
    .checkout-builder-preview-breadcrumb {
        color: #64748b;
        font-size: 0.84rem;
        font-weight: 600;
        margin-bottom: 0.7rem;
    }
    .checkout-builder-preview-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.3rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .checkout-builder-preview-copy,
    .checkout-builder-preview-meta,
    .checkout-builder-preview-note-text {
        color: #64748b;
        line-height: 1.6;
        font-size: 0.9rem;
    }
    .checkout-builder-preview-note {
        color: #1d4ed8;
        font-size: 0.86rem;
        font-weight: 700;
    }
    .checkout-builder-preview-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-top: 0.9rem;
    }
    .checkout-builder-preview-step {
        display: inline-flex;
        align-items: center;
        padding: 0.42rem 0.78rem;
        border-radius: 999px;
        background: rgba(226, 232, 240, 0.9);
        color: #475569;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .checkout-builder-preview-step.is-active {
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }
    .checkout-builder-preview-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1rem;
    }
    .checkout-builder-preview-column {
        display: grid;
        gap: 0.9rem;
        min-width: 0;
    }
    .checkout-builder-preview-card {
        padding: 1rem;
    }
    .checkout-builder-preview-card-title {
        margin: 0;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
    }
    .checkout-builder-preview-kv {
        display: grid;
        gap: 0.6rem;
        margin-top: 0.9rem;
    }
    .checkout-builder-preview-kv-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: start;
        padding-bottom: 0.6rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.14);
    }
    .checkout-builder-preview-kv-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .checkout-builder-preview-kv-label {
        color: #64748b;
        font-size: 0.86rem;
        font-weight: 600;
    }
    .checkout-builder-preview-kv-value {
        color: #0f172a;
        font-size: 0.88rem;
        font-weight: 700;
        text-align: right;
    }
    .checkout-builder-preview-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.76rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 800;
        margin-top: 0.8rem;
    }
    .checkout-builder-preview-item-list {
        display: grid;
        gap: 0.75rem;
        margin-top: 0.9rem;
    }
    .checkout-builder-preview-item {
        padding: 0.85rem;
    }
    .checkout-builder-preview-item-grid {
        display: grid;
        grid-template-columns: 72px minmax(0, 1fr);
        gap: 0.8rem;
        align-items: start;
    }
    .checkout-builder-preview-item-thumb {
        width: 72px;
        height: 92px;
        border-radius: 14px;
        background: #f8fafc;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.74rem;
        font-weight: 700;
    }
    .checkout-builder-preview-item-name {
        margin: 0;
        color: #0f172a;
        font-size: 0.92rem;
        font-weight: 800;
        line-height: 1.4;
    }
    .checkout-builder-preview-item-bottom {
        display: flex;
        justify-content: space-between;
        gap: 0.7rem;
        align-items: center;
        margin-top: 0.65rem;
        flex-wrap: wrap;
    }
    .checkout-builder-preview-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.34rem 0.66rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.76rem;
        font-weight: 700;
    }
    .checkout-builder-preview-item-total {
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 800;
    }
    .checkout-builder-preview-summary-rows {
        display: grid;
        gap: 0.75rem;
        margin-top: 0.9rem;
    }
    .checkout-builder-preview-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 0.7rem;
        color: #475569;
        font-size: 0.88rem;
    }
    .checkout-builder-preview-summary-row strong {
        color: #0f172a;
    }
    .checkout-builder-preview-summary-row.savings strong {
        color: #16a34a;
    }
    .checkout-builder-preview-divider {
        height: 1px;
        background: rgba(148, 163, 184, 0.2);
        margin: 0.9rem 0;
    }
    .checkout-builder-preview-total {
        display: flex;
        justify-content: space-between;
        gap: 0.7rem;
        color: #0f172a;
        font-size: 0.96rem;
        font-weight: 800;
    }
    .checkout-builder-preview-actions {
        display: grid;
        gap: 0.65rem;
        margin-top: 1rem;
    }
    .checkout-builder-preview-btn {
        min-height: 42px;
        border-radius: 14px;
        font-size: 0.84rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .checkout-builder-preview-btn.disabled {
        background: #e2e8f0;
        color: #475569;
        border: 1px solid rgba(148, 163, 184, 0.22);
    }
    .checkout-builder-preview-btn.secondary {
        background: rgba(248, 250, 252, 0.95);
        border: 1px solid rgba(148, 163, 184, 0.22);
        color: #334155;
    }
    .checkout-builder-preview-note-list {
        display: grid;
        gap: 0.55rem;
        margin-top: 0.9rem;
    }
    .checkout-builder-preview-note-row {
        display: flex;
        gap: 0.55rem;
        align-items: start;
        color: #475569;
        font-size: 0.86rem;
        line-height: 1.55;
    }
    .checkout-builder-preview-note-row i {
        color: #1d4ed8;
        margin-top: 0.08rem;
    }
    @media (max-width: 767.98px) {
        .checkout-builder-preview-item-grid {
            grid-template-columns: 1fr;
        }
        .checkout-builder-preview-item-thumb {
            width: 100%;
            height: 120px;
        }
    }
</style>

<div class="checkout-builder-preview">
    <div class="card border shadow-none bg-light mb-0">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <div class="fw-semibold mb-1">Canli odeme akisina yakin on izleme</div>
                    <div class="small text-muted">Bu alan readonly storefront shell yapisini ornekler; gercek odeme veya kart islemi yapilmaz.</div>
                </div>
                <span class="badge bg-light-primary"><?= esc((string) $visibleSectionCount) ?> aktif bolum</span>
            </div>
        </div>
    </div>

    <?php if (! $hasVisibleSections): ?>
        <div class="alert alert-light border mb-0">
            <div class="fw-semibold mb-1">Preview hazir</div>
            <div class="small text-muted">Tum bolumler pasif olsa bile checkout akisinin bos kalmamasi icin guvenli fallback durum gosterilir.</div>
        </div>
    <?php endif; ?>

    <div class="checkout-builder-preview-shell">
        <?php if (! empty($config['breadcrumb_goster']) && ! empty($topSection['active'])): ?>
            <div class="checkout-builder-preview-breadcrumb">Ana Sayfa / Sepetim / <?= esc($pageTitle !== '' ? $pageTitle : 'Odeme Bilgileri') ?></div>
        <?php endif; ?>

        <?php if (! empty($topSection['active'])): ?>
            <div class="checkout-builder-preview-header">
                <span class="checkout-builder-preview-kicker">Readonly checkout shell</span>
                <h3 class="checkout-builder-preview-title"><?= esc($pageTitle !== '' ? $pageTitle : 'Odeme Bilgileri') ?></h3>
                <?php if ($pageSubtitle !== ''): ?>
                    <div class="checkout-builder-preview-copy"><?= esc($pageSubtitle) ?></div>
                <?php endif; ?>
                <?php if ($trustNote !== ''): ?>
                    <div class="checkout-builder-preview-note"><?= esc($trustNote) ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($stepsSection['active']) && ! empty($config['adim_cubugu_gorunur'])): ?>
            <div class="checkout-builder-preview-steps">
                <span class="checkout-builder-preview-step">Sepet</span>
                <span class="checkout-builder-preview-step is-active">Odeme</span>
                <span class="checkout-builder-preview-step">Onay</span>
            </div>
            <?php if ($stepsDescription !== ''): ?>
                <div class="checkout-builder-preview-copy mt-2"><?= esc($stepsDescription) ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="checkout-builder-preview-layout">
        <div class="checkout-builder-preview-column">
            <section class="checkout-builder-preview-card">
                <h4 class="checkout-builder-preview-card-title">Iletisim Bilgileri</h4>
                <div class="checkout-builder-preview-copy mt-2">Siparis onayi ve odeme baglanti adimlari icin kullanilacak temel bilgiler.</div>
                <div class="checkout-builder-preview-kv">
                    <div class="checkout-builder-preview-kv-row">
                        <span class="checkout-builder-preview-kv-label">Ad Soyad</span>
                        <span class="checkout-builder-preview-kv-value">Ornek Kullanici</span>
                    </div>
                    <div class="checkout-builder-preview-kv-row">
                        <span class="checkout-builder-preview-kv-label">E-posta</span>
                        <span class="checkout-builder-preview-kv-value">preview@test.local</span>
                    </div>
                    <div class="checkout-builder-preview-kv-row">
                        <span class="checkout-builder-preview-kv-label">Telefon</span>
                        <span class="checkout-builder-preview-kv-value">Sonraki sprintte profil alanina eklenecek</span>
                    </div>
                </div>
            </section>

            <?php if (! empty($deliverySection['active'])): ?>
                <section class="checkout-builder-preview-card">
                    <h4 class="checkout-builder-preview-card-title"><?= esc($deliveryTitle !== '' ? $deliveryTitle : 'Teslimat bilgileri') ?></h4>
                    <?php if ($deliveryDescription !== ''): ?>
                        <div class="checkout-builder-preview-copy mt-2"><?= esc($deliveryDescription) ?></div>
                    <?php endif; ?>
                    <span class="checkout-builder-preview-pill">Adres defteri sonraki sprintte eklenecek.</span>
                    <?php if ($sameAddressNote !== '' || $requiredFieldsNote !== ''): ?>
                        <div class="checkout-builder-preview-note-list">
                            <?php if ($sameAddressNote !== ''): ?>
                                <div class="checkout-builder-preview-note-row">
                                    <i class="ti ti-info-circle"></i>
                                    <span><?= esc($sameAddressNote) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($requiredFieldsNote !== ''): ?>
                                <div class="checkout-builder-preview-note-row">
                                    <i class="ti ti-alert-circle"></i>
                                    <span><?= esc($requiredFieldsNote) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <section class="checkout-builder-preview-card">
                <h4 class="checkout-builder-preview-card-title">Fatura Bilgileri</h4>
                <div class="checkout-builder-preview-copy mt-2">Kurumsal veya bireysel fatura tercihleri odeme altyapisi ile birlikte tamamlanacak.</div>
                <span class="checkout-builder-preview-pill">Fatura detay formu sonraki sprintte acilacak.</span>
            </section>

            <?php if (! empty($paymentSection['active'])): ?>
                <section class="checkout-builder-preview-card">
                    <h4 class="checkout-builder-preview-card-title"><?= esc($paymentTitle !== '' ? $paymentTitle : 'Odeme yontemi') ?></h4>
                    <?php if ($paymentDescription !== ''): ?>
                        <div class="checkout-builder-preview-copy mt-2"><?= esc($paymentDescription) ?></div>
                    <?php endif; ?>
                    <?php if (! empty($config['kart_logo_goster']) || ! empty($config['guven_rozeti_goster'])): ?>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <?php if (! empty($config['kart_logo_goster'])): ?>
                                <span class="checkout-builder-preview-badge">Kart logo placeholder</span>
                            <?php endif; ?>
                            <?php if (! empty($config['guven_rozeti_goster'])): ?>
                                <span class="checkout-builder-preview-badge">Guven rozeti placeholder</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="checkout-builder-preview-note-list">
                        <div class="checkout-builder-preview-note-row">
                            <i class="ti ti-lock"></i>
                            <span>Sanal POS entegrasyonu sonraki sprintte baglanacak.</span>
                        </div>
                        <?php if ($securePaymentNote !== ''): ?>
                            <div class="checkout-builder-preview-note-row">
                                <i class="ti ti-shield-check"></i>
                                <span><?= esc($securePaymentNote) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="checkout-builder-preview-card">
                <h4 class="checkout-builder-preview-card-title">Sepetteki Urunler</h4>
                <div class="checkout-builder-preview-copy mt-2">Odeme oncesi urunlerin ve adetlerin compact ozet alani.</div>
                <div class="checkout-builder-preview-item-list">
                    <article class="checkout-builder-preview-item">
                        <div class="checkout-builder-preview-item-grid">
                            <div class="checkout-builder-preview-item-thumb">URUN</div>
                            <div>
                                <h5 class="checkout-builder-preview-item-name">Sepet Fiyat Dusus Test Kitabi</h5>
                                <div class="checkout-builder-preview-meta mt-1">Yazar: Ornek Yazar</div>
                                <div class="checkout-builder-preview-meta">Adet: 2</div>
                                <div class="checkout-builder-preview-item-bottom">
                                    <span class="checkout-builder-preview-badge">basili</span>
                                    <span class="checkout-builder-preview-item-total">400,00 TL</span>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="checkout-builder-preview-item">
                        <div class="checkout-builder-preview-item-grid">
                            <div class="checkout-builder-preview-item-thumb">URUN</div>
                            <div>
                                <h5 class="checkout-builder-preview-item-name">Sepet Normal Fiyat Test Kitabi</h5>
                                <div class="checkout-builder-preview-meta mt-1">Dijital urun ornegi</div>
                                <div class="checkout-builder-preview-meta">Adet: 1</div>
                                <div class="checkout-builder-preview-item-bottom">
                                    <span class="checkout-builder-preview-badge">dijital</span>
                                    <span class="checkout-builder-preview-item-total">180,00 TL</span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </div>

        <?php if (! empty($summarySection['active']) || ! empty($infoSection['active'])): ?>
            <div class="checkout-builder-preview-column">
                <?php if (! empty($summarySection['active'])): ?>
                    <aside class="checkout-builder-preview-card">
                        <h4 class="checkout-builder-preview-card-title"><?= esc($summaryTitle !== '' ? $summaryTitle : 'Siparis Ozeti') ?></h4>
                        <div class="checkout-builder-preview-summary-rows">
                            <div class="checkout-builder-preview-summary-row">
                                <span>Urun adedi</span>
                                <strong>3</strong>
                            </div>
                            <div class="checkout-builder-preview-summary-row">
                                <span>Urunler toplami</span>
                                <strong>580,00 TL</strong>
                            </div>
                            <?php if (! empty($config['indirim_satiri_goster'])): ?>
                                <div class="checkout-builder-preview-summary-row savings">
                                    <span>Toplam kazanciniz</span>
                                    <strong>100,00 TL</strong>
                                </div>
                            <?php endif; ?>
                            <?php if (! empty($config['kargo_satiri_goster'])): ?>
                                <div class="checkout-builder-preview-summary-row">
                                    <span>Kargo</span>
                                    <strong>Odeme adiminda</strong>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="checkout-builder-preview-divider"></div>

                        <div class="checkout-builder-preview-total">
                            <span>Genel toplam</span>
                            <span>580,00 TL</span>
                        </div>

                        <?php if ($orderTypeNote !== ''): ?>
                            <div class="checkout-builder-preview-copy mt-3"><?= esc($orderTypeNote) ?></div>
                        <?php endif; ?>

                        <div class="checkout-builder-preview-actions">
                            <span class="checkout-builder-preview-btn disabled"><?= esc($completeButtonLabel !== '' ? $completeButtonLabel : 'Odeme Altyapisi Hazirlaniyor') ?></span>
                            <span class="checkout-builder-preview-btn secondary">Sepete Don</span>
                        </div>
                    </aside>
                <?php endif; ?>

                <?php if (! empty($infoSection['active'])): ?>
                    <section class="checkout-builder-preview-card">
                        <?php if ($infoTitle !== '' || $infoDescription !== ''): ?>
                            <div class="alert alert-light border mb-3">
                                <?php if ($infoTitle !== ''): ?>
                                    <div class="fw-semibold mb-1"><?= esc($infoTitle) ?></div>
                                <?php endif; ?>
                                <?php if ($infoDescription !== ''): ?>
                                    <div class="small"><?= esc($infoDescription) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($trustMessage !== '' || $helpText !== ''): ?>
                            <div class="checkout-builder-preview-note-list">
                                <?php if ($trustMessage !== ''): ?>
                                    <div class="checkout-builder-preview-note-row">
                                        <i class="ti ti-shield-check"></i>
                                        <span><?= esc($trustMessage) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($helpText !== ''): ?>
                                    <div class="checkout-builder-preview-note-row">
                                        <i class="ti ti-help-circle"></i>
                                        <span><?= esc($helpText) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="checkout-builder-preview-note-list mt-3">
                            <div class="checkout-builder-preview-note-row">
                                <i class="ti ti-shield-lock"></i>
                                <span>Gercek kart cekimi veya odeme islemi bu on izlemede yapilmaz.</span>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
