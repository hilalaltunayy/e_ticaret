<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$report = is_array($report ?? null) ? $report : [];
$summary = is_array($report['summary'] ?? null) ? $report['summary'] : [];
$recentOrders = is_array($report['recentOrders'] ?? null) ? $report['recentOrders'] : [];
$amountColumn = $report['amountColumn'] ?? null;
$money = static fn ($value): string => number_format((float) $value, 2, ',', '.') . ' TL';
$statusText = static function (string $status): string {
    return match ($status) {
        'pending' => 'Beklemede',
        'preparing' => 'Hazirlaniyor',
        'packed' => 'Paketlendi',
        'shipped' => 'Kargoya Verildi',
        'delivered' => 'Teslim Edildi',
        'cancelled' => 'Iptal Edildi',
        'return_in_progress' => 'Iade Surecinde',
        'return_done', 'returned' => 'Iade Tamamlandi',
        default => $status !== '' ? $status : '-',
    };
};
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Y&ouml;netim</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('admin/orders') ?>">Sipari&#351;ler</a></li>
          <li class="breadcrumb-item" aria-current="page">&Ouml;deme Raporlar&#305;</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0">&Ouml;deme Raporlar&#305;</h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-secondary text-secondary">Sa&#287;lay&#305;c&#305; entegrasyonu hen&uuml;z aktif de&#287;il</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam Sipari&#351;</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Bekleyen Sipari&#351;</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['pending'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Kargoda / Teslim</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['shipped'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Iade / Iptal</h6>
        <h4 class="mb-0"><?= esc((string) ((int) ($summary['returned'] ?? 0) + (int) ($summary['cancelled'] ?? 0))) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam Tutar</h6>
        <h4 class="mb-0"><?= $amountColumn === null ? 'Henuz aktif degil' : esc($money($report['totalAmount'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">&Ouml;denen Tutar</h6>
        <h4 class="mb-0"><?= $amountColumn === null ? 'Henuz aktif degil' : esc($money($report['paidAmount'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Iade Tutar Etkisi</h6>
        <h4 class="mb-0"><?= $amountColumn === null ? 'Henuz aktif degil' : esc($money($report['returnedAmount'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Sa&#287;lay&#305;c&#305; Metrikleri</h6>
        <span class="badge bg-light-secondary text-secondary">Hen&uuml;z aktif de&#287;il</span>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="mb-0">Son Sipari&#351; &Ouml;deme &Ouml;zeti</h5>
    <a href="<?= site_url('admin/orders') ?>" class="btn btn-sm btn-outline-primary">Sipari&#351;lere Git</a>
  </div>
  <div class="card-body">
    <div class="dt-responsive table-responsive">
      <table id="paymentReportsTable" class="table table-hover table-striped align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>Sipari&#351;</th>
            <th>M&uuml;&#351;teri</th>
            <th>Tutar</th>
            <th>&Ouml;deme</th>
            <th>Durum</th>
            <th>Tarih</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $order): ?>
            <?php
            $orderId = (string) ($order['id'] ?? '');
            $orderNo = (string) ($order['order_no'] ?? $orderId);
            $amount = $order['total_amount'] ?? ($order['total_price'] ?? 0);
            $status = (string) ($order['order_status'] ?? ($order['status'] ?? ''));
            ?>
            <tr>
              <td><a href="<?= site_url('admin/orders/' . $orderId) ?>"><?= esc($orderNo !== '' ? $orderNo : '-') ?></a></td>
              <td><?= esc((string) ($order['customer_name'] ?? '-')) ?></td>
              <td><?= esc($money($amount)) ?></td>
              <td><?= esc((string) ($order['payment_status'] ?? 'Henuz aktif degil')) ?></td>
              <td><?= esc($statusText($status)) ?></td>
              <td><?= esc((string) ($order['created_at'] ?? ($order['order_date'] ?? '-'))) ?></td>
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
    $('#paymentReportsTable').DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[5, 'desc']],
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        lengthMenu: '_MENU_ kayit goster',
        search: 'Ara:',
        zeroRecords: 'Raporlanacak siparis bulunamadi',
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
