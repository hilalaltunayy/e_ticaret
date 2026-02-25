<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$typeOptions = ['basili', 'dijital', 'paket'];
if (! empty($types ?? [])) {
    $typeOptions = [];
    foreach ($types as $typeRow) {
        $rawType = strtolower(trim((string) ($typeRow['name'] ?? '')));
        if (in_array($rawType, ['basili', 'dijital', 'paket'], true)) {
            $typeOptions[] = $rawType;
        }
    }
    $typeOptions = array_values(array_unique($typeOptions));
    if (empty($typeOptions)) {
        $typeOptions = ['basili', 'dijital', 'paket'];
    }
}
$selectedCategory = old('category_id', (string) (session()->getFlashdata('new_category_id') ?? ''));
$returnToCreateUrl = site_url('admin/products/create');
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Yeni Ürün Ekle</h4>
            <a href="<?= site_url('admin/products') ?>" class="btn btn-light">Listeye Dön</a>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (isset($validation) && $validation->getErrors()): ?>
    <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ürün Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="<?= site_url('admin/products/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ürün Adı</label>
                            <input type="text" name="product_name" class="form-control" value="<?= esc(old('product_name')) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Yazar</label>
                            <select name="author_id" id="authorSelect" class="form-select">
                                <option value="">Yazar seçin</option>
                                <option value="__new__" <?= old('author_id') === '__new__' ? 'selected' : '' ?>>+ Yeni yazar ekle</option>
                                <?php foreach (($authors ?? []) as $author): ?>
                                    <?php $id = (string) ($author['id'] ?? ''); ?>
                                    <option value="<?= esc($id) ?>" <?= old('author_id') === $id ? 'selected' : '' ?>>
                                        <?= esc($author['name'] ?? '-') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 <?= old('author_id') === '__new__' ? '' : 'd-none' ?>" id="newAuthorWrap">
                            <label class="form-label">Yeni yazar adı</label>
                            <input type="text" name="new_author_name" id="newAuthorInput" class="form-control" placeholder="Yeni yazar adı" value="<?= esc(old('new_author_name')) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" id="categorySelect" class="form-select" required>
                                <option value="">Kategori seçin</option>
                                <option value="__new__">+ Yeni kategori ekle</option>
                                <?php foreach (($categories ?? []) as $category): ?>
                                    <?php $categoryId = (string) ($category['id'] ?? ''); ?>
                                    <option value="<?= esc($categoryId) ?>" <?= $selectedCategory === $categoryId ? 'selected' : '' ?>>
                                        <?= esc($category['category_name'] ?? '-') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tür</label>
                            <select name="type" id="typeSelect" class="form-select" required>
                                <?php foreach ($typeOptions as $type): ?>
                                    <option value="<?= esc($type) ?>" <?= old('type', 'basili') === $type ? 'selected' : '' ?>>
                                        <?= esc(ucfirst($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Fiyat</label>
                            <input type="number" name="price" step="0.01" min="0" class="form-control" value="<?= esc(old('price', '0.00')) ?>" required>
                        </div>

                        <div class="col-md-4" id="stockWrap">
                            <label class="form-label">Stok Adedi</label>
                            <input type="number" name="stock_count" id="stockInput" min="0" class="form-control" value="<?= esc(old('stock_count', '0')) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Aktiflik</label>
                            <select name="is_active" class="form-select" required>
                                <option value="1" <?= old('is_active', '1') === '1' ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= old('is_active') === '0' ? 'selected' : '' ?>>Pasif</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <textarea name="description" class="form-control" rows="4"><?= esc(old('description')) ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?= site_url('admin/products') ?>" class="btn btn-light">İptal</a>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    const typeSelect = document.getElementById('typeSelect');
    const stockWrap = document.getElementById('stockWrap');
    const stockInput = document.getElementById('stockInput');
    const authorSelect = document.getElementById('authorSelect');
    const newAuthorWrap = document.getElementById('newAuthorWrap');
    const newAuthorInput = document.getElementById('newAuthorInput');
    const categorySelect = document.getElementById('categorySelect');
    const categoryCreateUrl = "<?= site_url('admin/categories/create') ?>?return=<?= rawurlencode($returnToCreateUrl) ?>";

    function syncStockField() {
      if (!typeSelect) return;
      if (typeSelect.value === 'dijital') {
        stockWrap.classList.add('d-none');
        stockInput.value = 0;
      } else {
        stockWrap.classList.remove('d-none');
      }
    }

    function syncNewAuthorField() {
      if (!authorSelect || !newAuthorWrap || !newAuthorInput) return;
      if (authorSelect.value === '__new__') {
        newAuthorWrap.classList.remove('d-none');
        newAuthorInput.required = true;
      } else {
        newAuthorWrap.classList.add('d-none');
        newAuthorInput.required = false;
      }
    }

    function syncCategorySelection() {
      if (!categorySelect) return;
      if (categorySelect.value === '__new__') {
        window.location.href = categoryCreateUrl;
      }
    }

    typeSelect.addEventListener('change', syncStockField);
    authorSelect.addEventListener('change', syncNewAuthorField);
    categorySelect.addEventListener('change', syncCategorySelection);

    syncStockField();
    syncNewAuthorField();
  })();
</script>
<?= $this->endSection() ?>
