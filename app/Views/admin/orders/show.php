<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .order-timeline {
        position: relative;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .order-timeline-item {
        position: relative;
        padding-left: 28px;
        padding-bottom: 18px;
    }

    .order-timeline-item:last-child {
        padding-bottom: 0;
    }

    .order-timeline-item::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 24px;
        bottom: -8px;
        width: 2px;
        background: #e9ecef;
    }

    .order-timeline-item:last-child::before {
        display: none;
    }

    .order-timeline-dot {
        position: absolute;
        left: 4px;
        top: 6px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$order = $order ?? [];
$items = $items ?? [];
$logs = $logs ?? [];

$orderNo = trim((string) ($order['order_no'] ?? ''));
if ($orderNo === '') {
    $orderNo = '#' . strtoupper(substr(str_replace('-', '', (string) ($order['id'] ?? '')), 0, 8));
}

$formatDate = static function (?string $value): string {
    $v = trim((string) $value);
    return $v !== '' ? $v : '-';
};

$timelineActionMap = [
    'order_created' => 'Sipariş oluşturuldu',
    'payment_updated' => 'Ödeme durumu güncellendi',
    'payment_status_changed' => 'Ödeme durumu güncellendi',
    'status_updated' => 'Sipariş durumu güncellendi',
    'status_changed' => 'Sipariş durumu güncellendi',
    'shipping_updated' => 'Kargo bilgisi güncellendi',
    'admin_note_added' => 'Admin notu eklendi',
    'cancelled' => 'Sipariş iptal edildi',
    'order_cancelled' => 'Sipariş iptal edildi',
    'return_started' => 'İade başlatıldı',
    'return_completed' => 'İade tamamlandı',
];

$timelineLogs = array_slice($logs, 0, 8);
?>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/orders') ?>">Siparişler</a></li>
                    <li class="breadcrumb-item" aria-current="page"><?= esc($orderNo) ?></li>
                </ul>
            </div>
            <div class="col-sm-6">
                <div class="page-header-title">
                    <h2 class="mb-0">Sipariş Detayı</h2>
                </div>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= site_url('admin/orders') ?>" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
            </div>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="container-fluid px-0">
<div class="row g-3">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Sipariş Bilgisi</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted d-block">Sipariş No</label>
                        <strong><?= esc($orderNo) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted d-block">Müşteri</label>
                        <strong><?= esc((string) ($order['customer_name'] ?? $order['user_name'] ?? '-')) ?></strong>
                        <div class="text-muted small"><?= esc((string) ($order['user_email'] ?? '-')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted d-block">Tarih</label>
                        <strong><?= esc($formatDate((string) ($order['created_at'] ?? $order['order_date'] ?? ''))) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted d-block">Toplam</label>
                        <strong><?= esc(number_format((float) ($order['total_amount'] ?? 0), 2, ',', '.')) ?> &#8378;</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sipariş Zaman Çizelgesi</h5>
            </div>
            <div class="card-body">
                <?php if ($timelineLogs === []): ?>
                    <p class="text-muted mb-0">Henüz kayıtlı işlem geçmişi yok.</p>
                <?php else: ?>
                    <ul class="order-timeline">
                        <?php foreach ($timelineLogs as $timelineLog): ?>
                            <?php
                            $action = trim((string) ($timelineLog['action'] ?? ''));
                            $title = $timelineActionMap[$action] ?? 'İşlem';
                            $message = trim((string) ($timelineLog['message'] ?? ''));
                            $fromStatus = trim((string) ($timelineLog['from_status'] ?? ''));
                            $toStatus = trim((string) ($timelineLog['to_status'] ?? ''));
                            $metaText = '';
                            $metaRaw = trim((string) ($timelineLog['meta_json'] ?? ''));
                            if ($metaRaw !== '') {
                                $meta = json_decode($metaRaw, true);
                                if (is_array($meta) && $meta !== []) {
                                    $metaParts = [];
                                    foreach ($meta as $metaKey => $metaValue) {
                                        if (is_scalar($metaValue) && trim((string) $metaValue) !== '') {
                                            $metaParts[] = $metaKey . ': ' . (string) $metaValue;
                                        }
                                    }
                                    $metaText = implode(' | ', $metaParts);
                                }
                            }

                            $detailParts = [];
                            if ($message !== '') {
                                $detailParts[] = $message;
                            }
                            if ($fromStatus !== '' || $toStatus !== '') {
                                $detailParts[] = ($fromStatus !== '' ? $fromStatus : '-') . ' -> ' . ($toStatus !== '' ? $toStatus : '-');
                            }
                            if ($metaText !== '') {
                                $detailParts[] = $metaText;
                            }
                            $detail = $detailParts !== [] ? implode(' | ', $detailParts) : '-';
                            ?>
                            <li class="order-timeline-item">
                                <span class="order-timeline-dot" aria-hidden="true"></span>
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <strong><?= esc($title) ?></strong>
                                    <span class="text-muted small text-nowrap"><?= esc((string) ($timelineLog['created_at'] ?? '-')) ?></span>
                                </div>
                                <div class="text-muted small mt-1"><?= esc($detail) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-3">
                        <a href="#order-history" class="btn btn-link btn-sm p-0">Tüm geçmişe git</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Adres</h5></div>
            <div class="card-body">
                <p class="mb-1"><?= esc((string) ($order['shipping_address_line1'] ?? '-')) ?></p>
                <?php if (!empty($order['shipping_address_line2'])): ?>
                    <p class="mb-1"><?= esc((string) $order['shipping_address_line2']) ?></p>
                <?php endif; ?>
                <p class="mb-0 text-muted">
                    <?= esc((string) ($order['shipping_district'] ?? '-')) ?> /
                    <?= esc((string) ($order['shipping_city'] ?? '-')) ?> -
                    <?= esc((string) ($order['shipping_postal_code'] ?? '-')) ?> /
                    <?= esc((string) ($order['shipping_country'] ?? '-')) ?>
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Ürünler</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Birim Fiyat</th>
                                <th>Adet</th>
                                <th class="text-end">Satır Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= esc((string) ($item['product_name_snapshot'] ?? '-')) ?></td>
                                    <td><?= esc(number_format((float) ($item['unit_price'] ?? 0), 2, ',', '.')) ?> &#8378;</td>
                                    <td><?= esc((string) ($item['quantity'] ?? 0)) ?></td>
                                    <td class="text-end"><?= esc(number_format((float) ($item['line_total'] ?? 0), 2, ',', '.')) ?> &#8378;</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card" id="order-history">
            <div class="card-header"><h5 class="mb-0">Sipariş Geçmişi</h5></div>
            <div class="card-body">
                <?php if ($logs === []): ?>
                    <p class="text-muted mb-0">Henüz kayıtlı işlem geçmişi yok.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($logs as $log): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= esc((string) ($log['action'] ?? '-')) ?></strong>
                                        <div class="text-muted small">
                                            <?= esc((string) ($log['message'] ?? '-')) ?>
                                            <?php if (!empty($log['from_status']) || !empty($log['to_status'])): ?>
                                                (<?= esc((string) ($log['from_status'] ?? '-')) ?> -> <?= esc((string) ($log['to_status'] ?? '-')) ?>)
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div><?= esc((string) ($log['created_at'] ?? '-')) ?></div>
                                        <div class="text-muted small"><?= esc((string) ($log['username'] ?? $log['actor_role'] ?? '-')) ?></div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Ödeme Bilgisi</h5></div>
            <div class="card-body">
                <p class="mb-1"><strong>Yöntem:</strong> <?= esc((string) ($order['payment_method'] ?? '-')) ?></p>
                <p class="mb-1"><strong>Durum:</strong> <?= esc((string) ($order['payment_status'] ?? '-')) ?></p>
                <p class="mb-0"><strong>Paid At:</strong> <?= esc($formatDate((string) ($order['paid_at'] ?? ''))) ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Kargo Bilgisi</h5></div>
            <div class="card-body">
                <p class="mb-1"><strong>Firma:</strong> <?= esc((string) ($order['shipping_company'] ?? '-')) ?></p>
                <p class="mb-1"><strong>Takip No:</strong> <?= esc((string) ($order['tracking_number'] ?? '-')) ?></p>
                <p class="mb-1"><strong>Shipped At:</strong> <?= esc($formatDate((string) ($order['shipped_at'] ?? ''))) ?></p>
                <p class="mb-0"><strong>Delivered At:</strong> <?= esc($formatDate((string) ($order['delivered_at'] ?? ''))) ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Durum Güncelle</h5></div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/orders/update-status/' . (string) ($order['id'] ?? '')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label" for="order_status">Sipariş Durumu</label>
                        <select class="form-select" id="order_status" name="order_status" required>
                            <option value="pending">Beklemede</option>
                            <option value="preparing">Hazırlanıyor</option>
                            <option value="packed">Paketlendi</option>
                            <option value="shipped">Kargoya Verildi</option>
                            <option value="delivered">Teslim Edildi</option>
                            <option value="cancelled">İptal Edildi</option>
                            <option value="return_in_progress">İade Sürecinde</option>
                            <option value="return_done">İade Tamamlandı</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="payment_status">Ödeme Durumu</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="">Değiştirme</option>
                            <option value="unpaid">Ödenmedi</option>
                            <option value="paid">Ödendi</option>
                            <option value="refunded">İade Edildi</option>
                            <option value="partial_refund">Kısmi İade</option>
                            <option value="failed">Başarısız</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Durumu Kaydet</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Kargo No Güncelle</h5></div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/orders/update-shipping/' . (string) ($order['id'] ?? '')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label" for="shipping_company">Kargo Firması</label>
                        <input class="form-control" type="text" id="shipping_company" name="shipping_company" value="<?= esc((string) ($order['shipping_company'] ?? '')) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="tracking_number">Takip Numarası</label>
                        <input class="form-control" type="text" id="tracking_number" name="tracking_number" value="<?= esc((string) ($order['tracking_number'] ?? '')) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="shipping_status">Kargo Durumu</label>
                        <select class="form-select" id="shipping_status" name="shipping_status">
                            <option value="">Değiştirme</option>
                            <option value="not_shipped">Gönderilmedi</option>
                            <option value="shipped">Kargoda</option>
                            <option value="delivered">Teslim</option>
                            <option value="returned">İade</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">Kargo Bilgisi Kaydet</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Admin Notu</h5></div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/orders/add-note/' . (string) ($order['id'] ?? '')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <textarea class="form-control" name="note" rows="3" placeholder="Not ekleyin..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-secondary w-100">Not Ekle</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Hızlı İşlemler</h5></div>
            <div class="card-body d-grid gap-2">
                <form method="post" action="<?= site_url('admin/orders/cancel/' . (string) ($order['id'] ?? '')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100">İptal Et</button>
                </form>
                <form method="post" action="<?= site_url('admin/orders/return/start/' . (string) ($order['id'] ?? '')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-warning w-100">İade Başlat</button>
                </form>
                <form method="post" action="<?= site_url('admin/orders/return/complete/' . (string) ($order['id'] ?? '')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-dark w-100">İade Tamamla</button>
                </form>
                <button type="button" class="btn btn-outline-primary w-100" disabled>Fatura Oluştur (Yakında)</button>
            </div>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
