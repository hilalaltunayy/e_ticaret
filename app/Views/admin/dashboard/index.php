<?php
/** @var \App\DTO\Admin\DashboardDTO $dto */

$builderDashboard = is_array($builderDashboard ?? null) ? $builderDashboard : [];
$builderBlocks = is_array($builderBlocks ?? null) ? $builderBlocks : [];
$builderBlockTypes = is_array($builderBlockTypes ?? null) ? $builderBlockTypes : [];
$builderBlockErrors = session('dashboard_block_errors') ?? [];
$builderBlockEditErrors = session('dashboard_block_edit_errors') ?? [];
$builderBlockDeleteErrors = session('dashboard_block_delete_errors') ?? [];
$builderBlockModal = (string) (session('dashboard_block_modal') ?? '');
$builderBlockEditId = (string) (session('dashboard_block_edit_id') ?? '');
$builderBlockEditOld = is_array(session('dashboard_block_edit_old') ?? null) ? session('dashboard_block_edit_old') : [];
$builderBlockTypeCount = count($builderBlockTypes);
$builderBlockCount = count($builderBlocks);
$selectedBlockTypeId = (string) old('block_type_id', '');
$selectedBlockTypeCode = '';
$builderCharts = [];

foreach ($builderBlockTypes as $builderBlockType) {
    if ((string) ($builderBlockType['id'] ?? '') === $selectedBlockTypeId) {
        $selectedBlockTypeCode = (string) ($builderBlockType['code'] ?? '');
        break;
    }
}

