<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$secretaries = is_array($secretaries ?? null) ? $secretaries : [];
$selectedUserId = (string) ($selectedUserId ?? '');
$matrix = is_array($matrix ?? null) ? $matrix : [];
$error = (string) ($error ?? '');
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'Yetkilendirme') ?></h2>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Kullanıcı Seçimi</h5></div>
      <div class="card-body">
        <form method="get" action="<?= site_url('admin/settings/permissions') ?>">
          <label for="user_id" class="form-label">Secretary Kullanıcısı</label>
          <select id="user_id" name="user_id" class="form-select" onchange="this.form.submit()">
            <?php foreach ($secretaries as $user): ?>
              <?php $uid = (string) ($user['id'] ?? ''); ?>
              <option value="<?= esc($uid) ?>"<?= $uid === $selectedUserId ? ' selected' : '' ?>>
                <?= esc((string) ($user['username'] ?? '-')) ?> (<?= esc((string) ($user['email'] ?? '-')) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">İzin Matrisi</h5>
        <span class="badge bg-light-secondary text-secondary">Deny-by-default</span>
      </div>
      <div class="card-body">
        <?php if ($error !== ''): ?>
          <div class="alert alert-danger"><?= esc($error) ?></div>
        <?php endif; ?>
        <div id="permAlert" class="alert d-none"></div>

        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>Modül</th>
                <th>Permission</th>
                <th>Açıklama</th>
                <th>Kişiye Özel</th>
                <th class="text-end">İzin</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($matrix === []): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">Gösterilecek izin bulunamadı.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($matrix as $row): ?>
                  <tr>
                    <td><?= esc((string) ($row['module'] ?? '-')) ?></td>
                    <td><code><?= esc((string) ($row['code'] ?? '-')) ?></code></td>
                    <td><?= esc((string) ($row['description'] ?? '')) ?></td>
                    <td>
                      <?php if (($row['override'] ?? false) === true): ?>
                        <span class="badge bg-light-warning text-warning">Override</span>
                      <?php else: ?>
                        <span class="badge bg-light-secondary text-secondary">Rol</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end">
                      <div class="form-check form-switch d-inline-block m-0">
                        <input
                          class="form-check-input perm-toggle"
                          type="checkbox"
                          data-user-id="<?= esc($selectedUserId) ?>"
                          data-perm-code="<?= esc((string) ($row['code'] ?? '')) ?>"
                          <?= ($row['effective'] ?? false) ? 'checked' : '' ?>
                        >
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    var endpoint = "<?= site_url('admin/settings/permissions/update') ?>";
    var alertBox = document.getElementById('permAlert');

    function showAlert(message, type) {
      if (!alertBox) return;
      if (!message) {
        alertBox.className = 'alert d-none';
        alertBox.textContent = '';
        return;
      }
      alertBox.className = 'alert alert-' + (type || 'info');
      alertBox.textContent = message;
    }

    document.addEventListener('change', function (event) {
      var el = event.target;
      if (!el.classList.contains('perm-toggle')) return;

      var fd = new FormData();
      fd.append('user_id', el.getAttribute('data-user-id') || '');
      fd.append('perm_code', el.getAttribute('data-perm-code') || '');
      fd.append('allowed', el.checked ? '1' : '0');
      fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

      fetch(endpoint, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json.ok) {
          throw new Error(json.message || 'İşlem başarısız.');
        }
        showAlert(json.message || 'Kaydedildi.', 'success');
        setTimeout(function () { window.location.reload(); }, 350);
      })
      .catch(function (err) {
        el.checked = !el.checked;
        showAlert(err.message || 'İşlem başarısız.', 'danger');
      });
    });
  })();
</script>
<?= $this->endSection() ?>