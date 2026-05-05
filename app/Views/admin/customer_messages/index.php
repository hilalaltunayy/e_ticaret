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
          <li class="breadcrumb-item"><a href="<?= site_url('admin/customers') ?>">M&uuml;&#351;teriler</a></li>
          <li class="breadcrumb-item" aria-current="page">M&uuml;&#351;teri Not ve Mesajlar&#305;</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0">M&uuml;&#351;teri Not ve Mesajlar&#305;</h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-secondary text-secondary">Hen&uuml;z mesaj veya not bulunmuyor</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam Kay&#305;t</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">A&ccedil;&#305;k</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['open'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Yan&#305;t Bekleyen</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['waiting'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Kapanan</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['closed'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h5 class="mb-0">Not ve Mesaj Listesi</h5>
      <small class="text-muted">Mesaj altyap&#305;s&#305; haz&#305;r oldu&#287;unda ger&ccedil;ek kay&#305;tlar burada listelenecek.</small>
    </div>
  </div>
  <div class="card-body">
    <div class="dt-responsive table-responsive">
      <table id="customerMessagesTable" class="table table-hover table-striped align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>M&uuml;&#351;teri</th>
            <th>Konu</th>
            <th>Tip</th>
            <th>Durum</th>
            <th>Son G&uuml;ncelleme</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= esc((string) ($item['customer'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['subject'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['type'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['status'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['updated_at'] ?? '-')) ?></td>
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
    $('#customerMessagesTable').DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[4, 'desc']],
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        lengthMenu: '_MENU_ kayit goster',
        search: 'Ara:',
        zeroRecords: 'Henuz mesaj veya not bulunmuyor',
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
