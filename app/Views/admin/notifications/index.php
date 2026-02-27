<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Bildirim Yönetimi') ?></h2>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-2 text-muted">SMS Gönderimi</h6>
        <span class="badge bg-light-secondary text-secondary">Kapalı</span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-2 text-muted">E-posta Bildirimi</h6>
        <span class="badge bg-light-warning text-warning">Durum: Placeholder</span>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-2 text-muted">WhatsApp Bildirimi</h6>
        <span class="badge bg-light-secondary text-secondary">Kapalı</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Otomatik Mesaj Şablonları</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Olay</th>
                <th>Kanal</th>
                <th>Şablon</th>
                <th>Durum</th>
                <th>İşlem</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Kargonuz dağıtıma çıktı</td>
                <td>SMS</td>
                <td>Merhaba, siparişiniz bugün dağıtıma çıkmıştır.</td>
                <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox"></div></td>
                <td><button type="button" class="btn btn-sm btn-light-secondary" disabled>Düzenle (Yakında)</button></td>
              </tr>
              <tr>
                <td>Kargoya verildi</td>
                <td>E-posta</td>
                <td>Siparişiniz kargoya verildi, takip kodunuz ektedir.</td>
                <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" checked></div></td>
                <td><button type="button" class="btn btn-sm btn-light-secondary" disabled>Düzenle (Yakında)</button></td>
              </tr>
              <tr>
                <td>Teslim edildi</td>
                <td>WhatsApp</td>
                <td>Siparişiniz başarıyla teslim edilmiştir.</td>
                <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox"></div></td>
                <td><button type="button" class="btn btn-sm btn-light-secondary" disabled>Düzenle (Yakında)</button></td>
              </tr>
              <tr>
                <td>Gecikme bildirimi</td>
                <td>SMS</td>
                <td>Teslimat süresinde gecikme yaşanmaktadır, özür dileriz.</td>
                <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox"></div></td>
                <td><button type="button" class="btn btn-sm btn-light-secondary" disabled>Düzenle (Yakında)</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Entegrasyon Kartı</h5>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush mb-3">
          <li class="list-group-item px-0">SMS sağlayıcı: Tanımlı değil</li>
          <li class="list-group-item px-0">SMTP: Tanımlı değil</li>
          <li class="list-group-item px-0">WhatsApp Business API: Tanımlı değil</li>
        </ul>
        <button type="button" class="btn btn-outline-primary w-100" disabled>Entegrasyonu Başlat (Yakında)</button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
