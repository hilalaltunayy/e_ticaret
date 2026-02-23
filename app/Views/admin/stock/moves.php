<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$allProducts = $allProducts ?? [];
$selectedProductId = $selectedProductId ?? '';
$selectedProduct = $selectedProduct ?? [];
$selectedStockHistory = $selectedStockHistory ?? [];
$selectedStockMoves = $selectedStockMoves ?? [];
$stockReasons = $stockReasons ?? [];
$validation = $validation ?? null;

$lineLabels = [];
$lineSeries = [];
foreach ($selectedStockHistory as $point) {
    $lineLabels[] = (string) ($point['d'] ?? '');
    $lineSeries[] = $point['stock'] === null ? null : (int) $point['stock'];
}
?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Tüm Basılı Ürünler - Stok Hareketleri</h4>
        <a href="<?= site_url('admin/stock') ?>" class="btn btn-light">Stok Özetine Dön</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if ($validation && $validation->getErrors()): ?>
    <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ürün Listesi</h5>
            </div>
            <div class="card-body">
                <?php if (empty($allProducts)): ?>
                    <div class="alert alert-info mb-0">Aktif basılı ürün bulunamadı.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Kategori</th>
                                    <th>Mevcut</th>
                                    <th>Rezerve</th>
                                    <th>Satılabilir</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allProducts as $row): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc((string) ($row['product_name'] ?? '-')) ?></td>
                                        <td><?= esc((string) ($row['category_name'] ?? '-')) ?></td>
                                        <td><?= esc((int) ($row['stock_count'] ?? 0)) ?></td>
                                        <td><?= esc((int) ($row['reserved_count'] ?? 0)) ?></td>
                                        <td><?= esc((int) ($row['salable'] ?? 0)) ?></td>
                                        <td class="text-end">
                                            <a href="<?= site_url('admin/stock/moves') . '?product_id=' . urlencode((string) ($row['id'] ?? '')) ?>"
                                               class="btn btn-outline-primary btn-sm">
                                                Stok Hareketi Gör
                                            </a>
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

    <div id="stock-detail" class="col-12"></div>
    <?php if ($selectedProductId === '' || empty($selectedProduct)): ?>
        <div class="col-12">
            <div class="alert alert-info mb-0">Detay ve stok hareketi işlemleri için ürün seçin.</div>
        </div>
    <?php else: ?>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Ürün Bazlı Stok Değişimi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-light-primary text-primary me-1">Mevcut: <?= esc((int) ($selectedProduct['stock_count'] ?? 0)) ?></span>
                        <span class="badge bg-light-warning text-warning me-1">Rezerve: <?= esc((int) ($selectedProduct['reserved_count'] ?? 0)) ?></span>
                        <span class="badge bg-light-success text-success">Satılabilir: <?= esc((int) ($selectedProduct['sellable'] ?? 0)) ?></span>
                    </div>
                    <?php if (empty($selectedStockHistory)): ?>
                        <div class="alert alert-secondary mb-0">Seçilen ürün için son 30 günde stok değişimi yok.</div>
                    <?php else: ?>
                        <div id="stock-history-line" style="min-height:280px;"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Stok Hareketi Ekle</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/stock/move/' . ($selectedProduct['id'] ?? $selectedProductId)) ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Yön</label>
                            <select name="direction" class="form-select" required>
                                <option value="">Seçin</option>
                                <option value="in" <?= old('direction') === 'in' ? 'selected' : '' ?>>Giriş (+)</option>
                                <option value="out" <?= old('direction') === 'out' ? 'selected' : '' ?>>Çıkış (-)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Miktar</label>
                            <input type="number" name="quantity" min="1" class="form-control" value="<?= esc((string) old('quantity')) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sebep</label>
                            <select name="reason" class="form-select" required>
                                <option value="">Sebep seçin</option>
                                <?php foreach ($stockReasons as $reason): ?>
                                    <option value="<?= esc($reason) ?>" <?= old('reason') === $reason ? 'selected' : '' ?>>
                                        <?= esc($reason) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Not</label>
                            <textarea name="note" rows="3" class="form-control" required><?= esc((string) old('note')) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Stok Hareketini Kaydet</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Son Stok Hareketleri</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($selectedStockMoves)): ?>
                        <div class="alert alert-secondary mb-0">Seçilen ürün için stok hareketi bulunamadı.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>İşlem</th>
                                        <th>Sebep</th>
                                        <th>Not</th>
                                        <th>Kim Yaptı</th>
                                        <th>Order ID / Ref No</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selectedStockMoves as $log): ?>
                                        <?php
                                        $delta = (int) ($log['delta'] ?? 0);
                                        $actorName = trim((string) ($log['actor_name'] ?? ''));
                                        $actorEmail = trim((string) ($log['actor_email'] ?? ''));
                                        $actorText = $actorName !== '' ? $actorName : ($actorEmail !== '' ? $actorEmail : '-');
                                        $orderId = trim((string) ($log['related_order_id'] ?? ''));
                                        $refNo = trim((string) ($log['ref_no'] ?? ''));
                                        ?>
                                        <tr>
                                            <td><?= esc((string) ($log['created_at'] ?? '-')) ?></td>
                                            <td>
                                                <?php if ($delta >= 0): ?>
                                                    <span class="badge bg-light-success text-success">+<?= esc($delta) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-light-danger text-danger"><?= esc($delta) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc((string) ($log['reason'] ?? '-')) ?></td>
                                            <td><?= esc((string) ($log['note'] ?? '-')) ?></td>
                                            <td><?= esc($actorText) ?></td>
                                            <td><?= esc($orderId !== '' ? $orderId : ($refNo !== '' ? $refNo : '-')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<?php if ($selectedProductId !== ''): ?>
<script>
  window.addEventListener('load', function () {
    const el = document.getElementById('stock-detail');
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
</script>
<?php endif; ?>
<script>
  (function () {
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
