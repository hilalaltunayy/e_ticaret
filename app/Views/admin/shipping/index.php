<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<style>
  #avgDeliveryChart,
  #carrierPerformanceChart {
    min-height: 220px;
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$kpi = is_array($kpi ?? null) ? $kpi : [];
$kpiShippedToday = (int) ($kpi['shipped_today'] ?? 0);
$kpiInTransit = (int) ($kpi['in_transit'] ?? 0);
$kpiDelivered = (int) ($kpi['delivered'] ?? 0);
$kpiProblem = (int) ($kpi['problem'] ?? 0);
$statusPreparing = (int) ($kpi['status_preparing'] ?? 0);
$statusShipped = (int) ($kpi['status_shipped'] ?? 0);
$statusDelivered = (int) ($kpi['status_delivered'] ?? 0);
$statusReturned = (int) ($kpi['status_returned'] ?? 0);
$statusDelayed = (int) ($kpi['status_delayed'] ?? 0);
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item" aria-current="page">Kargo Takip</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0">Kargo Takip</h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <div class="d-inline-flex gap-2">
          <a href="<?= site_url('admin/shipping/automation') ?>" class="btn btn-primary btn-sm">Kargo Optimizasyonu</a>
          <button type="button" id="btnShippingRefresh" class="btn btn-outline-secondary btn-sm">Yenile</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden js-kpi-card" data-kpi-filter="shipped_today" role="button" style="cursor:pointer;">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Bugün Kargoya Verilen</h6>
        <h4 class="mb-0"><?= number_format($kpiShippedToday) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden js-kpi-card" data-kpi-filter="in_transit" role="button" style="cursor:pointer;">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Yolda</h6>
        <h4 class="mb-0"><?= number_format($kpiInTransit) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden js-kpi-card" data-kpi-filter="delivered" role="button" style="cursor:pointer;">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Teslim Edildi</h6>
        <h4 class="mb-0"><?= number_format($kpiDelivered) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden js-kpi-card" data-kpi-filter="problem" role="button" style="cursor:pointer;">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Sorunlu / Geciken</h6>
        <h4 class="mb-0"><?= number_format($kpiProblem) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Gönderiler</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="shippingTable" class="table table-hover table-striped align-middle mb-0 w-100">
            <thead>
              <tr>
                <th style="width:36px;">
                  <input type="checkbox" id="shippingSelectAll" class="form-check-input" aria-label="Tümünü seç">
                </th>
                <th>Sipariş No</th>
                <th>Müşteri</th>
                <th>Kargo Firması</th>
                <th>Takip No</th>
                <th>Kargo Durumu</th>
                <th>Son Güncelleme</th>
                <th>İşlemler</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Performans & Analitik</h5>
        <span class="badge bg-light-warning text-warning">Yakında canlı veri</span>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <h6 class="mb-2">Ortalama Teslim Süresi (gün)</h6>
            <div id="avgDeliveryChart"></div>
          </div>
          <div class="col-12 col-lg-6">
            <h6 class="mb-2">Kargo Firması Performansı</h6>
            <div id="carrierPerformanceChart"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="card mt-3">
      <div class="card-header">
        <h5 class="mb-0">Durum Dağılımı</h5>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between px-0"><span>Hazırlanıyor</span><span class="badge bg-light-secondary text-secondary"><?= number_format($statusPreparing) ?></span></li>
          <li class="list-group-item d-flex justify-content-between px-0"><span>Kargoda</span><span class="badge bg-light-primary text-primary"><?= number_format($statusShipped) ?></span></li>
          <li class="list-group-item d-flex justify-content-between px-0"><span>Teslim</span><span class="badge bg-light-success text-success"><?= number_format($statusDelivered) ?></span></li>
          <li class="list-group-item d-flex justify-content-between px-0"><span>İade</span><span class="badge bg-light-warning text-warning"><?= number_format($statusReturned) ?></span></li>
          <li class="list-group-item d-flex justify-content-between px-0"><span>Geciken</span><span class="badge bg-light-danger text-danger"><?= number_format($statusDelayed) ?></span></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="mb-0">Takip Sorgula</h5>
      </div>
      <div class="card-body">
        <form id="shippingTrackForm">
          <div class="mb-3">
            <label for="shipping_company" class="form-label">Kargo firması</label>
            <select id="shipping_company" class="form-select">
              <option value="">Seçiniz</option>
              <option value="yurtici">Yurtiçi Kargo</option>
              <option value="aras">Aras Kargo</option>
              <option value="mng">MNG Kargo</option>
              <option value="ptt">PTT Kargo</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="tracking_number" class="form-label">Takip numarası</label>
            <input type="text" id="tracking_number" class="form-control" placeholder="Örn: TRK123456789">
          </div>
          <button type="button" id="btnShippingTrackSearch" class="btn btn-primary w-100">Sorgula</button>
        </form>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Anlaşmalı Kargo Firmaları</h5>
        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#carrierList" aria-expanded="false" aria-controls="carrierList">
          Firmaları Göster
        </button>
      </div>
      <div class="collapse" id="carrierList">
        <div class="card-body pt-0">
          <?php $shippingCompanies = is_array($shippingCompanies ?? null) ? $shippingCompanies : []; ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($shippingCompanies as $company): ?>
              <?php
              $companyName = trim((string) ($company['name'] ?? ''));
              if ($companyName === '') {
                  continue;
              }
              $integrationType = trim((string) ($company['integration_type'] ?? 'Yok'));
              $isNotReady = $integrationType === '' || strtolower($integrationType) === 'yok';
              $labelClass = $isNotReady ? 'bg-light-secondary text-secondary' : 'bg-light-success text-success';
              $labelText = $isNotReady ? 'Entegrasyon: Hazır Değil' : ('Entegrasyon: ' . $integrationType);
