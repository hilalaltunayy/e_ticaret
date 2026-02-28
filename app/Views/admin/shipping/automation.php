<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$companies = is_array($companies ?? null) ? $companies : [];
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
          <li class="breadcrumb-item" aria-current="page">Kargo Otomasyon Kuralları</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Kargo Otomasyon Kuralları') ?></h2>
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
        <h4 class="mb-0"><?= (int) ($kpi['active_rule'] ?? 0) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Otomatik Atama (7g)</h6>
        <h4 class="mb-0"><?= (int) ($kpi['auto_assignment_7d'] ?? 0) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">SLA Uyum %</h6>
        <h4 class="mb-0"><?= (int) ($kpi['sla_compliance'] ?? 0) ?>%</h4>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card statistics-card-1 overflow-hidden">
      <div class="card-body">
        <h6 class="mb-1 text-muted">Ortalama Teslim Süresi</h6>
        <h4 class="mb-0"><?= esc((string) ($kpi['avg_delivery_days'] ?? '0.0')) ?> gün</h4>
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
    <button type="button" class="btn btn-primary btn-sm" id="btnNewRule">Kural Ekle</button>
  </div>
  <div class="card-body">
    <div id="pageAlert" class="alert d-none mb-3" role="alert"></div>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="pane-city" role="tabpanel" aria-labelledby="tab-city">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Şehir</th>
                <th>Öncelikli Firma</th>
                <th>İkincil Firma</th>
                <th>Durum</th>
                <th class="text-end">İşlemler</th>
              </tr>
            </thead>
            <tbody id="rulesBodyCity">
              <tr><td colspan="5" class="text-center text-muted py-4">Henüz kural yok.</td></tr>
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
                <th>Öncelikli Firma</th>
                <th>İkincil Firma</th>
                <th>Durum</th>
                <th class="text-end">İşlemler</th>
              </tr>
            </thead>
            <tbody id="rulesBodyDesi">
              <tr><td colspan="6" class="text-center text-muted py-4">Henüz kural yok.</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="pane-cod" role="tabpanel" aria-labelledby="tab-cod">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Kapıda Ödeme</th>
                <th>Öncelikli Firma</th>
                <th>Durum</th>
                <th class="text-end">İşlemler</th>
              </tr>
            </thead>
            <tbody id="rulesBodyCod">
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
                <th>SLA Hedef Gün</th>
                <th>Öncelikli Firma</th>
                <th>Durum</th>
                <th class="text-end">İşlemler</th>
              </tr>
            </thead>
            <tbody id="rulesBodySla">
              <tr><td colspan="5" class="text-center text-muted py-4">Henüz kural yok.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">
    <h5 class="mb-0">Simülasyon Motoru</h5>
  </div>
  <div class="card-body">
    <form id="simulationForm" class="row g-3">
      <?= csrf_field() ?>
      <div class="col-md-3">
        <label for="sim_city" class="form-label">Şehir</label>
        <input type="text" class="form-control" id="sim_city" name="city" required>
      </div>
      <div class="col-md-2">
        <label for="sim_sla_days" class="form-label">SLA (Gün)</label>
        <input type="number" class="form-control" id="sim_sla_days" name="sla_days" min="0" max="30" required>
      </div>
      <div class="col-md-2">
        <label for="sim_desi" class="form-label">Desi</label>
        <input type="number" class="form-control" id="sim_desi" name="desi" step="0.01" min="0" max="999" required>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="sim_cod" name="cod" value="1">
          <label class="form-check-label" for="sim_cod">Kapıda Ödeme</label>
        </div>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Simüle Et</button>
      </div>
    </form>

    <div id="simulationAlert" class="alert alert-danger d-none mt-3" role="alert"></div>

    <div id="simulationResult" class="d-none mt-3">
      <div class="card border">
        <div class="card-body">
          <h6 class="mb-2">Seçilen Firma</h6>
          <h4 class="mb-3" id="simSelectedCompany">-</h4>
          <ul class="mb-3" id="simReasonList"></ul>
          <h6 class="mb-2">Uygun Adaylar (Top 3)</h6>
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
              <thead>
                <tr>
                  <th>Firma</th>
                  <th>Maliyet</th>
                  <th>SLA</th>
                  <th>Öncelik</th>
                </tr>
              </thead>
              <tbody id="simCandidatesBody">
                <tr><td colspan="4" class="text-center text-muted py-3">Sonuç yok.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="ruleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="ruleForm" method="post" action="#">
        <div class="modal-header">
          <h5 class="modal-title" id="ruleModalTitle">Kural Ekle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <?= csrf_field() ?>
          <input type="hidden" id="ruleId" value="">
          <input type="hidden" id="ruleTypeInput" name="rule_type" value="city">

          <div id="modalAlert" class="alert alert-danger d-none mb-3" role="alert"></div>

          <div class="row g-3" id="cityFields">
            <div class="col-12">
              <label for="city" class="form-label">Şehir</label>
              <input type="text" class="form-control" id="city" name="city">
            </div>
            <div class="col-12">
              <label for="cityPrimaryCompany" class="form-label">Öncelikli Firma</label>
              <select class="form-select" id="cityPrimaryCompany">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?>
                  <option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label for="citySecondaryCompany" class="form-label">İkincil Firma</label>
              <select class="form-select" id="citySecondaryCompany">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?>
                  <option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 d-none" id="desiFields">
            <div class="col-6">
              <label for="desi_min" class="form-label">Desi Min</label>
              <input type="number" class="form-control" id="desi_min" name="desi_min" step="0.01" min="0">
            </div>
            <div class="col-6">
              <label for="desi_max" class="form-label">Desi Max</label>
              <input type="number" class="form-control" id="desi_max" name="desi_max" step="0.01" min="0">
            </div>
            <div class="col-12">
              <label for="desiPrimaryCompany" class="form-label">Öncelikli Firma</label>
              <select class="form-select" id="desiPrimaryCompany">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?>
                  <option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label for="desiSecondaryCompany" class="form-label">İkincil Firma</label>
              <select class="form-select" id="desiSecondaryCompany">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?>
                  <option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 d-none" id="codFields">
            <div class="col-12">
              <label for="codPrimaryCompany" class="form-label">Öncelikli Firma</label>
              <select class="form-select" id="codPrimaryCompany">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?>
                  <option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 d-none" id="slaFields">
            <div class="col-12">
              <label for="sla_city" class="form-label">Şehir</label>
              <input type="text" class="form-control" id="sla_city">
            </div>
            <div class="col-12">
              <label for="sla_days" class="form-label">SLA Hedef Gün</label>
              <input type="number" class="form-control" id="sla_days" name="sla_days" min="1" step="1">
            </div>
            <div class="col-12">
              <label for="slaPrimaryCompany" class="form-label">Öncelikli Firma</label>
              <select class="form-select" id="slaPrimaryCompany">
                <option value="">Seçiniz</option>
                <?php foreach ($companies as $company): ?>
                  <option value="<?= esc((string) ($company['id'] ?? '')) ?>"><?= esc((string) ($company['name'] ?? '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-12">
              <label for="is_active" class="form-label">Durum</label>
              <select class="form-select" id="is_active" name="is_active">
                <option value="1">Aktif</option>
                <option value="0">Pasif</option>
              </select>
            </div>
          </div>

          <input type="hidden" id="primary_company_id" name="primary_company_id" value="">
          <input type="hidden" id="secondary_company_id" name="secondary_company_id" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Vazgeç</button>
          <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    var allowedTypes = ['city', 'desi', 'cod', 'sla'];
    var initType = "<?= esc($initialType) ?>";
    var activeType = allowedTypes.indexOf(initType) !== -1 ? initType : 'city';

    var listEndpoint = "<?= site_url('admin/shipping/automation/rules') ?>";
    var showEndpointBase = "<?= site_url('admin/shipping/automation/rules/show') ?>";
    var createEndpoint = "<?= site_url('admin/shipping/automation/rules/create') ?>";
    var updateEndpointBase = "<?= site_url('admin/shipping/automation/rules/update') ?>";
    var simulateEndpoint = "<?= site_url('admin/shipping/automation/simulate') ?>";

    var modalEl = document.getElementById('ruleModal');
    var modal = typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;
    var form = document.getElementById('ruleForm');
    var simulationForm = document.getElementById('simulationForm');

    function esc(value) {
      return String(value === null || value === undefined ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function showPageAlert(message, type) {
      var alertBox = document.getElementById('pageAlert');
      if (!alertBox) return;
      if (!message) {
        alertBox.className = 'alert d-none mb-3';
        alertBox.textContent = '';
        return;
      }
      alertBox.className = 'alert alert-' + (type || 'danger') + ' mb-3';
      alertBox.textContent = message;
    }

    function showModalAlert(message) {
      var alertBox = document.getElementById('modalAlert');
      if (!alertBox) return;
      if (!message) {
        alertBox.classList.add('d-none');
        alertBox.textContent = '';
        return;
      }
      alertBox.classList.remove('d-none');
      alertBox.textContent = message;
    }

    function showSimulationAlert(message) {
      var box = document.getElementById('simulationAlert');
      if (!box) return;
      if (!message) {
        box.classList.add('d-none');
        box.textContent = '';
        return;
      }
      box.classList.remove('d-none');
      box.textContent = message;
    }

    function renderSimulationResult(payload) {
      var resultBox = document.getElementById('simulationResult');
      var selected = payload && payload.selected ? payload.selected : null;
      var candidates = payload && payload.top_candidates ? payload.top_candidates : [];
      var selectedCompany = document.getElementById('simSelectedCompany');
      var reasonList = document.getElementById('simReasonList');
      var candidatesBody = document.getElementById('simCandidatesBody');

      if (!resultBox || !selectedCompany || !reasonList || !candidatesBody) return;

      if (!selected) {
        selectedCompany.textContent = 'Uygun firma bulunamadı.';
        reasonList.innerHTML = '<li>Girilen kriterlere uygun aktif kural bulunmadı.</li>';
      } else {
        selectedCompany.textContent = selected.company_name || '-';
        var reason = selected.reason || {};
        reasonList.innerHTML =
          '<li>Şehir uyumu: ' + (reason.city_match ? 'Evet' : 'Hayır') + '</li>' +
          '<li>SLA uyumu: ' + (reason.sla_match ? 'Evet' : 'Hayır') + '</li>' +
          '<li>Kapıda ödeme uyumu: ' + (reason.cod_match ? 'Evet' : 'Hayır') + '</li>' +
          '<li>Desi uyumu: ' + (reason.desi_match ? 'Evet' : 'Hayır') + '</li>' +
          '<li>Maliyet: ' + esc(reason.cost || '-') + '</li>' +
          '<li>SLA: ' + esc(reason.sla || '-') + '</li>' +
          '<li>Öncelik: ' + esc(reason.priority || '-') + '</li>';
      }

      if (!candidates.length) {
        candidatesBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sonuç yok.</td></tr>';
      } else {
        var html = '';
        candidates.forEach(function (item) {
          html += '<tr>'
            + '<td>' + esc(item.company_name || '-') + '</td>'
            + '<td>' + esc(item.cost || '-') + '</td>'
            + '<td>' + esc(item.sla || '-') + '</td>'
            + '<td>' + esc(item.priority || '-') + '</td>'
            + '</tr>';
        });
        candidatesBody.innerHTML = html;
      }

      resultBox.classList.remove('d-none');
    }

    function statusText(isActive) {
      return Number(isActive) === 1 ? 'Aktif' : 'Pasif';
    }

    function statusClass(isActive) {
      return Number(isActive) === 1 ? 'badge bg-light-success text-success' : 'badge bg-light-secondary text-secondary';
    }

    function emptyRow(type) {
      if (type === 'city') return '<tr><td colspan="5" class="text-center text-muted py-4">Henüz kural yok.</td></tr>';
      if (type === 'desi') return '<tr><td colspan="6" class="text-center text-muted py-4">Henüz kural yok.</td></tr>';
      if (type === 'cod') return '<tr><td colspan="4" class="text-center text-muted py-4">Henüz kural yok.</td></tr>';
      return '<tr><td colspan="5" class="text-center text-muted py-4">Henüz kural yok.</td></tr>';
    }

    function renderRules(type, rows) {
      var bodyMap = {
        city: 'rulesBodyCity',
        desi: 'rulesBodyDesi',
        cod: 'rulesBodyCod',
        sla: 'rulesBodySla'
      };
      var body = document.getElementById(bodyMap[type] || '');
      if (!body) return;

      if (!rows || rows.length === 0) {
        body.innerHTML = emptyRow(type);
        return;
      }

      var html = '';
      rows.forEach(function (row) {
        var id = esc(row.id || '');
        var editBtn = '<button type="button" class="btn btn-outline-primary btn-sm" data-action="edit" data-id="' + id + '">Düzenle</button>';

        if (type === 'city') {
          html += '<tr><td>' + esc(row.city || '-') + '</td><td>' + esc(row.primary_company_name || '-') + '</td><td>' + esc(row.secondary_company_name || '-') + '</td><td><span class="' + statusClass(row.is_active) + '">' + statusText(row.is_active) + '</span></td><td class="text-end">' + editBtn + '</td></tr>';
        } else if (type === 'desi') {
          html += '<tr><td>' + esc(row.desi_min || '-') + '</td><td>' + esc(row.desi_max || '-') + '</td><td>' + esc(row.primary_company_name || '-') + '</td><td>' + esc(row.secondary_company_name || '-') + '</td><td><span class="' + statusClass(row.is_active) + '">' + statusText(row.is_active) + '</span></td><td class="text-end">' + editBtn + '</td></tr>';
        } else if (type === 'cod') {
          html += '<tr><td>Evet</td><td>' + esc(row.primary_company_name || '-') + '</td><td><span class="' + statusClass(row.is_active) + '">' + statusText(row.is_active) + '</span></td><td class="text-end">' + editBtn + '</td></tr>';
        } else {
          html += '<tr><td>' + esc(row.city || '-') + '</td><td>' + esc(row.sla_days || '-') + '</td><td>' + esc(row.primary_company_name || '-') + '</td><td><span class="' + statusClass(row.is_active) + '">' + statusText(row.is_active) + '</span></td><td class="text-end">' + editBtn + '</td></tr>';
        }
      });

      body.innerHTML = html;
    }

    function loadRules(type) {
      return fetch(listEndpoint + '?type=' + encodeURIComponent(type), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (!json.ok) {
            throw new Error(json.message || 'Kurallar alınamadı.');
          }
          renderRules(type, json.data || []);
        });
    }

    function clearTypeData() {
      document.getElementById('city').value = '';
      document.getElementById('desi_min').value = '';
      document.getElementById('desi_max').value = '';
      document.getElementById('sla_days').value = '';
      document.getElementById('sla_city').value = '';
      document.getElementById('cityPrimaryCompany').value = '';
      document.getElementById('desiPrimaryCompany').value = '';
      document.getElementById('codPrimaryCompany').value = '';
      document.getElementById('slaPrimaryCompany').value = '';
      document.getElementById('citySecondaryCompany').value = '';
      document.getElementById('desiSecondaryCompany').value = '';
      document.getElementById('primary_company_id').value = '';
      document.getElementById('secondary_company_id').value = '';
    }

    function setTypeFields(type) {
      document.getElementById('ruleTypeInput').value = type;
      document.getElementById('cityFields').classList.toggle('d-none', type !== 'city');
      document.getElementById('desiFields').classList.toggle('d-none', type !== 'desi');
      document.getElementById('codFields').classList.toggle('d-none', type !== 'cod');
      document.getElementById('slaFields').classList.toggle('d-none', type !== 'sla');
    }

    function syncCompanyFields(type) {
      var primary = '';
      var secondary = '';

      if (type === 'city') {
        primary = document.getElementById('cityPrimaryCompany').value || '';
        secondary = document.getElementById('citySecondaryCompany').value || '';
      } else if (type === 'desi') {
        primary = document.getElementById('desiPrimaryCompany').value || '';
        secondary = document.getElementById('desiSecondaryCompany').value || '';
      } else if (type === 'cod') {
        primary = document.getElementById('codPrimaryCompany').value || '';
      } else {
        primary = document.getElementById('slaPrimaryCompany').value || '';
      }

      document.getElementById('primary_company_id').value = primary;
      document.getElementById('secondary_company_id').value = secondary;
    }

    function resetForm(type) {
      form.reset();
      clearTypeData();
      document.getElementById('ruleId').value = '';
      document.getElementById('ruleModalTitle').textContent = 'Kural Ekle';
      document.getElementById('is_active').value = '1';
      setTypeFields(type);
      syncCompanyFields(type);
      showModalAlert('');
    }

    function openCreateModal() {
      resetForm(activeType);
      if (modal) modal.show();
    }

    function fillEditForm(type, data) {
      document.getElementById('ruleId').value = String(data.id || '');
      document.getElementById('ruleModalTitle').textContent = 'Kural Düzenle';
      document.getElementById('is_active').value = Number(data.is_active) === 1 ? '1' : '0';

      if (type === 'city') {
        document.getElementById('city').value = data.city || '';
        document.getElementById('cityPrimaryCompany').value = data.primary_company_id || '';
        document.getElementById('citySecondaryCompany').value = data.secondary_company_id || '';
      } else if (type === 'desi') {
        document.getElementById('desi_min').value = data.desi_min || '';
        document.getElementById('desi_max').value = data.desi_max || '';
        document.getElementById('desiPrimaryCompany').value = data.primary_company_id || '';
        document.getElementById('desiSecondaryCompany').value = data.secondary_company_id || '';
      } else if (type === 'cod') {
        document.getElementById('codPrimaryCompany').value = data.primary_company_id || '';
      } else {
        document.getElementById('sla_city').value = data.city || '';
        document.getElementById('city').value = data.city || '';
        document.getElementById('sla_days').value = data.sla_days || '';
        document.getElementById('slaPrimaryCompany').value = data.primary_company_id || '';
      }

      setTypeFields(type);
      syncCompanyFields(type);
    }

    function openEditModal(id) {
      showModalAlert('');
      fetch(showEndpointBase + '/' + encodeURIComponent(id), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (!json.ok || !json.data) {
            throw new Error(json.message || 'Kural bilgisi alınamadı.');
          }

          var type = allowedTypes.indexOf(json.data.rule_type) !== -1 ? json.data.rule_type : 'city';
          fillEditForm(type, json.data);
          if (modal) modal.show();
        })
        .catch(function (err) {
          showPageAlert(err.message || 'Kural bilgisi alınamadı.', 'danger');
        });
    }

    function submitForm(event) {
      event.preventDefault();
      showModalAlert('');
      showPageAlert('');

      var type = document.getElementById('ruleTypeInput').value;
      syncCompanyFields(type);

      if (type === 'sla') {
        document.getElementById('city').value = document.getElementById('sla_city').value || '';
      }
      if (type !== 'city' && type !== 'sla') {
        document.getElementById('city').value = '';
      }

      var id = document.getElementById('ruleId').value || '';
      var url = id ? (updateEndpointBase + '/' + encodeURIComponent(id)) : createEndpoint;
      var formData = new FormData(form);

      fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (!json.ok) {
            throw new Error(json.message || 'İşlem başarısız.');
          }

          showModalAlert('');
          if (modal) modal.hide();
          return loadRules(activeType);
        })
        .catch(function (err) {
          showModalAlert(err.message || 'İşlem başarısız.');
        });
    }

    document.getElementById('btnNewRule').addEventListener('click', function () {
      openCreateModal();
    });

    document.getElementById('shippingAutomationTabs').addEventListener('shown.bs.tab', function (event) {
      var nextType = event.target.getAttribute('data-rule-type') || 'city';
      activeType = allowedTypes.indexOf(nextType) !== -1 ? nextType : 'city';
      loadRules(activeType).catch(function (err) {
        showPageAlert(err.message || 'Kurallar alınamadı.', 'danger');
      });
    });

    document.addEventListener('click', function (event) {
      var btn = event.target.closest('[data-action="edit"]');
      if (!btn) return;
      var id = btn.getAttribute('data-id') || '';
      if (id === '') return;
      openEditModal(id);
    });

    document.getElementById('cityPrimaryCompany').addEventListener('change', function () { syncCompanyFields('city'); });
    document.getElementById('desiPrimaryCompany').addEventListener('change', function () { syncCompanyFields('desi'); });
    document.getElementById('codPrimaryCompany').addEventListener('change', function () { syncCompanyFields('cod'); });
    document.getElementById('slaPrimaryCompany').addEventListener('change', function () { syncCompanyFields('sla'); });

    form.addEventListener('submit', submitForm);

    if (simulationForm) {
      simulationForm.addEventListener('submit', function (event) {
        event.preventDefault();
        showSimulationAlert('');

        var formData = new FormData(simulationForm);
        if (!document.getElementById('sim_cod').checked) {
          formData.set('cod', '0');
        }

        fetch(simulateEndpoint, {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (res) { return res.json(); })
          .then(function (json) {
            if (!json.ok) {
              throw new Error(json.message || 'Simülasyon başarısız.');
            }
            renderSimulationResult(json.data || {});
          })
          .catch(function (err) {
            showSimulationAlert(err.message || 'Simülasyon hesaplanamadı.');
          });
      });
    }

    setTypeFields(activeType);
    loadRules(activeType).catch(function (err) {
      showPageAlert(err.message || 'Kurallar alınamadı.', 'danger');
    });
  })();
</script>
<?= $this->endSection() ?>


