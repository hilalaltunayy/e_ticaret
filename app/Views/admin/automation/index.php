<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Otomasyon & Akıllı Kurallar') ?></h2>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Aktif Kurallar</h6>
        <h4 class="mb-0">0</h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Şehir Bazlı Atama</h6>
        <span class="badge bg-light-secondary text-secondary">Kapalı</span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Desi Bazlı Kurallar</h6>
        <span class="badge bg-light-secondary text-secondary">Kapalı</span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-1 text-muted">SLA Optimizasyonu</h6>
        <span class="badge bg-light-warning text-warning">Beta / Yakında</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-xl-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Şehre Göre Otomatik Kargo Seçimi</h5>
        <div class="form-check form-switch m-0">
          <input class="form-check-input" type="checkbox">
        </div>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
            <span>İstanbul → Firma A</span>
            <span class="badge bg-light-secondary text-secondary">Placeholder</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Desi (Hacim) Bazlı Kurallar</h5>
        <div class="form-check form-switch m-0">
          <input class="form-check-input" type="checkbox">
        </div>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <li class="list-group-item px-0">0-5 desi → Firma X</li>
          <li class="list-group-item px-0">5-15 desi → Firma Y</li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Kapıda Ödeme Kuralları</h5>
        <div class="form-check form-switch m-0">
          <input class="form-check-input" type="checkbox">
        </div>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <li class="list-group-item px-0">Kapıda ödeme → Sadece Firma Z</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">SLA (Teslim Süresi) Optimizasyonu (Veri Temelli)</h5>
      </div>
      <div class="card-body">
        <p class="text-muted">Sistem, şehir/firma gecikme oranlarını analiz ederek öneri üretir. Bu bölüm entegrasyon sonrası otomatik çalışacaktır.</p>
        <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="min-height: 180px;">
          <span class="text-muted">Mini chart alanı - Yakında</span>
        </div>
        <div class="mt-3">
          <button type="button" class="btn btn-outline-primary" disabled>Önerileri Hesapla (Yakında)</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
