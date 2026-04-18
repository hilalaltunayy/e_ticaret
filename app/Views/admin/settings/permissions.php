<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$secretaries = is_array($secretaries ?? null) ? $secretaries : [];
$selectedUserId = (string) ($selectedUserId ?? '');
$matrix = is_array($matrix ?? null) ? $matrix : [];
$error = (string) ($error ?? '');
$validation = $validation ?? session('validation');
$openSecretaryModal = (bool) ($openSecretaryModal ?? false);
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
      <div class="card-header d-flex justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Kullanici Secimi</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createSecretaryModal">
          Yeni Sekreter Ekle
        </button>
      </div>
      <div class="card-body">
        <?php if (session('success')): ?>
          <div class="alert alert-success"><?= esc((string) session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
          <div class="alert alert-danger"><?= esc((string) session('error')) ?></div>
        <?php endif; ?>

        <form method="get" action="<?= site_url('admin/settings/permissions') ?>">
          <label for="user_id" class="form-label">Secretary Kullanicisi</label>
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
        <h5 class="mb-0">Izin Matrisi</h5>
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
                <th>Modul</th>
                <th>Permission</th>
                <th>Aciklama</th>
                <th>Kisiye Ozel</th>
                <th class="text-end">Izin</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($matrix === []): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">Gosterilecek izin bulunamadi.</td>
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

<div class="modal fade" id="createSecretaryModal" tabindex="-1" aria-labelledby="createSecretaryModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= site_url('admin/settings/permissions/secretaries/create') ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="createSecretaryModalTitle">Yeni Sekreter Ekle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <?= csrf_field() ?>
          <input type="hidden" name="return_user_id" value="<?= esc($selectedUserId) ?>">

          <?php if ($validation && $validation->getErrors()): ?>
            <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
          <?php endif; ?>

          <div class="row g-3">
            <div class="col-12">
              <label for="secretary_username" class="form-label">Kullanici Adi / Ad</label>
              <input type="text" class="form-control" id="secretary_username" name="username" value="<?= esc((string) old('username', '')) ?>" maxlength="100" required>
            </div>
            <div class="col-12">
              <label for="secretary_email" class="form-label">E-posta</label>
              <input type="email" class="form-control" id="secretary_email" name="email" value="<?= esc((string) old('email', '')) ?>" maxlength="100" required>
            </div>
            <div class="col-12 col-md-6">
              <label for="secretary_password" class="form-label">Sifre</label>
              <input type="password" class="form-control" id="secretary_password" name="password" required>
            </div>
            <div class="col-12 col-md-6">
              <label for="secretary_password_confirm" class="form-label">Sifre Tekrar</label>
              <input type="password" class="form-control" id="secretary_password_confirm" name="password_confirm" required>
            </div>
            <div class="col-12">
              <label for="secretary_status" class="form-label">Durum</label>
              <select class="form-select" id="secretary_status" name="status" required>
                <option value="active"<?= old('status', 'active') === 'active' ? ' selected' : '' ?>>Aktif</option>
                <option value="suspended"<?= old('status', 'active') === 'suspended' ? ' selected' : '' ?>>Askida</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Vazgec</button>
          <button type="submit" class="btn btn-primary">Sekreteri Olustur</button>
        </div>
      </form>
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
          throw new Error(json.message || 'Islem basarisiz.');
        }
        showAlert(json.message || 'Kaydedildi.', 'success');
        setTimeout(function () { window.location.reload(); }, 350);
      })
      .catch(function (err) {
        el.checked = !el.checked;
        showAlert(err.message || 'Islem basarisiz.', 'danger');
      });
    });

    <?php if ($openSecretaryModal): ?>
    var createSecretaryModalElement = document.getElementById('createSecretaryModal');
    if (createSecretaryModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(createSecretaryModalElement).show();
    }
    <?php endif; ?>
  })();
</script>
<?= $this->endSection() ?>
