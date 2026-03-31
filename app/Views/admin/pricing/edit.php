<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('admin/pricing/rules') ?>">Fiyat Kuralları</a></li>
          <li class="breadcrumb-item" aria-current="page">Fiyat Kuralı Düzenle</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Fiyat Kuralı Düzenle') ?></h2>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Fiyat Kuralı Bilgileri</h5>
  </div>
  <div class="card-body">
    <form method="post" action="<?= site_url('admin/pricing/rules/update/' . ($ruleId ?? '')) ?>">
      <?= csrf_field() ?>
      <?= $this->include('admin/pricing/_form') ?>
      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= site_url('admin/pricing/rules') ?>" class="btn btn-light-secondary">İptal / Geri dön</a>
        <button type="submit" class="btn btn-primary">Güncelle</button>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
