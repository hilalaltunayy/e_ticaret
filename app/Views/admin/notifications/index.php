<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$selectedTemplate = $selectedTemplate ?? null;
$templateDefaults = $templateDefaults ?? [];
$templateOptions = $templateOptions ?? [];
$savedTemplates = $savedTemplates ?? [];
$recentEmailLogs = $recentEmailLogs ?? [];
$placeholderHelp = $placeholderHelp ?? [];
$selectedTemplateType = old('template_type', $selectedTemplate['template_type'] ?? 'custom');
$defaultTemplateSubject = (string) ($templateDefaults[$selectedTemplateType]['subject'] ?? 'BeAble Pro test e-postası');
$defaultTemplateMessage = (string) ($templateDefaults[$selectedTemplateType]['message'] ?? "Merhaba {name},\n\nBu alan manuel içerik için ayrılmıştır. Buraya kendi test mesajınızı güvenle yazabilir, konu ve metin üzerinde doğrudan düzenleme yapabilirsiniz.\n\nİletişim e-posta adresi: {email}");
$initialDrawerTemplate = [
    'id' => (string) old('template_id', $selectedTemplate['id'] ?? ''),
    'template_name' => (string) old('template_name', $selectedTemplate['template_name'] ?? ''),
    'template_type' => (string) $selectedTemplateType,
    'subject' => (string) old('template_subject', $selectedTemplate['subject'] ?? $defaultTemplateSubject),
    'message' => (string) old('template_message', $selectedTemplate['message'] ?? $defaultTemplateMessage),
    'is_active' => (string) old('template_is_active', isset($selectedTemplate['is_active']) ? ($selectedTemplate['is_active'] ? '1' : '0') : '1'),
    'created_at_label' => (string) ($selectedTemplate['created_at_label'] ?? 'Henüz kaydedilmedi'),
    'updated_at_label' => (string) ($selectedTemplate['updated_at_label'] ?? 'Henüz kaydedilmedi'),
    'template_type_label' => (string) ($selectedTemplate['template_type_label'] ?? ($templateOptions[$selectedTemplateType] ?? 'Manuel İçerik')),
];
$templateDefaultsJson = json_encode($templateDefaults, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$initialDrawerTemplateJson = json_encode($initialDrawerTemplate, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$drawerFlashError = (string) (session()->getFlashdata('error') ?? '');
$drawerFlashSuccess = (string) (session()->getFlashdata('success') ?? '');
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-sm-8">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'E-posta / SMS Gönderimi') ?></h2>
        </div>
        <div class="text-muted mt-2">E-posta ve SMS operasyonları için başlangıç yönetim paneli. Bu sürümde tekli test e-postası ve kayıtlı hazır şablon yönetimi desteklenir.</div>
      </div>
    </div>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <?php foreach (($deliveryChannels ?? []) as $channel): ?>
    <div class="col-12 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="rounded bg-light-primary d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;">
              <i class="<?= esc((string) ($channel['icon'] ?? 'ti ti-mail')) ?>"></i>
            </div>
            <span class="badge <?= esc((string) ($channel['statusClass'] ?? 'bg-light-secondary text-secondary')) ?>"><?= esc((string) ($channel['status'] ?? 'Yakında')) ?></span>
          </div>
          <h5 class="mb-2"><?= esc((string) ($channel['title'] ?? 'Kanal')) ?></h5>
          <p class="text-muted mb-0"><?= esc((string) ($channel['description'] ?? '')) ?></p>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-7">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Test E-posta Gönderimi</h5>
      </div>
      <div class="card-body">
        <form action="<?= site_url('admin/notifications/test-email') ?>" method="post" data-template-form="notification-email">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Şablon Tipi</label>
              <select name="test_email_template" class="form-select" data-template-select required>
                <?php $selectedTemplateOption = old('test_email_template', 'custom'); ?>
                <?php foreach ($templateOptions as $templateKey => $templateLabel): ?>
                  <option value="<?= esc((string) $templateKey) ?>" <?= $selectedTemplateOption === $templateKey ? 'selected' : '' ?>><?= esc((string) $templateLabel) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Alıcı Adı</label>
              <input type="text" name="test_email_name" class="form-control" value="<?= esc(old('test_email_name', '')) ?>" placeholder="Örn. Ayşe Yılmaz">
              <div class="form-text">{name} alanı boşsa güvenli varsayılan değer kullanılır.</div>
            </div>
            <div class="col-12">
              <label class="form-label">Alıcı E-posta</label>
              <input type="email" name="test_email_to" class="form-control" value="<?= esc(old('test_email_to', '')) ?>" placeholder="ornek@site.com" required>
            </div>
            <div class="col-12">
              <label class="form-label">Konu</label>
              <input type="text" name="test_email_subject" class="form-control" value="<?= esc(old('test_email_subject', 'BeAble Pro test e-postası')) ?>" placeholder="E-posta konusu" data-template-subject required>
            </div>
            <div class="col-12">
              <label class="form-label">Mesaj</label>
              <textarea name="test_email_message" rows="8" class="form-control" placeholder="Test e-posta mesajınızı yazın." data-template-message required><?= esc(old('test_email_message', "Merhaba {name},\n\nBu alan manuel içerik için ayrılmıştır. Buraya kendi test mesajınızı güvenle yazabilir, konu ve metin üzerinde doğrudan düzenleme yapabilirsiniz.\n\nİletişim e-posta adresi: {email}")) ?></textarea>
            </div>
          </div>
          <div class="alert alert-light border mt-3 mb-3">
            <div class="fw-semibold mb-2">Desteklenen Placeholder Alanları</div>
            <div class="d-flex flex-wrap gap-2 mb-2">
              <?php foreach ($placeholderHelp as $placeholderKey => $placeholderLabel): ?>
                <span class="badge bg-light-primary text-primary border"><?= esc((string) $placeholderKey) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="small text-muted">
              <?php foreach ($placeholderHelp as $placeholderKey => $placeholderLabel): ?>
                <div><span class="fw-semibold"><?= esc((string) $placeholderKey) ?></span> : <?= esc((string) $placeholderLabel) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center gap-3">
            <div class="small text-muted">Tekli test gönderimi yapılır. Toplu gönderim, SMS ve kuyruk sistemi bu sprint kapsamı dışındadır.</div>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-send me-1"></i> Test E-postası Gönder
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header">
        <h5 class="mb-0">Test SMS Gönderimi</h5>
      </div>
      <div class="card-body">
        <form action="<?= site_url('admin/notifications/test-sms') ?>" method="post">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Alıcı Telefon Numarası</label>
              <input type="text" name="test_sms_to" class="form-control" value="<?= esc(old('test_sms_to', '')) ?>" placeholder="+905551112233" required>
              <div class="form-text">Twilio için mümkünse E.164 formatı kullanın. Örn: +905551112233</div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Alıcı Adı</label>
              <input type="text" name="test_sms_name" class="form-control" value="<?= esc(old('test_sms_name', '')) ?>" placeholder="Örn. Ayşe Yılmaz">
            </div>
            <div class="col-12">
              <label class="form-label">Mesaj</label>
              <textarea name="test_sms_message" rows="5" class="form-control" placeholder="Test SMS mesajınızı yazın." required><?= esc(old('test_sms_message', "Merhaba {name},\n\nBu mesaj BeAble Pro admin panelindeki test SMS ekranından gönderilmiştir.")) ?></textarea>
              <div class="form-text">Maksimum 612 karakter önerilir. `{name}` alanı alıcı adı ile doldurulur.</div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
            <div class="small text-muted">Bu alan yalnızca tek bir numaraya test SMS gönderimi içindir. Toplu SMS ve kuyruk sistemi bu sprint kapsamı dışındadır.</div>
            <button type="submit" class="btn btn-warning">
              <i class="ti ti-device-mobile-message me-1"></i> Test SMS Gönder
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-5">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">Hazır Şablonlar</h5>
          <div class="small text-muted">Kayıtlı e-posta şablonlarını sağ panelden yönetin.</div>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-template-create="1">
          <i class="ti ti-plus me-1"></i> Yeni Şablon
        </button>
      </div>
      <div class="card-body p-0">
        <?php if ($savedTemplates === []): ?>
          <div class="p-3">
            <div class="alert alert-light border mb-0">Henüz kayıtlı hazır e-posta şablonu bulunmuyor. Yeni Şablon ile ilk kaydı oluşturabilirsiniz.</div>
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($savedTemplates as $template): ?>
              <?php $templatePayload = htmlspecialchars((string) json_encode($template, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>
              <button type="button" class="list-group-item list-group-item-action border-0 rounded-0 px-3 py-3 text-start" data-template-item="<?= $templatePayload ?>">
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div>
                    <div class="fw-semibold"><?= esc((string) $template['template_name']) ?></div>
                    <div class="small text-muted mt-1"><?= esc((string) $template['template_type_label']) ?> · Son güncelleme: <?= esc((string) $template['updated_at_label']) ?></div>
                  </div>
                  <span class="badge <?= $template['is_active'] ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' ?>">
                    <?= $template['is_active'] ? 'Aktif' : 'Pasif' ?>
                  </span>
                </div>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h5 class="mb-0">Gönderim Geçmişi Özeti</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php foreach (($historySummary ?? []) as $item): ?>
            <div class="col-12 col-md-4 col-xl-12">
              <div class="border rounded p-3 h-100 bg-light">
                <div class="small text-muted mb-1"><?= esc((string) ($item['label'] ?? 'Özet')) ?></div>
                <div class="fw-semibold"><?= esc((string) ($item['value'] ?? '-')) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3">
          <?php if ($recentEmailLogs === []): ?>
            <div class="alert alert-light border mb-0">Henüz kayıtlı e-posta gönderim geçmişi bulunmuyor.</div>
          <?php else: ?>
            <div class="d-grid gap-2">
              <?php foreach ($recentEmailLogs as $log): ?>
                <div class="border rounded p-3">
                  <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                    <div>
                      <div class="fw-semibold"><?= esc((string) $log['recipient']) ?></div>
                      <div class="small text-muted mt-1"><?= esc((string) $log['sent_at_label']) ?></div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-1">
                      <span class="badge <?= esc((string) $log['channel_class']) ?>"><?= esc((string) $log['channel_label']) ?></span>
                      <span class="badge <?= esc((string) $log['status_class']) ?>"><?= esc((string) $log['status_label']) ?></span>
                    </div>
                  </div>
                  <div class="small mb-1"><span class="text-muted">Konu:</span> <?= esc((string) $log['subject']) ?></div>
                  <div class="small mb-1"><span class="text-muted">Kaynak:</span> <?= esc((string) $log['source_type_label']) ?> · <?= esc((string) $log['template_type_label']) ?></div>
                  <?php if (!empty($log['error_message'])): ?>
                    <div class="small text-danger mt-2"><?= esc((string) $log['error_message']) ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Yakın Zamanda Eklenecek Alanlar</h5>
      </div>
      <div class="card-body">
        <div class="d-grid gap-3">
          <?php foreach (($placeholders ?? []) as $placeholder): ?>
            <div class="border rounded p-3">
              <div class="fw-semibold mb-1"><?= esc((string) ($placeholder['title'] ?? 'Bilgi')) ?></div>
              <div class="small text-muted"><?= esc((string) ($placeholder['description'] ?? '')) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="emailTemplateOffcanvas" aria-labelledby="emailTemplateOffcanvasLabel">
  <div class="offcanvas-header border-bottom">
    <div>
      <h5 class="offcanvas-title mb-1" id="emailTemplateOffcanvasLabel">Hazır E-posta Şablonu</h5>
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="badge bg-light-primary text-primary" data-drawer-template-type-label>Manuel İçerik</span>
        <span class="badge bg-light-secondary text-secondary" data-drawer-template-status>Yeni kayıt</span>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <?php if (!empty($drawerShouldOpen) && $drawerFlashError !== ''): ?>
      <div class="alert alert-danger"><?= esc($drawerFlashError) ?></div>
    <?php endif; ?>
    <?php if (!empty($drawerShouldOpen) && $drawerFlashSuccess !== ''): ?>
      <div class="alert alert-success"><?= esc($drawerFlashSuccess) ?></div>
    <?php endif; ?>
    <form action="<?= site_url('admin/notifications/templates/save') ?>" method="post" id="templateEditorForm">
      <?= csrf_field() ?>
      <input type="hidden" name="template_id" value="<?= esc((string) $initialDrawerTemplate['id']) ?>" data-drawer-template-id>
      <div class="card border shadow-none mb-3">
        <div class="card-header">
          <h6 class="mb-0">Şablon Bilgileri</h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Şablon Adı</label>
            <input type="text" name="template_name" class="form-control" value="<?= esc((string) $initialDrawerTemplate['template_name']) ?>" placeholder="Örn. Yeni Üye Karşılama" data-drawer-template-name required>
          </div>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Şablon Tipi</label>
              <select name="template_type" class="form-select" data-drawer-template-type required>
                <?php foreach ($templateOptions as $templateKey => $templateLabel): ?>
                  <option value="<?= esc((string) $templateKey) ?>" <?= $selectedTemplateType === $templateKey ? 'selected' : '' ?>><?= esc((string) $templateLabel) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Durum</label>
              <select name="template_is_active" class="form-select" data-drawer-template-active required>
                <option value="1" <?= (string) $initialDrawerTemplate['is_active'] === '1' ? 'selected' : '' ?>>Aktif</option>
                <option value="0" <?= (string) $initialDrawerTemplate['is_active'] === '0' ? 'selected' : '' ?>>Pasif</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-12">
              <label class="form-label">Konu</label>
              <input type="text" name="template_subject" class="form-control" value="<?= esc((string) $initialDrawerTemplate['subject']) ?>" data-drawer-template-subject required>
            </div>
            <div class="col-12">
              <label class="form-label">Mesaj</label>
              <textarea name="template_message" rows="9" class="form-control" data-drawer-template-message required><?= esc((string) $initialDrawerTemplate['message']) ?></textarea>
            </div>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-12 col-md-6">
              <div class="border rounded p-3 bg-light h-100">
                <div class="small text-muted mb-1">Oluşturulma</div>
                <div class="fw-semibold" data-drawer-created-at><?= esc((string) $initialDrawerTemplate['created_at_label']) ?></div>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="border rounded p-3 bg-light h-100">
                <div class="small text-muted mb-1">Son Güncelleme</div>
                <div class="fw-semibold" data-drawer-updated-at><?= esc((string) $initialDrawerTemplate['updated_at_label']) ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>

    <div class="card border shadow-none mb-3">
      <div class="card-header">
        <h6 class="mb-0">Placeholder Yardımı</h6>
      </div>
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-2">
          <?php foreach ($placeholderHelp as $placeholderKey => $placeholderLabel): ?>
            <span class="badge bg-light-primary text-primary border"><?= esc((string) $placeholderKey) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="small text-muted">
          <?php foreach ($placeholderHelp as $placeholderKey => $placeholderLabel): ?>
            <div><span class="fw-semibold"><?= esc((string) $placeholderKey) ?></span> : <?= esc((string) $placeholderLabel) ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="card border shadow-none mb-0">
      <div class="card-header">
        <h6 class="mb-0">Test Olarak Gönder</h6>
      </div>
      <div class="card-body">
        <form action="<?= site_url('admin/notifications/templates/send-test') ?>" method="post" id="templateSendForm">
          <?= csrf_field() ?>
          <input type="hidden" name="template_id" value="<?= esc((string) $initialDrawerTemplate['id']) ?>" data-drawer-send-template-id>
          <div class="mb-3">
            <label class="form-label">Alıcı E-posta</label>
            <input type="email" name="template_test_email_to" class="form-control" value="<?= esc(old('template_test_email_to', '')) ?>" placeholder="ornek@site.com" data-drawer-send-to required>
          </div>
          <div class="mb-3">
            <label class="form-label">Alıcı Adı</label>
            <input type="text" name="template_test_email_name" class="form-control" value="<?= esc(old('template_test_email_name', '')) ?>" placeholder="Örn. Ayşe Yılmaz" data-drawer-send-name>
          </div>
          <div class="alert alert-light border mb-3" data-drawer-send-note>
            Şablonu kaydedip ardından tek kişiye test olarak gönderebilirsiniz.
          </div>
          <button type="submit" class="btn btn-outline-primary w-100" data-drawer-send-button>
            <i class="ti ti-send me-1"></i> Şablon ile Test Gönder
          </button>
        </form>
      </div>
    </div>
  </div>
  <div class="offcanvas-footer border-top p-3">
    <div class="d-flex justify-content-end gap-2">
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Kapat</button>
      <button type="button" class="btn btn-primary" data-drawer-save-button>Kaydet</button>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    var singleForm = document.querySelector('[data-template-form="notification-email"]');
    var templateDefaults = <?= $templateDefaultsJson ?: '{}' ?>;

    if (singleForm) {
      var singleSelect = singleForm.querySelector('[data-template-select]');
      var singleSubject = singleForm.querySelector('[data-template-subject]');
      var singleMessage = singleForm.querySelector('[data-template-message]');

      var applySingleTemplate = function () {
        if (!singleSelect || !singleSubject || !singleMessage) {
          return;
        }

        var selectedKey = singleSelect.value || 'custom';
        var selectedTemplate = templateDefaults[selectedKey] || templateDefaults.custom || null;
        if (!selectedTemplate) {
          return;
        }

        singleSubject.value = selectedTemplate.subject || '';
        singleMessage.value = selectedTemplate.message || '';
      };

      if (singleSelect) {
        singleSelect.addEventListener('change', applySingleTemplate);
      }
    }

    var offcanvasEl = document.getElementById('emailTemplateOffcanvas');
    if (!offcanvasEl || !window.bootstrap || !window.bootstrap.Offcanvas) {
      return;
    }

    var offcanvas = window.bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
    var initialTemplate = <?= $initialDrawerTemplateJson ?: '{}' ?>;
    var shouldOpenDrawer = <?= !empty($drawerShouldOpen) ? 'true' : 'false' ?>;

    var elements = {
      idInput: offcanvasEl.querySelector('[data-drawer-template-id]'),
      sendIdInput: offcanvasEl.querySelector('[data-drawer-send-template-id]'),
      nameInput: offcanvasEl.querySelector('[data-drawer-template-name]'),
      typeInput: offcanvasEl.querySelector('[data-drawer-template-type]'),
      activeInput: offcanvasEl.querySelector('[data-drawer-template-active]'),
      subjectInput: offcanvasEl.querySelector('[data-drawer-template-subject]'),
      messageInput: offcanvasEl.querySelector('[data-drawer-template-message]'),
      createdAt: offcanvasEl.querySelector('[data-drawer-created-at]'),
      updatedAt: offcanvasEl.querySelector('[data-drawer-updated-at]'),
      typeLabel: offcanvasEl.querySelector('[data-drawer-template-type-label]'),
      statusLabel: offcanvasEl.querySelector('[data-drawer-template-status]'),
      sendButton: offcanvasEl.querySelector('[data-drawer-send-button]'),
      sendNote: offcanvasEl.querySelector('[data-drawer-send-note]'),
      sendTo: offcanvasEl.querySelector('[data-drawer-send-to]'),
      sendName: offcanvasEl.querySelector('[data-drawer-send-name]'),
      saveButton: offcanvasEl.querySelector('[data-drawer-save-button]'),
      editorForm: document.getElementById('templateEditorForm')
    };

    var typeLabels = {};
    <?php foreach ($templateOptions as $templateKey => $templateLabel): ?>
      typeLabels['<?= esc((string) $templateKey) ?>'] = '<?= esc((string) $templateLabel) ?>';
    <?php endforeach; ?>

    var emptyTemplate = function () {
      var selectedType = 'custom';
      var defaults = templateDefaults[selectedType] || {};

      return {
        id: '',
        template_name: '',
        template_type: selectedType,
        template_type_label: typeLabels[selectedType] || 'Manuel İçerik',
        subject: defaults.subject || '',
        message: defaults.message || '',
        is_active: true,
        created_at_label: 'Henüz kaydedilmedi',
        updated_at_label: 'Henüz kaydedilmedi'
      };
    };

    var setSendState = function (hasId) {
      if (!elements.sendButton || !elements.sendTo || !elements.sendName || !elements.sendNote) {
        return;
      }

      elements.sendButton.disabled = !hasId;
      elements.sendNote.textContent = hasId
        ? 'Kaydedilmiş şablon üzerinden tek kişiye test e-postası gönderebilirsiniz.'
        : 'Şablonu kaydedip ardından tek kişiye test olarak gönderebilirsiniz.';
    };

    var fillDrawer = function (template) {
      var resolved = template || emptyTemplate();
      var templateType = resolved.template_type || 'custom';
      var isActive = resolved.is_active === true || resolved.is_active === 1 || resolved.is_active === '1';

      if (elements.idInput) {
        elements.idInput.value = resolved.id || '';
      }
      if (elements.sendIdInput) {
        elements.sendIdInput.value = resolved.id || '';
      }
      if (elements.nameInput) {
        elements.nameInput.value = resolved.template_name || '';
      }
      if (elements.typeInput) {
        elements.typeInput.value = templateType;
      }
      if (elements.activeInput) {
        elements.activeInput.value = isActive ? '1' : '0';
      }
      if (elements.subjectInput) {
        elements.subjectInput.value = resolved.subject || '';
      }
      if (elements.messageInput) {
        elements.messageInput.value = resolved.message || '';
      }
      if (elements.createdAt) {
        elements.createdAt.textContent = resolved.created_at_label || 'Henüz kaydedilmedi';
      }
      if (elements.updatedAt) {
        elements.updatedAt.textContent = resolved.updated_at_label || 'Henüz kaydedilmedi';
      }
      if (elements.typeLabel) {
        elements.typeLabel.textContent = resolved.template_type_label || typeLabels[templateType] || 'Manuel İçerik';
      }
      if (elements.statusLabel) {
        elements.statusLabel.textContent = resolved.id ? (isActive ? 'Aktif kayıt' : 'Pasif kayıt') : 'Yeni kayıt';
      }

      setSendState(!!resolved.id);
    };

    var applyDefaultsForDrawerType = function () {
      if (!elements.typeInput || !elements.subjectInput || !elements.messageInput || !elements.typeLabel) {
        return;
      }

      var selectedKey = elements.typeInput.value || 'custom';
      var selectedTemplate = templateDefaults[selectedKey] || templateDefaults.custom || {};
      elements.subjectInput.value = selectedTemplate.subject || '';
      elements.messageInput.value = selectedTemplate.message || '';
      elements.typeLabel.textContent = typeLabels[selectedKey] || 'Manuel İçerik';
    };

    document.querySelectorAll('[data-template-item]').forEach(function (button) {
      button.addEventListener('click', function () {
        try {
          var payload = JSON.parse(button.getAttribute('data-template-item') || '{}');
          fillDrawer(payload);
          offcanvas.show();
        } catch (error) {
        }
      });
    });

    document.querySelectorAll('[data-template-create]').forEach(function (button) {
      button.addEventListener('click', function () {
        fillDrawer(emptyTemplate());
        if (elements.sendTo) {
          elements.sendTo.value = '';
        }
        if (elements.sendName) {
          elements.sendName.value = '';
        }
        offcanvas.show();
      });
    });

    if (elements.typeInput) {
      elements.typeInput.addEventListener('change', applyDefaultsForDrawerType);
    }

    if (elements.saveButton && elements.editorForm) {
      elements.saveButton.addEventListener('click', function () {
        if (typeof elements.editorForm.requestSubmit === 'function') {
          elements.editorForm.requestSubmit();
          return;
        }

        elements.editorForm.submit();
      });
    }

    fillDrawer(initialTemplate && Object.keys(initialTemplate).length ? initialTemplate : emptyTemplate());

    if (shouldOpenDrawer) {
      offcanvas.show();
    }
  })();
</script>
<?= $this->endSection() ?>
