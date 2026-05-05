<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$pageData = is_array($pageData ?? null) ? $pageData : [];
$definitions = is_array($pageData['definitions'] ?? null) ? $pageData['definitions'] : [];
$preferences = is_array($pageData['preferences'] ?? null) ? $pageData['preferences'] : [];
$summary = is_array($pageData['summary'] ?? null) ? $pageData['summary'] : [];
$history = is_array($pageData['history'] ?? null) ? $pageData['history'] : [];
$setupWarning = (string) ($pageData['setupWarning'] ?? '');
$userRole = (string) ($pageData['userRole'] ?? '');
$oldPreferences = old('preferences');
$oldPreferences = is_array($oldPreferences) ? $oldPreferences : [];
$preferencesByCategory = [];
foreach ($preferences as $preference) {
    $categoryKey = (string) ($preference['category_key'] ?? 'general');
    if (! isset($preferencesByCategory[$categoryKey])) {
        $preferencesByCategory[$categoryKey] = [
            'label' => (string) ($preference['category_label'] ?? 'Genel'),
            'items' => [],
        ];
    }
    $preferencesByCategory[$categoryKey]['items'][] = $preference;
}
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Operasyon Bildirimleri') ?></h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-primary text-primary"><?= esc(ucfirst($userRole !== '' ? $userRole : 'kullanici')) ?></span>
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
<?php if ($setupWarning !== ''): ?>
  <div class="alert alert-warning"><?= esc($setupWarning) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Aktif Tercih</h6>
        <h4 class="mb-0"><?= (int) ($summary['enabled_count'] ?? 0) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Esikli Kural</h6>
        <h4 class="mb-0"><?= (int) ($summary['threshold_count'] ?? 0) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Aktif Uyari</h6>
        <h4 class="mb-0"><?= (int) ($summary['active_alert_count'] ?? 0) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Kritik Kayit</h6>
        <h4 class="mb-0"><?= (int) ($summary['critical_alert_count'] ?? 0) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Bildirim Tercihleri</h5>
      </div>
      <div class="card-body">
        <form method="post" action="<?= site_url('admin/notifications-management') ?>">
          <?= csrf_field() ?>
          <?php foreach ($preferencesByCategory as $category): ?>
            <div class="border rounded p-3 mb-3">
              <h6 class="mb-3"><?= esc($category['label']) ?></h6>
              <?php foreach ($category['items'] as $item): ?>
                <?php
                $itemKey = (string) $item['key'];
                $oldRow = is_array($oldPreferences[$itemKey] ?? null) ? $oldPreferences[$itemKey] : [];
                $enabledOld = (string) ($oldRow['enabled'] ?? ($item['is_enabled'] ? '1' : '0'));
                $thresholdOld = (string) ($oldRow['threshold'] ?? (string) ($item['threshold_value'] ?? ''));
                ?>
                <div class="row g-3 align-items-center mb-3">
                  <div class="col-12 col-lg-6">
                    <div class="form-check form-switch">
                      <input type="hidden" name="preferences[<?= esc($itemKey) ?>][enabled]" value="0">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        id="pref_<?= esc($itemKey) ?>"
                        name="preferences[<?= esc($itemKey) ?>][enabled]"
                        value="1"
                        <?= (string) $enabledOld === '1' ? 'checked' : '' ?>
                      >
                      <label class="form-check-label fw-semibold" for="pref_<?= esc($itemKey) ?>"><?= esc((string) $item['label']) ?></label>
                    </div>
                    <div class="small text-muted mt-1"><?= esc((string) $item['description']) ?></div>
                  </div>
                  <div class="col-12 col-lg-6">
                    <?php if (! empty($item['has_threshold'])): ?>
                      <label class="form-label mb-1" for="threshold_<?= esc($itemKey) ?>"><?= esc((string) $item['threshold_label']) ?></label>
                      <input
                        type="number"
                        step="<?= (string) ($item['threshold_type'] ?? '') === 'decimal' ? '0.01' : '1' ?>"
                        min="0"
                        class="form-control"
                        id="threshold_<?= esc($itemKey) ?>"
                        name="preferences[<?= esc($itemKey) ?>][threshold]"
                        value="<?= esc((string) $thresholdOld) ?>"
                      >
                    <?php else: ?>
                      <div class="small text-muted pt-4">Bu bildirim icin ek esik degeri gerekmez.</div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>

          <div class="text-end">
            <button type="submit" class="btn btn-primary">Tercihleri Kaydet</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="mb-0">Aktif Bildirim Onizlemesi</h5>
      </div>
      <div class="card-body">
        <?php if ($history === []): ?>
          <div class="alert alert-light border mb-0">Aktif veya son bildirim kaydi bulunmuyor.</div>
        <?php else: ?>
          <?php foreach ($history as $row): ?>
            <?php
            $severity = (string) ($row['severity'] ?? 'info');
            $badgeClass = match ($severity) {
                'critical' => 'bg-light-danger text-danger',
                'warning' => 'bg-light-warning text-warning',
                default => 'bg-light-primary text-primary',
            };
            ?>
            <div class="border rounded p-3 mb-3">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <div class="fw-semibold"><?= esc((string) ($row['title'] ?? 'Bildirim')) ?></div>
                  <div class="small text-muted mt-1"><?= esc((string) ($row['message'] ?? '')) ?></div>
                </div>
                <span class="badge <?= esc($badgeClass) ?>"><?= esc($severity) ?></span>
              </div>
              <div class="small text-muted mt-2"><?= esc((string) ($row['updated_at'] ?? $row['created_at'] ?? '')) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Modul Ozeti</h5>
      </div>
      <div class="card-body">
        <div class="small text-muted mb-2">Bu alan yalnizca ic operasyon bildirimlerini yonetir.</div>
        <ul class="list-unstyled mb-0">
          <li class="mb-2">Stok, siparis, iade ve sistem uyarilari kullanici bazli kaydedilir.</li>
          <li class="mb-2">Gercek e-posta veya push gonderimi bu sprintte kapsam disidir.</li>
          <li class="mb-0">Gecmis listesi mevcut veriyle uretilen ic kayitlari gosterir.</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
