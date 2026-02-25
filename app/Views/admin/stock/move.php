<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$product = $product ?? [];
$logs = $logs ?? [];
$reasons = $reasons ?? [];
$validation = $validation ?? null;
$reasonLabels = [
    'depo_girisi' => 'Depo Girişi',
    'depo_transferi' => 'Depo Transferi',
    'iade_alindi' => 'İade Alındı',
    'tedarikci_girisi' => 'Tedarikçi Girişi',
    'hasarli_urun' => 'Hasarlı Ürün',
    'kayip_urun' => 'Kayıp Ürün',
    'kampanya_promosyon' => 'Kampanya / Promosyon',
    'manuel_duzeltme' => 'Manuel Düzeltme',
    'hediye_gonderimi' => 'Hediye Gönderimi',
    'sayim_duzeltme' => 'Sayım Düzeltme',
];

$stock = (int) ($product['stock_count'] ?? 0);
$reserved = (int) ($product['reserved_count'] ?? 0);
$sellable = (int) ($product['sellable'] ?? max(0, $stock - $reserved));
?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Stok Hareketi Yönetimi</h4>
        <a href="<?= site_url('admin/stock') ?>" class="btn btn-light">Stok Paneline Dön</a>
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
                <h5 class="mb-0"><?= esc((string) ($product['product_name'] ?? '-')) ?></h5>
                <small class="text-muted"><?= esc((string) ($product['category_name'] ?? '-')) ?></small>
            </div>
            <div class="card-body">
                <span class="badge bg-light-primary text-primary me-1">Mevcut: <?= esc($stock) ?></span>
                <span class="badge bg-light-warning text-warning me-1">Rezerve: <?= esc($reserved) ?></span>
                <span class="badge bg-light-success text-success">Satılabilir: <?= esc($sellable) ?></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Stok Hareketi Ekle</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/stock/move/' . ($product['id'] ?? '')) ?>">
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
                        <label class="form-label">Hareket Türü</label>
                        <select name="reason" class="form-select" required>
                            <option value="">Hareket türü seçin</option>
                            <?php foreach ($reasons as $reason): ?>
                                <option value="<?= esc($reason) ?>" <?= old('reason') === $reason ? 'selected' : '' ?>>
                                    <?= esc($reasonLabels[$reason] ?? $reason) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Not</label>
                        <textarea name="note" rows="4" class="form-control" required><?= esc((string) old('note')) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Stok Hareketini Kaydet</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Son Stok Hareketleri</h5>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info mb-0">Kayıtlı stok hareketi bulunamadı.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>İşlem</th>
                                    <th>Hareket Türü</th>
                                    <th>Not</th>
                                    <th>Kim Yaptı</th>
                                    <th>Order ID / Ref No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <?php
                                    $delta = (int) ($log['delta'] ?? 0);
                                    $actorName = trim((string) ($log['actor_name'] ?? ''));
                                    $actorEmail = trim((string) ($log['actor_email'] ?? ''));
                                    $actorText = $actorName !== '' ? $actorName : ($actorEmail !== '' ? $actorEmail : '-');
                                    $orderId = trim((string) ($log['related_order_id'] ?? ''));
                                    $refNo = trim((string) ($log['ref_no'] ?? ''));
                                    $reason = (string) ($log['reason'] ?? '');
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
                                        <td><?= esc($reasonLabels[$reason] ?? ($reason !== '' ? $reason : '-')) ?></td>
                                        <td><?= esc((string) ($log['note'] ?? '-')) ?></td>
                                        <td><?= esc($actorText) ?></td>
                                        <td>
                                            <?= esc($orderId !== '' ? $orderId : ($refNo !== '' ? $refNo : '-')) ?>
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
</div>

<?= $this->endSection() ?>
