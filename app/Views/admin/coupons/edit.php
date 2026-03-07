<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('admin/coupons') ?>">Kupon Yönetimi</a></li>
          <li class="breadcrumb-item" aria-current="page">Kupon Düzenle</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Kupon Düzenle') ?></h2>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Kupon Bilgileri</h5>
  </div>
  <div class="card-body">
    <form method="post" action="<?= site_url('admin/coupons/update/' . ($couponId ?? '')) ?>">
      <?= csrf_field() ?>
      <?= $this->include('admin/coupons/_form') ?>
      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= site_url('admin/coupons') ?>" class="btn btn-light-secondary">Vazgeç</a>
        <button type="submit" class="btn btn-primary">Güncelle</button>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