?>
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><?= esc($companyName) ?></span>
                <span class="badge <?= esc($labelClass) ?>"><?= esc($labelText) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="mt-3">
            <a href="<?= site_url('admin/shipping/companies/create?return_url=' . urlencode(site_url('admin/shipping'))) ?>" class="btn btn-primary w-100">+ Firma Ekle</a>
          </div>
        </div>
      </div>
    </div>

    <div class="mb-3">
      <a id="btnPackingLabel" href="#" class="btn btn-primary w-100 disabled" aria-disabled="true">Kargo Etiketi Çıkar</a>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h5 class="mb-0">Toplu Kargo İşlemleri</h5>
      </div>
      <div class="card-body">
        <div id="bulkShippingAlert" class="alert alert-warning py-2 px-3 d-none mb-3" role="alert"></div>
        <div class="d-grid gap-2">
          <button type="button" class="btn btn-light-secondary text-start js-bulk-action" data-action="labels" data-endpoint="<?= site_url('admin/shipping/bulk/labels') ?>">Toplu Etiket Oluştur</button>
          <button type="button" class="btn btn-light-secondary text-start js-bulk-action" data-action="barcodes" data-endpoint="<?= site_url('admin/shipping/bulk/barcodes') ?>">Barkod Çıktısı</button>
          <button type="button" class="btn btn-light-secondary text-start js-bulk-action" data-action="tracking-upload" data-href="<?= site_url('admin/shipping/templates/tracking-upload') ?>">Toplu Takip No Yükleme (Excel)</button>
          <button type="button" class="btn btn-light-secondary text-start js-bulk-action" data-action="manifest" data-endpoint="<?= site_url('admin/shipping/bulk/manifest') ?>">Manifesto Oluştur</button>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">İade & Problem Yönetimi</h5>
      </div>
      <div class="card-body d-grid gap-2">
        <button type="button" class="btn btn-outline-warning">İade talebi oluştur (Yakında)</button>
        <button type="button" class="btn btn-outline-danger">Kayıp kargo bildirimi (Yakında)</button>
        <button type="button" class="btn btn-outline-secondary">Hasarlı ürün bildirimi (Yakında)</button>
        <button type="button" class="btn btn-outline-primary">Yazışma geçmişi (Yakında)</button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.bootstrap5.min.js') ?>"></script>
