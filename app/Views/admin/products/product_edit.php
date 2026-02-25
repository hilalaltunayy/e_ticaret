<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$productId = (string) ($product['id'] ?? '');
$returnToEditUrl = site_url('admin/products/edit/' . $productId);
$selectedAuthorId = old('author_id', (string) (session()->getFlashdata('new_author_id') ?? ($product['author_id'] ?? '')));
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Ürün Düzenle</h4>
            <a href="<?= site_url('admin/products') ?>" class="btn btn-light">Listeye Dön</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Temel Bilgiler</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/products/update/' . $productId) ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Ürün Adı</label>
                            <input type="text" name="product_name" class="form-control" value="<?= esc(old('product_name', (string) ($product['product_name'] ?? ''))) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Yazar</label>
                            <select name="author_id" id="authorSelect" class="form-select" required>
                                <option value="">Yazar seçin</option>
                                <option value="__new__">+ Yeni yazar ekle</option>
                                <?php foreach (($authors ?? []) as $author): ?>
                                    <?php $authorId = (string) ($author['id'] ?? ''); ?>
                                    <option value="<?= esc($authorId) ?>" <?= $selectedAuthorId === $authorId ? 'selected' : '' ?>>
                                        <?= esc((string) ($author['name'] ?? '-')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fiyat</label>
                            <input type="number" name="price" step="0.01" min="0" class="form-control" value="<?= esc(old('price', (string) ($product['price'] ?? '0'))) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Açıklama (opsiyonel)</label>
                            <textarea name="description" rows="4" class="form-control"><?= esc(old('description', (string) ($product['description'] ?? ''))) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= site_url('admin/products') ?>" class="btn btn-light">İptal</a>
                            <button type="submit" class="btn btn-primary">Güncelle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    const authorSelect = document.getElementById('authorSelect');
    const authorCreateUrl = "<?= site_url('admin/authors/create') ?>?return=<?= rawurlencode($returnToEditUrl) ?>";

    if (authorSelect) {
      authorSelect.addEventListener('change', function () {
        if (authorSelect.value === '__new__') {
          window.location.href = authorCreateUrl;
        }
      });
    }
  })();
</script>
<?= $this->endSection() ?>
