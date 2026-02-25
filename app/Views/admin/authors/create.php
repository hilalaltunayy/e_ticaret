<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Yeni Yazar Ekle</h4>
            <a href="<?= esc($returnUrl ?? site_url('admin/products')) ?>" class="btn btn-light">Geri Dön</a>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (isset($validation) && $validation->getErrors()): ?>
    <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Yazar Bilgisi</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/authors/store') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="return_url" value="<?= esc($returnUrl ?? site_url('admin/products')) ?>">

                    <div class="mb-3">
                        <label class="form-label">Yazar Adı</label>
                        <input type="text" name="name" class="form-control" value="<?= esc(old('name')) ?>" required>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= esc($returnUrl ?? site_url('admin/products')) ?>" class="btn btn-light">Vazgeç</a>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
