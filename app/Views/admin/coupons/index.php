<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$summary = is_array($summary ?? null) ? $summary : [];
$coupons = is_array($coupons ?? null) ? $coupons : [];
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('admin/marketing') ?>">Kampanya / Kupon / Fiyat</a></li>
          <li class="breadcrumb-item" aria-current="page">Kupon Yönetimi</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Kupon Yönetimi') ?></h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('admin/coupons/create') ?>" class="btn btn-primary">Kupon Ekle</a>
      </div>
    </div>
  </div>
</div>

<?php if (session('success')): ?>
  <div class="alert alert-success"><?= esc((string) session('success')) ?></div>
<?php endif; ?>
<?php if (session('error')): ?>
  <div class="alert alert-danger"><?= esc((string) session('error')) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Toplam Kupon</h6>
        <h4 class="mb-0"><?= number_format((int) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Aktif Kupon</h6>
        <h4 class="mb-0"><?= number_format((int) ($summary['active'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Süresi Yaklaşan</h6>
        <h4 class="mb-0"><?= number_format((int) ($summary['expiring_soon'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Pasif Kupon</h6>
        <h4 class="mb-0"><?= number_format((int) ($summary['passive'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Kupon Listesi</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Kod</th>
            <th>Tür</th>
            <th>İndirim</th>
            <th>Minimum Sepet</th>
            <th>Toplam Limit</th>
            <th>Kullanıcı Limit</th>
            <th>Tarih Aralığı</th>
            <th>Kısıt</th>
            <th>Durum</th>
            <th>İşlemler</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($coupons as $coupon): ?>
            <?php
            $id = (string) ($coupon['id'] ?? '');
            $couponKind = (string) ($coupon['coupon_kind'] ?? 'discount');
            $discountType = (string) ($coupon['discount_type'] ?? 'none');
            $discountValue = $coupon['discount_value'] ?? null;
            $discountLabel = 'Yok';
            if ($couponKind === 'free_shipping' && $discountType === 'none') {
                $discountLabel = 'Ücretsiz Kargo';
            } elseif ($discountType === 'percent') {
                $discountLabel = number_format((float) $discountValue, 2, ',', '.') . ' %';
            } elseif ($discountType === 'fixed') {
                $discountLabel = number_format((float) $discountValue, 2, ',', '.') . ' TL';
            }

            $range = '-';
            $startsAt = trim((string) ($coupon['starts_at'] ?? ''));
            $endsAt = trim((string) ($coupon['ends_at'] ?? ''));
            if ($startsAt !== '' || $endsAt !== '') {
                $range = ($startsAt !== '' ? $startsAt : '-') . ' / ' . ($endsAt !== '' ? $endsAt : '-');
            }
            $counts = is_array($coupon['target_counts'] ?? null) ? $coupon['target_counts'] : ['category' => 0, 'product' => 0];
            $statusBadge = (bool) ($coupon['is_active'] ?? false)
                ? '<span class="badge bg-light-success text-success">Aktif</span>'
                : '<span class="badge bg-light-secondary text-secondary">Pasif</span>';
            ?>
            <tr>
              <td><?= esc((string) ($coupon['code'] ?? '')) ?></td>
              <td><?= esc($couponKind === 'free_shipping' ? 'Ücretsiz Kargo' : 'İndirim') ?></td>
              <td><?= esc($discountLabel) ?></td>
              <td><?= esc($coupon['min_cart_amount'] !== null ? number_format((float) $coupon['min_cart_amount'], 2, ',', '.') . ' TL' : '-') ?></td>
              <td><?= esc($coupon['max_usage_total'] !== null ? (string) $coupon['max_usage_total'] : '-') ?></td>
              <td><?= esc($coupon['max_usage_per_user'] !== null ? (string) $coupon['max_usage_per_user'] : '-') ?></td>
              <td><?= esc($range) ?></td>
              <td><?= esc('Kategori: ' . (int) ($counts['category'] ?? 0) . ' / Ürün: ' . (int) ($counts['product'] ?? 0)) ?></td>
              <td><?= $statusBadge ?></td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <a href="<?= site_url('admin/coupons/edit/' . $id) ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                  <form method="post" action="<?= site_url('admin/coupons/toggle/' . $id) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-warning">Aktif/Pasif</button>
                  </form>
                  <form method="post" action="<?= site_url('admin/coupons/delete/' . $id) ?>" onsubmit="return confirm('Kupon silinsin mi?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($coupons === []): ?>
            <tr>
              <td colspan="10" class="text-center text-muted py-4">Henüz kupon yok.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

