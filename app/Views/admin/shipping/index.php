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
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Bugün Kargoya Verilen</h6>
        <h4 class="mb-0">0</h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Yolda</h6>
        <h4 class="mb-0">0</h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Teslim Edildi</h6>
        <h4 class="mb-0">0</h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Sorunlu / Geciken</h6>
        <h4 class="mb-0">0</h4>
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
          <li class="list-group-item d-flex justify-content-between px-0"><span>Hazırlanıyor</span><span class="badge bg-light-secondary text-secondary">0</span></li>
          <li class="list-group-item d-flex justify-content-between px-0"><span>Kargoda</span><span class="badge bg-light-primary text-primary">0</span></li>
          <li class="list-group-item d-flex justify-content-between px-0"><span>Teslim</span><span class="badge bg-light-success text-success">0</span></li>
          <li class="list-group-item d-flex justify-content-between px-0"><span>İade</span><span class="badge bg-light-warning text-warning">0</span></li>
          <li class="list-group-item d-flex justify-content-between px-0"><span>Geciken</span><span class="badge bg-light-danger text-danger">0</span></li>
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
        <form>
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
          <button type="button" class="btn btn-primary w-100">Sorgula</button>
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
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between px-0"><span>Yurtiçi Kargo</span><span class="badge bg-light-secondary text-secondary">Entegrasyon: Hazır Değil</span></li>
            <li class="list-group-item d-flex justify-content-between px-0"><span>Aras Kargo</span><span class="badge bg-light-secondary text-secondary">Entegrasyon: Hazır Değil</span></li>
            <li class="list-group-item d-flex justify-content-between px-0"><span>MNG Kargo</span><span class="badge bg-light-secondary text-secondary">Entegrasyon: Hazır Değil</span></li>
            <li class="list-group-item d-flex justify-content-between px-0"><span>Sürat Kargo</span><span class="badge bg-light-secondary text-secondary">Entegrasyon: Hazır Değil</span></li>
            <li class="list-group-item d-flex justify-content-between px-0"><span>PTT Kargo</span><span class="badge bg-light-secondary text-secondary">Entegrasyon: Hazır Değil</span></li>
            <li class="list-group-item d-flex justify-content-between px-0"><span>UPS</span><span class="badge bg-light-secondary text-secondary">Entegrasyon: Hazır Değil</span></li>
          </ul>
          <div class="mt-3">
            <a href="<?= site_url('admin/shipping/companies/create?return_url=' . urlencode(site_url('admin/shipping'))) ?>" class="btn btn-primary w-100">+ Firma Ekle</a>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h5 class="mb-0">Toplu Kargo İşlemleri</h5>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush mb-3">
          <li class="list-group-item px-0">Toplu etiket oluşturma</li>
          <li class="list-group-item px-0">Barkod çıktısı</li>
          <li class="list-group-item px-0">Toplu takip no yükleme (Excel)</li>
          <li class="list-group-item px-0">Otomatik kargo atama (desi/şehir)</li>
          <li class="list-group-item px-0">Manifesto oluşturma</li>
        </ul>
        <div class="d-grid gap-2">
          <a href="#" class="btn btn-light-secondary">Excel Şablonu İndir (Yakında)</a>
          <button type="button" class="btn btn-outline-secondary" disabled>Toplu Yükleme (Yakında)</button>
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
    var table = $('#shippingTable').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[5, 'desc']],
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      ajax: {
        url: "<?= site_url('admin/api/shipping') ?>",
        type: 'GET'
      },
      columns: [
        { data: 'order_no', name: 'order_no' },
        { data: 'customer_name', name: 'customer_name' },
        { data: 'shipping_company', name: 'shipping_company' },
        { data: 'tracking_no', name: 'tracking_no' },
        { data: 'shipping_status', name: 'shipping_status', orderable: false, searchable: false },
        { data: 'updated_at', name: 'updated_at' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
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

    $('#btnShippingRefresh').on('click', function () {
      table.ajax.reload(null, false);
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
  })();
</script>
<?= $this->endSection() ?>
