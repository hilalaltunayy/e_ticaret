<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/orders') ?>">Siparişler</a></li>
                    <li class="breadcrumb-item" aria-current="page">Sipariş Durumları</li>
                </ul>
            </div>
            <div class="col-sm-6">
                <div class="page-header-title">
                    <h2 class="mb-0">Sipariş Durumları</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6 col-xl-3"><span class="badge bg-light-secondary text-secondary w-100 py-2">Beklemede (ödeme bekliyor)</span></div>
            <div class="col-md-6 col-xl-3"><span class="badge bg-light-primary text-primary w-100 py-2">Hazırlanıyor</span></div>
            <div class="col-md-6 col-xl-3"><span class="badge bg-light-info text-info w-100 py-2">Paketlendi</span></div>
            <div class="col-md-6 col-xl-3"><span class="badge bg-light-primary text-primary w-100 py-2">Kargoya Verildi</span></div>
            <div class="col-md-6 col-xl-3"><span class="badge bg-light-success text-success w-100 py-2">Teslim Edildi</span></div>
            <div class="col-md-6 col-xl-3"><span class="badge bg-light-danger text-danger w-100 py-2">İptal Edildi</span></div>
            <div class="col-md-6 col-xl-3"><span class="badge bg-light-warning text-warning w-100 py-2">İade Sürecinde</span></div>
            <div class="col-md-6 col-xl-3"><span class="badge bg-light-dark text-dark w-100 py-2">İade Tamamlandı</span></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
