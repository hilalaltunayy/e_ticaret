<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('admin/shipping') ?>">Kargo Takip</a></li>
          <li class="breadcrumb-item" aria-current="page">Kargo Firması Ekle</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Kargo Firması Ekle') ?></h2>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Firma Bilgileri</h5>
      </div>
      <div class="card-body">
        <?php if (session('error')): ?>
          <div class="alert alert-danger"><?= esc((string) session('error')) ?></div>
        <?php endif; ?>
        <form action="<?= site_url('admin/shipping/companies/store') ?>" method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="return_url" value="<?= esc($returnUrl ?? site_url('admin/shipping')) ?>">

          <div class="mb-3">
            <label for="name" class="form-label">Firma Adı</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= old('name') ?>" required>
          </div>

          <div class="mb-3">
            <label for="integration_type" class="form-label">API Entegrasyon Tipi</label>
            <select name="integration_type" id="integration_type" class="form-select">
              <option value="Yok" <?= old('integration_type') === 'Yok' ? 'selected' : '' ?>>Yok</option>
              <option value="REST" <?= old('integration_type') === 'REST' ? 'selected' : '' ?>>REST</option>
              <option value="SOAP" <?= old('integration_type') === 'SOAP' ? 'selected' : '' ?>>SOAP</option>
              <option value="Webhook" <?= old('integration_type') === 'Webhook' ? 'selected' : '' ?>>Webhook</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="note" class="form-label">Not</label>
            <textarea name="note" id="note" rows="4" class="form-control"><?= old('note') ?></textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="<?= esc($returnUrl ?? site_url('admin/shipping')) ?>" class="btn btn-light-secondary">Vazgeç</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
