<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Müşteri Operasyonu') ?></h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-warning text-warning">Yakında</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Özet</h5></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-4"><div class="border rounded p-3"><div class="text-muted">Toplam Müşteri</div><h4 class="mb-0">0</h4></div></div>
          <div class="col-12 col-md-4"><div class="border rounded p-3"><div class="text-muted">Bugün Yeni Kayıt</div><h4 class="mb-0">0</h4></div></div>
          <div class="col-12 col-md-4"><div class="border rounded p-3"><div class="text-muted">Sorunlu Talepler</div><h4 class="mb-0">0</h4></div></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Hızlı İşlemler</h5></div>
      <div class="card-body d-grid gap-2 d-md-flex">
        <button type="button" class="btn btn-outline-primary" disabled>Müşteri Ara</button>
        <button type="button" class="btn btn-outline-secondary" disabled>İade Talebi Yönet</button>
        <button type="button" class="btn btn-outline-dark" disabled>Mesaj Geçmişi</button>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Liste / Modül</h5></div>
      <div class="card-body">
        <p class="text-muted mb-0">Bu modül yakında aktif olacak.</p>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