foreach ($builderBlocks as $builderBlock) {
    $render = is_array($builderBlock['render'] ?? null) ? $builderBlock['render'] : [];
    if (! empty($render['chartId']) && is_array($render['chartOptions'] ?? null)) {
        $builderCharts[] = [
            'id' => (string) $render['chartId'],
            'options' => $render['chartOptions'],
        ];
    }
}
?>
<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<style>
  .builder-chip-grid {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .builder-color-chip {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    border: 2px solid rgba(17, 24, 39, .08);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .4);
    transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease;
  }

  .builder-color-chip.is-selected {
    border-color: #111827;
    box-shadow: 0 0 0 3px rgba(70, 128, 255, .18);
    transform: scale(1.06);
  }

  .builder-category-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .75rem 1rem;
    border: 1px solid rgba(17, 24, 39, .08);
    border-radius: .75rem;
    background: rgba(248, 250, 252, .7);
  }

  .builder-category-row + .builder-category-row {
    margin-top: .75rem;
  }

  .builder-detail-trigger {
    width: 100%;
    text-align: left;
    border: 1px solid rgba(17, 24, 39, .08);
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
  }

  .builder-detail-trigger:hover {
    border-color: rgba(70, 128, 255, .35);
    box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
    transform: translateY(-1px);
  }

  .dashboard-builder-grid .builder-draggable-item {
    cursor: default;
  }

  .dashboard-builder-grid.builder-edit-mode .builder-draggable-item {
    cursor: grab;
  }

  .dashboard-builder-grid.builder-edit-mode .builder-draggable-item.is-dragging {
    opacity: .55;
  }

  .dashboard-builder-grid.builder-edit-mode .builder-draggable-item.drag-over > .card {
    box-shadow: 0 0 0 3px rgba(70, 128, 255, .18);
    border-color: rgba(70, 128, 255, .45);
  }

  .dashboard-builder-status {
    min-height: 1.5rem;
  }

  .builder-modal-dialog {
    max-height: calc(100vh - 2rem);
  }

  .builder-modal-content {
    max-height: calc(100vh - 2rem);
  }

  .builder-modal-form {
    display: flex;
    flex-direction: column;
    min-height: 0;
    height: 100%;
  }

  .builder-modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
  }

  .builder-modal-footer {
    flex-shrink: 0;
    background: #fff;
    border-top: 1px solid var(--bs-border-color);
  }

  @media (max-width: 575.98px) {
    .builder-modal-dialog,
    .builder-modal-content {
      max-height: calc(100vh - 1rem);
    }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="mb-0">Analytics & Finance Dashboard</h2>
    <span class="text-muted small">Able Pro demo blend</span>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="card shadow-sm border-primary border-opacity-25">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <h5 class="mb-1">Dashboard Builder</h5>
            <p class="text-muted small mb-0">
              Mevcut dashboard akışını bozmadan, yeni builder altyapısındaki blokları burada önizleyebilirsiniz.
            </p>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light-primary text-primary"><?= esc($builderBlockTypeCount) ?> blok tipi</span>
            <button
              type="button"
              class="btn btn-outline-primary btn-sm"
              id="dashboardBuilderEditToggle"
              aria-pressed="false"
            >
              Düzenleme Modu
            </button>
            <button
              type="button"
              class="btn btn-primary btn-sm"
              data-bs-toggle="modal"
              data-bs-target="#builderBlockModal"
            >
              Yeni Kart Ekle
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="badge bg-light-success text-success">
              <?= !empty($builderDashboard) ? 'Aktif Dashboard Hazır' : 'Varsayılan Dashboard Bekleniyor' ?>
            </span>
            <span class="text-muted small">
              <?= !empty($builderDashboard['name']) ? esc($builderDashboard['name']) : 'Dashboard builder kaydı henüz yüklenmedi.' ?>
            </span>
            <span class="text-muted small">Toplam blok: <?= esc($builderBlockCount) ?></span>
            <span class="badge bg-light-warning text-warning" id="dashboardBuilderEditBadge">Düzenleme modu kapalı</span>
          </div>
          <div id="dashboardBuilderReorderStatus" class="dashboard-builder-status small text-muted mb-3"></div>

          <?php if (empty($builderBlocks)): ?>
            <div class="border rounded-3 bg-light-subtle p-4 text-center">
              <div class="mb-2">
                <span class="badge bg-light-secondary text-secondary">Boş Durum</span>
              </div>
              <h6 class="mb-2">Henüz kart eklenmedi</h6>
              <p class="text-muted mb-0">
                İlk builder kartını eklediğinizde gerçek istatistik kartları ve ApexCharts grafik blokları burada listelenecek.
              </p>
            </div>
          <?php else: ?>
            <div class="row g-3 dashboard-builder-grid" id="dashboardBuilderBlocksGrid">
              <?php foreach ($builderBlocks as $block): ?>
                <?php
                $render = is_array($block['render'] ?? null) ? $block['render'] : [];
                $blockTypeCode = trim((string) ($block['block_type_code'] ?? ''));
                $blockTypeName = trim((string) ($block['block_type_name'] ?? $blockTypeCode));
                $width = max(3, min(12, (int) ($block['width'] ?? 4)));
                $colClass = 'col-12';
                if ($width <= 4) {
                    $colClass .= ' col-md-6 col-xxl-3';
                } elseif ($width <= 8) {
                    $colClass .= ' col-xxl-6';
                }

                $isVisible = (int) ($block['is_visible'] ?? 0) === 1;
                ?>
                <div
                  class="<?= esc($colClass) ?> builder-draggable-item"
                  draggable="false"
                  data-block-id="<?= esc((string) ($block['id'] ?? '')) ?>"
                  data-position-x="<?= esc((string) ($block['position_x'] ?? 0)) ?>"
                  data-position-y="<?= esc((string) ($block['position_y'] ?? 0)) ?>"
                >
                  <?php if (($render['kind'] ?? '') === 'stat_card'): ?>
                    <?php $theme = is_array($render['theme'] ?? null) ? $render['theme'] : []; ?>
                    <div class="card shadow-sm h-100" style="border-top: 3px solid <?= esc((string) ($theme['color'] ?? '#4680FF')) ?>;">
                      <div class="card-body">
                        <div class="d-flex align-items-center">
                          <div class="flex-shrink-0">
                            <div class="avtar avtar-s <?= esc((string) ($theme['avatarClass'] ?? 'bg-light-primary')) ?>">
                              <i class="<?= esc((string) ($theme['iconClass'] ?? 'ti ti-layout-grid')) ?> <?= esc((string) ($theme['textClass'] ?? 'text-primary')) ?> f-20"></i>
                            </div>
                          </div>
                          <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0"><?= esc((string) ($render['title'] ?? $block['title'] ?? 'İstatistik')) ?></h6>
                            <div class="small text-muted mt-1"><?= esc($blockTypeName) ?></div>
                          </div>
                          <div class="flex-shrink-0 ms-3 text-end">
                            <span class="badge <?= $isVisible ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' ?>">
                              <?= $isVisible ? 'Görünür' : 'Gizli' ?>
                            </span>
                          </div>
                        </div>

                        <div class="bg-body p-3 mt-3 rounded">
                          <div class="row align-items-center g-2">
                            <div class="col-7">
                              <?php if (!empty($render['chartId']) && !empty($render['chartOptions'])): ?>
                                <div id="<?= esc((string) $render['chartId']) ?>"></div>
                              <?php else: ?>
                                <div class="text-muted small">Mini grafik verisi hazır değil.</div>
                              <?php endif; ?>
                            </div>
                            <div class="col-5">
                              <p class="text-muted mb-1"><?= esc((string) ($render['valueLabel'] ?? 'Metrik')) ?></p>
                              <h5 class="mb-1"><?= esc((string) ($render['value'] ?? '--')) ?></h5>
                              <p class="<?= esc((string) ($render['trendClass'] ?? 'text-muted')) ?> mb-0">
                                <?= esc((string) ($render['trendText'] ?? '')) ?>
                              </p>
                            </div>
                          </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 gap-2">
                          <div class="small text-muted"><?= esc((string) ($render['subtitle'] ?? '')) ?></div>
                          <div class="d-flex flex-wrap gap-2">
                            <button
                              type="button"
                              class="btn btn-sm btn-outline-primary"
                              data-builder-action="edit"
                              data-fetch-url="<?= site_url('admin/dashboard/blocks/fetch/' . (string) ($block['id'] ?? '')) ?>"
                              data-update-url="<?= site_url('admin/dashboard/blocks/update/' . (string) ($block['id'] ?? '')) ?>"
                              data-block-id="<?= esc((string) ($block['id'] ?? '')) ?>"
                              data-category-labels="[]"
                            >Ayarlar</button>
                            <form method="post" action="<?= site_url('admin/dashboard/blocks/delete/' . (string) ($block['id'] ?? '')) ?>" onsubmit="return confirm('Bu dashboard karti silinsin mi?');">
                              <?= csrf_field() ?>
                              <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Tasima akisi bir sonraki sprintte acilacak." disabled>Taşı</button>
                          </div>
                        </div>

                        <?php if (!empty($render['message'])): ?>
                          <div class="alert alert-light border mt-3 mb-0"><?= esc((string) $render['message']) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php elseif (($render['kind'] ?? '') === 'chart'): ?>
                    <?php $chartAccent = (string) (($render['chartOptions']['colors'][0] ?? '#4680FF')); ?>
                    <div class="card shadow-sm h-100" style="border-top: 3px solid <?= esc($chartAccent) ?>;">
                      <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                          <div>
                            <h5 class="mb-0"><?= esc((string) ($render['title'] ?? $block['title'] ?? 'Grafik')) ?></h5>
                            <p class="text-muted small mb-0 mt-1"><?= esc($blockTypeName) ?></p>
                          </div>
                          <div class="d-flex align-items-center gap-2">
                            <span class="badge <?= $isVisible ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' ?>">
                              <?= $isVisible ? 'Görünür' : 'Gizli' ?>
                            </span>
                            <div class="d-flex gap-1">
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-builder-action="edit"
                                data-fetch-url="<?= site_url('admin/dashboard/blocks/fetch/' . (string) ($block['id'] ?? '')) ?>"
                                data-update-url="<?= site_url('admin/dashboard/blocks/update/' . (string) ($block['id'] ?? '')) ?>"
                                data-block-id="<?= esc((string) ($block['id'] ?? '')) ?>"
                                data-category-labels="<?= esc(json_encode(array_values(array_filter(array_map(static fn($summaryItem) => (string) ($summaryItem['label'] ?? ''), is_array($render['summary'] ?? null) ? $render['summary'] : []))), JSON_UNESCAPED_UNICODE)) ?>"
                              >Ayarlar</button>
                              <form method="post" action="<?= site_url('admin/dashboard/blocks/delete/' . (string) ($block['id'] ?? '')) ?>" onsubmit="return confirm('Bu dashboard karti silinsin mi?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                              </form>
                              <button type="button" class="btn btn-sm btn-outline-secondary" title="Tasima akisi bir sonraki sprintte acilacak." disabled>Taşı</button>
                            </div>
                          </div>
                        </div>

                        <p class="text-muted mt-3 mb-2"><?= esc((string) ($render['subtitle'] ?? '')) ?></p>

                        <?php if (!empty($render['chartId']) && !empty($render['chartOptions'])): ?>
                          <div id="<?= esc((string) $render['chartId']) ?>"></div>
                        <?php else: ?>
                          <div class="border rounded-3 bg-light-subtle p-4 text-center text-muted">
                            <?= esc((string) ($render['message'] ?? 'Grafik verisi hazır değil.')) ?>
                          </div>
                        <?php endif; ?>

                        <?php if (!empty($render['summary']) && is_array($render['summary'])): ?>
                          <div class="row g-3 mt-3">
                            <?php foreach ($render['summary'] as $summaryItem): ?>
                              <?php $detailSource = (string) ($render['detailSource'] ?? ''); ?>
                              <?php $detailEnabled = in_array($detailSource, ['sales_by_category', 'print_vs_digital_sales'], true); ?>
                              <div class="col-sm-6">
                                <?php if ($detailEnabled): ?>
                                  <button
                                    type="button"
                                    class="btn bg-body p-3 rounded builder-detail-trigger"
                                    data-builder-detail="open"
                                    data-detail-source="<?= esc($detailSource) ?>"
                                    data-detail-label="<?= esc((string) ($summaryItem['label'] ?? '')) ?>"
                                  >
                                    <div class="d-flex align-items-center mb-2">
                                      <div class="flex-shrink-0">
                                        <span class="p-1 d-block <?= esc((string) ($summaryItem['dotClass'] ?? 'bg-primary')) ?> rounded-circle">
                                          <span class="visually-hidden">summary</span>
                                        </span>
                                      </div>
                                      <div class="flex-grow-1 ms-2">
                                        <p class="mb-0"><?= esc((string) ($summaryItem['label'] ?? '-')) ?></p>
                                      </div>
                                      <div class="flex-shrink-0 text-muted small">Detay</div>
                                    </div>
                                    <h6 class="mb-0"><?= esc((string) ($summaryItem['value'] ?? '0')) ?></h6>
                                  </button>
                                <?php else: ?>
                                  <div class="bg-body p-3 rounded">
                                    <div class="d-flex align-items-center mb-2">
                                      <div class="flex-shrink-0">
                                        <span class="p-1 d-block <?= esc((string) ($summaryItem['dotClass'] ?? 'bg-primary')) ?> rounded-circle">
                                          <span class="visually-hidden">summary</span>
                                        </span>
                                      </div>
                                      <div class="flex-grow-1 ms-2">
                                        <p class="mb-0"><?= esc((string) ($summaryItem['label'] ?? '-')) ?></p>
                                      </div>
                                    </div>
                                    <h6 class="mb-0"><?= esc((string) ($summaryItem['value'] ?? '0')) ?></h6>
                                  </div>
                                <?php endif; ?>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php elseif (($render['kind'] ?? '') === 'note'): ?>
                    <div class="card shadow-sm h-100 border-warning border-opacity-25">
                      <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                          <div class="d-flex align-items-center gap-2">
                            <div class="avtar avtar-s bg-light-warning">
                              <i class="ti ti-notebook text-warning f-20"></i>
                            </div>
                            <div>
                              <h6 class="mb-0"><?= esc((string) ($render['title'] ?? $block['title'] ?? 'Not')) ?></h6>
                              <div class="small text-muted"><?= esc((string) ($render['subtitle'] ?? 'Kısa not')) ?></div>
                            </div>
                          </div>
                          <span class="badge <?= $isVisible ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' ?>">
                            <?= $isVisible ? 'Görünür' : 'Gizli' ?>
                          </span>
                        </div>
                        <div class="bg-body rounded p-3">
                          <?= nl2br(esc((string) ($render['content'] ?? ''))) ?>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                          <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            data-builder-action="edit"
                            data-fetch-url="<?= site_url('admin/dashboard/blocks/fetch/' . (string) ($block['id'] ?? '')) ?>"
                            data-update-url="<?= site_url('admin/dashboard/blocks/update/' . (string) ($block['id'] ?? '')) ?>"
                            data-block-id="<?= esc((string) ($block['id'] ?? '')) ?>"
                            data-category-labels="[]"
                          >Ayarlar</button>
                          <form method="post" action="<?= site_url('admin/dashboard/blocks/delete/' . (string) ($block['id'] ?? '')) ?>" onsubmit="return confirm('Bu dashboard karti silinsin mi?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                          </form>
                          <button type="button" class="btn btn-sm btn-outline-secondary" title="Tasima akisi bir sonraki sprintte acilacak." disabled>Taşı</button>
                        </div>
                      </div>
                    </div>
                  <?php else: ?>
                    <div class="card shadow-sm h-100">
                      <div class="card-body text-muted">
                        <?= esc((string) ($render['message'] ?? 'Bu blok tipi için görünüm tanımlı değil.')) ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="builderBlockModal" tabindex="-1" aria-labelledby="builderBlockModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <form method="post" action="<?= site_url('admin/dashboard/blocks/store') ?>">
          <div class="modal-header">
            <h5 class="modal-title" id="builderBlockModalTitle">Yeni Kart Ekle</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
          </div>
          <div class="modal-body">
            <?= csrf_field() ?>

            <?php if (!empty($builderBlockErrors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                  <?php foreach ($builderBlockErrors as $builderBlockError): ?>
                    <li><?= esc((string) $builderBlockError) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="builderBlockType" class="form-label">Blok Tipi</label>
                <select class="form-select" id="builderBlockType" name="block_type_id" required>
                  <option value="">Seçiniz</option>
                  <?php foreach ($builderBlockTypes as $builderBlockType): ?>
                    <option
                      value="<?= esc((string) ($builderBlockType['id'] ?? '')) ?>"
                      data-block-code="<?= esc((string) ($builderBlockType['code'] ?? '')) ?>"
                      <?= $selectedBlockTypeId === (string) ($builderBlockType['id'] ?? '') ? 'selected' : '' ?>
                    >
                      <?= esc((string) ($builderBlockType['name'] ?? $builderBlockType['code'] ?? 'Blok')) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderBlockTitle" class="form-label">Başlık</label>
                <input
                  type="text"
                  class="form-control"
                  id="builderBlockTitle"
                  name="title"
                  value="<?= esc((string) old('title', '')) ?>"
                  placeholder="Örn. Günlük Sipariş Özeti"
                  required
                >
              </div>
              <div class="col-12 col-md-6">
                <label for="builderBlockVisibility" class="form-label">Durum</label>
                <select class="form-select" id="builderBlockVisibility" name="is_visible">
                  <option value="1" <?= (string) old('is_visible', '1') === '1' ? 'selected' : '' ?>>Görünür</option>
                  <option value="0" <?= (string) old('is_visible', '1') === '0' ? 'selected' : '' ?>>Gizli</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderDateRange" class="form-label">Tarih Aralığı</label>
                <select class="form-select" id="builderDateRange" name="date_range">
                  <option value="7d" <?= (string) old('date_range', '7d') === '7d' ? 'selected' : '' ?>>Son 7 Gün</option>
                  <option value="14d" <?= (string) old('date_range', '7d') === '14d' ? 'selected' : '' ?>>Son 14 Gün</option>
                  <option value="30d" <?= (string) old('date_range', '7d') === '30d' ? 'selected' : '' ?>>Son 30 Gün</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mt-1 <?= $selectedBlockTypeCode === 'stat_card' ? '' : 'd-none' ?>" data-block-config="stat_card">
              <div class="col-12" id="builderEditStatCustomColorField">
                <h6 class="mb-0">İstatistik Kartı</h6>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderStatDataSource" class="form-label">Veri Kaynağı</label>
                <select class="form-select" id="builderStatDataSource" name="data_source">
                  <option value="">Seçiniz</option>
                  <option value="total_orders" <?= (string) old('data_source', '') === 'total_orders' ? 'selected' : '' ?>>Toplam Sipariş</option>
                  <option value="today_orders" <?= (string) old('data_source', '') === 'today_orders' ? 'selected' : '' ?>>Bugün Sipariş</option>
                  <option value="weekly_orders" <?= (string) old('data_source', '') === 'weekly_orders' ? 'selected' : '' ?>>Haftalık Sipariş</option>
                  <option value="pending_orders" <?= (string) old('data_source', '') === 'pending_orders' ? 'selected' : '' ?>>Bekleyen Sipariş</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderValueLabel" class="form-label">Değer Etiketi</label>
                <input type="text" class="form-control" id="builderValueLabel" name="value_label" value="<?= esc((string) old('value_label', '')) ?>" placeholder="Sipariş">
              </div>
              <div class="col-12 col-md-6">
                <label for="builderSubtitle" class="form-label">Alt Metin</label>
                <input type="text" class="form-control" id="builderSubtitle" name="subtitle" value="<?= esc((string) old('subtitle', '')) ?>" placeholder="Düne göre karşılaştırma">
              </div>
              <div class="col-12 col-md-6">
                <label for="builderFallbackValue" class="form-label">Yedek Değer</label>
                <input type="text" class="form-control" id="builderFallbackValue" name="value" value="<?= esc((string) old('value', '')) ?>" placeholder="Kaynak yoksa gösterilecek değer">
              </div>
            </div>

            <div class="row g-3 mt-1 <?= $selectedBlockTypeCode === 'chart' ? '' : 'd-none' ?>" data-block-config="chart">
              <div class="col-12" id="builderEditChartCustomColorField">
                <h6 class="mb-0">Grafik</h6>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderChartDataSource" class="form-label">Veri Kaynağı</label>
                <select class="form-select" id="builderChartDataSource" name="data_source">
                  <option value="">Seçiniz</option>
                  <option value="orders_by_period" <?= (string) old('data_source', '') === 'orders_by_period' ? 'selected' : '' ?>>Döneme Göre Sipariş</option>
                  <option value="sales_by_category" <?= (string) old('data_source', '') === 'sales_by_category' ? 'selected' : '' ?>>Kategoriye Göre Satış</option>
                  <option value="print_vs_digital_sales" <?= (string) old('data_source', '') === 'print_vs_digital_sales' ? 'selected' : '' ?>>Baskı / Dijital Satış</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderChartType" class="form-label">Grafik Tipi</label>
                <select class="form-select" id="builderChartType" name="chart_type">
                  <option value="">Seçiniz</option>
                  <option value="line" <?= (string) old('chart_type', '') === 'line' ? 'selected' : '' ?>>Line</option>
                  <option value="bar" <?= (string) old('chart_type', '') === 'bar' ? 'selected' : '' ?>>Bar</option>
                  <option value="pie" <?= (string) old('chart_type', '') === 'pie' ? 'selected' : '' ?>>Pie / Donut</option>
                </select>
              </div>
              <div class="col-12" id="builderEditCategoryColorField">
                <label for="builderChartSubtitle" class="form-label">Alt Açıklama</label>
                <input type="text" class="form-control" id="builderChartSubtitle" name="subtitle" value="<?= esc((string) old('subtitle', '')) ?>" placeholder="Grafik altında gösterilecek kısa açıklama">
              </div>
            </div>

            <div class="row g-3 mt-1 <?= $selectedBlockTypeCode === 'note' ? '' : 'd-none' ?>" data-block-config="note">
              <div class="col-12">
                <h6 class="mb-0">Not Alanı</h6>
              </div>
              <div class="col-12">
                <label for="builderNoteSubtitle" class="form-label">Alt Açıklama</label>
                <input type="text" class="form-control" id="builderNoteSubtitle" name="subtitle" value="<?= esc((string) old('subtitle', '')) ?>" placeholder="Örn. Ekip notu">
              </div>
              <div class="col-12">
                <label for="builderNoteContent" class="form-label">İçerik</label>
                <textarea class="form-control" id="builderNoteContent" name="content" rows="4" placeholder="Kısa ekip notu veya dashboard açıklaması"><?= esc((string) old('content', '')) ?></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">İptal</button>
            <button type="submit" class="btn btn-primary">Kartı Kaydet</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="builderEditModal" tabindex="-1" aria-labelledby="builderEditModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable builder-modal-dialog">
      <div class="modal-content builder-modal-content">
        <form method="post" action="#" id="builderEditForm" class="builder-modal-form">
          <div class="modal-header">
            <div>
              <h5 class="modal-title" id="builderEditModalTitle">Kart Ayarlari</h5>
              <div class="small text-muted" id="builderEditBlockTypeLabel">Blok ayarlari yukleniyor</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
          </div>
          <div class="modal-body builder-modal-body">
            <?= csrf_field() ?>

            <?php if (!empty($builderBlockEditErrors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                  <?php foreach ($builderBlockEditErrors as $builderBlockEditError): ?>
                    <li><?= esc((string) $builderBlockEditError) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <input type="hidden" id="builderEditBlockId" value="<?= esc($builderBlockEditId) ?>">
            <input type="hidden" id="builderEditBlockCode" value="">

            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="builderEditTitle" class="form-label">Baslik</label>
                <input type="text" class="form-control" id="builderEditTitle" name="title" value="">
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditVisibility" class="form-label">Durum</label>
                <select class="form-select" id="builderEditVisibility" name="is_visible">
                  <option value="1">Gorunur</option>
                  <option value="0">Gizli</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditDateRange" class="form-label">Tarih Araligi</label>
                <select class="form-select" id="builderEditDateRange" name="date_range">
                  <option value="7d">Son 7 Gun</option>
                  <option value="14d">Son 14 Gun</option>
                  <option value="30d">Son 30 Gun</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditSubtitle" class="form-label">Alt Metin</label>
                <input type="text" class="form-control" id="builderEditSubtitle" name="subtitle" value="">
              </div>
            </div>

            <div class="row g-3 mt-1 d-none" data-edit-block-config="stat_card">
              <div class="col-12">
                <h6 class="mb-0">Istatistik Karti</h6>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditStatDataSource" class="form-label">Veri Kaynagi</label>
                <select class="form-select" id="builderEditStatDataSource" name="data_source">
                  <option value="">Seciniz</option>
                  <option value="total_orders">Toplam Siparis</option>
                  <option value="today_orders">Bugun Siparis</option>
                  <option value="weekly_orders">Haftalik Siparis</option>
                  <option value="pending_orders">Bekleyen Siparis</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditStatVariant" class="form-label">Variant</label>
                <select class="form-select" id="builderEditStatVariant" name="variant">
                  <option value="mini_spark">Mini Spark</option>
                  <option value="metric_tile">Metric Tile</option>
                  <option value="income_card">Income Card</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditValueLabel" class="form-label">Deger Etiketi</label>
                <input type="text" class="form-control" id="builderEditValueLabel" name="value_label" value="">
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditFallbackValue" class="form-label">Yedek Deger</label>
                <input type="text" class="form-control" id="builderEditFallbackValue" name="value" value="">
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditStatPalette" class="form-label">Renk Paleti</label>
                <select class="form-select" id="builderEditStatPalette" name="color_palette">
                  <option value="default">Default</option>
                  <option value="blue">Blue</option>
                  <option value="orange">Orange</option>
                  <option value="green">Green</option>
                  <option value="purple">Purple</option>
                  <option value="finance">Finance</option>
                  <option value="analytics">Analytics</option>
                  <option value="pastel">Pastel</option>
                  <option value="dark">Dark</option>
                  <option value="custom">Ozel Renkler</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label mb-2">Ozel Renkler</label>
                <input type="hidden" id="builderEditStatCustomColors" name="custom_colors" value="">
                <div class="builder-chip-grid" id="builderEditStatCustomChips"></div>
                <small class="text-muted d-block mt-2">Birden fazla vurgu rengi secebilirsiniz. Ilk secim mini chart icin kullanilir.</small>
              </div>
            </div>

            <div class="row g-3 mt-1 d-none" data-edit-block-config="chart">
              <div class="col-12">
                <h6 class="mb-0">Grafik</h6>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditChartDataSource" class="form-label">Veri Kaynagi</label>
                <select class="form-select" id="builderEditChartDataSource" name="data_source">
                  <option value="">Seciniz</option>
                  <option value="orders_by_period">Doneme Gore Siparis</option>
                  <option value="sales_by_category">Kategoriye Gore Satis</option>
                  <option value="print_vs_digital_sales">Baski / Dijital Satis</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditChartType" class="form-label">Grafik Tipi</label>
                <select class="form-select" id="builderEditChartType" name="chart_type">
                  <option value="line">Line</option>
                  <option value="bar">Bar</option>
                  <option value="pie">Pie / Donut</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditChartVariant" class="form-label">Variant</label>
                <select class="form-select" id="builderEditChartVariant" name="variant">
                  <option value="line_trend">Line Trend</option>
                  <option value="bar_overview">Bar Overview</option>
                  <option value="donut_summary">Donut Summary</option>
                  <option value="pie_breakdown">Pie Breakdown</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditColorPalette" class="form-label">Renk Paleti</label>
                <select class="form-select" id="builderEditColorPalette" name="color_palette">
                  <option value="default">Default</option>
                  <option value="blue">Blue</option>
                  <option value="orange">Orange</option>
                  <option value="green">Green</option>
                  <option value="purple">Purple</option>
                  <option value="finance">Finance</option>
                  <option value="analytics">Analytics</option>
                  <option value="pastel">Pastel</option>
                  <option value="dark">Dark</option>
                  <option value="custom">Ozel Renkler</option>
                </select>
              </div>
              <div class="col-12">
                <label for="builderEditCustomColors" class="form-label">Ozel Renkler</label>
                <input type="hidden" id="builderEditCustomColors" name="custom_colors" value="">
                <div class="builder-chip-grid" id="builderEditCustomColorChips"></div>
                <small class="text-muted d-block mt-2">Grafik renklerini chip secerek ozellestirebilirsiniz.</small>
              </div>
              <div class="col-12">
                <label for="builderEditCategoryColors" class="form-label">Kategori Renk Esleme</label>
                <input type="hidden" id="builderEditCategoryColors" name="category_colors" value="">
                <div id="builderEditCategoryColorRows"></div>
                <small class="text-muted d-block mt-2">Sadece kategori bazli grafiklerde gorunen kategoriler burada listelenir.</small>
              </div>
            </div>

            <div class="row g-3 mt-1 d-none" data-edit-block-config="note">
              <div class="col-12">
                <h6 class="mb-0">Not Alani</h6>
              </div>
              <div class="col-12 col-md-6">
                <label for="builderEditNoteVariant" class="form-label">Variant</label>
                <select class="form-select" id="builderEditNoteVariant" name="variant">
                  <option value="simple_note">Simple Note</option>
                  <option value="accent_note">Accent Note</option>
                </select>
              </div>
              <div class="col-12">
                <label for="builderEditNoteContent" class="form-label">Icerik</label>
                <textarea class="form-control" id="builderEditNoteContent" name="content" rows="5"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer builder-modal-footer">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Iptal</button>
            <button type="submit" class="btn btn-primary">Degisiklikleri Kaydet</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="builderDetailModal" tabindex="-1" aria-labelledby="builderDetailModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable builder-modal-dialog">
      <div class="modal-content builder-modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="builderDetailModalTitle">Satis Detayi</h5>
            <div class="small text-muted" id="builderDetailModalSubtitle">Secilen kirilima gore urun listesi</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body builder-modal-body">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="btn-group btn-group-sm" role="group" aria-label="detail-period">
              <button type="button" class="btn btn-primary" data-detail-period="weekly">Haftalik</button>
              <button type="button" class="btn btn-outline-primary" data-detail-period="daily">Gunluk</button>
              <button type="button" class="btn btn-outline-primary" data-detail-period="monthly">Aylik</button>
            </div>
            <div class="small text-muted" id="builderDetailMeta">Detay verisi hazirlaniyor</div>
          </div>

          <div id="builderDetailAlert" class="alert alert-danger d-none"></div>

          <div class="dt-responsive table-responsive">
            <table id="builderDetailTable" class="table table-hover table-striped align-middle mb-0 w-100">
              <thead>
                <tr>
                  <th>Kitap Adi</th>
                  <th>Tur / Format</th>
                  <th>Satilan Adet</th>
                  <th>Kalan Stok</th>
                  <th>Son Satis Tarihi</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer builder-modal-footer">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Kapat</button>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <?php foreach (($dto->orderCards ?? []) as $card): ?>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small"><?= esc($card->title ?? '-') ?></div>
            <div class="fs-3 fw-semibold"><?= esc($card->value ?? 0) ?></div>
            <div class="small text-muted"><?= esc($card->subtitle ?? ' ') ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <strong>Overview (Analytics)</strong>
          <div class="btn-group btn-group-sm" role="group" aria-label="overview-actions">
            <button class="btn btn-outline-primary">Day</button>
            <button class="btn btn-outline-primary">Week</button>
            <button class="btn btn-primary">Month</button>
          </div>
        </div>
        <div class="card-body">
          <div id="overview-chart-1"></div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Category Split (Finance)</strong></div>
        <div class="card-body">
          <div id="category-donut-chart"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>New Orders</strong></div>
        <div class="card-body">
          <div id="new-orders-graph"></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>New Users</strong></div>
        <div class="card-body">
          <div id="new-users-graph"></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Overview Snapshot A</strong></div>
        <div class="card-body">
          <div id="overview-chart-2"></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Overview Snapshot B</strong></div>
        <div class="card-body">
          <div id="overview-chart-3"></div>
          <div class="mt-3" id="overview-chart-4"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Cashflow (Finance)</strong></div>
        <div class="card-body">
          <div id="cashflow-bar-chart"></div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Calendar Template</strong></div>
        <div class="card-body">
          <div id="pc-datepicker-6"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Top 10 Authors</strong></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr><th>#</th><th>Author</th><th class="text-end">Sales</th></tr>
              </thead>
              <tbody>
                <?php $rank = 1; foreach (array_slice(($dto->topAuthors ?? []), 0, 10) as $a): ?>
                  <tr>
                    <td><?= esc($rank++) ?></td>
                    <td><?= esc($a['label'] ?? '-') ?></td>
                    <td class="text-end"><?= esc($a['value'] ?? 0) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Top 10 Digital Books</strong></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr><th>#</th><th>Book</th><th class="text-end">Sales</th></tr>
              </thead>
              <tbody>
                <?php $rank = 1; foreach (array_slice(($dto->topDigitalBooks ?? []), 0, 10) as $b): ?>
                  <tr>
                    <td><?= esc($rank++) ?></td>
                    <td><?= esc($b['label'] ?? '-') ?></td>
                    <td class="text-end"><?= esc($b['value'] ?? 0) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Latest Orders (Analytics block)</strong></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Customer</th>
                  <th>Status / Product</th>
                  <th class="text-end">Amount</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
              <?php if (!empty($dto->latestOrders)): ?>
                <?php foreach ($dto->latestOrders as $o): ?>
                  <tr>
                    <td><?= esc($o->id ?? '-') ?></td>
                    <td><?= esc($o->customerName ?? '-') ?></td>
                    <td><?= esc($o->status ?? '-') ?></td>
                    <td class="text-end"><?= esc(number_format((float) ($o->totalAmount ?? 0), 2)) ?></td>
                    <td><?= esc($o->createdAt ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Component Buttons</strong></div>
        <div class="card-body d-flex flex-wrap gap-2">
          <button class="btn btn-primary">Primary</button>
          <button class="btn btn-outline-primary">Outline</button>
          <button class="btn btn-secondary">Secondary</button>
          <button class="btn btn-success">Success</button>
          <button class="btn btn-warning">Warning</button>
          <button class="btn btn-danger">Danger</button>
          <button class="btn btn-light border">Light</button>
          <button class="btn btn-dark">Dark</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/admin/js/plugins/datepicker-full.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/new-orders-graph.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/new-users-graph.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/overview-chart.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/cashflow-bar-chart.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/category-donut-chart.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/widget-calender.js') ?>"></script>
<script>
  (function () {
    var blockTypeSelect = document.getElementById('builderBlockType');
    var builderCharts = <?= json_encode($builderCharts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var editModalElement = document.getElementById('builderEditModal');
    var editForm = document.getElementById('builderEditForm');
    var detailModalElement = document.getElementById('builderDetailModal');
    var detailModalTitle = document.getElementById('builderDetailModalTitle');
    var detailMeta = document.getElementById('builderDetailMeta');
    var detailAlert = document.getElementById('builderDetailAlert');
    var detailTableSelector = '#builderDetailTable';
    var detailTableInstance = null;
    var detailTableMode = null;
    var detailModalShown = false;
    var detailState = {
      source: '',
      label: '',
      period: 'weekly'
    };
    var editState = {
      modal: '<?= esc($builderBlockModal) ?>',
      blockId: '<?= esc($builderBlockEditId) ?>',
      old: <?= json_encode($builderBlockEditOld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    };
    var colorChoices = ['#4680FF', '#3B82F6', '#2563EB', '#F97316', '#FB923C', '#F59E0B', '#2CA87F', '#22C55E', '#16A34A', '#7C3AED', '#A855F7', '#8B5CF6', '#14B8A6', '#06B6D4', '#0F172A', '#111827', '#F9A8D4', '#FCD34D'];
    var currentCategoryLabels = [];

    function renderBuilderCharts() {
      if (!Array.isArray(builderCharts) || typeof ApexCharts === 'undefined') {
        return;
      }

      builderCharts.forEach(function (item) {
        var element = document.getElementById(item.id);
        if (!element || !item.options) {
          return;
        }

        var chart = new ApexCharts(element, item.options);
        chart.render();
      });
    }

    function setDetailPeriodButtons(period) {
      document.querySelectorAll('[data-detail-period]').forEach(function (button) {
        var active = button.getAttribute('data-detail-period') === period;
        button.classList.toggle('btn-primary', active);
        button.classList.toggle('btn-outline-primary', !active);
      });
    }

    function escapeHtml(value) {
      return String(value === undefined || value === null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function renderDetailRows(rows) {
      var tableBody = document.querySelector(detailTableSelector + ' tbody');
      if (!tableBody) {
        return;
      }

      tableBody.innerHTML = '';

      rows.forEach(function (row) {
        var tr = document.createElement('tr');
        tr.innerHTML =
          '<td>' + escapeHtml(row.book_name) + '</td>' +
          '<td>' + escapeHtml(row.format) + '</td>' +
          '<td>' + escapeHtml(row.sold_qty) + '</td>' +
          '<td>' + escapeHtml(row.remaining_stock) + '</td>' +
          '<td>' + escapeHtml(row.last_sale_date) + '</td>';
        tableBody.appendChild(tr);
      });
    }

    function destroyDetailDataTable() {
      if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(detailTableSelector)) {
        jQuery(detailTableSelector).DataTable().clear().destroy();
      }

      detailTableInstance = null;
      detailTableMode = null;
    }

    function initDetailDataTable() {
      if (typeof jQuery === 'undefined' || !jQuery.fn || !jQuery.fn.DataTable) {
        return;
      }

      jQuery(detailTableSelector).css('width', '100%');

      detailTableInstance = jQuery(detailTableSelector).DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[2, 'desc']],
        scrollX: false,
        autoWidth: false,
        dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
          lengthMenu: '_MENU_ kayit goster',
          search: 'Ara:',
          zeroRecords: 'Kayit bulunamadi',
          info: '_TOTAL_ kayittan _START_ - _END_ arasi gosteriliyor',
          infoEmpty: '0 kayittan 0 - 0 arasi gosteriliyor',
          infoFiltered: '(_MAX_ kayit icinden filtrelendi)',
          processing: 'Yukleniyor...',
          paginate: {
            first: 'Ilk',
            last: 'Son',
            next: 'Sonraki',
            previous: 'Onceki'
          }
        },
        initComplete: function () {
          var wrapper = document.querySelector('#builderDetailTable_wrapper');
          var lengthLabel = wrapper ? wrapper.querySelector('.dt-length label') : null;
          if (!lengthLabel) {
            if (detailTableInstance && detailModalShown) {
              setTimeout(function () {
                jQuery(detailTableSelector).css('width', '100%');
                detailTableInstance.columns.adjust().draw(false);
              }, 50);
            }
            return;
          }

          Array.from(lengthLabel.childNodes).forEach(function (node) {
            if (node.nodeType === Node.TEXT_NODE) {
              node.textContent = node.textContent.replace('{select}', '').replace(/\s+/g, ' ').trim();
            }
          });

          var select = lengthLabel.querySelector('select');
          if (select) {
            lengthLabel.innerHTML = '';
            lengthLabel.appendChild(select);
            lengthLabel.appendChild(document.createTextNode(' kayit goster'));
          }

          if (detailTableInstance && detailModalShown) {
            setTimeout(function () {
              jQuery(detailTableSelector).css('width', '100%');
              detailTableInstance.columns.adjust().draw(false);
            }, 50);
          }
        }
      });
      detailTableMode = 'jquery';
    }

    function loadDetailData() {
      if (!detailState.source || !detailState.label) {
        return;
      }

      detailAlert.classList.add('d-none');
      detailAlert.textContent = '';
      detailMeta.textContent = 'Veri yukleniyor...';

      var params = new URLSearchParams({
        source: detailState.source,
        label: detailState.label,
        period: detailState.period
      });

      fetch('<?= site_url('admin/dashboard/blocks/detail') ?>?' + params.toString(), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('detail_failed');
          }

          return response.json();
        })
        .then(function (payload) {
          if (!payload || !payload.success) {
            throw new Error(payload && payload.message ? payload.message : 'detail_invalid');
          }

          destroyDetailDataTable();
          detailModalTitle.textContent = payload.title || 'Satis Detayi';
          detailMeta.textContent = (payload.rows || []).length + ' kitap listeleniyor, toplam ' + String(payload.totalSoldQty || 0) + ' adet satis';
          renderDetailRows(payload.rows || []);
          initDetailDataTable();
        })
        .catch(function (error) {
          destroyDetailDataTable();
          detailMeta.textContent = 'Detay verisi alinamadi';
          renderDetailRows([]);
          detailAlert.textContent = error && error.message && error.message !== 'detail_failed' ? error.message : 'Detay verisi yuklenemedi.';
          detailAlert.classList.remove('d-none');
        });
    }

    function openDetailModal(source, label) {
      if (!detailModalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
        return;
      }

      detailState.source = source;
      detailState.label = label;
      detailState.period = 'summary';
      setDetailPeriodButtons(detailState.period);
      detailModalTitle.textContent = label + ' Satis Detayi';
      detailMeta.textContent = 'Veri yukleniyor...';
      bootstrap.Modal.getOrCreateInstance(detailModalElement).show();
      loadDetailData();
    }

    function toggleConfigSections(selectElement, selector, selectedCode) {
      if (!selectedCode && selectElement) {
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        selectedCode = selectedOption ? selectedOption.getAttribute('data-block-code') : '';
      }

      document.querySelectorAll(selector).forEach(function (section) {
        var isActive = section.getAttribute(selector === '[data-block-config]' ? 'data-block-config' : 'data-edit-block-config') === selectedCode;
        section.classList.toggle('d-none', !isActive);

        section.querySelectorAll('input, select, textarea').forEach(function (field) {
          field.disabled = !isActive;
        });
      });
    }

    function valueOrDefault(value, fallback) {
      return value === undefined || value === null ? fallback : value;
    }

    function parseColorList(value) {
      if (Array.isArray(value)) {
        return value.filter(Boolean);
      }

      return String(value || '').split(/[\r\n,]+/).map(function (item) {
        return item.trim();
      }).filter(Boolean);
    }

    function parseCategoryMap(value) {
      if (value && typeof value === 'object' && !Array.isArray(value)) {
        return value;
      }

      var result = {};

      String(value || '').split(/[\r\n]+/).forEach(function (line) {
        var parts = line.split('=');
        if (parts.length < 2) {
          return;
        }

        var key = parts[0].trim();
        var color = parts.slice(1).join('=').trim();
        if (key && color) {
          result[key] = color;
        }
      });

      return result;
    }

    function serializeColorList(colors) {
      return (colors || []).join(',');
    }

    function serializeCategoryMap(map) {
      return Object.keys(map || {}).filter(function (key) {
        return !!map[key];
      }).map(function (key) {
        return key + '=' + map[key];
      }).join('\n');
    }

    function buildColorChipGroup(container, selectedColors, options) {
      if (!container) {
        return;
      }

      var config = options || {};
      var allowMultiple = !!config.multiple;
      var onChange = typeof config.onChange === 'function' ? config.onChange : function () {};
      var selected = Array.isArray(selectedColors) ? selectedColors.slice() : [];

      container.innerHTML = '';

      colorChoices.forEach(function (color) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'builder-color-chip' + (selected.indexOf(color) !== -1 ? ' is-selected' : '');
        button.style.backgroundColor = color;
        button.setAttribute('title', color);
        button.setAttribute('aria-label', color);
        button.dataset.color = color;
        button.addEventListener('click', function () {
          var nextSelected = selected.slice();
          var index = nextSelected.indexOf(color);

          if (allowMultiple) {
            if (index === -1) {
              nextSelected.push(color);
            } else {
              nextSelected.splice(index, 1);
            }
          } else {
            nextSelected = index === -1 ? [color] : [];
          }

          selected = nextSelected;
          buildColorChipGroup(container, selected, options);
          onChange(selected.slice());
        });
        container.appendChild(button);
      });
    }

    function setPaletteFieldState(selectId, fieldId) {
      var paletteSelect = document.getElementById(selectId);
      var field = document.getElementById(fieldId);
      if (!paletteSelect || !field) {
        return;
      }

      field.classList.toggle('opacity-50', paletteSelect.value !== 'custom');
    }

    function renderCategoryColorRows(labels, selectedMap) {
      var container = document.getElementById('builderEditCategoryColorRows');
      var hiddenInput = document.getElementById('builderEditCategoryColors');
      var field = document.getElementById('builderEditCategoryColorField');
      var chartSource = document.getElementById('builderEditChartDataSource');
      var entries = Array.isArray(labels) ? labels.filter(Boolean) : [];
      var mapping = selectedMap || {};

      if (!container || !hiddenInput || !field) {
        return;
      }

      field.classList.toggle('d-none', !(chartSource && chartSource.value === 'sales_by_category'));
      container.innerHTML = '';

      if (entries.length === 0) {
        hiddenInput.value = serializeCategoryMap(mapping);
        if (chartSource && chartSource.value === 'sales_by_category') {
          container.innerHTML = '<div class="text-muted small">Grafikte gorunen kategori etiketi bulunursa burada secilebilir renk kutulari acilir.</div>';
        }
        return;
      }

      entries.forEach(function (label) {
        var row = document.createElement('div');
        row.className = 'builder-category-row';

        var name = document.createElement('div');
        name.className = 'fw-semibold';
        name.textContent = label;

        var chips = document.createElement('div');
        chips.className = 'builder-chip-grid';

        buildColorChipGroup(chips, mapping[label] ? [mapping[label]] : [], {
          multiple: false,
          onChange: function (colors) {
            if (colors.length) {
              mapping[label] = colors[0];
            } else {
              delete mapping[label];
            }

            hiddenInput.value = serializeCategoryMap(mapping);
          }
        });

        row.appendChild(name);
        row.appendChild(chips);
        container.appendChild(row);
      });

      hiddenInput.value = serializeCategoryMap(mapping);
    }

    function populateEditForm(block, overrideValues) {
      if (!editForm || !block) {
        return;
      }

      var config = block.config || {};
      var overrides = overrideValues || {};
      var blockCode = block.block_type_code || '';
      var title = valueOrDefault(overrides.title, block.title || config.title || '');
      var subtitle = valueOrDefault(overrides.subtitle, config.subtitle || '');
      var dateRange = valueOrDefault(overrides.date_range, config.date_range || '7d');
      var isVisible = valueOrDefault(overrides.is_visible, String(block.is_visible || '1'));
      var variant = valueOrDefault(overrides.variant, config.variant || '');
      var dataSource = valueOrDefault(overrides.data_source, config.data_source || '');
      var chartType = valueOrDefault(overrides.chart_type, config.chart_type || 'line');
      var valueLabel = valueOrDefault(overrides.value_label, config.value_label || '');
      var fallbackValue = valueOrDefault(overrides.value, config.value || '');
      var noteContent = valueOrDefault(overrides.content, config.content || '');
      var colorPalette = valueOrDefault(overrides.color_palette, config.color_palette || 'default');
      var statCustomColors = parseColorList(valueOrDefault(overrides.custom_colors, config.custom_colors || []));
      var chartCustomColors = parseColorList(valueOrDefault(overrides.custom_colors, config.custom_colors || []));
      var categoryColors = parseCategoryMap(valueOrDefault(overrides.category_colors, config.category_colors || {}));

      editForm.setAttribute('action', block.update_url || '#');
      document.getElementById('builderEditBlockId').value = block.id || '';
      document.getElementById('builderEditBlockCode').value = blockCode;
      document.getElementById('builderEditBlockTypeLabel').textContent = block.block_type_name || blockCode || 'Blok';
      document.getElementById('builderEditTitle').value = title;
      document.getElementById('builderEditVisibility').value = String(isVisible);
      document.getElementById('builderEditDateRange').value = dateRange;
      document.getElementById('builderEditSubtitle').value = subtitle;
      document.getElementById('builderEditStatDataSource').value = dataSource;
      document.getElementById('builderEditStatVariant').value = variant || 'mini_spark';
      document.getElementById('builderEditValueLabel').value = valueLabel;
      document.getElementById('builderEditFallbackValue').value = fallbackValue;
      document.getElementById('builderEditStatPalette').value = colorPalette;
      document.getElementById('builderEditStatCustomColors').value = serializeColorList(statCustomColors);
      document.getElementById('builderEditChartDataSource').value = dataSource;
      document.getElementById('builderEditChartType').value = chartType;
      document.getElementById('builderEditChartVariant').value = variant || 'line_trend';
      document.getElementById('builderEditColorPalette').value = colorPalette;
      document.getElementById('builderEditCustomColors').value = serializeColorList(chartCustomColors);
      document.getElementById('builderEditCategoryColors').value = serializeCategoryMap(categoryColors);
      document.getElementById('builderEditNoteVariant').value = variant || 'simple_note';
      document.getElementById('builderEditNoteContent').value = noteContent;

      currentCategoryLabels = Array.isArray(block.category_labels) ? block.category_labels.slice() : [];

      buildColorChipGroup(document.getElementById('builderEditStatCustomChips'), statCustomColors, {
        multiple: true,
        onChange: function (colors) {
          document.getElementById('builderEditStatCustomColors').value = serializeColorList(colors);
        }
      });

      buildColorChipGroup(document.getElementById('builderEditCustomColorChips'), chartCustomColors, {
        multiple: true,
        onChange: function (colors) {
          document.getElementById('builderEditCustomColors').value = serializeColorList(colors);
        }
      });

      setPaletteFieldState('builderEditStatPalette', 'builderEditStatCustomColorField');
      setPaletteFieldState('builderEditColorPalette', 'builderEditChartCustomColorField');
      renderCategoryColorRows(currentCategoryLabels, categoryColors);
      toggleConfigSections(null, '[data-edit-block-config]', blockCode);
    }

    function openEditModalFromButton(button, overrideValues) {
      if (!button || !button.dataset.fetchUrl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
        return;
      }

      fetch(button.dataset.fetchUrl, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('fetch_failed');
          }

          return response.json();
        })
        .then(function (payload) {
          if (!payload || !payload.success || !payload.block) {
            throw new Error('invalid_payload');
          }

          payload.block.update_url = button.dataset.updateUrl || '#';
          payload.block.category_labels = JSON.parse(button.dataset.categoryLabels || '[]');
          populateEditForm(payload.block, overrideValues || {});
          bootstrap.Modal.getOrCreateInstance(editModalElement).show();
        })
        .catch(function () {
          window.alert('Kart ayarlari yuklenemedi.');
        });
    }

    renderBuilderCharts();

    if (blockTypeSelect) {
      blockTypeSelect.addEventListener('change', function () {
        toggleConfigSections(blockTypeSelect, '[data-block-config]');
      });
      toggleConfigSections(blockTypeSelect, '[data-block-config]');
    }

    ['builderEditStatPalette', 'builderEditColorPalette'].forEach(function (id) {
      var select = document.getElementById(id);
      if (!select) {
        return;
      }

      select.addEventListener('change', function () {
        if (id === 'builderEditStatPalette') {
          setPaletteFieldState('builderEditStatPalette', 'builderEditStatCustomColorField');
        } else {
          setPaletteFieldState('builderEditColorPalette', 'builderEditChartCustomColorField');
        }
      });
    });

    var chartSourceSelect = document.getElementById('builderEditChartDataSource');
    if (chartSourceSelect) {
      chartSourceSelect.addEventListener('change', function () {
        renderCategoryColorRows(currentCategoryLabels, parseCategoryMap(document.getElementById('builderEditCategoryColors').value));
      });
    }

    document.querySelectorAll('[data-builder-action="edit"]').forEach(function (button) {
      button.addEventListener('click', function () {
        openEditModalFromButton(button, {});
      });
    });

    document.querySelectorAll('[data-builder-detail="open"]').forEach(function (button) {
      button.addEventListener('click', function () {
        openDetailModal(button.getAttribute('data-detail-source') || '', button.getAttribute('data-detail-label') || '');
      });
    });

    document.querySelectorAll('[data-detail-period]').forEach(function (button) {
      button.addEventListener('click', function () {
        detailState.period = button.getAttribute('data-detail-period') || 'weekly';
        setDetailPeriodButtons(detailState.period);
        loadDetailData();
      });
    });

    if (detailModalElement) {
      detailModalElement.addEventListener('shown.bs.modal', function () {
        detailModalShown = true;
        if (detailTableInstance && typeof detailTableInstance.columns === 'function') {
          setTimeout(function () {
            jQuery(detailTableSelector).css('width', '100%');
            detailTableInstance.columns.adjust().draw(false);
          }, 50);
        }
      });

      detailModalElement.addEventListener('hidden.bs.modal', function () {
        detailModalShown = false;
        destroyDetailDataTable();
        renderDetailRows([]);
        detailMeta.textContent = '';
        detailAlert.classList.add('d-none');
        detailAlert.textContent = '';
      });
    }

    if (editModalElement) {
      toggleConfigSections(null, '[data-edit-block-config]', '');
    }

    <?php if (!empty($builderBlockErrors)): ?>
    var builderModalElement = document.getElementById('builderBlockModal');
    if (builderModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(builderModalElement).show();
    }
    <?php endif; ?>

    if (editState.modal === 'edit' && editState.blockId) {
      var editTrigger = document.querySelector('[data-builder-action="edit"][data-block-id="' + editState.blockId + '"]');
      if (editTrigger) {
        openEditModalFromButton(editTrigger, editState.old || {});
      }
    }
  })();
</script>
<?= view('admin/dashboard_builder/_reorder_script', [
  'reorderGridId' => 'dashboardBuilderBlocksGrid',
  'reorderStatusId' => 'dashboardBuilderReorderStatus',
  'reorderToggleId' => 'dashboardBuilderEditToggle',
  'reorderBadgeId' => 'dashboardBuilderEditBadge',
  'reorderEnabledByDefault' => false,
]) ?>
<?= $this->endSection() ?>