<script>
  (function () {
    function initShippingPage() {
      var activeKpiFilter = '';
      var activeTrackCompany = '';
      var activeTrackNo = '';
      var packingLabelBase = "<?= site_url('admin/orders') ?>";
      var csrfTokenName = "<?= csrf_token() ?>";
      var csrfHash = "<?= csrf_hash() ?>";
      var selectedOrderIds = {};

      function extractOrderIdFromActions(actionsHtml) {
        var html = String(actionsHtml || '');
        var match = html.match(/admin\/orders\/([^"'/?#]+)/i);
        return match && match[1] ? String(match[1]).trim() : '';
      }

      function buildPackingLabelUrl(orderId) {
        if (!orderId) {
          return '#';
        }

        return packingLabelBase.replace(/\/+$/, '') + '/' + encodeURIComponent(orderId) + '/packing/label';
      }

      var table = $('#shippingTable').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[6, 'desc']],
        dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        ajax: {
          url: "<?= site_url('admin/api/shipping') ?>",
          type: 'GET',
          data: function (d) {
            if (activeKpiFilter !== '') {
              d.kpi_filter = activeKpiFilter;
            } else if (Object.prototype.hasOwnProperty.call(d, 'kpi_filter')) {
              delete d.kpi_filter;
            }

            if (activeTrackCompany !== '') {
              d.track_company = activeTrackCompany;
            } else if (Object.prototype.hasOwnProperty.call(d, 'track_company')) {
              delete d.track_company;
            }

            if (activeTrackNo !== '') {
              d.track_no = activeTrackNo;
            } else if (Object.prototype.hasOwnProperty.call(d, 'track_no')) {
              delete d.track_no;
            }
          }
        },
        columns: [
          {
            data: 'order_id',
            name: 'order_id',
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function (data) {
              var orderId = String(data || '').trim();
              if (orderId === '') {
                return '';
              }
              var checked = selectedOrderIds[orderId] ? ' checked' : '';
              return '<input type="checkbox" class="form-check-input js-shipping-select" data-order-id="' + orderId + '"' + checked + '>';
            }
          },
          { data: 'order_no', name: 'order_no' },
          { data: 'customer_name', name: 'customer_name' },
          { data: 'shipping_company', name: 'shipping_company' },
          { data: 'tracking_no', name: 'tracking_no' },
          { data: 'shipping_status', name: 'shipping_status', orderable: false, searchable: false },
          { data: 'updated_at', name: 'updated_at' },
          {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
              var html = String(data || '');
              var orderId = row && row.order_id ? String(row.order_id).trim() : extractOrderIdFromActions(html);
              if (orderId !== '') {
                html += ' <a href="' + buildPackingLabelUrl(orderId) + '" class="btn btn-sm btn-primary">Etiket</a>';
              }
              return html;
            }
          }
        ],
        language: {
          lengthMenu: '_MENU_ kayıt göster',
          search: 'Ara:',
          zeroRecords: 'Henüz kayıt yok',
          info: '_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor',
          infoEmpty: '0 kayıttan 0 - 0 arası gösteriliyor',
          infoFiltered: '(_MAX_ kayıt içinden filtrelendi)',
          paginate: { first: 'İlk', last: 'Son', next: 'Sonraki', previous: 'Önceki' },
          processing: 'Yükleniyor...'
        }
      });

      function updateTopPackingLabelButton() {
        var button = document.getElementById('btnPackingLabel');
        if (!button) {
          return;
        }

        var rows = table.rows({ page: 'current' }).data().toArray();
        var firstOrderId = '';
        for (var i = 0; i < rows.length; i++) {
          firstOrderId = String((rows[i] && rows[i].order_id) || '').trim();
          if (firstOrderId === '') {
            firstOrderId = extractOrderIdFromActions(rows[i] && rows[i].actions);
          }
          if (firstOrderId !== '') {
            break;
          }
        }

        if (firstOrderId === '') {
          button.setAttribute('href', '#');
          button.classList.add('disabled');
          button.setAttribute('aria-disabled', 'true');
          return;
        }

        button.setAttribute('href', buildPackingLabelUrl(firstOrderId));
        button.classList.remove('disabled');
        button.setAttribute('aria-disabled', 'false');
      }

      table.on('draw', function () {
        updateTopPackingLabelButton();
        syncSelectAllCheckbox();
      });

      function getSelectedOrderIds() {
        return Object.keys(selectedOrderIds);
      }

      function showBulkAlert(message) {
        var alertBox = document.getElementById('bulkShippingAlert');
        if (!alertBox) {
          return;
        }
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
      }

      function hideBulkAlert() {
        var alertBox = document.getElementById('bulkShippingAlert');
        if (!alertBox) {
          return;
        }
        alertBox.classList.add('d-none');
        alertBox.textContent = '';
      }

      function syncSelectAllCheckbox() {
        var selectAll = document.getElementById('shippingSelectAll');
        if (!selectAll) {
          return;
        }

        var $checkboxes = $('#shippingTable tbody .js-shipping-select');
        var total = $checkboxes.length;
        var checked = $('#shippingTable tbody .js-shipping-select:checked').length;

        selectAll.checked = total > 0 && checked === total;
        selectAll.indeterminate = checked > 0 && checked < total;
      }

      function submitBulkPost(url, orderIds) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.target = '_blank';
        form.style.display = 'none';

        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = csrfTokenName;
        csrfInput.value = csrfHash;
        form.appendChild(csrfInput);

        for (var i = 0; i < orderIds.length; i++) {
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'order_ids[]';
          input.value = orderIds[i];
          form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
        form.remove();
      }

      function clearAllSearches() {
        table.search('');
        var count = table.columns().count();
        for (var i = 0; i < count; i++) {
          table.column(i).search('');
        }
      }

      function applyKpiFilter(filterName) {
        clearAllSearches();
        activeTrackCompany = '';
        activeTrackNo = '';
        activeKpiFilter = filterName;
        table.ajax.reload(null, false);
      }

      function normalizeTrackCompany(companyValue) {
        if (companyValue === 'yurtici') {
          return 'Yurtiçi Kargo';
        }
        if (companyValue === 'aras') {
          return 'Aras Kargo';
        }
        if (companyValue === 'mng') {
          return 'MNG Kargo';
        }
        if (companyValue === 'ptt') {
          return 'PTT Kargo';
        }

        return companyValue;
      }

      function applyTrackSearch() {
        var companyValue = String($('#shipping_company').val() || '').trim();
        var trackNoValue = String($('#tracking_number').val() || '').trim();

        activeTrackCompany = normalizeTrackCompany(companyValue);
        activeTrackNo = trackNoValue;
        activeKpiFilter = '';

        clearAllSearches();
        table.ajax.reload(null, false);
      }

      document.addEventListener('click', function (event) {
        var card = event.target.closest('[data-kpi-filter]');
        if (!card) {
          return;
        }

        var filterName = card.getAttribute('data-kpi-filter') || '';
        if (filterName === '') {
          return;
        }

        applyKpiFilter(filterName);
      });

      $('#shippingTrackForm').on('submit', function (event) {
        event.preventDefault();
        applyTrackSearch();
      });

      $('#btnShippingTrackSearch').on('click', function (event) {
        event.preventDefault();
        applyTrackSearch();
      });

      $('#btnShippingRefresh').on('click', function () {
        clearAllSearches();
        activeKpiFilter = '';
        activeTrackCompany = '';
        activeTrackNo = '';
        table.ajax.reload(null, false);
      });

      $('#shippingTable tbody').on('change', '.js-shipping-select', function () {
        var orderId = String($(this).data('order-id') || '').trim();
        if (orderId === '') {
          return;
        }

        if (this.checked) {
          selectedOrderIds[orderId] = true;
        } else {
          delete selectedOrderIds[orderId];
        }

        syncSelectAllCheckbox();
        hideBulkAlert();
      });

      $('#shippingSelectAll').on('change', function () {
        var checked = !!this.checked;
        $('#shippingTable tbody .js-shipping-select').each(function () {
          var orderId = String($(this).data('order-id') || '').trim();
          if (orderId === '') {
            return;
          }

          this.checked = checked;
          if (checked) {
            selectedOrderIds[orderId] = true;
          } else {
            delete selectedOrderIds[orderId];
          }
        });

        syncSelectAllCheckbox();
        hideBulkAlert();
      });

      $('.js-bulk-action').on('click', function () {
        var orderIds = getSelectedOrderIds();
        if (orderIds.length === 0) {
          showBulkAlert('Lütfen en az bir gönderi seçin.');
          return;
        }

        hideBulkAlert();
        var action = String($(this).data('action') || '');

        if (action === 'tracking-upload') {
          window.location.href = String($(this).data('href') || '#');
          return;
        }

        var endpoint = String($(this).data('endpoint') || '');
        if (endpoint === '') {
          return;
        }

        submitBulkPost(endpoint, orderIds);
      });

      if (typeof ApexCharts === 'undefined') {
        $('#avgDeliveryChart').html('<p class="text-muted mb-0">Grafik kütüphanesi bulunamadı. Yakında canlı veri.</p>');
        $('#carrierPerformanceChart').html('<p class="text-muted mb-0">Grafik kütüphanesi bulunamadı. Yakında canlı veri.</p>');
        return;
      }

      var avgDeliveryChart = new ApexCharts(document.querySelector('#avgDeliveryChart'), {
        chart: { type: 'area', height: 220, toolbar: { show: false } },
        series: [{ name: 'Teslim Süresi', data: [2.4, 2.2, 2.8, 2.1, 2.5, 2.3, 2.0] }],
        xaxis: { categories: ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'] },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        noData: { text: 'Yakında canlı veri' }
      });
      avgDeliveryChart.render();

      var carrierPerformanceChart = new ApexCharts(document.querySelector('#carrierPerformanceChart'), {
        chart: { type: 'bar', height: 220, toolbar: { show: false } },
        series: [{ name: 'Başarı', data: [86, 82, 79, 75] }],
        xaxis: { categories: ['Yurtiçi', 'Aras', 'MNG', 'PTT'] },
        dataLabels: { enabled: false },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
        noData: { text: 'Yakında canlı veri' }
      });
      carrierPerformanceChart.render();
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initShippingPage);
    } else {
      initShippingPage();
    }
  })();
</script>
<?= $this->endSection() ?>
