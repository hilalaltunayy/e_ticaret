<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$banners = is_array($banners ?? null) ? $banners : [];
$summary = is_array($summary ?? null) ? $summary : ['total' => 0, 'active' => 0, 'passive' => 0];
$bannerTypes = is_array($bannerTypes ?? null) ? $bannerTypes : [];
$defaultBanner = is_array($defaultBanner ?? null) ? $defaultBanner : [];
$selectedBanner = is_array($selectedBanner ?? null) ? $selectedBanner : null;
$validation = session('validation');
$typeDescriptions = [
    'hero' => 'Büyük tanıtım alanı. Vurgulu başlık, açıklama ve buton kullanımı için uygun.',
    'inline' => 'Orta boy yatay kullanım. İç sayfalarda akışı kesmeden kampanya ya da yönlendirme göstermek için uygun.',
    'announcement' => 'Daha sade ve ince bilgi alanı. Duyuru, teslimat veya kısa bilgilendirme mesajları için uygun.',
];
$initialBanner = old('banner_name') !== null ? [
    'id' => (string) old('banner_id', ''),
    'banner_name' => (string) old('banner_name', ''),
    'banner_type' => (string) old('banner_type', 'hero'),
    'title' => (string) old('title', ''),
    'subtitle' => (string) old('subtitle', ''),
    'image_path' => (string) old('image_path', ''),
    'button_text' => (string) old('button_text', ''),
    'button_link' => (string) old('button_link', ''),
    'display_order' => (int) old('display_order', 0),
    'is_active' => (int) old('is_active', 1),
    'created_at_label' => (string) ($selectedBanner['created_at_label'] ?? 'Henüz kaydedilmedi'),
    'updated_at_label' => (string) ($selectedBanner['updated_at_label'] ?? 'Henüz kaydedilmedi'),
] : ($selectedBanner ?? $defaultBanner);
$initialBannerJson = json_encode($initialBanner, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$typeDescriptionsJson = json_encode($typeDescriptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>

<div class="page-header pb-0">
  <div class="page-block">
    <div class="row align-items-start g-3">
      <div class="col-lg-8">
        <div class="page-header-title">
          <h2 class="mb-2"><?= esc($title ?? 'Banner Yönetimi') ?></h2>
        </div>
        <p class="text-muted mb-2">Banner içeriklerini bağımsız olarak yönetin, aktif/pasif durumunu kontrol edin ve küçük admin önizlemesi ile hızlıca gözden geçirin.</p>
        <ul class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item" aria-current="page">Banner Yönetimi</li>
        </ul>
      </div>
      <div class="col-lg-4 d-flex justify-content-lg-end align-items-start">
        <button type="button" class="btn btn-primary" data-banner-create data-bs-toggle="offcanvas" data-bs-target="#bannerOffcanvas" aria-controls="bannerOffcanvas">Yeni Banner</button>
      </div>
    </div>
  </div>
</div>

<?php if (session('success')): ?>
  <div class="alert alert-success"><?= esc((string) session('success')) ?></div>
<?php endif; ?>
<?php if (session('error')): ?>
  <div class="alert alert-danger"><?= esc((string) session('error')) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-4">
    <div class="card statistics-card-1 mb-0"><div class="card-body py-3"><h6 class="mb-1 text-muted">Toplam Banner</h6><h4 class="mb-0"><?= number_format((int) ($summary['total'] ?? 0)) ?></h4></div></div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card statistics-card-1 mb-0"><div class="card-body py-3"><h6 class="mb-1 text-muted">Aktif Banner</h6><h4 class="mb-0"><?= number_format((int) ($summary['active'] ?? 0)) ?></h4></div></div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card statistics-card-1 mb-0"><div class="card-body py-3"><h6 class="mb-1 text-muted">Pasif Banner</h6><h4 class="mb-0"><?= number_format((int) ($summary['passive'] ?? 0)) ?></h4></div></div>
  </div>
</div>

<div class="card">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-1">Banner Listesi</h5>
      <div class="text-muted small">Hero, ara ve duyuru banner kayıtlarını bu alandan yönetebilirsiniz.</div>
    </div>
    <button type="button" class="btn btn-light-primary" data-banner-create data-bs-toggle="offcanvas" data-bs-target="#bannerOffcanvas" aria-controls="bannerOffcanvas">Yeni Banner</button>
  </div>
  <div class="card-body">
    <?php if ($banners === []): ?>
      <div class="alert alert-light border mb-0">Henüz kayıtlı banner bulunmuyor. İlk banner kaydını oluşturarak başlayabilirsiniz.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($banners as $banner): ?>
          <?php
          $cardPreviewClass = match ((string) ($banner['banner_type'] ?? 'hero')) {
              'announcement' => 'border-start border-4 border-warning-subtle bg-light-warning',
              'inline' => 'border-start border-4 border-info-subtle bg-light-primary',
              default => 'border-start border-4 border-primary-subtle bg-light-primary',
          };
          ?>
          <div class="col-12 col-xl-6">
            <div class="card border shadow-none h-100 mb-0">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                  <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                      <h5 class="mb-0"><?= esc((string) ($banner['banner_name'] ?? 'İsimsiz Banner')) ?></h5>
                      <span class="badge bg-light-secondary text-secondary border"><?= esc((string) ($banner['banner_type_label'] ?? '-')) ?></span>
                      <span class="badge <?= (int) ($banner['is_active'] ?? 0) === 1 ? 'bg-light-success text-success' : 'bg-light-danger text-danger' ?>">
                        <?= (int) ($banner['is_active'] ?? 0) === 1 ? 'Aktif' : 'Pasif' ?>
                      </span>
                    </div>
                    <div class="text-muted small">Sıra: <?= esc((string) ($banner['display_order'] ?? 0)) ?> • Güncelleme: <?= esc((string) ($banner['updated_at_label'] ?? '-')) ?></div>
                  </div>
                </div>

                <div class="rounded border p-3 mb-3 <?= esc($cardPreviewClass) ?>">
                  <div class="small text-uppercase text-muted mb-2">Kısa Preview</div>
                  <div class="fw-semibold mb-1"><?= esc((string) ($banner['title'] ?? 'Başlık yok')) ?></div>
                  <div class="text-muted small mb-2"><?= esc((string) ($banner['subtitle'] ?? 'Alt başlık veya kısa açıklama eklenmedi.')) ?></div>
                  <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <span class="badge bg-light-secondary text-secondary border"><?= ! empty($banner['image_path']) ? esc((string) $banner['image_path']) : 'Görsel yolu eklenmedi' ?></span>
                    <?php if (! empty($banner['button_text'])): ?>
                      <span class="badge bg-primary"><?= esc((string) $banner['button_text']) ?></span>
                    <?php else: ?>
                      <span class="badge bg-light-secondary text-secondary border">Buton kullanılmıyor</span>
                    <?php endif; ?>
                  </div>
                  <div class="small text-muted"><?= ! empty($banner['button_link']) ? esc((string) $banner['button_link']) : 'Buton linki tanımlanmadı' ?></div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-outline-primary" data-banner-item='<?= esc(json_encode($banner, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), 'attr') ?>' data-bs-toggle="offcanvas" data-bs-target="#bannerOffcanvas" aria-controls="bannerOffcanvas">Düzenle</button>
                  <form action="<?= site_url('admin/banners/toggle/' . rawurlencode((string) ($banner['id'] ?? ''))) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-secondary"><?= (int) ($banner['is_active'] ?? 0) === 1 ? 'Pasife Al' : 'Aktife Al' ?></button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="bannerOffcanvas" aria-labelledby="bannerOffcanvasLabel" style="width:min(560px, 100%);">
  <div class="offcanvas-header border-bottom">
    <div>
      <h5 id="bannerOffcanvasLabel" class="mb-1">Banner Düzenleyici</h5>
      <div class="text-muted small">Banner içeriklerini kaydedin ve küçük önizleme ile kontrol edin.</div>
    </div>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
  </div>
  <div class="offcanvas-body">
    <?php if (is_array($validation) && $validation !== []): ?>
      <div class="alert alert-danger">
        <?php foreach ($validation as $message): ?>
          <div><?= esc((string) $message) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form action="<?= site_url('admin/banners/save') ?>" method="post" id="bannerEditorForm">
      <?= csrf_field() ?>
      <input type="hidden" name="banner_id" id="banner_id" value="<?= esc((string) ($initialBanner['id'] ?? '')) ?>">

      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Banner Adı</label>
          <input type="text" name="banner_name" id="banner_name" class="form-control" value="<?= esc((string) ($initialBanner['banner_name'] ?? '')) ?>" required>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Banner Tipi</label>
          <select name="banner_type" id="banner_type" class="form-select" required>
            <?php foreach ($bannerTypes as $value => $label): ?>
              <option value="<?= esc((string) $value) ?>" <?= (string) ($initialBanner['banner_type'] ?? 'hero') === (string) $value ? 'selected' : '' ?>><?= esc((string) $label) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text" data-type-help><?= esc((string) ($typeDescriptions[$initialBanner['banner_type'] ?? 'hero'] ?? '')) ?></div>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Sıra</label>
          <input type="number" min="0" max="999" name="display_order" id="display_order" class="form-control" value="<?= esc((string) ($initialBanner['display_order'] ?? 0)) ?>" required>
          <div class="form-text">Küçük değerler listede üstte görünür.</div>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Durum</label>
          <select name="is_active" id="is_active" class="form-select">
            <option value="1" <?= (int) ($initialBanner['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= (int) ($initialBanner['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Pasif</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Başlık</label>
          <input type="text" name="title" id="title" class="form-control" value="<?= esc((string) ($initialBanner['title'] ?? '')) ?>" required>
          <div class="form-text">Preview’da öne çıkan ana metin olarak gösterilir.</div>
        </div>
        <div class="col-12">
          <label class="form-label">Alt Başlık / Kısa Açıklama</label>
          <textarea name="subtitle" id="subtitle" rows="4" class="form-control"><?= esc((string) ($initialBanner['subtitle'] ?? '')) ?></textarea>
          <div class="form-text">Kısa açıklama, kampanya ya da duyuru metni için kullanılabilir.</div>
        </div>
        <div class="col-12">
          <label class="form-label">Görsel Yolu</label>
          <input type="text" name="image_path" id="image_path" class="form-control" value="<?= esc((string) ($initialBanner['image_path'] ?? '')) ?>" placeholder="uploads/banners/hero-01.jpg">
          <div class="form-text">Şimdilik görsel kütüphanesi yerine dosya yolu kullanılır. Örn: <code>uploads/banners/hero-01.jpg</code></div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Buton Metni</label>
          <input type="text" name="button_text" id="button_text" class="form-control" value="<?= esc((string) ($initialBanner['button_text'] ?? '')) ?>">
          <div class="form-text">Boş bırakılırsa preview’da buton gösterimi kapalı kabul edilir.</div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Buton Linki</label>
          <input type="text" name="button_link" id="button_link" class="form-control" value="<?= esc((string) ($initialBanner['button_link'] ?? '')) ?>" placeholder="/kampanyalar">
          <div class="form-text">İç link ya da tam URL kullanılabilir. Örn: <code>/kampanyalar</code></div>
        </div>
      </div>
    </form>

    <div class="border rounded p-3 bg-light mt-4" data-preview-shell>
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Admin Preview</h6>
        <span class="badge bg-light-secondary text-secondary border" data-preview-type>Hero Banner</span>
      </div>
      <div class="small text-uppercase text-muted mb-2" data-preview-tone>Tanıtım Alanı</div>
      <div class="rounded border p-3 p-md-4 bg-white" data-preview-stage>
        <div class="row g-3 align-items-center">
          <div class="col-12 col-md-7">
            <div class="fw-semibold mb-2" data-preview-title>Banner başlığı</div>
            <div class="text-muted small mb-3" data-preview-subtitle>Alt başlık veya kısa açıklama burada görünecek.</div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <span class="badge bg-primary" data-preview-button>Buton</span>
              <span class="badge bg-light-secondary text-secondary border" data-preview-link>/kampanyalar</span>
            </div>
          </div>
          <div class="col-12 col-md-5">
            <div class="rounded border bg-light p-3 small text-muted h-100 d-flex align-items-center justify-content-center text-center" data-preview-image>
              Görsel yolu
            </div>
          </div>
        </div>
      </div>
      <div class="small text-muted mt-3">Oluşturulma: <span data-preview-created><?= esc((string) ($initialBanner['created_at_label'] ?? '-')) ?></span></div>
      <div class="small text-muted">Güncellenme: <span data-preview-updated><?= esc((string) ($initialBanner['updated_at_label'] ?? '-')) ?></span></div>
    </div>
  </div>
  <div class="offcanvas-footer border-top p-3 d-flex justify-content-between align-items-center">
    <div class="text-muted small">Banner kaydı oluşturulduktan sonra listede düzenlenebilir ve aktif/pasif yapılabilir.</div>
    <button type="submit" class="btn btn-primary" form="bannerEditorForm">Kaydet</button>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var offcanvasElement = document.getElementById('bannerOffcanvas');
  var bootstrapApi = window.bootstrap || null;
  if (!offcanvasElement || !bootstrapApi || typeof bootstrapApi.Offcanvas === 'undefined') {
    return;
  }

  var currentBanner = <?= $initialBannerJson ?: '{}' ?>;
  var typeLabels = <?= json_encode($bannerTypes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  var typeDescriptions = <?= $typeDescriptionsJson ?: '{}' ?>;
  var defaultBanner = <?= json_encode($defaultBanner, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  var offcanvas = bootstrapApi.Offcanvas.getOrCreateInstance(offcanvasElement);

  var fields = {
    id: document.getElementById('banner_id'),
    name: document.getElementById('banner_name'),
    type: document.getElementById('banner_type'),
    title: document.getElementById('title'),
    subtitle: document.getElementById('subtitle'),
    imagePath: document.getElementById('image_path'),
    buttonText: document.getElementById('button_text'),
    buttonLink: document.getElementById('button_link'),
    order: document.getElementById('display_order'),
    active: document.getElementById('is_active')
  };

  var preview = {
    shell: offcanvasElement.querySelector('[data-preview-shell]'),
    stage: offcanvasElement.querySelector('[data-preview-stage]'),
    type: offcanvasElement.querySelector('[data-preview-type]'),
    tone: offcanvasElement.querySelector('[data-preview-tone]'),
    title: offcanvasElement.querySelector('[data-preview-title]'),
    subtitle: offcanvasElement.querySelector('[data-preview-subtitle]'),
    button: offcanvasElement.querySelector('[data-preview-button]'),
    link: offcanvasElement.querySelector('[data-preview-link]'),
    image: offcanvasElement.querySelector('[data-preview-image]'),
    created: offcanvasElement.querySelector('[data-preview-created]'),
    updated: offcanvasElement.querySelector('[data-preview-updated]')
  };
  var typeHelp = offcanvasElement.querySelector('[data-type-help]');

  function applyBannerData(banner) {
    var data = banner || {};
    fields.id.value = data.id || '';
    fields.name.value = data.banner_name || '';
    fields.type.value = data.banner_type || 'hero';
    fields.title.value = data.title || '';
    fields.subtitle.value = data.subtitle || '';
    fields.imagePath.value = data.image_path || '';
    fields.buttonText.value = data.button_text || '';
    fields.buttonLink.value = data.button_link || '';
    fields.order.value = data.display_order != null ? data.display_order : 0;
    fields.active.value = String(data.is_active != null ? data.is_active : 1);
    preview.created.textContent = data.created_at_label || 'Henüz kaydedilmedi';
    preview.updated.textContent = data.updated_at_label || 'Henüz kaydedilmedi';
    refreshPreview();
  }

  function refreshPreview() {
    var selectedType = fields.type.value || 'hero';
    var typeMeta = {
      hero: {
        tone: 'Büyük tanıtım alanı',
        stageClass: 'border-primary-subtle bg-light-primary',
        titleClass: 'fs-4',
        imageText: 'Hero görsel alanı',
        buttonClass: 'bg-primary'
      },
      inline: {
        tone: 'Yatay ara banner',
        stageClass: 'border-info-subtle bg-light',
        titleClass: 'fs-5',
        imageText: 'Ara banner görsel alanı',
        buttonClass: 'bg-info'
      },
      announcement: {
        tone: 'Sade duyuru alanı',
        stageClass: 'border-warning-subtle bg-light-warning',
        titleClass: 'fs-6 text-uppercase',
        imageText: 'Duyuru görsel alanı',
        buttonClass: 'bg-warning text-dark'
      }
    }[selectedType] || {
      tone: 'Banner alanı',
      stageClass: 'border-secondary-subtle bg-light',
      titleClass: 'fs-5',
      imageText: 'Görsel alanı',
      buttonClass: 'bg-primary'
    };

    preview.type.textContent = typeLabels[selectedType] || 'Banner';
    preview.tone.textContent = typeMeta.tone;
    preview.stage.className = 'rounded border p-3 p-md-4 ' + typeMeta.stageClass;
    preview.title.className = 'fw-semibold mb-2 ' + typeMeta.titleClass;
    preview.title.textContent = fields.title.value.trim() || 'Banner başlığı';
    preview.subtitle.textContent = fields.subtitle.value.trim() || 'Alt başlık veya kısa açıklama burada görünecek.';
    preview.button.textContent = fields.buttonText.value.trim() || 'Buton kullanılmıyor';
    preview.button.className = 'badge ' + typeMeta.buttonClass;
    preview.button.style.display = fields.buttonText.value.trim() ? 'inline-flex' : 'none';
    preview.link.textContent = fields.buttonLink.value.trim() || 'Buton linki girilmedi';
    preview.link.style.display = fields.buttonText.value.trim() || fields.buttonLink.value.trim() ? 'inline-flex' : 'none';
    preview.image.textContent = fields.imagePath.value.trim() || typeMeta.imageText + '\nGörsel yolu girildiğinde burada görünür.';
    typeHelp.textContent = typeDescriptions[selectedType] || '';
  }

  document.querySelectorAll('[data-banner-item]').forEach(function (button) {
    button.addEventListener('click', function () {
      try {
        applyBannerData(JSON.parse(button.getAttribute('data-banner-item') || '{}'));
      } catch (error) {
        applyBannerData(currentBanner);
      }
    });
  });

  document.querySelectorAll('[data-banner-create]').forEach(function (button) {
    button.addEventListener('click', function () {
      applyBannerData(defaultBanner);
    });
  });

  Object.values(fields).forEach(function (field) {
    if (field) {
      field.addEventListener('input', refreshPreview);
      field.addEventListener('change', refreshPreview);
    }
  });

  applyBannerData(currentBanner);

  <?php if (! empty($drawerShouldOpen)): ?>
  offcanvas.show();
  <?php endif; ?>
});
</script>
<?= $this->endSection() ?>
