<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$order = $order ?? [];
$session = $session ?? [];
$verifyUrl = trim((string) ($verifyUrl ?? ''));

$orderNo = trim((string) ($order['order_no'] ?? ''));
if ($orderNo === '') {
    $orderNo = '#' . strtoupper(substr(str_replace('-', '', (string) ($order['id'] ?? '')), 0, 8));
}

$customerName = trim((string) ($order['customer_name'] ?? $order['user_name'] ?? '-'));
$shippingCompany = trim((string) ($order['shipping_company'] ?? ''));
$trackingNumber = trim((string) ($order['tracking_number'] ?? ''));
$packageCode = trim((string) ($session['package_code'] ?? '-'));
?>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Y&ouml;netim</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/orders') ?>">Sipari&#351;ler</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/orders/' . (string) ($order['id'] ?? '')) ?>"><?= esc($orderNo) ?></a></li>
                    <li class="breadcrumb-item" aria-current="page">Paket Etiketi</li>
                </ul>
            </div>
            <div class="col-sm-6">
                <div class="page-header-title">
                    <h2 class="mb-0">Paket Etiketi</h2>
                </div>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= site_url('admin/orders/' . (string) ($order['id'] ?? '')) ?>" class="btn btn-outline-secondary btn-sm">Sipari&#351;e D&ouml;n</a>
            </div>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Etiket Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted d-block">Sipari&#351; No</label>
                        <strong><?= esc($orderNo) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted d-block">M&uuml;&#351;teri</label>
                        <strong><?= esc($customerName !== '' ? $customerName : '-') ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted d-block">Kargo Firmas&#305;</label>
                        <strong><?= esc($shippingCompany !== '' ? $shippingCompany : '-') ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted d-block">Takip No</label>
                        <strong><?= esc($trackingNumber !== '' ? $trackingNumber : '-') ?></strong>
                    </div>
                    <div class="col-md-12">
                        <label class="text-muted d-block">Paket Kodu</label>
                        <span class="badge bg-light-primary text-primary fs-6"><?= esc($packageCode) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Paket Do&#287;rulama Linki</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Bu s&uuml;r&uuml;mde offline yap&#305; nedeniyle QR yerine do&#287;rulama linki kullan&#305;l&#305;r.</p>
                <a href="<?= esc($verifyUrl) ?>" class="btn btn-primary btn-sm w-100 mb-2">Do&#287;rulama Sayfas&#305;n&#305; A&ccedil;</a>
                <div class="small text-muted" style="word-break: break-all;"><?= esc($verifyUrl) ?></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
