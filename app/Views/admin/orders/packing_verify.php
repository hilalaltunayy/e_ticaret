<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const scanForm = document.getElementById('packing-scan-form');
    const finishForm = document.getElementById('packing-finish-form');
    const scanInput = document.getElementById('scan_code');
    const scanMessage = document.getElementById('packing-scan-message');
    const verifyMessage = document.getElementById('packing-verify-message');
    const expectedTableBody = document.getElementById('expected-items-body');
    const scannedTableBody = document.getElementById('scanned-items-body');
    const errorList = document.getElementById('verification-errors');
    const finishButton = document.getElementById('finish-btn');

    if (!scanForm || !finishForm || !expectedTableBody || !scannedTableBody || !errorList || !finishButton) {
        return;
    }

    let verificationState = <?= json_encode($verification ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const expectedItemsSeed = <?= json_encode($expectedItems ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const scanStateSeed = <?= json_encode($scanState ?? ['items' => [], 'unknown_scans' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    if (!verificationState || typeof verificationState !== 'object') {
        verificationState = {};
    }
    if (!Array.isArray(verificationState.expected_items) || verificationState.expected_items.length === 0) {
        verificationState.expected_items = Array.isArray(expectedItemsSeed) ? expectedItemsSeed.map(function (item) {
            return {
                name: item.name || '-',
                product_id: item.product_id || '',
                barcode: item.barcode || item.product_id || '',
                isbn: item.isbn || '',
                expected_qty: Number(item.qty || 1),
                scanned_qty: 0,
                status: 'missing'
            };
        }) : [];
    }
    if (!Array.isArray(verificationState.scanned_items)) {
        verificationState.scanned_items = [];
    }
    if (verificationState.scanned_items.length === 0) {
        const scannedItemsSeed = scanStateSeed && Array.isArray(scanStateSeed.items) ? scanStateSeed.items : [];
        verificationState.scanned_items = scannedItemsSeed.map(function (item) {
            return {
                type: 'known',
                code: item.code || item.barcode || '-',
                name: item.name || '-',
                qty: Number(item.qty || 1),
                status: 'ok'
            };
        });

        const unknownSeed = scanStateSeed && Array.isArray(scanStateSeed.unknown_scans) ? scanStateSeed.unknown_scans : [];
        unknownSeed.forEach(function (item) {
            verificationState.scanned_items.push({
                type: 'unknown',
                code: item.code || item.barcode || '-',
                name: 'Bilinmeyen Urun',
                qty: Number(item.qty || 1),
                status: 'unknown'
            });
        });
    }

    const setCsrf = function (form, csrf) {
        if (!csrf || !csrf.token || !csrf.hash) {
            return;
        }
        const input = form.querySelector('input[name="' + csrf.token + '"]');
        if (input) {
            input.value = csrf.hash;
        }
    };

    const escapeHtml = function (value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    };

    const renderVerification = function (state) {
        verificationState = state || {};
        const expectedItems = Array.isArray(verificationState.expected_items) ? verificationState.expected_items : [];
        const scannedItems = Array.isArray(verificationState.scanned_items) ? verificationState.scanned_items : [];
        const errors = Array.isArray(verificationState.errors) ? verificationState.errors : [];
        const canFinish = Boolean(verificationState.can_finish);

        expectedTableBody.innerHTML = '';
        if (expectedItems.length === 0) {
            expectedTableBody.innerHTML = '<tr><td colspan="4" class="text-muted">Beklenen ürün bulunamadı.</td></tr>';
        } else {
            expectedItems.forEach(function (item) {
                const status = String(item.status || '');
                const rowClass = (status === 'missing' || status === 'excess') ? 'table-danger' : '';
                const code = item.barcode || item.isbn || item.product_id || '-';
                expectedTableBody.innerHTML += '<tr class="' + rowClass + '">' +
                    '<td>' + escapeHtml(item.name || '-') + '<div class="small text-muted">' + escapeHtml(code) + '</div></td>' +
                    '<td>' + escapeHtml(item.expected_qty || 0) + '</td>' +
                    '<td>' + escapeHtml(item.scanned_qty || 0) + '</td>' +
                    '<td>' + (status === 'missing' ? 'Eksik' : (status === 'excess' ? 'Fazla' : 'Tamam')) + '</td>' +
                '</tr>';
            });
        }

        scannedTableBody.innerHTML = '';
        if (scannedItems.length === 0) {
            scannedTableBody.innerHTML = '<tr><td colspan="4" class="text-muted">Henüz okutma yapılmadı.</td></tr>';
        } else {
            scannedItems.forEach(function (item) {
                const type = String(item.type || 'known');
                const status = String(item.status || 'ok');
                const rowClass = (type === 'unknown' || status === 'excess') ? 'table-danger' : '';
                const name = type === 'unknown' ? 'Bilinmeyen Ürün' : (item.name || '-');
                const rowStatus = type === 'unknown' ? 'Yanlış' : (status === 'excess' ? 'Fazla' : 'Uygun');
                scannedTableBody.innerHTML += '<tr class="' + rowClass + '">' +
                    '<td>' + escapeHtml(name) + '</td>' +
                    '<td>' + escapeHtml(item.code || '-') + '</td>' +
                    '<td>' + escapeHtml(item.qty || 0) + '</td>' +
                    '<td>' + rowStatus + '</td>' +
                '</tr>';
            });
        }

        errorList.innerHTML = '';
        if (errors.length === 0) {
            errorList.innerHTML = '<li class="list-group-item text-success">Doğrulama için tüm koşullar uygun.</li>';
        } else {
            errors.forEach(function (error) {
                errorList.innerHTML += '<li class="list-group-item text-danger">' + escapeHtml(error) + '</li>';
            });
        }

        finishButton.disabled = !canFinish;
    };

    const showAlert = function (element, type, message) {
        element.className = 'alert alert-' + type;
        element.textContent = message;
        element.classList.remove('d-none');
    };

    scanForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        scanMessage.classList.add('d-none');

        const response = await fetch(scanForm.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(scanForm)
        });
        const payload = await response.json();
        setCsrf(scanForm, payload.csrf);
        setCsrf(finishForm, payload.csrf);

        if (!payload.success) {
            showAlert(scanMessage, 'danger', payload.message || 'Okutma kaydedilemedi.');
            if (payload.verification) {
                renderVerification(payload.verification);
            }
            return;
        }

        showAlert(scanMessage, payload.message === 'Bilinmeyen ürün okutuldu.' ? 'warning' : 'success', payload.message || 'Okutma kaydedildi.');
        if (payload.verification) {
            renderVerification(payload.verification);
        }

        if (scanInput) {
            scanInput.value = '';
            scanInput.focus();
        }
    });

    finishForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        verifyMessage.classList.add('d-none');

        if (finishButton.disabled) {
            showAlert(verifyMessage, 'danger', 'Doğrulama tamamlanamaz. Eksik/fazla/yanlış okutma var.');
            return;
        }

        const response = await fetch(finishForm.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(finishForm)
        });
        const payload = await response.json();
        setCsrf(scanForm, payload.csrf);
        setCsrf(finishForm, payload.csrf);

        if (!payload.success) {
            showAlert(verifyMessage, 'danger', payload.message || 'Doğrulama tamamlanamadı.');
            if (payload.verification) {
                renderVerification(payload.verification);
            }
            return;
        }

        showAlert(verifyMessage, 'success', payload.message || 'Paket doğrulaması tamamlandı.');
        finishButton.disabled = true;
        setTimeout(function () {
            window.location.reload();
        }, 700);
    });

    renderVerification(verificationState);
    if (scanInput) {
        scanInput.focus();
    }
});
</script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$order = $order ?? [];
$session = $session ?? [];
$verification = is_array($verification ?? null) ? $verification : [];

