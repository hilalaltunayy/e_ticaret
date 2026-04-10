<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-sm-8">
        <div class="page-header-title">
          <h2 class="mb-0"><?= esc($title ?? 'E-posta / SMS Gönderimi') ?></h2>
        </div>
        <div class="text-muted mt-2">E-posta ve SMS operasyonları için başlangıç yönetim paneli. Bu sprintte tek kişiye test e-postası gönderimi desteklenir.</div>
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
        <form action="<?= site_url('admin/notifications/test-email') ?>" method="post">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Alıcı E-posta</label>
            <input type="email" name="test_email_to" class="form-control" value="<?= esc(old('test_email_to', '')) ?>" placeholder="ornek@site.com" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Konu</label>
            <input type="text" name="test_email_subject" class="form-control" value="<?= esc(old('test_email_subject', 'BeAble Pro test e-postası')) ?>" placeholder="E-posta konusu" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mesaj</label>
            <textarea name="test_email_message" rows="7" class="form-control" placeholder="Test e-posta mesajınızı yazın." required><?= esc(old('test_email_message', "Merhaba,\n\nBu mesaj BeAble Pro admin panelindeki test e-posta gönderimi ekranından iletilmiştir.\n\nİyi çalışmalar.")) ?></textarea>
          </div>
          <div class="d-flex justify-content-between align-items-center gap-3">
            <div class="small text-muted">Tekli test gönderimi yapılır. Toplu gönderim ve kuyruk sistemi bu sprint kapsamı dışındadır.</div>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-send me-1"></i> Test E-postası Gönder
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-5">
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
        <div class="alert alert-light border mt-3 mb-0">Gerçek gönderim kayıtları ve kanal raporları sonraki sprintlerde bu alana bağlanacaktır.</div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Hazır Şablonlar ve Placeholder Alanlar</h5>
      </div>
      <div class="card-body">
        <div class="d-grid gap-3">
          <?php foreach (($placeholders ?? []) as $placeholder): ?>
            <div class="border rounded p-3">
              <div class="fw-semibold mb-1"><?= esc((string) ($placeholder['title'] ?? 'Placeholder')) ?></div>
              <div class="small text-muted"><?= esc((string) ($placeholder['description'] ?? '')) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
