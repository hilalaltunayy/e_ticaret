<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<div class="pc-container">
    <div class="pc-content">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5 text-center">
                <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 72px; height: 72px;">
                    <i class="ti ti-route text-primary" style="font-size: 1.75rem;"></i>
                </div>
                <h1 class="h3 mb-3"><?= esc($pageTitle ?? 'Sayfa hazirlaniyor') ?></h1>
                <p class="text-muted mx-auto mb-4" style="max-width: 560px;">
                    <?= esc($pageDescription ?? 'Aradiginiz icerik su anda hazirlaniyor. Dilerseniz calisan sayfalardan devam edebilirsiniz.') ?>
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="<?= esc((string) ($primaryActionUrl ?? base_url('/'))) ?>" class="btn btn-primary">
                        <?= esc((string) ($primaryActionLabel ?? 'Devam Et')) ?>
                    </a>
                    <a href="<?= esc((string) ($secondaryActionUrl ?? base_url('/'))) ?>" class="btn btn-outline-secondary">
                        <?= esc((string) ($secondaryActionLabel ?? 'Ana Sayfaya Don')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
