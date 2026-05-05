<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$validation = $validation ?? null;
$generalSiteName = (string) ($general_site_name ?? '');
$generalAdminEmail = (string) ($general_admin_email ?? '');
$generalTimezone = (string) ($general_timezone ?? 'UTC');
$stockCriticalThreshold = (string) ($stock_critical_threshold ?? '5');
$stockLowNotificationEnabled = (string) ($stock_low_notification_enabled ?? '1');
$stockAllowZeroSale = (string) ($stock_allow_zero_sale ?? '0');
$orderReturnDays = (string) ($order_return_days ?? '14');
$orderCancelHours = (string) ($order_cancel_hours ?? '24');
$orderCashOnDeliveryEnabled = (string) ($order_cash_on_delivery_enabled ?? '1');
$shippingDefaultSlaDays = (string) ($shipping_default_sla_days ?? '2');
$shippingDefaultDesiLimit = (string) ($shipping_default_desi_limit ?? '30');
$shippingDefaultMode = (string) ($shipping_default_mode ?? 'dengeli');
$panelTableRowLimit = (string) ($panel_table_row_limit ?? '25');
$panelDefaultLanguage = strtolower((string) ($panel_default_language ?? 'tr'));
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Ayarlar') ?></h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="badge bg-light-success text-success">Aktif</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Sistem Ayarlari</h5>
      </div>
      <div class="card-body">
        <?php if (session('success')): ?>
          <div class="alert alert-success"><?= esc((string) session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
          <div class="alert alert-danger"><?= esc((string) session('error')) ?></div>
        <?php endif; ?>
        <?php if ($validation && $validation->getErrors()): ?>
          <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('admin/settings') ?>" class="row g-4">
          <?= csrf_field() ?>

          <div class="col-12">
            <h6 class="mb-3">Genel Ayarlar</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label" for="general_site_name">Site Adi</label>
                <input
                  id="general_site_name"
                  name="general_site_name"
                  type="text"
                  class="form-control"
                  value="<?= esc((string) old('general_site_name', $generalSiteName)) ?>"
                  maxlength="120"
                  required
                >
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="general_admin_email">Yonetici E-posta</label>
                <input
                  id="general_admin_email"
                  name="general_admin_email"
                  type="email"
                  class="form-control"
                  value="<?= esc((string) old('general_admin_email', $generalAdminEmail)) ?>"
                  maxlength="120"
                  required
                >
                <small class="text-muted d-block mt-1">Sistem bildirimleri icin kullanilacak yonetici adresi.</small>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="general_timezone">Zaman Dilimi</label>
                <input
                  id="general_timezone"
                  name="general_timezone"
                  type="text"
                  class="form-control"
                  value="<?= esc((string) old('general_timezone', $generalTimezone)) ?>"
                  maxlength="80"
                  required
                >
                <small class="text-muted d-block mt-1">Ornek: Europe/Istanbul</small>
              </div>
            </div>
          </div>

          <div class="col-12">
            <h6 class="mb-3">Stok Yonetimi</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label" for="stock_critical_threshold">Kritik Stok Esigi</label>
                <input
                  id="stock_critical_threshold"
                  name="stock_critical_threshold"
                  type="number"
                  min="0"
                  class="form-control"
                  value="<?= esc((string) old('stock_critical_threshold', $stockCriticalThreshold)) ?>"
                  required
                >
                <small class="text-muted d-block mt-1">Bu degerin altina dusen urunler kritik stok kabul edilir.</small>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="stock_low_notification_enabled">Dusuk Stok Bildirimi</label>
                <?php $lowStockNotif = (string) old('stock_low_notification_enabled', $stockLowNotificationEnabled); ?>
                <select id="stock_low_notification_enabled" name="stock_low_notification_enabled" class="form-select" required>
                  <option value="1"<?= $lowStockNotif === '1' ? ' selected' : '' ?>>Aktif</option>
                  <option value="0"<?= $lowStockNotif === '0' ? ' selected' : '' ?>>Pasif</option>
                </select>
                <small class="text-muted d-block mt-1">Kritik stok urunleri icin yoneticiye bildirim kullanimi.</small>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="stock_allow_zero_sale">Stok Sifirken Satis Izni</label>
                <?php $allowZeroSale = (string) old('stock_allow_zero_sale', $stockAllowZeroSale); ?>
                <select id="stock_allow_zero_sale" name="stock_allow_zero_sale" class="form-select" required>
                  <option value="1"<?= $allowZeroSale === '1' ? ' selected' : '' ?>>Aktif</option>
                  <option value="0"<?= $allowZeroSale === '0' ? ' selected' : '' ?>>Pasif</option>
                </select>
                <small class="text-muted d-block mt-1">Stok 0 oldugunda siparis kabul edilip edilmeyecegini belirler.</small>
              </div>
            </div>
          </div>

          <div class="col-12">
            <h6 class="mb-3">Siparis Ayarlari</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label" for="order_return_days">Iade Suresi (gun)</label>
                <input
                  id="order_return_days"
                  name="order_return_days"
                  type="number"
                  min="0"
                  class="form-control"
                  value="<?= esc((string) old('order_return_days', $orderReturnDays)) ?>"
                  required
                >
                <small class="text-muted d-block mt-1">Musterinin iade talebi acabilecegi gun siniri.</small>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="order_cancel_hours">Siparis Iptal Suresi (saat)</label>
                <input
                  id="order_cancel_hours"
                  name="order_cancel_hours"
                  type="number"
                  min="0"
                  class="form-control"
                  value="<?= esc((string) old('order_cancel_hours', $orderCancelHours)) ?>"
                  required
                >
                <small class="text-muted d-block mt-1">Siparis olustuktan sonra iptal icin taninan saat araligi.</small>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="order_cash_on_delivery_enabled">Kapida Odeme</label>
                <?php $cashOnDelivery = (string) old('order_cash_on_delivery_enabled', $orderCashOnDeliveryEnabled); ?>
                <select id="order_cash_on_delivery_enabled" name="order_cash_on_delivery_enabled" class="form-select" required>
                  <option value="1"<?= $cashOnDelivery === '1' ? ' selected' : '' ?>>Aktif</option>
                  <option value="0"<?= $cashOnDelivery === '0' ? ' selected' : '' ?>>Pasif</option>
                </select>
                <small class="text-muted d-block mt-1">Odeme seceneklerinde kapida odeme gosterimini kontrol eder.</small>
              </div>
            </div>
          </div>

          <div class="col-12">
            <h6 class="mb-3">Kargo Ayarlari</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label" for="shipping_default_sla_days">Varsayilan SLA Gunu</label>
                <input
                  id="shipping_default_sla_days"
                  name="shipping_default_sla_days"
                  type="number"
                  min="1"
                  class="form-control"
                  value="<?= esc((string) old('shipping_default_sla_days', $shippingDefaultSlaDays)) ?>"
                  required
                >
                <small class="text-muted d-block mt-1">Kargo hedef teslim suresi icin gun bazli varsayilan deger.</small>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="shipping_default_desi_limit">Varsayilan Desi Siniri</label>
                <input
                  id="shipping_default_desi_limit"
                  name="shipping_default_desi_limit"
                  type="number"
                  min="0"
                  class="form-control"
                  value="<?= esc((string) old('shipping_default_desi_limit', $shippingDefaultDesiLimit)) ?>"
                  required
                >
                <small class="text-muted d-block mt-1">Kargo hesaplamasinda kullanilan varsayilan desi limiti.</small>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="shipping_default_mode">Varsayilan Kargo Modu</label>
                <?php $shippingMode = (string) old('shipping_default_mode', $shippingDefaultMode); ?>
                <select id="shipping_default_mode" name="shipping_default_mode" class="form-select" required>
                  <option value="hizli"<?= $shippingMode === 'hizli' ? ' selected' : '' ?>>Hizli</option>
                  <option value="ekonomik"<?= $shippingMode === 'ekonomik' ? ' selected' : '' ?>>Ekonomik</option>
                  <option value="dengeli"<?= $shippingMode === 'dengeli' ? ' selected' : '' ?>>Dengeli</option>
                </select>
                <small class="text-muted d-block mt-1">Yeni kargo planlamalari icin kullanilacak varsayilan mod.</small>
              </div>
            </div>
          </div>

          <div class="col-12">
            <h6 class="mb-3">Panel Gorunumu</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label" for="panel_table_row_limit">Varsayilan Tablo Satir Sayisi</label>
                <?php $rowLimit = (string) old('panel_table_row_limit', $panelTableRowLimit); ?>
                <select id="panel_table_row_limit" name="panel_table_row_limit" class="form-select" required>
                  <option value="10"<?= $rowLimit === '10' ? ' selected' : '' ?>>10</option>
                  <option value="25"<?= $rowLimit === '25' ? ' selected' : '' ?>>25</option>
                  <option value="50"<?= $rowLimit === '50' ? ' selected' : '' ?>>50</option>
                  <option value="100"<?= $rowLimit === '100' ? ' selected' : '' ?>>100</option>
                </select>
                <small class="text-muted d-block mt-1">Tablolar ilk acildiginda gosterilecek varsayilan satir sayisi.</small>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="panel_default_language">Varsayilan Dil</label>
                <?php $panelLanguage = strtolower((string) old('panel_default_language', $panelDefaultLanguage)); ?>
                <select id="panel_default_language" name="panel_default_language" class="form-select" required>
                  <option value="tr"<?= $panelLanguage === 'tr' ? ' selected' : '' ?>>TR</option>
                  <option value="en"<?= $panelLanguage === 'en' ? ' selected' : '' ?>>EN</option>
                </select>
                <small class="text-muted d-block mt-1">Yonetim paneli icin varsayilan arayuz dili.</small>
              </div>
            </div>
          </div>

          <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Ayarlari Kaydet</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
