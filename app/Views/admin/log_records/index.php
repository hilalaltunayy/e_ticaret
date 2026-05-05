<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$summary = is_array($summary ?? null) ? $summary : [];
$items = is_array($items ?? null) ? $items : [];
$hasAuditTable = (bool) ($hasAuditTable ?? false);
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Y&ouml;netim</a></li>
          <li class="breadcrumb-item" aria-current="page">Log Kay&#305;tlar&#305;</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0">Log Kay&#305;tlar&#305;</h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-secondary text-secondary">Son 6 ay</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam Log</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Bug&uuml;n</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['today'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Akt&ouml;r</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['actors'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Log Listesi - Son 6 ay</h5>
  </div>
  <div class="card-body">
    <?php if (! $hasAuditTable): ?>
      <div class="alert alert-light-secondary mb-3">Audit/log altyap&#305;s&#305; ba&#287;land&#305;&#287;&#305;nda kay&#305;tlar burada g&ouml;r&uuml;necek.</div>
    <?php endif; ?>
    <div class="dt-responsive table-responsive">
      <table id="logRecordsTable" class="table table-hover table-striped align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>Akt&ouml;r</th>
            <th>Aksiyon</th>
            <th>Mod&uuml;l</th>
            <th>Varl&#305;k</th>
            <th>Detay</th>
            <th>Olu&#351;turma Tarihi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <?php
              $meta = json_decode((string) ($item['meta_json'] ?? ''), true);
              $detail = trim((string) ($item['detail_text'] ?? ''));
              $actorIdentifier = is_array($meta) ? (string) ($meta['actor_identifier'] ?? '') : '';
            ?>
            <tr>
              <td><?= esc((string) ($item['actor_name'] ?? $item['actor_email'] ?? ($actorIdentifier !== '' ? $actorIdentifier : ($item['actor_role'] ?? '-')))) ?></td>
              <td><?= esc((string) ($item['action'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['entity_type'] ?? '-')) ?></td>
              <td><?= esc((string) ($item['entity_id'] ?? '-')) ?></td>
              <td><span class="text-muted"><?= esc($detail !== '' ? $detail : 'Detay bulunamadi') ?></span></td>
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
    $('#logRecordsTable').DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[5, 'desc']],
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        lengthMenu: '_MENU_ kayit goster',
        search: 'Ara:',
        zeroRecords: 'Henuz log kaydi bulunmuyor',
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
