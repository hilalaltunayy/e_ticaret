<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$report = is_array($customersReport ?? null) ? $customersReport : [];
$summary = is_array($report['summary'] ?? null) ? $report['summary'] : [];
$customers = is_array($report['customers'] ?? null) ? $report['customers'] : [];
$filters = is_array($filters ?? null) ? $filters : [];
$hasStatus = (bool) ($report['hasStatus'] ?? false);
$hasRole = (bool) ($report['hasRole'] ?? false);
$hasCreatedAt = (bool) ($report['hasCreatedAt'] ?? false);
$lastLoginField = $report['lastLoginField'] ?? null;
$statusText = static function (string $status): string {
    return match (strtolower($status)) {
        'active', 'enabled' => 'Aktif',
        'suspended' => 'Askida',
        'inactive', 'disabled' => 'Pasif',
        'banned' => 'Engelli',
        'pending_verification' => 'Dogrulama Bekliyor',
        default => $status !== '' ? $status : '-',
    };
};
$statusBadge = static function (string $status): string {
    return match (strtolower($status)) {
        'active', 'enabled' => 'bg-light-success text-success',
        'suspended', 'inactive', 'disabled', 'banned' => 'bg-light-danger text-danger',
        'pending_verification' => 'bg-light-warning text-warning',
        default => 'bg-light-secondary text-secondary',
    };
};
$displayDate = static fn ($value): string => trim((string) $value) !== '' ? (string) $value : '-';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Y&ouml;netim</a></li>
          <li class="breadcrumb-item" aria-current="page">M&uuml;&#351;teriler</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0">M&uuml;&#351;teriler</h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-primary text-primary">Kullan&#305;c&#305; verisi</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam M&uuml;&#351;teri</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Aktif M&uuml;&#351;teri</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['active'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Pasif / Ask&#305;da</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['inactive'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Bug&uuml;n Yeni</h6>
        <h4 class="mb-0"><?= $hasCreatedAt ? esc((string) ($summary['new'] ?? 0)) : '<span class="text-muted f-16">Hen&uuml;z yok</span>' ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="mb-0">M&uuml;&#351;teri Listesi</h5>
    <span class="text-muted small">En fazla 200 kay&#305;t g&ouml;sterilir</span>
  </div>
  <div class="card-body">
    <div class="dt-responsive table-responsive">
      <table id="customersTable" class="table table-hover table-striped align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>Ad</th>
            <th>E-posta</th>
            <?php if ($hasStatus): ?><th>Durum</th><?php endif; ?>
            <?php if ($hasRole): ?><th>Rol</th><?php endif; ?>
            <?php if ($hasCreatedAt): ?><th>Kay&#305;t Tarihi</th><?php endif; ?>
            <?php if ($lastLoginField !== null): ?><th>Son Giri&#351;</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($customers as $customer): ?>
            <?php
            $name = trim((string) ($customer['name'] ?? $customer['username'] ?? ''));
            $status = trim((string) ($customer['status'] ?? ''));
            ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= esc($name !== '' ? $name : '-') ?></div>
                <?php if (! empty($customer['username']) && $name !== (string) $customer['username']): ?>
                  <small class="text-muted"><?= esc((string) $customer['username']) ?></small>
                <?php endif; ?>
              </td>
              <td><?= esc((string) ($customer['email'] ?? '-')) ?></td>
              <?php if ($hasStatus): ?>
                <td><span class="badge <?= esc($statusBadge($status)) ?>"><?= esc($statusText($status)) ?></span></td>
              <?php endif; ?>
              <?php if ($hasRole): ?><td><?= esc((string) ($customer['role'] ?? '-')) ?></td><?php endif; ?>
              <?php if ($hasCreatedAt): ?><td><?= esc($displayDate($customer['created_at'] ?? '')) ?></td><?php endif; ?>
              <?php if ($lastLoginField !== null): ?><td><?= esc($displayDate($customer[$lastLoginField] ?? 'Henuz yok')) ?></td><?php endif; ?>
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
    $('#customersTable').DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[0, 'asc']],
      search: {
        search: <?= json_encode((string) ($filters['q'] ?? ''), JSON_UNESCAPED_SLASHES) ?>
      },
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        lengthMenu: '_MENU_ kayit goster',
        search: 'Ara:',
        zeroRecords: 'Musteri bulunamadi',
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
