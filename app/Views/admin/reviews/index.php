<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$summary = is_array($summary ?? null) ? $summary : [];
$items = is_array($items ?? null) ? $items : [];
$filters = is_array($filters ?? null) ? $filters : [];
$canDeleteReviews = (bool) ($canDeleteReviews ?? false);
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Y&ouml;netim</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('admin/products') ?>">&Uuml;r&uuml;nler</a></li>
          <li class="breadcrumb-item" aria-current="page">&Uuml;r&uuml;n Yorumlar&#305;</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0">&Uuml;r&uuml;n Yorumlar&#305;</h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge <?= ! empty($items) ? 'bg-light-primary text-primary' : 'bg-light-secondary text-secondary' ?>">
          <?= ! empty($items) ? esc((string) count($items)) . ' yorum listelendi' : 'Hen&uuml;z yorum bulunmuyor' ?>
        </span>
      </div>
    </div>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam Yorum</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Bekleyen Yorum</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['pending'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Onayl&#305; Yorum</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['approved'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Gizli / Reddedilen</h6>
        <h4 class="mb-0"><?= esc((string) (((int) ($summary['hidden'] ?? 0)) + ((int) ($summary['rejected'] ?? 0)))) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="mb-0">Yorum Listesi</h5>
    <form method="get" action="<?= site_url('admin/reviews') ?>" class="d-flex flex-wrap gap-2">
      <input
        type="text"
        name="q"
        class="form-control"
        style="min-width: 220px;"
        placeholder="Ürün, kullanıcı veya yorum ara"
        value="<?= esc((string) ($filters['q'] ?? '')) ?>"
      >
      <select name="status" class="form-select" style="min-width: 180px;">
        <option value="">Tüm durumlar</option>
        <option value="pending" <?= (($filters['status'] ?? '') === 'pending') ? 'selected' : '' ?>>Onay Bekliyor</option>
        <option value="approved" <?= (($filters['status'] ?? '') === 'approved') ? 'selected' : '' ?>>Yayında</option>
        <option value="hidden" <?= (($filters['status'] ?? '') === 'hidden') ? 'selected' : '' ?>>Gizli</option>
        <option value="rejected" <?= (($filters['status'] ?? '') === 'rejected') ? 'selected' : '' ?>>Reddedildi</option>
      </select>
      <button type="submit" class="btn btn-primary">Filtrele</button>
    </form>
  </div>
  <div class="card-body">
    <?php if ($items === []): ?>
      <div class="text-center py-4 text-muted">Henüz moderasyona düşen yorum bulunmuyor.</div>
    <?php else: ?>
    <div class="dt-responsive table-responsive">
      <table id="reviewsTable" class="table table-hover table-striped align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>ID</th>
            <th>&Uuml;r&uuml;n Ad&#305;</th>
            <th>Kullan&#305;c&#305;</th>
            <th>Puan</th>
            <th>Ba&#351;l&#305;k / Yorum</th>
            <th>Durum</th>
            <th>Tarih</th>
            <th>İşlemler</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><span class="fw-semibold">#<?= esc((string) ($item['short_id'] ?? '-')) ?></span></td>
              <td><?= esc((string) ($item['product_name'] ?? '-')) ?></td>
              <td>
                <div><?= esc((string) ($item['customer'] ?? '-')) ?></div>
                <?php if (trim((string) ($item['customer_email'] ?? '')) !== ''): ?>
                  <small class="text-muted"><?= esc((string) $item['customer_email']) ?></small>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge bg-light-warning text-warning"><?= esc((string) ($item['rating'] ?? 0)) ?>/5</span>
              </td>
              <td style="min-width: 280px;">
                <?php if (trim((string) ($item['title'] ?? '')) !== ''): ?>
                  <div class="fw-semibold mb-1"><?= esc((string) $item['title']) ?></div>
                <?php endif; ?>
                <div class="text-muted small"><?= esc(trim((string) ($item['comment'] ?? '')) !== '' ? (string) $item['comment'] : '-') ?></div>
              </td>
              <td><span class="badge <?= esc((string) ($item['status_badge_class'] ?? 'bg-light-secondary text-secondary')) ?>"><?= esc((string) ($item['status_label'] ?? '-')) ?></span></td>
              <td><?= esc((string) ($item['created_at'] ?? '-')) ?></td>
              <td>
                <div class="d-flex flex-wrap gap-2">
                  <?php if (! empty($item['can_approve'])): ?>
                    <form method="post" action="<?= site_url('admin/reviews/' . urlencode((string) ($item['id'] ?? '')) . '/approve') ?>">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-success">Onayla</button>
                    </form>
                  <?php endif; ?>

                  <?php if (! empty($item['can_hide'])): ?>
                    <form method="post" action="<?= site_url('admin/reviews/' . urlencode((string) ($item['id'] ?? '')) . '/hide') ?>">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-outline-secondary">Gizle</button>
                    </form>
                  <?php endif; ?>

                  <?php if (! empty($item['can_reject'])): ?>
                    <form method="post" action="<?= site_url('admin/reviews/' . urlencode((string) ($item['id'] ?? '')) . '/reject') ?>">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-outline-danger">Reddet</button>
                    </form>
                  <?php endif; ?>

                  <?php if ($canDeleteReviews): ?>
                    <form method="post" action="<?= site_url('admin/reviews/' . urlencode((string) ($item['id'] ?? '')) . '/delete') ?>" onsubmit="return confirm('Yorumu silmek istediğinize emin misiniz?');">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.bootstrap5.min.js') ?>"></script>
<script>
  (function () {
    $('#reviewsTable').DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[6, 'desc']],
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        lengthMenu: '_MENU_ kayit goster',
        search: 'Ara:',
        zeroRecords: 'Henuz yorum bulunmuyor',
        info: '_TOTAL_ kayittan _START_ - _END_ arasi gosteriliyor',
        infoEmpty: '0 kayittan 0 - 0 arasi gosteriliyor',
        infoFiltered: '(_MAX_ kayit icinden filtrelendi)',
        paginate: {
          first: 'Ilk',
          last: 'Son',
          next: 'Sonraki',
          previous: 'Onceki'
        }
      }
    });
  })();
</script>
<?= $this->endSection() ?>
