<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$returnsReport = is_array($returnsReport ?? null) ? $returnsReport : [];
$summary = is_array($returnsReport['summary'] ?? null) ? $returnsReport['summary'] : [];
$orders = is_array($returnsReport['orders'] ?? null) ? $returnsReport['orders'] : [];
$money = static fn ($value): string => number_format((float) $value, 2, ',', '.') . ' TL';
$statusText = static function (string $status): string {
    return match ($status) {
        'return_in_progress' => 'Iade Surecinde',
        'return_done', 'returned' => 'Iade Tamamlandi',
        'cancelled' => 'Iptal Edildi',
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
          <li class="breadcrumb-item" aria-current="page">&#304;adeler</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0">&#304;adeler</h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-info text-info">&#304;ade mod&uuml;l&uuml;</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam &#304;ade Kayd&#305;</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">&#304;ade S&uuml;recinde</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['inProgress'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Tamamlanan &#304;ade</h6>
        <h4 class="mb-0"><?= esc((string) ($summary['completed'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">&#304;ade Tutar Etkisi</h6>
        <h4 class="mb-0"><?= esc($money($summary['amount'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h5 class="mb-0">&#304;ade &#304;li&#351;kili Sipari&#351;ler</h5>
      <small class="text-muted">Mevcut sipari&#351; durum verileri kullan&#305;l&#305;r; ger&ccedil;ek iade i&#351;leme entegrasyonu hen&uuml;z aktif de&#287;il.</small>
    </div>
    <a href="<?= site_url('admin/orders') ?>" class="btn btn-sm btn-outline-primary">Sipari&#351; Listesi</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Sipari&#351;</th>
            <th>M&uuml;&#351;teri</th>
            <th>Tutar</th>
            <th>Sipari&#351; Durumu</th>
            <th>Kargo Durumu</th>
            <th>&#304;ade Tarihi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($orders === []): ?>
            <tr>
              <td colspan="6" class="text-center py-5">
                <div class="mb-1 fw-semibold">&#304;ade kayd&#305; bulunamad&#305;.</div>
                <div class="text-muted">&#304;ade mod&uuml;l&uuml;, sipari&#351; durumlar&#305; iade s&uuml;recine girdi&#287;inde burada listeleyecek.</div>
              </td>
            </tr>
          <?php endif; ?>
          <?php foreach ($orders as $order): ?>
            <?php
            $orderId = (string) ($order['id'] ?? '');
            $orderNo = (string) ($order['order_no'] ?? $orderId);
            $amount = $order['total_amount'] ?? ($order['total_price'] ?? 0);
            $orderStatus = (string) ($order['order_status'] ?? ($order['status'] ?? ''));
            $shippingStatus = (string) ($order['shipping_status'] ?? '');
            $returnDate = (string) ($order['returned_at'] ?? ($order['return_completed_at'] ?? ($order['return_started_at'] ?? '')));
            ?>
            <tr>
              <td><a href="<?= site_url('admin/orders/' . $orderId) ?>"><?= esc($orderNo !== '' ? $orderNo : '-') ?></a></td>
              <td><?= esc((string) ($order['customer_name'] ?? '-')) ?></td>
              <td><?= esc($money($amount)) ?></td>
              <td><span class="badge bg-light-warning text-warning"><?= esc($statusText($orderStatus)) ?></span></td>
              <td><?= esc($statusText($shippingStatus)) ?></td>
              <td><?= esc($returnDate !== '' ? $returnDate : '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
