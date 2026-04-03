<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<style>
  .builder-shell-card {
    border: 1px solid rgba(17, 24, 39, .08);
    box-shadow: 0 10px 30px rgba(17, 24, 39, .04);
  }

  .builder-stat-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .6rem .9rem;
    border-radius: 999px;
    background: rgba(70, 128, 255, .08);
    color: #3158c9;
    font-weight: 600;
  }

  .builder-block-card {
    height: 100%;
    border: 1px solid rgba(17, 24, 39, .08);
    box-shadow: 0 10px 30px rgba(17, 24, 39, .05);
    transition: transform .15s ease, box-shadow .15s ease;
  }

  .builder-block-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 36px rgba(17, 24, 39, .08);
  }

  .builder-draggable-item {
    cursor: grab;
  }

  .builder-draggable-item.is-dragging {
    opacity: .55;
  }

  .builder-draggable-item.drag-over .builder-block-card {
    border-color: #4680ff;
    box-shadow: 0 0 0 3px rgba(70, 128, 255, .16);
  }

  .builder-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .75rem;
  }

  .builder-meta-box {
    padding: .75rem .9rem;
    border-radius: .85rem;
    background: #f8fafc;
    border: 1px solid rgba(17, 24, 39, .06);
  }

  .builder-meta-label {
    display: block;
    font-size: .75rem;
    color: #6b7280;
    margin-bottom: .25rem;
  }

  .builder-config-box {
    border-radius: .9rem;
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    border: 1px solid rgba(17, 24, 39, .08);
    padding: 1rem;
  }

  .builder-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .builder-status {
    min-height: 1.5rem;
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$builderDashboard = is_array($builderDashboard ?? null) ? $builderDashboard : [];
$builderBlocks = is_array($builderBlocks ?? null) ? $builderBlocks : [];
$builderBlockTypes = is_array($builderBlockTypes ?? null) ? $builderBlockTypes : [];
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yonetim</a></li>
          <li class="breadcrumb-item" aria-current="page">Dashboard Builder</li>
        </ul>
      </div>
      <div class="col-sm-12">
        <div class="page-header-title">
          <h2 class="mb-0">Dashboard Builder</h2>
        </div>
        <p class="text-muted mb-0 mt-2">Mevcut builder bloklarini duzenli, okunabilir ve takip edilebilir sekilde goruntuleyin.</p>
        <div class="d-flex flex-wrap gap-2 mt-3">
          <span class="builder-stat-badge"><?= esc((string) count($builderBlockTypes)) ?> blok tipi</span>
          <span class="builder-stat-badge"><?= esc((string) count($builderBlocks)) ?> toplam blok</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mb-4 builder-shell-card">
  <div class="card-body">
    <div class="row align-items-center g-3">
      <div class="col-lg-8">
        <h5 class="mb-2"><?= esc((string) ($builderDashboard['name'] ?? 'Dashboard bulunamadi')) ?></h5>
        <p class="text-muted mb-0"><?= esc((string) ($builderDashboard['description'] ?? 'Dashboard aciklamasi henuz tanimli degil.')) ?></p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <span class="badge bg-light-success text-success"><?= (int) ($builderDashboard['is_active'] ?? 0) === 1 ? 'Aktif dashboard' : 'Pasif dashboard' ?></span>
      </div>
    </div>
    <div id="builderReorderStatus" class="builder-status small text-muted mt-3"></div>
  </div>
</div>

<div class="row g-3 builder-edit-mode" id="builderBlocksGrid">
  <?php foreach ($builderBlocks as $block): ?>
    <div
      class="col-md-6 col-xl-4 builder-draggable-item"
      draggable="true"
      data-block-id="<?= esc((string) ($block['id'] ?? '')) ?>"
      data-position-x="<?= esc((string) ($block['position_x'] ?? 0)) ?>"
      data-position-y="<?= esc((string) ($block['position_y'] ?? 0)) ?>"
    >
      <div class="card builder-block-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
            <div>
              <h5 class="mb-1"><?= esc((string) ($block['title'] ?? $block['block_type_name'] ?? 'Blok')) ?></h5>
              <p class="text-muted small mb-0"><?= esc((string) ($block['block_type_name'] ?? 'Bilinmeyen tip')) ?></p>
            </div>
            <div class="text-end">
              <span class="badge bg-light-primary text-primary mb-2"><?= esc((string) ($block['block_type_code'] ?? '-')) ?></span>
              <br>
              <?php if ((int) ($block['is_visible'] ?? 0) === 1): ?>
                <span class="badge bg-light-success text-success">Gorunur</span>
              <?php else: ?>
                <span class="badge bg-light-secondary text-secondary">Gizli</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="builder-meta-grid mb-3">
            <div class="builder-meta-box">
              <span class="builder-meta-label">Sira</span>
              <strong data-order-label>#<?= esc((string) ($block['order_index'] ?? 0)) ?></strong>
            </div>
            <div class="builder-meta-box">
              <span class="builder-meta-label">Boyut</span>
              <strong><?= esc((string) ($block['width'] ?? 0)) ?> x <?= esc((string) ($block['height'] ?? 0)) ?></strong>
            </div>
            <div class="builder-meta-box">
              <span class="builder-meta-label">Pozisyon X</span>
              <strong><?= esc((string) ($block['position_x'] ?? 0)) ?></strong>
            </div>
            <div class="builder-meta-box">
              <span class="builder-meta-label">Pozisyon Y</span>
              <strong><?= esc((string) ($block['position_y'] ?? 0)) ?></strong>
            </div>
          </div>

          <div class="builder-config-box mb-3">
            <span class="builder-meta-label">Config ozeti</span>
            <div class="small text-muted mb-0"><?= esc((string) ($block['config_summary'] ?? 'Konfigürasyon bilgisi yok.')) ?></div>
          </div>

          <div class="builder-actions">
            <button type="button" class="btn btn-sm btn-outline-primary" disabled>Düzenle</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Tasima yakinda</button>
            <button type="button" class="btn btn-sm btn-outline-danger" disabled>Sil yakinda</button>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if ($builderBlocks === []): ?>
    <div class="col-12">
      <div class="card builder-shell-card">
        <div class="card-body text-center py-5">
          <h5 class="mb-2">Henuz blok eklenmedi</h5>
          <p class="text-muted mb-0">Bu dashboard icin kayitli block instance bulunmuyor.</p>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<?= view('admin/dashboard_builder/_reorder_script', [
  'reorderGridId' => 'builderBlocksGrid',
  'reorderStatusId' => 'builderReorderStatus',
  'reorderToggleId' => '',
  'reorderEnabledByDefault' => true,
]) ?>
<?= $this->endSection() ?>
