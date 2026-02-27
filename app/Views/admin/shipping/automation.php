<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$companies = is_array($companies ?? null) ? $companies : [];
$initialRules = is_array($initialRules ?? null) ? $initialRules : [];
$initialType = (string) ($initialType ?? 'city');
$kpi = is_array($kpi ?? null) ? $kpi : [];
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yönetim</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('admin/shipping') ?>">Kargo Takip</a></li>
          <li class="breadcrumb-item" aria-current="page">Kargo Optimizasyonu</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Kargo Optimizasyonu') ?></h2>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Aktif Kural</h6>
        <h4 class="mb-0" id="kpiActiveRule"><?= (int) ($kpi['active_rule'] ?? 0) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Otomatik Atama (7g)</h6>
        <h4 class="mb-0" id="kpiAutoAssignment"><?= (int) ($kpi['auto_assignment_7d'] ?? 0) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">SLA Uyum %</h6>
        <h4 class="mb-0" id="kpiSlaCompliance"><?= (int) ($kpi['sla_compliance'] ?? 0) ?>%</h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Ortalama Teslim Süresi</h6>
        <h4 class="mb-0" id="kpiAvgDelivery"><?= esc((string) ($kpi['avg_delivery_days'] ?? '0.0')) ?> gün</h4>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <ul class="nav nav-tabs card-header-tabs mb-0" id="shippingAutomationTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-city" data-bs-toggle="tab" data-bs-target="#pane-city" data-rule-type="city" type="button" role="tab">Şehre Göre Otomatik Seçim</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-desi" data-bs-toggle="tab" data-bs-target="#pane-desi" data-rule-type="desi" type="button" role="tab">Desi Bazlı Kurallar</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-cod" data-bs-toggle="tab" data-bs-target="#pane-cod" data-rule-type="cod" type="button" role="tab">Kapıda Ödeme Kuralları</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-sla" data-bs-toggle="tab" data-bs-target="#pane-sla" data-rule-type="sla" type="button" role="tab">SLA Optimizasyonu</button>
      </li>
    </ul>
    <button type="button" class="btn btn-primary btn-sm" id="btnNewRule" data-action="new-rule">Yeni kural</button>
  </div>
  <div class="card-body">
    <div id="ruleAlert" class="alert d-none mb-3" role="alert"></div>
    <div class="tab-content">
      <div class="tab-pane fade show active" id="pane-city" role="tabpanel" aria-labelledby="tab-city">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Şehir</th>
                <th>Öncelikli firma</th>
                <th>İkincil firma</th>
                <th>Durum</th>
                <th class="text-end">İşlemler</th>
              </tr>
            </thead>
            <tbody id="body-city">
              <?php if ($initialRules === []): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Henüz kural yok.</td></tr>
              <?php else: ?>
                <?php foreach ($initialRules as $rule): ?>
                  <tr data-rule-id="<?= esc((string) ($rule['id'] ?? '')) ?>">
                    <td><?= esc((string) ($rule['city'] ?? '-')) ?></td>
                    <td><?= esc((string) ($rule['primary_company_name'] ?? '-')) ?></td>
                    <td><?= esc((string) ($rule['secondary_company_name'] ?? '-')) ?></td>
                    <td>
                      <?php $active = (int) ($rule['is_active'] ?? 0) === 1; ?>
                      <span class="badge <?= $active ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' ?>"><?= $active ? 'Aktif' : 'Pasif' ?></span>
                    </td>
                    <td class="text-end">
                      <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" data-action="edit-rule" data-id="<?= esc((string) ($rule['id'] ?? '')) ?>">Düzenle</button>
                        <button type="button" class="btn btn-outline-danger" data-action="delete-rule" data-id="<?= esc((string) ($rule['id'] ?? '')) ?>">Sil</button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="pane-desi" role="tabpanel" aria-labelledby="tab-desi">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Desi Min</th>
                <th>Desi Max</th>
                <th>Firma</th>
                <th>Durum</th>
                <th class="text-end">İşlemler</th>
              </tr>
            </thead>
            <tbody id="body-desi">
              <tr><td colspan="5" class="text-center text-muted py-4">Henüz kural yok.</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="pane-cod" role="tabpanel" aria-labelledby="tab-cod">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Kapıda ödeme</th>
                <th>Firma</th>
                <th>Durum</th>
                <th class="text-end">İşlemler</th>
              </tr>
            </thead>
            <tbody id="body-cod">
              <tr><td colspan="4" class="text-center text-muted py-4">Henüz kural yok.</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="pane-sla" role="tabpanel" aria-labelledby="tab-sla">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Şehir</th>
                <th>SLA hedef gün</th>
                <th>Firma</th>
                <th>Durum</th>
                <th class="text-end">İşlemler</th>
              </tr>
            </thead>
            <tbody id="body-sla">
              <tr><td colspan="5" class="text-center text-muted py-4">Henüz kural yok.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="ruleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="ruleForm" method="post" action="<?= site_url('admin/shipping/automation/rules') ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="ruleModalTitle">Yeni Kural Ekle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <?= csrf_field() ?>
          <input type="hidden" id="ruleId" value="">
          <div id="ruleFormAlert" class="alert alert-danger d-none"></div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="rule_type">Kural tipi</label>
              <select class="form-select" id="rule_type" name="rule_type">
                <option value="city">city</option>
                <option value="desi">desi</option>
                <option value="cod">cod</option>
                <option value="sla">sla</option>
              </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                <label class="form-check-label" for="is_active">aktif_mi</label>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-1" id="group-city">
            <div class="col-md-4">
              <label class="form-label" for="city">Şehir</label>
              <input type="text" class="form-control" id="city" name="city">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="city_primary_company">Öncelikli firma</label>
              <select class="form-select company-picker" id="city_primary_company" data-role="primary">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?><option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="secondary_company_id">İkincil firma</label>
              <select class="form-select" id="secondary_company_id" name="secondary_company_id">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?><option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option><?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 mt-1 d-none" id="group-desi">
            <div class="col-md-4">
              <label class="form-label" for="desi_min">Min desi</label>
              <input type="number" class="form-control" id="desi_min" name="desi_min" step="0.01" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="desi_max">Max desi</label>
              <input type="number" class="form-control" id="desi_max" name="desi_max" step="0.01" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="desi_primary_company">Firma</label>
              <select class="form-select company-picker" id="desi_primary_company" data-role="primary">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?><option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option><?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 mt-1 d-none" id="group-cod">
            <div class="col-md-4">
              <label class="form-label">Kapıda ödeme</label>
              <input class="form-control" type="text" value="Evet" disabled>
            </div>
            <div class="col-md-8">
              <label class="form-label" for="cod_primary_company">Firma</label>
              <select class="form-select company-picker" id="cod_primary_company" data-role="primary">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?><option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option><?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 mt-1 d-none" id="group-sla">
            <div class="col-md-4">
              <label class="form-label" for="sla_city">Şehir (opsiyonel)</label>
              <input type="text" class="form-control" id="sla_city">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="sla_days">SLA hedef gün</label>
              <input type="number" class="form-control" id="sla_days" name="sla_days" min="1">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="sla_primary_company">Firma</label>
              <select class="form-select company-picker" id="sla_primary_company" data-role="primary">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?><option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option><?php endforeach; ?>
              </select>
            </div>
          </div>

          <input type="hidden" id="primary_company_id" name="primary_company_id" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">İptal</button>
          <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteRuleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Kuralı Sil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Bu kuralı silmek istediğinize emin misiniz?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Vazgeç</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteRule">Sil</button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    var apiBase = "<?= site_url('admin/shipping/automation/rules') ?>";
    var currentType = "<?= esc($initialType) ?>";
    var loadedTypes = { city: true, desi: false, cod: false, sla: false };
    var ruleMap = {};
    var deleteRuleId = '';

    var ruleForm = document.getElementById('ruleForm');
    var ruleType = document.getElementById('rule_type');
    var ruleModal = (typeof bootstrap !== 'undefined') ? new bootstrap.Modal(document.getElementById('ruleModal')) : null;
    var deleteModal = (typeof bootstrap !== 'undefined') ? new bootstrap.Modal(document.getElementById('deleteRuleModal')) : null;

    function esc(v) {
      return String(v === null || v === undefined ? '' : v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function flash(message, type) {
      var alertBox = document.getElementById('ruleAlert');
      if (!alertBox) return;
      if (!message) {
        alertBox.className = 'alert d-none mb-3';
        alertBox.textContent = '';
        return;
      }
      alertBox.className = 'alert alert-' + (type || 'info') + ' mb-3';
      alertBox.textContent = message;
    }

    function formError(message) {
      var box = document.getElementById('ruleFormAlert');
      if (!box) return;
      if (!message) {
        box.classList.add('d-none');
        box.textContent = '';
        return;
      }
      box.classList.remove('d-none');
      box.textContent = message;
    }

    function emptyRow(type) {
      var colspan = type === 'cod' ? 4 : 5;
      return '<tr><td colspan="' + colspan + '" class="text-center text-muted py-4">Henüz kural yok.</td></tr>';
    }

    function statusBadge(active) {
      if (Number(active) === 1) return '<span class="badge bg-light-success text-success">Aktif</span>';
      return '<span class="badge bg-light-secondary text-secondary">Pasif</span>';
    }

    function actionButtons(id) {
      return '<div class="btn-group btn-group-sm">'
        + '<button type="button" class="btn btn-outline-primary" data-action="edit-rule" data-id="' + esc(id) + '">Düzenle</button>'
        + '<button type="button" class="btn btn-outline-danger" data-action="delete-rule" data-id="' + esc(id) + '">Sil</button>'
        + '</div>';
    }

    function rowHtml(type, rule) {
      var id = rule.id || '';
      if (type === 'city') return '<tr><td>' + esc(rule.city || '-') + '</td><td>' + esc(rule.primary_company_name || '-') + '</td><td>' + esc(rule.secondary_company_name || '-') + '</td><td>' + statusBadge(rule.is_active) + '</td><td class="text-end">' + actionButtons(id) + '</td></tr>';
      if (type === 'desi') return '<tr><td>' + esc(rule.desi_min || '-') + '</td><td>' + esc(rule.desi_max || '-') + '</td><td>' + esc(rule.primary_company_name || '-') + '</td><td>' + statusBadge(rule.is_active) + '</td><td class="text-end">' + actionButtons(id) + '</td></tr>';
      if (type === 'cod') return '<tr><td>Evet</td><td>' + esc(rule.primary_company_name || '-') + '</td><td>' + statusBadge(rule.is_active) + '</td><td class="text-end">' + actionButtons(id) + '</td></tr>';
      return '<tr><td>' + esc(rule.city || '-') + '</td><td>' + esc(rule.sla_days || '-') + '</td><td>' + esc(rule.primary_company_name || '-') + '</td><td>' + statusBadge(rule.is_active) + '</td><td class="text-end">' + actionButtons(id) + '</td></tr>';
    }

    function renderType(type, rows) {
      var body = document.getElementById('body-' + type);
      if (!body) return;
      if (!rows || rows.length === 0) {
        body.innerHTML = emptyRow(type);
        return;
      }
      var html = '';
      rows.forEach(function (rule) {
        ruleMap[String(rule.id || '')] = rule;
        html += rowHtml(type, rule);
      });
      body.innerHTML = html;
    }

    function updateKpi(kpi) {
      if (!kpi) return;
      document.getElementById('kpiActiveRule').textContent = String(kpi.active_rule || 0);
      document.getElementById('kpiAutoAssignment').textContent = String(kpi.auto_assignment_7d || 0);
      document.getElementById('kpiSlaCompliance').textContent = String(kpi.sla_compliance || 0) + '%';
      document.getElementById('kpiAvgDelivery').textContent = String(kpi.avg_delivery_days || '0.0') + ' gün';
    }

    function updateCsrf(csrf) {
      if (!csrf || !csrf.name) return;
      var input = ruleForm.querySelector('input[name="' + csrf.name + '"]');
      if (input) input.value = csrf.hash || '';
    }

    function fetchType(type) {
      return fetch(apiBase + '?type=' + encodeURIComponent(type), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      }).then(function (res) {
        return res.json().then(function (json) {
          if (!res.ok || !json.success) {
            throw new Error((json && json.message) ? json.message : 'Kural listesi alınamadı.');
          }
          updateCsrf(json.csrf || {});
          updateKpi(json.kpi || {});
          renderType(type, json.rules || []);
          loadedTypes[type] = true;
          return json;
        });
      });
    }

    function syncPrimaryCompany() {
      var selected = '';
      var t = ruleType.value;
      if (t === 'city') selected = document.getElementById('city_primary_company').value || '';
      if (t === 'desi') selected = document.getElementById('desi_primary_company').value || '';
      if (t === 'cod') selected = document.getElementById('cod_primary_company').value || '';
      if (t === 'sla') selected = document.getElementById('sla_primary_company').value || '';
      document.getElementById('primary_company_id').value = selected;
    }

    function setGroups() {
      var t = ruleType.value;
      document.getElementById('group-city').classList.toggle('d-none', t !== 'city');
      document.getElementById('group-desi').classList.toggle('d-none', t !== 'desi');
      document.getElementById('group-cod').classList.toggle('d-none', t !== 'cod');
      document.getElementById('group-sla').classList.toggle('d-none', t !== 'sla');
    }

    function normalizeForm() {
      var t = ruleType.value;
      syncPrimaryCompany();
      if (t === 'sla') {
        document.getElementById('city').value = document.getElementById('sla_city').value || '';
      }
      if (t !== 'sla' && t !== 'city') {
        document.getElementById('city').value = '';
      }
      if (t !== 'city') {
        document.getElementById('secondary_company_id').value = '';
      }
      if (t !== 'desi') {
        document.getElementById('desi_min').value = '';
        document.getElementById('desi_max').value = '';
      }
      if (t !== 'sla') {
        document.getElementById('sla_days').value = '';
      }
    }

    function openCreateModal() {
      ruleForm.reset();
      formError('');
      document.getElementById('ruleId').value = '';
      document.getElementById('ruleModalTitle').textContent = 'Yeni Kural Ekle';
      ruleType.value = currentType;
      document.getElementById('is_active').checked = true;
      setGroups();
      syncPrimaryCompany();
      if (ruleModal) ruleModal.show();
    }

    function openEditModal(id) {
      var rule = ruleMap[id];
      if (!rule) return;
      formError('');
      document.getElementById('ruleId').value = String(rule.id || '');
      document.getElementById('ruleModalTitle').textContent = 'Kural Düzenle';
      ruleType.value = String(rule.rule_type || 'city');
      document.getElementById('is_active').checked = Number(rule.is_active || 0) === 1;
      document.getElementById('city').value = String(rule.city || '');
      document.getElementById('sla_city').value = String(rule.city || '');
      document.getElementById('desi_min').value = rule.desi_min || '';
      document.getElementById('desi_max').value = rule.desi_max || '';
      document.getElementById('sla_days').value = rule.sla_days || '';
      document.getElementById('secondary_company_id').value = String(rule.secondary_company_id || '');
      document.getElementById('city_primary_company').value = String(rule.primary_company_id || '');
      document.getElementById('desi_primary_company').value = String(rule.primary_company_id || '');
      document.getElementById('cod_primary_company').value = String(rule.primary_company_id || '');
      document.getElementById('sla_primary_company').value = String(rule.primary_company_id || '');
      setGroups();
      syncPrimaryCompany();
      if (ruleModal) ruleModal.show();
    }

    function post(url, formData) {
      return fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      }).then(function (res) {
        return res.json().then(function (json) {
          if (!res.ok || !json.success) {
            var error = new Error((json && json.message) ? json.message : 'İşlem başarısız.');
            error.payload = json;
            throw error;
          }
          return json;
        });
      });
    }

    document.addEventListener('click', function (event) {
      var target = event.target.closest('[data-action]');
      if (!target) return;

      var action = target.getAttribute('data-action');
      var id = target.getAttribute('data-id') || '';

      if (action === 'new-rule') {
        event.preventDefault();
        openCreateModal();
        return;
      }
      if (action === 'edit-rule' && id !== '') {
        event.preventDefault();
        openEditModal(id);
        return;
      }
      if (action === 'delete-rule' && id !== '') {
        event.preventDefault();
        deleteRuleId = id;
        if (deleteModal) deleteModal.show();
      }
    });

    ruleType.addEventListener('change', setGroups);
    document.querySelectorAll('.company-picker').forEach(function (el) {
      el.addEventListener('change', syncPrimaryCompany);
    });
    document.getElementById('sla_city').addEventListener('input', function () {
      if (ruleType.value === 'sla') {
        document.getElementById('city').value = this.value;
      }
    });

    document.getElementById('shippingAutomationTabs').addEventListener('shown.bs.tab', function (event) {
      var type = event.target.getAttribute('data-rule-type');
      if (!type) return;
      currentType = type;
      if (!loadedTypes[type]) {
        fetchType(type).catch(function (err) {
          flash(err.message || 'Liste alınamadı.', 'danger');
        });
      }
    });

    ruleForm.addEventListener('submit', function (event) {
      event.preventDefault();
      formError('');
      normalizeForm();

      var formData = new FormData(ruleForm);
      formData.set('rule_type', ruleType.value);
      formData.set('is_active', document.getElementById('is_active').checked ? '1' : '0');
      formData.set('city', document.getElementById('city').value || '');
      formData.set('desi_min', document.getElementById('desi_min').value || '');
      formData.set('desi_max', document.getElementById('desi_max').value || '');
      formData.set('sla_days', document.getElementById('sla_days').value || '');
      formData.set('primary_company_id', document.getElementById('primary_company_id').value || '');
      formData.set('secondary_company_id', document.getElementById('secondary_company_id').value || '');

      var id = document.getElementById('ruleId').value || '';
      var url = id ? (apiBase + '/' + encodeURIComponent(id)) : apiBase;

      post(url, formData)
        .then(function (json) {
          updateCsrf(json.csrf || {});
          updateKpi(json.kpi || {});
          renderType(json.type || currentType, json.rules || []);
          loadedTypes[json.type || currentType] = true;
          flash(json.message || 'Kaydedildi.', 'success');
          if (ruleModal) ruleModal.hide();
        })
        .catch(function (err) {
          var message = err.message || 'Kayıt yapılamadı.';
          if (err.payload && err.payload.errors) {
            var key = Object.keys(err.payload.errors)[0];
            if (key) message += ' ' + err.payload.errors[key];
          }
          formError(message);
        });
    });

    document.getElementById('confirmDeleteRule').addEventListener('click', function () {
      if (deleteRuleId === '') return;
      var fd = new FormData(ruleForm);
      fd.set('rule_type', currentType);
      post(apiBase + '/' + encodeURIComponent(deleteRuleId) + '/delete', fd)
        .then(function (json) {
          updateCsrf(json.csrf || {});
          updateKpi(json.kpi || {});
          renderType(json.type || currentType, json.rules || []);
          loadedTypes[json.type || currentType] = true;
          flash(json.message || 'Silindi.', 'success');
          deleteRuleId = '';
          if (deleteModal) deleteModal.hide();
        })
        .catch(function (err) {
          flash(err.message || 'Silme işlemi başarısız.', 'danger');
        });
    });

    fetchType(currentType).catch(function (err) {
      flash(err.message || 'Liste alınamadı.', 'danger');
    });
  })();
</script>
<?= $this->endSection() ?>
