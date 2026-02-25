<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/style.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$orders = $orders ?? [];
$summary = $summary ?? [];

$getStatusBadge = static function (string $status): string {
    return match ($status) {
        'reserved' => '<span class="badge bg-light-warning text-warning">Beklemede</span>',
        'shipped' => '<span class="badge bg-light-primary text-primary">Kargoda</span>',
        'returned' => '<span class="badge bg-light-info text-info">İade</span>',
        'cancelled' => '<span class="badge bg-light-danger text-danger">İptal</span>',
        default => '<span class="badge bg-light-success text-success">Tamamlandı</span>',
    };
};
?>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
                    <li class="breadcrumb-item" aria-current="page">Siparişler</li>
                </ul>
            </div>
            <div class="col-sm-6">
                <div class="page-header-title">
                    <h2 class="mb-0">Siparişler</h2>
                </div>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0"></div>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <h5 class="mb-1">Toplam Sipariş</h5>
                <h4 class="mb-0"><?= esc((string) ($summary['total'] ?? 0)) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <h5 class="mb-1">Bekleyen</h5>
                <h4 class="mb-0"><?= esc((string) ($summary['reserved'] ?? 0)) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <h5 class="mb-1">Kargoda</h5>
                <h4 class="mb-0"><?= esc((string) ($summary['shipped'] ?? 0)) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <h5 class="mb-1">İade / İptal</h5>
                <h4 class="mb-0"><?= esc((string) ((int) ($summary['returned'] ?? 0) + (int) ($summary['cancelled'] ?? 0))) ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header">
        <h5 class="mb-0">Sipariş Listesi</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="ordersTable">
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Müşteri</th>
                        <th>Tarih</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>Ödeme</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Kayıtlı sipariş bulunamadı.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $id = (string) ($order['id'] ?? '');
                            $customer = trim((string) ($order['customer_name'] ?? ''));
                            $status = (string) ($order['status'] ?? '');
                            $amount = (float) ($order['total_amount'] ?? 0);
                            $date = (string) ($order['order_date'] ?? '-');
                            ?>
                            <tr>
                                <td class="fw-semibold">#<?= esc($id !== '' ? substr($id, 0, 8) : '-') ?></td>
                                <td>
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="mb-1"><?= esc($customer !== '' ? $customer : 'Müşteri adı yok') ?></h6>
                                            <p class="f-12 mb-0 text-muted"><?= esc((string) ($order['product_name'] ?? '-')) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($date) ?></td>
                                <td><?= esc(number_format($amount, 2, ',', '.')) ?> ₺</td>
                                <td><?= $getStatusBadge($status) ?></td>
                                <td><span class="badge bg-light-success">Kayıtlı</span></td>
                                <td class="text-end">
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item">
                                            <a href="#" class="avtar avtar-s btn-link-info btn-pc-default" title="Detay">
                                                <i class="ti ti-eye f-20"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="#" class="avtar avtar-s btn-link-success btn-pc-default" title="Güncelle">
                                                <i class="ti ti-edit f-20"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/admin/js/plugins/simple-datatables.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tableElement = document.querySelector('#ordersTable');
    if (!tableElement || !window.simpleDatatables) {
        return;
    }

    new simpleDatatables.DataTable(tableElement, {
        searchable: true,
        fixedHeight: false,
        perPage: 10
    });
});
</script>
<?= $this->endSection() ?>