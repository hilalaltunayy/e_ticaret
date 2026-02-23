<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$criticalStocks = $criticalStocks ?? [];
$categoryCounts = $categoryCounts ?? [];
$productsForSelect = $productsForSelect ?? [];
$selectedProductId = $selectedProductId ?? '';
$selectedStockHistory = $selectedStockHistory ?? [];

$pieLabels = [];
$pieSeries = [];
$pieTotal = 0;
foreach ($categoryCounts as $row) {
    $label = (string) ($row['category_name'] ?? 'Kategori Yok');
    $count = (int) ($row['count'] ?? 0);
    $pieLabels[] = $label;
    $pieSeries[] = $count;
    $pieTotal += $count;
}

$lineLabels = [];
$lineSeries = [];
foreach ($selectedStockHistory as $point) {
    $lineLabels[] = (string) ($point['d'] ?? '');
    $lineSeries[] = $point['stock'] === null ? null : (int) $point['stock'];
}
?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Stok Takip Paneli</h4>
        <a href="<?= site_url('admin/stock/moves') ?>" class="btn btn-primary">Tüm Basılı Ürünler</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Kritik Stoklar (Satılabilir)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($criticalStocks)): ?>
                    <div class="alert alert-info mb-0">Kritik stoklu ürün yok</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Kategori</th>
                                    <th>Durum</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criticalStocks as $row): ?>
                                    <?php
                                    $stock = (int) ($row['stock_count'] ?? 0);
                                    $reserved = (int) ($row['reserved_count'] ?? 0);
                                    $available = (int) ($row['available_stock'] ?? max(0, $stock - $reserved));
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc($row['product_name'] ?? '-') ?></td>
                                        <td><?= esc($row['category_name'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-light-primary text-primary me-1">Mevcut: <?= esc($stock) ?></span>
                                            <span class="badge bg-light-warning text-warning me-1">Rezerve: <?= esc($reserved) ?></span>
                                            <?php if ($available <= 0): ?>
                                                <span class="badge bg-danger">Satılabilir: <?= esc($available) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Satılabilir: <?= esc($available) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= site_url('admin/stock/moves') . '?product_id=' . urlencode($row['id']) ?>"
                                               class="btn btn-sm btn-primary">
                                              Stok Hareketi Ekle
                                            </a>
                                            <form action="<?= site_url('admin/stock/deactivate/' . ($row['id'] ?? '')) ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Ürünü satıştan kaldırmak istiyor musunuz?')">Satıştan Kaldır</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Kategori Bazlı Dağılım (Basılı)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($categoryCounts)): ?>
                    <div class="text-muted">Veri yok</div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div id="stock-category-pie" style="min-height:260px;"></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($categoryCounts as $row): ?>
                                    <?php $count = (int) ($row['count'] ?? 0); ?>
                                    <?php $pct = $pieTotal > 0 ? round(($count / $pieTotal) * 100, 1) : 0; ?>
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <span><?= esc($row['category_name'] ?? '-') ?></span>
                                        <span class="text-muted"><?= esc($count) ?> (<?= esc($pct) ?>%)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="mt-3 small text-muted">
                                Toplam Basılı Ürün: <strong><?= esc($pieTotal) ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Ürün Bazlı Stok Değişimi</h5>
            </div>
            <div class="card-body">
                <form method="get" action="<?= site_url('admin/stock') ?>" class="mb-3">
                    <div class="input-group">
                        <select name="product_id" class="form-select">
                            <option value="">Grafik için ürün seçin</option>
                            <?php foreach ($productsForSelect as $product): ?>
                                <?php $id = (string) ($product['id'] ?? ''); ?>
                                <option value="<?= esc($id) ?>" <?= $selectedProductId === $id ? 'selected' : '' ?>>
                                    <?= esc($product['product_name'] ?? '-') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Getir</button>
                    </div>
                </form>

                <?php if ($selectedProductId === ''): ?>
                    <div class="alert alert-info mb-0">Grafik için ürün seçin</div>
                <?php elseif (empty($selectedStockHistory)): ?>
                    <div class="alert alert-secondary mb-0">Seçilen ürün için son 30 günde stok değişimi yok.</div>
                <?php else: ?>
                    <div id="stock-history-line" style="min-height:280px;"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    const pieLabels = <?= json_encode($pieLabels, JSON_UNESCAPED_UNICODE) ?>;
    const pieSeries = <?= json_encode($pieSeries) ?>;
    if (pieLabels.length > 0 && pieSeries.length > 0) {
      const pieEl = document.querySelector('#stock-category-pie');
      if (pieEl) {
        const pieChart = new ApexCharts(pieEl, {
          chart: { type: 'pie', height: 260, toolbar: { show: false } },
          labels: pieLabels,
          series: pieSeries,
          legend: { show: false },
          dataLabels: { enabled: true }
        });
        pieChart.render();
      }
    }

    const lineLabels = <?= json_encode($lineLabels, JSON_UNESCAPED_UNICODE) ?>;
    const lineSeries = <?= json_encode($lineSeries) ?>;
    if (lineLabels.length > 0 && lineSeries.length > 0) {
      const lineEl = document.querySelector('#stock-history-line');
      if (lineEl) {
        const lineChart = new ApexCharts(lineEl, {
          chart: { type: 'line', height: 280, toolbar: { show: false } },
          stroke: { curve: 'smooth', width: 3 },
          series: [{ name: 'Stok', data: lineSeries }],
          xaxis: { categories: lineLabels },
          yaxis: { min: 0 },
          markers: { size: 4 },
          noData: { text: 'Veri yok' }
        });
        lineChart.render();
      }
    }
  })();
</script>
<?= $this->endSection() ?>
