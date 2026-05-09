<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$checkoutView = is_array($checkoutView ?? null) ? $checkoutView : [];
$cartView = is_array($checkoutView['cartView'] ?? null) ? $checkoutView['cartView'] : [];
$items = is_array($checkoutView['items'] ?? null) ? $checkoutView['items'] : [];
$contact = is_array($checkoutView['contact'] ?? null) ? $checkoutView['contact'] : [];
$delivery = is_array($checkoutView['delivery'] ?? null) ? $checkoutView['delivery'] : [];
$billing = is_array($checkoutView['billing'] ?? null) ? $checkoutView['billing'] : [];
$payment = is_array($checkoutView['payment'] ?? null) ? $checkoutView['payment'] : [];
$securityNotes = is_array($checkoutView['securityNotes'] ?? null) ? $checkoutView['securityNotes'] : [];
$checkoutBuilderBinding = is_array($checkoutBuilderBinding ?? null) ? $checkoutBuilderBinding : [];
$checkoutBuilderPresenter = is_array($checkoutBuilderBinding['presenter'] ?? null) ? $checkoutBuilderBinding['presenter'] : [];
$hasCheckoutBuilderConfig = ! empty($checkoutBuilderBinding['hasPublishedConfig']);

$showCheckoutBreadcrumb = ! $hasCheckoutBuilderConfig || ! empty($checkoutBuilderPresenter['showBreadcrumb']);
$checkoutPageTitle = trim((string) ($checkoutBuilderPresenter['pageTitle'] ?? '')) ?: 'Odeme Bilgileri';
$checkoutPageSubtitle = trim((string) ($checkoutBuilderPresenter['pageSubtitle'] ?? ''));
if (! $hasCheckoutBuilderConfig && $checkoutPageSubtitle === '') {
    $checkoutPageSubtitle = 'Iletisim, teslimat ve odeme adiminin son halini bu ekranda gozden gecirebilirsiniz. Gercek sanal POS baglantisi sonraki sprintte acilacak.';
}
$checkoutTrustNote = trim((string) ($checkoutBuilderPresenter['trustNote'] ?? ''));
$checkoutDeliveryTitle = trim((string) ($checkoutBuilderPresenter['deliveryTitle'] ?? ''));
$checkoutDeliveryDescription = trim((string) ($checkoutBuilderPresenter['deliveryDescription'] ?? ''));
$checkoutPaymentTitle = trim((string) ($checkoutBuilderPresenter['paymentTitle'] ?? ''));
$checkoutPaymentDescription = trim((string) ($checkoutBuilderPresenter['paymentDescription'] ?? ''));
$checkoutSecurePaymentNote = trim((string) ($checkoutBuilderPresenter['securePaymentNote'] ?? ''));
$checkoutSummaryTitle = trim((string) ($checkoutBuilderPresenter['summaryTitle'] ?? '')) ?: 'Siparis Ozeti';
$checkoutInfoBoxTitle = trim((string) ($checkoutBuilderPresenter['infoBoxTitle'] ?? ''));
$checkoutInfoBoxDescription = trim((string) ($checkoutBuilderPresenter['infoBoxDescription'] ?? ''));
$checkoutTrustMessage = trim((string) ($checkoutBuilderPresenter['trustMessage'] ?? ''));
$checkoutCompleteButtonLabel = trim((string) ($checkoutBuilderPresenter['completeButtonLabel'] ?? '')) ?: 'Siparisi Tamamla';
$checkoutHelpText = trim((string) ($checkoutBuilderPresenter['helpText'] ?? ''));
?>
<style>
    .checkout-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 1.25rem 1rem 2.5rem;
    }
    .checkout-header {
        margin-bottom: 1.5rem;
    }
    .checkout-breadcrumb {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.75rem;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .checkout-breadcrumb a {
        color: #475569;
        text-decoration: none;
    }
    .checkout-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.45rem, 2vw, 1.95rem);
        font-weight: 800;
    }
    .checkout-subtitle {
        margin: 0.45rem 0 0;
        color: #64748b;
        max-width: 760px;
        line-height: 1.65;
    }
    .checkout-trust-note {
        margin: 0.75rem 0 0;
        color: #1d4ed8;
        font-size: 0.92rem;
        font-weight: 700;
    }
    .checkout-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(300px, 380px);
        gap: 1.35rem;
        align-items: start;
    }
    .checkout-column {
        display: grid;
        gap: 1rem;
    }
    .checkout-card,
    .checkout-summary-card,
    .checkout-item-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }
    .checkout-card,
    .checkout-summary-card {
        padding: 1.2rem;
    }
    .checkout-card-title,
    .checkout-summary-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 800;
    }
    .checkout-card-text {
        margin: 0.45rem 0 0;
        color: #64748b;
        line-height: 1.6;
    }
    .checkout-kv {
        display: grid;
        gap: 0.7rem;
        margin-top: 1rem;
    }
    .checkout-form-grid {
        display: grid;
        gap: 0.9rem;
        margin-top: 1rem;
    }
    .checkout-form-grid.two-col {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .checkout-field {
        display: grid;
        gap: 0.4rem;
    }
    .checkout-field.full {
        grid-column: 1 / -1;
    }
    .checkout-label {
        color: #334155;
        font-size: 0.88rem;
        font-weight: 700;
    }
    .checkout-input,
    .checkout-select,
    .checkout-textarea {
        width: 100%;
        min-height: 46px;
        padding: 0.78rem 0.9rem;
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: #fff;
        color: #0f172a;
        font-size: 0.94rem;
    }
    .checkout-textarea {
        min-height: 110px;
        resize: vertical;
    }
    .checkout-input[readonly] {
        background: #f8fafc;
        color: #475569;
    }
    .checkout-radio-row {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 0.85rem;
    }
    .checkout-radio-card {
        flex: 1 1 180px;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.9rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: #f8fafc;
    }
    .checkout-radio-card input {
        margin: 0;
    }
    .checkout-radio-card strong {
        color: #0f172a;
        font-size: 0.92rem;
    }
    .checkout-radio-card span {
        color: #64748b;
        font-size: 0.82rem;
    }
    .checkout-kv-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: start;
        padding-bottom: 0.7rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    }
    .checkout-kv-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }
    .checkout-kv-label {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .checkout-kv-value {
        color: #0f172a;
        font-size: 0.94rem;
        font-weight: 700;
        text-align: right;
    }
    .checkout-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.42rem 0.8rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-size: 0.82rem;
        font-weight: 800;
        margin-top: 0.9rem;
    }
    .checkout-inline-note {
        margin-top: 0.85rem;
        color: #64748b;
        font-size: 0.86rem;
        line-height: 1.6;
    }
    .checkout-item-list {
        display: grid;
        gap: 0.85rem;
        margin-top: 1rem;
    }
    .checkout-item-card {
        padding: 0.95rem;
    }
    .checkout-item-grid {
        display: grid;
        grid-template-columns: 84px minmax(0, 1fr);
        gap: 0.85rem;
        align-items: start;
    }
    .checkout-item-image {
        width: 84px;
        height: 104px;
        object-fit: cover;
        display: block;
        border-radius: 14px;
        background: #f8fafc;
    }
    .checkout-item-name {
        margin: 0;
        color: #0f172a;
        font-size: 0.98rem;
        font-weight: 800;
        line-height: 1.4;
    }
    .checkout-item-meta {
        margin-top: 0.35rem;
        color: #64748b;
        font-size: 0.88rem;
        line-height: 1.55;
    }
    .checkout-item-bottom {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: center;
        margin-top: 0.7rem;
        flex-wrap: wrap;
    }
    .checkout-item-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.34rem 0.68rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .checkout-item-total {
        color: #0f172a;
        font-size: 0.95rem;
        font-weight: 800;
    }
    .checkout-summary-card {
        position: sticky;
        top: 110px;
    }
    .checkout-summary-rows {
        display: grid;
        gap: 0.8rem;
        margin-top: 1rem;
    }
    .checkout-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        color: #475569;
        font-size: 0.94rem;
    }
    .checkout-summary-row strong {
        color: #0f172a;
    }
    .checkout-summary-row.savings strong {
        color: #16a34a;
    }
    .checkout-summary-divider {
        height: 1px;
        background: rgba(148, 163, 184, 0.2);
        margin: 1rem 0;
    }
    .checkout-summary-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
    }
    .checkout-summary-note {
        margin-top: 0.9rem;
        color: #64748b;
        font-size: 0.88rem;
        line-height: 1.6;
    }
    .checkout-actions {
        display: grid;
        gap: 0.7rem;
        margin-top: 1.15rem;
    }
    .checkout-actions .btn {
        min-height: 46px;
        border-radius: 14px;
        font-weight: 700;
    }
    .checkout-payment-options {
        display: grid;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    .checkout-payment-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.95rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: #f8fafc;
        color: #475569;
    }
    .checkout-payment-option input {
        margin: 0;
    }
    .checkout-payment-option strong {
        display: block;
        color: #0f172a;
        font-size: 0.92rem;
    }
    .checkout-payment-option span {
        display: block;
        color: #64748b;
        font-size: 0.82rem;
        line-height: 1.5;
    }
    .checkout-disabled {
        background: #e2e8f0;
        color: #475569;
        border: 1px solid rgba(148, 163, 184, 0.22);
        cursor: not-allowed;
    }
    .checkout-notes {
        display: grid;
        gap: 0.65rem;
        margin-top: 1rem;
    }
    .checkout-note {
        display: flex;
        gap: 0.6rem;
        align-items: start;
        color: #475569;
        font-size: 0.9rem;
        line-height: 1.6;
    }
    .checkout-note i {
        color: #1d4ed8;
        margin-top: 0.1rem;
    }
    @media (max-width: 991.98px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }
        .checkout-summary-card {
            position: static;
        }
    }
    @media (max-width: 767.98px) {
        .checkout-page {
            padding-inline: 0.85rem;
        }
        .checkout-form-grid.two-col {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="checkout-page">
    <header class="checkout-header">
        <?php if ($showCheckoutBreadcrumb): ?>
            <div class="checkout-breadcrumb">
                <a href="<?= base_url('/') ?>">Ana Sayfa</a>
                <span>/</span>
                <a href="<?= base_url('yardim/sepetim') ?>">Sepetim</a>
                <span>/</span>
                <span><?= esc($checkoutPageTitle) ?></span>
            </div>
        <?php endif; ?>
        <h1 class="checkout-title"><?= esc($checkoutPageTitle) ?></h1>
        <?php if ($checkoutPageSubtitle !== ''): ?>
            <p class="checkout-subtitle"><?= esc($checkoutPageSubtitle) ?></p>
        <?php endif; ?>
        <?php if ($checkoutTrustNote !== ''): ?>
            <p class="checkout-trust-note"><?= esc($checkoutTrustNote) ?></p>
        <?php endif; ?>
    </header>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mb-3"><?= esc((string) session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success mb-3"><?= esc((string) session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('yardim/odeme/tamamla') ?>">
        <?= csrf_field() ?>
    <div class="checkout-layout">
        <div class="checkout-column">
            <section class="checkout-card">
                <h2 class="checkout-card-title">Iletisim Bilgileri</h2>
                <p class="checkout-card-text">Siparis onayi ve odeme baglanti adimlari icin kullanilacak temel bilgiler.</p>
                <div class="checkout-kv">
                    <div class="checkout-kv-row">
                        <span class="checkout-kv-label">Ad Soyad</span>
                        <span class="checkout-kv-value"><?= esc(trim((string) ($contact['name'] ?? '')) !== '' ? (string) $contact['name'] : 'Hesap bilgisi ile devam edilecek') ?></span>
                    </div>
                    <div class="checkout-kv-row">
                        <span class="checkout-kv-label">E-posta</span>
                        <span class="checkout-kv-value"><?= esc(trim((string) ($contact['email'] ?? '')) !== '' ? (string) $contact['email'] : 'Giris yapan hesap e-postasi kullanilacak') ?></span>
                    </div>
                </div>
                <div class="checkout-form-grid">
                    <div class="checkout-field">
                        <label for="checkout-phone" class="checkout-label">Telefon</label>
                        <input
                            type="tel"
                            name="contact_phone"
                            id="checkout-phone"
                            class="checkout-input"
                            placeholder="05xx xxx xx xx"
                            value="<?= esc((string) old('contact_phone', (string) ($contact['phone'] ?? ''))) ?>"
                            autocomplete="tel"
                            required
                        >
                    </div>
                </div>
            </section>

            <section class="checkout-card">
                <h2 class="checkout-card-title"><?= esc($checkoutDeliveryTitle !== '' ? $checkoutDeliveryTitle : (string) ($delivery['title'] ?? 'Teslimat bilgileri')) ?></h2>
                <?php
                $deliveryDescription = $checkoutDeliveryDescription;
                if (! $hasCheckoutBuilderConfig && $deliveryDescription === '') {
                    $deliveryDescription = (string) ($delivery['description'] ?? '');
                }
                ?>
                <?php if ($deliveryDescription !== ''): ?>
                    <p class="checkout-card-text"><?= esc($deliveryDescription) ?></p>
                <?php endif; ?>
                <div class="checkout-form-grid two-col">
                    <div class="checkout-field">
                        <label for="delivery-label" class="checkout-label">Adres Basligi</label>
                        <input type="text" name="delivery_label" id="delivery-label" class="checkout-input" placeholder="Ev, Is, Yazlik" value="<?= esc((string) old('delivery_label')) ?>">
                    </div>
                    <div class="checkout-field">
                        <label for="delivery-name" class="checkout-label">Ad Soyad</label>
                        <input type="text" name="delivery_name" id="delivery-name" class="checkout-input" placeholder="Ad Soyad" value="<?= esc((string) old('delivery_name', (string) ($contact['name'] ?? ''))) ?>" required>
                    </div>
                    <div class="checkout-field">
                        <label for="delivery-phone" class="checkout-label">Telefon</label>
                        <input type="tel" name="delivery_phone" id="delivery-phone" class="checkout-input" placeholder="05xx xxx xx xx" value="<?= esc((string) old('delivery_phone', (string) ($contact['phone'] ?? ''))) ?>" required>
                    </div>
                    <div class="checkout-field">
                        <label for="delivery-city" class="checkout-label">Il</label>
                        <input type="text" name="delivery_city" id="delivery-city" class="checkout-input" placeholder="Il" value="<?= esc((string) old('delivery_city')) ?>" required>
                    </div>
                    <div class="checkout-field">
                        <label for="delivery-town" class="checkout-label">Ilce</label>
                        <input type="text" name="delivery_town" id="delivery-town" class="checkout-input" placeholder="Ilce" value="<?= esc((string) old('delivery_town')) ?>" required>
                    </div>
                    <div class="checkout-field full">
                        <label for="delivery-address" class="checkout-label">Adres</label>
                        <textarea name="delivery_address" id="delivery-address" class="checkout-textarea" placeholder="Mahalle, sokak, bina ve daire bilgilerinizi girin." required><?= esc((string) old('delivery_address')) ?></textarea>
                    </div>
                </div>
                <p class="checkout-inline-note">Adres bilgileri siparis olusturma entegrasyonu baglandiginda kaydedilecektir.</p>
            </section>

            <section class="checkout-card">
                <h2 class="checkout-card-title"><?= esc((string) ($billing['title'] ?? 'Fatura bilgileri')) ?></h2>
                <?php if (trim((string) ($billing['description'] ?? '')) !== ''): ?>
                    <p class="checkout-card-text"><?= esc((string) $billing['description']) ?></p>
                <?php endif; ?>
                <div class="checkout-radio-row">
                    <label class="checkout-radio-card">
                        <input type="radio" name="invoice_type" checked>
                        <div>
                            <strong>Bireysel</strong>
                            <span>Kisisel fatura bilgileri ile devam edin.</span>
                        </div>
                    </label>
                    <label class="checkout-radio-card">
                        <input type="radio" name="invoice_type">
                        <div>
                            <strong>Kurumsal</strong>
                            <span>Vergi bilgileri ile fatura duzenleyin.</span>
                        </div>
                    </label>
                </div>
                <div class="checkout-form-grid two-col">
                    <div class="checkout-field full">
                        <label for="invoice-name" class="checkout-label">Fatura Adi</label>
                        <input type="text" name="invoice_name" id="invoice-name" class="checkout-input" placeholder="Fatura unvani veya ad soyad" value="<?= esc((string) old('invoice_name')) ?>">
                    </div>
                    <div class="checkout-field">
                        <label for="invoice-tax-no" class="checkout-label">Vergi No</label>
                        <input type="text" name="invoice_tax_no" id="invoice-tax-no" class="checkout-input" placeholder="Opsiyonel" value="<?= esc((string) old('invoice_tax_no')) ?>">
                    </div>
                    <div class="checkout-field">
                        <label for="invoice-tax-office" class="checkout-label">Vergi Dairesi</label>
                        <input type="text" name="invoice_tax_office" id="invoice-tax-office" class="checkout-input" placeholder="Opsiyonel" value="<?= esc((string) old('invoice_tax_office')) ?>">
                    </div>
                </div>
            </section>

            <section class="checkout-card">
                <h2 class="checkout-card-title"><?= esc($checkoutPaymentTitle !== '' ? $checkoutPaymentTitle : (string) ($payment['title'] ?? 'Odeme yontemi')) ?></h2>
                <?php
                $paymentDescription = $checkoutPaymentDescription;
                if (! $hasCheckoutBuilderConfig && $paymentDescription === '') {
                    $paymentDescription = (string) ($payment['description'] ?? 'Sanal POS entegrasyonu sonraki sprintte baglanacak.');
                }
                ?>
                <?php if ($paymentDescription !== ''): ?>
                    <p class="checkout-card-text"><?= esc($paymentDescription) ?></p>
                <?php endif; ?>
                <div class="checkout-payment-options">
                    <label class="checkout-payment-option">
                        <input type="radio" name="payment_method" checked disabled>
                        <div>
                            <strong>Kredi / Banka Karti</strong>
                            <span>Gercek kart alanlari entegrasyon sonrasi aktif olacaktir.</span>
                        </div>
                    </label>
                    <label class="checkout-payment-option">
                        <input type="radio" name="payment_method" disabled>
                        <div>
                            <strong>Sanal POS</strong>
                            <span>Bu adimda yalnizca placeholder odeme akisi gosterilir.</span>
                        </div>
                    </label>
                </div>
                <p class="checkout-inline-note">Kart cekimi su an yapilmaz. Sanal POS entegrasyonu baglandiginda bu alan aktif olacaktir.</p>
                <?php if ($checkoutSecurePaymentNote !== ''): ?>
                    <p class="checkout-card-text"><?= esc($checkoutSecurePaymentNote) ?></p>
                <?php endif; ?>
            </section>

            <section class="checkout-card">
                <h2 class="checkout-card-title">Sepetteki Urunler</h2>
                <p class="checkout-card-text">Odeme oncesi urunlerinizi ve adetlerini son kez kontrol edin.</p>
                <div class="checkout-item-list">
                    <?php foreach ($items as $item): ?>
                        <article class="checkout-item-card">
                            <div class="checkout-item-grid">
                                <img src="<?= esc((string) ($item['image_url'] ?? '')) ?>" alt="<?= esc((string) ($item['product_name'] ?? 'Urun')) ?>" class="checkout-item-image">
                                <div>
                                    <h3 class="checkout-item-name"><?= esc((string) ($item['product_name'] ?? '')) ?></h3>
                                    <div class="checkout-item-meta">
                                        <?php if (trim((string) ($item['author'] ?? '')) !== ''): ?>
                                            <div>Yazar: <?= esc((string) $item['author']) ?></div>
                                        <?php endif; ?>
                                        <div>Adet: <?= esc((string) ($item['quantity'] ?? 1)) ?></div>
                                    </div>
                                    <div class="checkout-item-bottom">
                                        <span class="checkout-item-badge"><?= esc((string) ($item['type'] ?? 'urun')) ?></span>
                                        <span class="checkout-item-total"><?= number_format((float) ($item['line_total_current'] ?? 0), 2, ',', '.') ?> TL</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <aside class="checkout-summary-card">
            <h2 class="checkout-summary-title"><?= esc($checkoutSummaryTitle) ?></h2>
            <div class="checkout-summary-rows">
                <div class="checkout-summary-row">
                    <span>Urun adedi</span>
                    <strong><?= esc((string) ($cartView['item_count'] ?? 0)) ?></strong>
                </div>
                <div class="checkout-summary-row">
                    <span>Urunler toplami</span>
                    <strong><?= number_format((float) ($cartView['subtotal_current'] ?? 0), 2, ',', '.') ?> TL</strong>
                </div>
                <?php if ((float) ($cartView['total_savings'] ?? 0) > 0): ?>
                    <div class="checkout-summary-row savings">
                        <span>Toplam kazanciniz</span>
                        <strong><?= number_format((float) ($cartView['total_savings'] ?? 0), 2, ',', '.') ?> TL</strong>
                    </div>
                <?php endif; ?>
                <div class="checkout-summary-row">
                    <span>Kargo</span>
                    <strong>Odeme adiminda netlesecek</strong>
                </div>
            </div>

            <div class="checkout-summary-divider"></div>

            <div class="checkout-summary-total">
                <span>Genel toplam</span>
                <span><?= number_format((float) ($cartView['grand_total_current'] ?? 0), 2, ',', '.') ?> TL</span>
            </div>

            <p class="checkout-summary-note"><?= esc((string) ($cartView['shipping_info'] ?? 'Kargo ucreti odeme adiminda hesaplanacak.')) ?></p>

            <div class="checkout-actions">
                <button type="submit" class="btn btn-primary"><?= esc($checkoutCompleteButtonLabel) ?></button>
                <a href="<?= base_url('yardim/sepetim') ?>" class="btn btn-outline-primary">Sepete Don</a>
            </div>
            <p class="checkout-inline-note">Bu adim simule siparis tamamlama yapar. Gercek odeme islemi ve sanal POS baglantisi henuz devreye alinmamistir.</p>

            <?php if ($checkoutInfoBoxTitle !== '' || $checkoutInfoBoxDescription !== ''): ?>
                <div class="alert alert-light border mt-3 mb-0">
                    <?php if ($checkoutInfoBoxTitle !== ''): ?>
                        <div class="fw-semibold mb-1"><?= esc($checkoutInfoBoxTitle) ?></div>
                    <?php endif; ?>
                    <?php if ($checkoutInfoBoxDescription !== ''): ?>
                        <div class="small"><?= esc($checkoutInfoBoxDescription) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($checkoutTrustMessage !== ''): ?>
                <p class="checkout-summary-note"><?= esc($checkoutTrustMessage) ?></p>
            <?php endif; ?>

            <?php if ($checkoutHelpText !== ''): ?>
                <p class="checkout-summary-note"><?= esc($checkoutHelpText) ?></p>
            <?php endif; ?>

            <div class="checkout-notes">
                <?php foreach ($securityNotes as $note): ?>
                    <div class="checkout-note">
                        <i class="ti ti-shield-check"></i>
                        <span><?= esc((string) $note) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>
    </form>
</section>
<?= $this->endSection() ?>