$orderNo = trim((string) ($order['order_no'] ?? ''));
if ($orderNo === '') {
    $orderNo = '#' . strtoupper(substr(str_replace('-', '', (string) ($order['id'] ?? '')), 0, 8));
}

$customerName = trim((string) ($order['customer_name'] ?? $order['user_name'] ?? '-'));
?>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Y&ouml;netim</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/orders') ?>">Sipari&#351;ler</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/orders/' . (string) ($order['id'] ?? '')) ?>"><?= esc($orderNo) ?></a></li>
                    <li class="breadcrumb-item" aria-current="page">Paket Do&#287;rulama</li>
                </ul>
            </div>
            <div class="col-sm-6">
                <div class="page-header-title">
                    <h2 class="mb-0">Paket Do&#287;rulama</h2>
                </div>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= site_url('admin/orders/' . (string) ($order['id'] ?? '') . '/packing/label') ?>" class="btn btn-outline-secondary btn-sm">Etikete D&ouml;n</a>
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

<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="text-muted d-block">Sipari&#351; No</label>
                        <strong><?= esc($orderNo) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted d-block">M&uuml;&#351;teri</label>
                        <strong><?= esc($customerName !== '' ? $customerName : '-') ?></strong>
                    </div>
                    <div class="col-md-5">
                        <label class="text-muted d-block">Paket Kodu</label>
                        <strong><?= esc((string) ($session['package_code'] ?? '-')) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Okutma Alan&#305;</h5></div>
            <div class="card-body">
                <div id="packing-scan-message" class="alert d-none" role="alert"></div>
                <form id="packing-scan-form" method="post" action="<?= site_url('admin/orders/' . (string) ($order['id'] ?? '') . '/packing/scan') ?>" class="row g-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="qty" value="1">
                    <div class="col-12">
                        <input type="text" id="scan_code" class="form-control" name="barcode" placeholder="Barkod / ISBN (gerekirse &uuml;r&uuml;n ID)" required>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Beklenen &Uuml;r&uuml;nler</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>&Uuml;r&uuml;n</th>
                                <th>Beklenen</th>
                                <th>Okutulan</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody id="expected-items-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Okutulan &Uuml;r&uuml;nler</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>&Uuml;r&uuml;n</th>
                                <th>Kod</th>
                                <th>Adet</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody id="scanned-items-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Anl&#305;k Do&#287;rulama</h5></div>
            <div class="card-body">
                <ul id="verification-errors" class="list-group mb-3"></ul>
                <div id="packing-verify-message" class="alert d-none" role="alert"></div>
                <form id="packing-finish-form" method="post" action="<?= site_url('admin/orders/' . (string) ($order['id'] ?? '') . '/packing/finish') ?>">
                    <?= csrf_field() ?>
                    <button id="finish-btn" type="submit" class="btn btn-outline-success w-100" <?= ! (bool) ($verification['can_finish'] ?? false) ? 'disabled' : '' ?>>Do&#287;rulamay&#305; Tamamla</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
