<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$rules = is_array($rules ?? null) ? $rules : [];
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('admin/pricing') ?>">Kampanya / Fiyat Paneli</a></li>
          <li class="breadcrumb-item" aria-current="page">Fiyat Kuralları</li>
        </ul>
      </div>
      <div class="col-sm-8">
        <div class="page-header-title">
          <h2 class="mb-0">Fiyat Kuralları</h2>
        </div>
        <p class="text-muted mb-0 mt-2">Sistemde tanımlı fiyat kurallarını görüntüleyebilir, aktif ve pasif durumlarını takip edebilirsiniz.</p>
      </div>
      <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
        <a href="<?= site_url('admin/pricing/rules/create') ?>" class="btn btn-primary">Yeni Fiyat Kuralı</a>
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

<div class="card">
  <div class="card-header py-3">
    <h5 class="mb-0">Kural Listesi</h5>
  </div>
  <div class="card-body pt-3">
    <div class="dt-responsive table-responsive">
      <table id="priceRulesTable" class="table table-hover table-striped table-sm align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>Ad</th>
            <th>Tip</th>
            <th>Değer</th>
            <th>Hedef</th>
            <th>Öncelik</th>
            <th>Durum</th>
            <th>İşlemler</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rules as $rule): ?>
            <?php
            $id = (string) ($rule['id'] ?? '');
            $type = (string) ($rule['type'] ?? '');
            $value = $rule['value'] ?? null;
            $target = (string) ($rule['target'] ?? '');

            $typeLabel = match ($type) {
                'percentage' => 'Yüzde',
                'fixed' => 'Sabit',
                default => $type,
            };

            $valueLabel = '-';
            if ($value !== null && $type === 'percentage') {
                $valueLabel = number_format((float) $value, 2, ',', '.') . ' %';
            } elseif ($value !== null && $type === 'fixed') {
                $valueLabel = number_format((float) $value, 2, ',', '.') . ' TL';
            }

            $targetLabel = match ($target) {
                'global' => 'Global',
                'product' => 'Ürün',
                'category' => 'Kategori',
                default => $target,
            };
            ?>
            <tr>
              <td><?= esc((string) ($rule['name'] ?? '-')) ?></td>
              <td><?= esc($typeLabel) ?></td>
              <td><?= esc($valueLabel) ?></td>
              <td><?= esc($targetLabel) ?></td>
              <td><?= esc((string) ($rule['priority'] ?? 0)) ?></td>
              <td>
                <?php if ((int) ($rule['is_active'] ?? 0) === 1): ?>
                  <span class="badge bg-light-success text-success">Aktif</span>
                <?php else: ?>
                  <span class="badge bg-light-secondary text-secondary">Pasif</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <a href="<?= site_url('admin/pricing/rules/edit/' . $id) ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                  <form method="post" action="<?= site_url('admin/pricing/rules/toggle/' . $id) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-warning">Aktif/Pasif</button>
                  </form>
                  <form method="post" action="<?= site_url('admin/pricing/rules/delete/' . $id) ?>" onsubmit="return confirm('Fiyat kuralı silinsin mi?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($rules === []): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Henüz tanımlı fiyat kuralı bulunmuyor.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.bootstrap5.min.js') ?>"></script>
<script>
  (function () {
    if (typeof window.jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
      return;
    }

    jQuery('#priceRulesTable').DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[4, 'asc']],
      scrollX: true,
      autoWidth: false,
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
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
