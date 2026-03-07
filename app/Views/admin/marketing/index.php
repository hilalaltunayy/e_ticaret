<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$summary = $summary ?? new \App\DTO\Marketing\MarketingPageSummaryDTO();
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item" aria-current="page">Kampanya / Kupon / Fiyat</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Kampanya / Kupon / Fiyat') ?></h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-primary text-primary">Admin Modülü</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-4" id="coupon-module">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Kupon</h5>
        <span class="badge bg-light-success text-success">Hazır</span>
      </div>
      <div class="card-body d-flex flex-column">
        <p class="text-muted mb-2">Kupon yönetim altyapısı bu modülde kullanılabilir.</p>
        <h4 class="mb-4"><?= number_format((int) $summary->couponCount) ?></h4>
        <div class="mt-auto">
          <a href="<?= site_url('admin/coupons') ?>" class="btn btn-primary">Kuponları Yönet</a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Kampanya</h5>
        <span class="badge bg-light-success text-success">Hazır</span>
      </div>
      <div class="card-body d-flex flex-column">
        <p class="text-muted mb-2">Kampanya motoru ve yönetim ekranı aktif.</p>
        <h4 class="mb-4"><?= number_format((int) $summary->campaignCount) ?></h4>
        <div class="mt-auto">
          <a href="<?= site_url('admin/campaigns') ?>" class="btn btn-primary">Kampanyaları Yönet</a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Fiyat Kuralları</h5>
        <span class="badge bg-light-warning text-warning">Yakında</span>
      </div>
      <div class="card-body d-flex flex-column">
        <p class="text-muted mb-2">Dinamik fiyat kural motoru sonraki adımda eklenecek.</p>
        <h4 class="mb-4"><?= number_format((int) $summary->pricingRuleCount) ?></h4>
        <div class="mt-auto">
          <button type="button" class="btn btn-light-secondary" disabled>Yakında</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

