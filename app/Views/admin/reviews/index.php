<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$summary = is_array($summary ?? null) ? $summary : [];
$items = is_array($items ?? null) ? $items : [];
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
        <span class="badge bg-light-secondary text-secondary">Hen&uuml;z yorum bulunmuyor</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam Yorum</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Bekleyen Yorum</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['pending'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Onayl&#305; Yorum</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['approved'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Yorum Listesi</h5>
  </div>
  <div class="card-body">
    <div class="dt-responsive table-responsive">
      <table id="reviewsTable" class="table table-hover table-striped align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>&Uuml;r&uuml;n Ad&#305;</th>
            <th>Kullan&#305;c&#305;</th>
            <th>Yorum</th>
            <th>Puan</th>
            <th>Durum</th>
            <th>Tarih</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= esc((string) ($item['product_name'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['user'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['comment'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['rating'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['status'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['created_at'] ?? '-')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
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
      order: [[5, 'desc']],
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
