<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<style>
  .summary-card-clickable { cursor: pointer; transition: all .2s ease; }
  .summary-card-clickable:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1); }
  .summary-card-active { outline: 2px solid rgba(13,110,253,.35); }
  .orders-trend-range .btn.active { background-color: #0d6efd; color: #fff; border-color: #0d6efd; }
  #ordersTrendChart { min-height: 320px; }
  #ordersStatusDistributionChart { min-height: 320px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $summary = $summary ?? []; ?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item" aria-current="page">Siparişler</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0">Siparişler</h2>
        </div>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0"></div>
    </div>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4" id="ordersSummaryCards">
  <div class="col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden summary-card-clickable js-summary-card" data-smart-filter="all">
      <div class="card-body">
        <h5 class="mb-1">Toplam Sipariş</h5>
        <h4 class="mb-0" id="summary-total"><?= esc((string) ($summary['total'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden summary-card-clickable js-summary-card" data-smart-filter="pending">
      <div class="card-body">
        <h5 class="mb-1">Bekleyen / Hazırlık</h5>
        <h4 class="mb-0" id="summary-pending"><?= esc((string) ($summary['pending'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden summary-card-clickable js-summary-card" data-smart-filter="shipped">
      <div class="card-body">
        <h5 class="mb-1">Kargoda / Teslim</h5>
        <h4 class="mb-0" id="summary-shipped"><?= esc((string) ($summary['shipped'] ?? 0)) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden summary-card-clickable js-summary-card" data-smart-filter="returned_cancelled">
      <div class="card-body">
        <h5 class="mb-1">İade / İptal</h5>
        <h4 class="mb-0" id="summary-returned-cancelled"><?= esc((string) ((int) ($summary['returned'] ?? 0) + (int) ($summary['cancelled'] ?? 0))) ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="mb-0">Sipariş Trendi</h5>
    <div class="btn-group orders-trend-range" role="group" aria-label="Sipariş trend aralığı">
      <button type="button" class="btn btn-sm btn-outline-primary js-orders-trend-range active" data-range="daily">Günlük</button>
      <button type="button" class="btn btn-sm btn-outline-primary js-orders-trend-range" data-range="weekly">Haftalık</button>
      <button type="button" class="btn btn-sm btn-outline-primary js-orders-trend-range" data-range="monthly">Aylık</button>
    </div>
  </div>
  <div class="card-body">
    <div id="ordersTrendChart"></div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">
    <h5 class="mb-0">Haftalık Sipariş Durum Dağılımı</h5>
  </div>
  <div class="card-body">
    <div id="ordersStatusDistributionChart"></div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="mb-0">Filtreler</h5>
    <button class="btn btn-light-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#advancedOrderFilters" aria-expanded="false" aria-controls="advancedOrderFilters">
      Gelişmiş Filtreler
    </button>
  </div>
  <div class="card-body">
    <form id="ordersFilterForm" class="row g-3 align-items-end">
      <input type="hidden" id="filter_order_statuses" name="filter_order_statuses" value="">

      <div class="col-12 col-md-6 col-lg-2">
        <label class="form-label" for="filter_order_no">Sipariş No</label>
        <input type="text" class="form-control" id="filter_order_no" name="filter_order_no" placeholder="ORD-...">
      </div>

      <div class="col-12 col-md-6 col-lg-2">
        <label class="form-label" for="filter_customer">Müşteri</label>
        <input type="text" class="form-control" id="filter_customer" name="filter_customer" placeholder="Ad / e-posta">
      </div>

      <div class="col-12 col-md-6 col-lg-2">
        <label class="form-label" for="filter_date_start">Başlangıç</label>
        <input type="date" class="form-control" id="filter_date_start" name="filter_date_start">
      </div>

      <div class="col-12 col-md-6 col-lg-2">
        <label class="form-label" for="filter_date_end">Bitiş</label>
        <input type="date" class="form-control" id="filter_date_end" name="filter_date_end">
      </div>

      <div class="col-12 col-md-6 col-lg-2">
        <label class="form-label" for="filter_order_status">Sipariş Durumu</label>
        <select class="form-select" id="filter_order_status" name="filter_order_status">
          <option value="">Tümü</option>
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

      <div class="col-12 col-md-6 col-lg-2 d-grid">
        <button type="button" id="btnFilterApply" class="btn btn-primary">Filtrele</button>
      </div>

      <div class="col-12">
        <div class="collapse" id="advancedOrderFilters">
          <div class="border rounded p-3 mt-1">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-md-4">
                <label class="form-label" for="filter_payment_method">Ödeme Türü</label>
                <select class="form-select" id="filter_payment_method" name="filter_payment_method">
                  <option value="">Tümü</option>
                  <option value="credit_card">Kredi Kartı</option>
                  <option value="bank_transfer">Havale / EFT</option>
                  <option value="cash_on_delivery">Kapıda Ödeme</option>
                  <option value="unknown">Bilinmiyor</option>
                </select>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label" for="filter_payment_status">Ödeme Durumu</label>
                <select class="form-select" id="filter_payment_status" name="filter_payment_status">
                  <option value="">Tümü</option>
                  <option value="unpaid">Ödenmedi</option>
                  <option value="paid">Ödendi</option>
                  <option value="refunded">İade Edildi</option>
                  <option value="partial_refund">Kısmi İade</option>
                  <option value="failed">Başarısız</option>
                </select>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label" for="filter_shipping_company">Kargo Firması</label>
                <input type="text" class="form-control" id="filter_shipping_company" name="filter_shipping_company" placeholder="Yurtiçi, Aras...">
              </div>

              <div class="col-12 d-flex justify-content-end">
                <button type="button" id="btnFilterReset" class="btn btn-light-secondary">Temizle</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card" id="ordersTableCard">
  <div class="card-body">
    <div class="dt-responsive table-responsive">
      <table id="ordersTable" class="table table-hover table-striped align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>Sipariş No</th>
            <th>Müşteri</th>
            <th>Tarih</th>
            <th>Tutar</th>
            <th>Ödeme Durumu</th>
            <th>Sipariş Durumu</th>
            <th>Kargo Durumu</th>
            <th>İşlemler</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.bootstrap5.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  (function () {
    var csrfTokenName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var activeTrendRange = 'daily';
    var trendChart = null;
    var trendPollTimer = null;
    var statusDistributionChart = null;
    var tlSymbol = $('<textarea/>').html('&#8378;').text();

    function updateCsrf(payload) {
      if (payload && payload.csrf && payload.csrf.hash) {
        csrfHash = payload.csrf.hash;
      }
    }

    function refreshSummaryCards(summary) {
      if (!summary) return;
      $('#summary-total').text(summary.total || 0);
      $('#summary-pending').text(summary.pending || 0);
      $('#summary-shipped').text(summary.shipped || 0);
      var returnedCancelled = (parseInt(summary.returned || 0, 10) + parseInt(summary.cancelled || 0, 10));
      $('#summary-returned-cancelled').text(returnedCancelled);
    }

    function fetchSummary() {
      $.get("<?= site_url('admin/orders/summary') ?>", function (res) {
        if (res && res.success) {
          refreshSummaryCards(res.summary || {});
          updateCsrf(res);
        }
      });
    }

    function setActiveTrendButton(range) {
      $('.js-orders-trend-range').removeClass('active');
      $('.js-orders-trend-range[data-range="' + range + '"]').addClass('active');
    }

    function startTrendPolling() {
      if (trendPollTimer) {
        clearInterval(trendPollTimer);
        trendPollTimer = null;
      }

      if (activeTrendRange !== 'daily') {
        return;
      }

      trendPollTimer = setInterval(function () {
        fetchTrendData('daily', true);
      }, 30000);
    }

    function fetchTrendData(range, keepRange) {
      var requestedRange = range || activeTrendRange;

      $.get("<?= site_url('admin/api/orders/analytics') ?>", { range: requestedRange }, function (res) {
        if (!res || !res.success || !trendChart) {
          return;
        }

        updateCsrf(res);

        if (!keepRange) {
          activeTrendRange = res.range || requestedRange;
        }

        trendChart.updateOptions({
          xaxis: {
            categories: res.categories || []
          }
        }, false, false, false);

        trendChart.updateSeries(res.series || [{ name: 'Toplam Tutar', data: [] }], true);
        setActiveTrendButton(activeTrendRange);
        startTrendPolling();
      });
    }

    function initTrendChart() {
      var el = document.querySelector('#ordersTrendChart');
      if (!el || typeof ApexCharts === 'undefined') {
        return;
      }

      trendChart = new ApexCharts(el, {
        chart: {
          type: 'line',
          height: 320,
          toolbar: { show: false },
          animations: { enabled: true }
        },
        stroke: {
          curve: 'smooth',
          width: 3
        },
        series: [{
          name: 'Toplam Tutar',
          data: []
        }],
        xaxis: {
          categories: [],
          labels: {
            rotate: -35
          }
        },
        yaxis: {
          labels: {
            formatter: function (value) {
              return Number(value || 0).toLocaleString('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              }) + ' ' + tlSymbol;
            }
          }
        },
        tooltip: {
          y: {
            formatter: function (value) {
              return Number(value || 0).toLocaleString('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              }) + ' ' + tlSymbol;
            }
          }
        },
        noData: {
          text: 'Veri yükleniyor...'
        }
      });

      trendChart.render();
      fetchTrendData('daily');
    }

    function fetchStatusDistribution() {
      $.get("<?= site_url('admin/api/orders/status-distribution') ?>", { range: 'weekly' }, function (res) {
        if (!res || !res.success || !statusDistributionChart) {
          return;
        }

        updateCsrf(res);

        statusDistributionChart.updateOptions({
          xaxis: {
            categories: res.categories || ['Son 7 Gün']
          }
        }, false, false, false);

        statusDistributionChart.updateSeries(res.series || [], true);
      });
    }

    function initStatusDistributionChart() {
      var el = document.querySelector('#ordersStatusDistributionChart');
      if (!el || typeof ApexCharts === 'undefined') {
        return;
      }

      statusDistributionChart = new ApexCharts(el, {
        chart: {
          type: 'bar',
          height: 320,
          stacked: true,
          toolbar: { show: false }
        },
        plotOptions: {
          bar: {
            horizontal: true,
            borderRadius: 4
          }
        },
        series: [],
        xaxis: {
          categories: ['Son 7 Gün'],
          title: {
            text: 'Sipariş Adedi'
          }
        },
        yaxis: {
          title: {
            text: 'Dönem'
          }
        },
        legend: {
          position: 'bottom'
        },
        dataLabels: {
          enabled: false
        },
        noData: {
          text: 'Veri yükleniyor...'
        }
      });

      statusDistributionChart.render();
      fetchStatusDistribution();
    }

    var table = $('#ordersTable').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[2, 'desc']],
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      ajax: {
        url: "<?= site_url('admin/api/orders') ?>",
        type: 'GET',
        data: function (d) {
          d.filter_order_no = $('#filter_order_no').val();
          d.filter_customer = $('#filter_customer').val();
          d.filter_date_start = $('#filter_date_start').val();
          d.filter_date_end = $('#filter_date_end').val();
          d.filter_order_status = $('#filter_order_status').val();
          d.filter_order_statuses = $('#filter_order_statuses').val();
          d.filter_payment_method = $('#filter_payment_method').val();
          d.filter_payment_status = $('#filter_payment_status').val();
          d.filter_shipping_company = $('#filter_shipping_company').val();
        }
      },
      columns: [
        { data: 'order_no', name: 'order_no' },
        { data: 'customer', name: 'customer' },
        { data: 'date', name: 'date' },
        { data: 'total_amount', name: 'total_amount' },
        { data: 'payment_status', orderable: false, searchable: false },
        { data: 'order_status', orderable: false, searchable: false },
        { data: 'shipping_status', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
      ],
      language: {
        lengthMenu: '_MENU_ kayıt göster',
        search: 'Ara:',
        zeroRecords: 'Kayıt bulunamadı',
        info: '_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor',
        infoEmpty: '0 kayıttan 0 - 0 arası gösteriliyor',
        infoFiltered: '(_MAX_ kayıt içinden filtrelendi)',
        paginate: { first: 'İlk', last: 'Son', next: 'Sonraki', previous: 'Önceki' },
        processing: 'Yükleniyor...'
      }
    });

    function smoothScrollToTable() {
      var el = document.getElementById('ordersTableCard');
      if (!el) return;
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function setActiveSummaryCard(key) {
      $('.js-summary-card').removeClass('summary-card-active');
      $('.js-summary-card[data-smart-filter="' + key + '"]').addClass('summary-card-active');
    }

    function applySmartFilter(key) {
      if (key === 'all') {
        document.getElementById('ordersFilterForm').reset();
        $('#filter_order_statuses').val('');
      } else if (key === 'pending') {
        $('#filter_order_status').val('');
        $('#filter_order_statuses').val('pending,preparing');
      } else if (key === 'shipped') {
        $('#filter_order_status').val('');
        $('#filter_order_statuses').val('shipped,delivered');
      } else if (key === 'returned_cancelled') {
        $('#filter_order_status').val('');
        $('#filter_order_statuses').val('cancelled,return_in_progress,return_done');
      }

      setActiveSummaryCard(key);
      table.ajax.reload(null, false);
      smoothScrollToTable();
    }

    $('#filter_order_status').on('change', function () {
      if ($(this).val() !== '') {
        $('#filter_order_statuses').val('');
      }
    });

    $('#btnFilterApply').on('click', function () {
      table.ajax.reload();
    });

    $('#btnFilterReset').on('click', function () {
      document.getElementById('ordersFilterForm').reset();
      $('#filter_order_statuses').val('');
      setActiveSummaryCard('all');
      table.ajax.reload();
    });

    $(document).on('click', '.js-summary-card', function () {
      var key = $(this).data('smart-filter') || 'all';
      applySmartFilter(key);
    });

    $(document).on('click', '.js-orders-trend-range', function () {
      var range = ($(this).data('range') || '').toString();
      if (!range || range === activeTrendRange) {
        return;
      }

      activeTrendRange = range;
      fetchTrendData(range);
    });

    $(document).on('click', '.js-inline-status-item', function (e) {
      e.preventDefault();
      var $item = $(this);
      var payload = {
        order_id: $item.data('order-id'),
        field: $item.data('field'),
        value: $item.data('value')
      };
      payload[csrfTokenName] = csrfHash;

      $.ajax({
        url: "<?= site_url('admin/orders/update-status') ?>",
        method: 'POST',
        dataType: 'json',
        data: payload
      }).done(function (res) {
        updateCsrf(res);
        if (!res || !res.success) {
          return;
        }

        table.ajax.reload(null, false);
        fetchStatusDistribution();
        if (res.summary) {
          refreshSummaryCards(res.summary);
        } else {
          fetchSummary();
        }
      }).fail(function (xhr) {
        if (xhr.responseJSON) {
          updateCsrf(xhr.responseJSON);
        }
      });
    });

    setActiveSummaryCard('all');
    initTrendChart();
    initStatusDistributionChart();
  })();
</script>
<?= $this->endSection() ?>
