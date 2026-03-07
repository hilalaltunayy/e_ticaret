<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$summary = is_array($summary ?? null) ? $summary : [];
$campaigns = is_array($campaigns ?? null) ? $campaigns : [];
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('admin/marketing') ?>">Kampanya / Kupon / Fiyat</a></li>
          <li class="breadcrumb-item" aria-current="page">Kampanya Yönetimi</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Kampanya Yönetimi') ?></h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('admin/campaigns/create') ?>" class="btn btn-primary">Kampanya Ekle</a>
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

<div class="row g-2 mb-3">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 mb-0">
      <div class="card-body py-3">
        <h6 class="mb-1 text-muted">Toplam Kampanya</h6>
        <h4 class="mb-0"><?= number_format((int) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 mb-0">
      <div class="card-body py-3">
        <h6 class="mb-1 text-muted">Aktif Kampanya</h6>
        <h4 class="mb-0"><?= number_format((int) ($summary['active'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 mb-0">
      <div class="card-body py-3">
        <h6 class="mb-1 text-muted">Süresi Yaklaşan</h6>
        <h4 class="mb-0"><?= number_format((int) ($summary['expiring_soon'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 mb-0">
      <div class="card-body py-3">
        <h6 class="mb-1 text-muted">Pasif Kampanya</h6>
        <h4 class="mb-0"><?= number_format((int) ($summary['passive'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header py-3">
    <h5 class="mb-0">Kampanya Listesi</h5>
  </div>
  <div class="card-body pt-3">
    <div class="table-responsive">
      <table id="campaignsTable" class="table table-hover table-striped table-sm align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>Kampanya Adı</th>
            <th>Kampanya Türü</th>
            <th>İndirim</th>
            <th>Minimum Sepet</th>
            <th>Hedef</th>
            <th>Tarih Aralığı</th>
            <th>Öncelik</th>
            <th>Durum</th>
            <th>İşlemler</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($campaigns as $campaign): ?>
            <?php
            $id = (string) ($campaign['id'] ?? '');
            $campaignType = (string) ($campaign['campaign_type'] ?? '');
            $typeLabel = match ($campaignType) {
                'category_discount' => 'Kategori İndirimi',
                'product_discount' => 'Ürün İndirimi',
                'cart_discount' => 'Sepet İndirimi',
                default => $campaignType,
            };

            $discountType = (string) ($campaign['discount_type'] ?? 'percent');
            $discountValue = $campaign['discount_value'] ?? null;
            $discountLabel = '-';
            if ($discountValue !== null && $discountType === 'percent') {
                $discountLabel = number_format((float) $discountValue, 2, ',', '.') . ' %';
            } elseif ($discountValue !== null && $discountType === 'fixed') {
                $discountLabel = number_format((float) $discountValue, 2, ',', '.') . ' TL';
            }

            $startsAt = trim((string) ($campaign['starts_at'] ?? ''));
            $endsAt = trim((string) ($campaign['ends_at'] ?? ''));
            $range = ($startsAt !== '' ? $startsAt : '-') . ' / ' . ($endsAt !== '' ? $endsAt : '-');
            $counts = is_array($campaign['target_counts'] ?? null) ? $campaign['target_counts'] : ['category' => 0, 'product' => 0];
            ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= esc((string) ($campaign['name'] ?? '')) ?></div>
                <small class="text-muted"><?= esc((string) ($campaign['slug'] ?? '')) ?></small>
              </td>
              <td><?= esc($typeLabel) ?></td>
              <td><?= esc($discountLabel) ?></td>
              <td><?= esc($campaign['min_cart_amount'] !== null ? number_format((float) $campaign['min_cart_amount'], 2, ',', '.') . ' TL' : '-') ?></td>
              <td><?= esc('Kategori: ' . (int) ($counts['category'] ?? 0) . ' / Ürün: ' . (int) ($counts['product'] ?? 0)) ?></td>
              <td><?= esc($range) ?></td>
              <td><?= esc((string) ($campaign['priority'] ?? 0)) ?></td>
              <td>
                <?php if ((bool) ($campaign['is_active'] ?? false)): ?>
                  <span class="badge bg-light-success text-success">Aktif</span>
                <?php else: ?>
                  <span class="badge bg-light-secondary text-secondary">Pasif</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <a href="<?= site_url('admin/campaigns/edit/' . $id) ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                  <form method="post" action="<?= site_url('admin/campaigns/toggle/' . $id) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-warning">Aktif/Pasif</button>
                  </form>
                  <form method="post" action="<?= site_url('admin/campaigns/delete/' . $id) ?>" onsubmit="return confirm('Kampanya silinsin mi?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($campaigns === []): ?>
            <tr>
              <td colspan="9" class="text-center text-muted py-4">Henüz kampanya yok.</td>
            </tr>
          <?php endif; ?>
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
    $('#campaignsTable').DataTable({
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      order: [[6, 'desc']],
      scrollX: true,
      autoWidth: false,
      dom: '<"row align-items-center mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        lengthMenu: '_MENU_ kayıt göster',
        search: 'Ara:',
        zeroRecords: 'Kayıt bulunamadı',
        info: '_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor',
        infoEmpty: '0 kayıttan 0 - 0 arası gösteriliyor',
        infoFiltered: '(_MAX_ kayıt içinden filtrelendi)',
        paginate: {
          first: 'İlk',
          last: 'Son',
          next: 'Sonraki',
          previous: 'Önceki'
        }
      }
    });
  })();
</script>
<?= $this->endSection() ?>

