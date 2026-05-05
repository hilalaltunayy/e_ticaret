<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$summary = is_array($summary ?? null) ? $summary : [];
$topPages = is_array($topPages ?? null) ? $topPages : [];
$sources = is_array($sources ?? null) ? $sources : [];
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Y&ouml;netim</a></li>
          <li class="breadcrumb-item" aria-current="page">Trafik Analizi</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0">Trafik Analizi</h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-secondary text-secondary">Canl&#305; takip hen&uuml;z aktif de&#287;il</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam Ziyaret</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['totalVisits'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Bug&uuml;n Ziyaret</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['todayVisits'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">G&ouml;r&uuml;nt&uuml;lenen Sayfa</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['topPages'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Kaynak / Cihaz</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['sources'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-7">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="mb-0">En &Ccedil;ok G&ouml;r&uuml;nt&uuml;lenen Sayfalar</h5>
      </div>
      <div class="card-body">
        <div class="dt-responsive table-responsive">
          <table id="trafficPagesTable" class="table table-hover table-striped align-middle mb-0 w-100">
            <thead>
              <tr>
                <th>Sayfa</th>
                <th>Ziyaret</th>
                <th>Son G&ouml;r&uuml;nt&uuml;leme</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($topPages as $page): ?>
                <tr>
                  <td><?= esc((string) ($page['page'] ?? '-')) ?></td>
                  <td><?= esc((string) ($page['visits'] ?? 0)) ?></td>
                  <td><?= esc((string) ($page['last_seen_at'] ?? '-')) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-5">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="mb-0">Kaynak / Cihaz Bilgisi</h5>
      </div>
      <div class="card-body">
        <?php if ($sources === []): ?>
          <div class="text-center text-muted py-5">
            <div class="mb-1 fw-semibold">Hen&uuml;z trafik verisi bulunmuyor.</div>
            <div>Canl&#305; trafik altyap&#305;s&#305; ba&#287;land&#305;&#287;&#305;nda kaynak ve cihaz da&#287;&#305;l&#305;m&#305; burada g&ouml;r&uuml;necek.</div>
          </div>
        <?php endif; ?>
      </div>
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
    $('#trafficPagesTable').DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[1, 'desc']],
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        lengthMenu: '_MENU_ kayit goster',
        search: 'Ara:',
        zeroRecords: 'Henuz trafik verisi bulunmuyor',
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
