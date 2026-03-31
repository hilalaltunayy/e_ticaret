<?php
$formData = is_array($formData ?? null) ? $formData : [];
$meta = is_array($meta ?? null) ? $meta : [];
$categories = is_array($meta['categories'] ?? null) ? $meta['categories'] : [];
$products = is_array($meta['products'] ?? null) ? $meta['products'] : [];
$val = static function (string $key, $default = null) use ($formData) {
    $oldValue = old($key);
    if ($oldValue !== null) {
        return $oldValue;
    }
    return $formData[$key] ?? $default;
};

$selectedCategoriesRaw = $val('category_ids', []);
$selectedProductsRaw = $val('product_ids', []);
$selectedCategories = is_array($selectedCategoriesRaw) ? $selectedCategoriesRaw : [];
$selectedProducts = is_array($selectedProductsRaw) ? $selectedProductsRaw : [];
$selectedTarget = (string) $val('target', 'global');
$selectedType = (string) $val('type', 'percentage');
?>
<?php if (! empty($errors ?? [])): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach (($errors ?? []) as $error): ?>
        <li><?= esc((string) $error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<input type="hidden" id="rule_target_id" name="target_id" value="<?= esc((string) $val('target_id', '')) ?>">

<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label" for="rule_name">Kural Adı</label>
    <input type="text" id="rule_name" name="name" class="form-control" placeholder="Örn. Bahar İndirimi" value="<?= esc((string) $val('name', '')) ?>" required>
  </div>

  <div class="col-md-6">
    <label class="form-label" for="rule_type">Tip</label>
    <select id="rule_type" name="type" class="form-select" required>
      <option value="percentage" <?= $selectedType === 'percentage' ? 'selected' : '' ?>>Yüzde</option>
      <option value="fixed" <?= $selectedType === 'fixed' ? 'selected' : '' ?>>Sabit Tutar</option>
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label" id="rule_value_label" for="rule_value">İndirim Değeri (%)</label>
    <input type="number" id="rule_value" name="value" step="0.01" min="0" class="form-control" placeholder="0.00" value="<?= esc((string) $val('value', '')) ?>" required>
    <small class="text-muted" id="rule_value_help">Yüzde tipinde 0 ile 100 arasında bir oran girin.</small>
  </div>

  <div class="col-md-4">
    <label class="form-label" for="rule_target">Hedef</label>
    <select id="rule_target" name="target" class="form-select" required>
      <option value="global" <?= $selectedTarget === 'global' ? 'selected' : '' ?>>Global</option>
      <option value="product" <?= $selectedTarget === 'product' ? 'selected' : '' ?>>Ürün</option>
      <option value="category" <?= $selectedTarget === 'category' ? 'selected' : '' ?>>Kategori</option>
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label" for="rule_target_id_preview">Hedef ID</label>
    <input type="text" id="rule_target_id_preview" class="form-control" placeholder="Global için boş bırakılır" value="<?= esc((string) $val('target_id', '')) ?>" readonly>
    <small class="text-muted" id="rule_target_help">Global seçimde hedef ID boş kalır.</small>
  </div>

  <div class="col-md-6">
    <label class="form-label" for="rule_priority">Öncelik</label>
    <input type="number" id="rule_priority" name="priority" min="0" class="form-control" value="<?= esc((string) $val('priority', '0')) ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label" for="rule_is_active">Aktif mi</label>
    <select id="rule_is_active" name="is_active" class="form-select">
      <option value="1" <?= (string) $val('is_active', 1) === '1' ? 'selected' : '' ?>>Aktif</option>
      <option value="0" <?= (string) $val('is_active', 1) === '0' ? 'selected' : '' ?>>Pasif</option>
    </select>
  </div>

  <div class="col-md-6 d-none" id="category_target_wrap">
    <label class="form-label" for="category_ids">Kategori Seçimi</label>
    <select id="category_ids" name="category_ids[]" class="form-select" multiple size="8">
      <?php foreach ($categories as $category): ?>
        <?php $categoryId = trim((string) ($category['id'] ?? '')); ?>
        <?php if ($categoryId === '') {
    continue;
} ?>
        <option value="<?= esc($categoryId) ?>" <?= in_array($categoryId, $selectedCategories, true) ? 'selected' : '' ?>>
          <?= esc((string) ($category['category_name'] ?? '-')) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <small class="text-muted">Bir veya birden fazla kategori seçebilirsiniz.</small>
  </div>

  <div class="col-md-6 d-none" id="product_target_wrap">
    <label class="form-label" for="product_search">Ürün Seçimi</label>
    <input type="search" id="product_search" class="form-control mb-2" placeholder="Ürün ara">
    <select id="product_ids" name="product_ids[]" class="form-select" multiple size="8">
      <?php foreach ($products as $product): ?>
        <?php $productId = trim((string) ($product['id'] ?? '')); ?>
        <?php if ($productId === '') {
    continue;
} ?>
        <option value="<?= esc($productId) ?>" <?= in_array($productId, $selectedProducts, true) ? 'selected' : '' ?>>
          <?= esc((string) ($product['product_name'] ?? '-')) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <small class="text-muted">Bir veya birden fazla ürün seçebilirsiniz.</small>
  </div>
</div>

<script>
  (function () {
    var typeEl = document.getElementById('rule_type');
    var valueEl = document.getElementById('rule_value');
    var valueLabelEl = document.getElementById('rule_value_label');
    var valueHelpEl = document.getElementById('rule_value_help');
    var targetEl = document.getElementById('rule_target');
    var categoryWrap = document.getElementById('category_target_wrap');
    var productWrap = document.getElementById('product_target_wrap');
    var categorySelect = document.getElementById('category_ids');
    var productSelect = document.getElementById('product_ids');
    var productSearch = document.getElementById('product_search');
    var targetIdHidden = document.getElementById('rule_target_id');
    var targetIdPreview = document.getElementById('rule_target_id_preview');
    var targetHelpEl = document.getElementById('rule_target_help');

    if (!typeEl || !valueEl || !valueLabelEl || !valueHelpEl || !targetEl || !categoryWrap || !productWrap || !categorySelect || !productSelect || !targetIdHidden || !targetIdPreview || !targetHelpEl) {
      return;
    }

    function getSelectedValues(selectEl) {
      return Array.prototype.slice.call(selectEl.options)
        .filter(function (option) { return option.selected; })
        .map(function (option) { return option.value; });
    }

    function syncTypeField() {
      if (typeEl.value === 'fixed') {
        valueLabelEl.textContent = 'Sabit Tutar';
        valueHelpEl.textContent = 'Sabit tutar tipinde para birimi bazlı indirim girin.';
        valueEl.removeAttribute('max');
      } else {
        valueLabelEl.textContent = 'İndirim Değeri (%)';
        valueHelpEl.textContent = 'Yüzde tipinde 0 ile 100 arasında bir oran girin.';
        valueEl.setAttribute('max', '100');
      }
    }

    function syncTargetField() {
      var target = targetEl.value;
      var isGlobal = target === 'global';
      var isCategory = target === 'category';
      var isProduct = target === 'product';

      categoryWrap.classList.toggle('d-none', !isCategory);
      productWrap.classList.toggle('d-none', !isProduct);

      categorySelect.disabled = !isCategory;
      productSelect.disabled = !isProduct;

      if (isGlobal) {
        Array.prototype.forEach.call(categorySelect.options, function (option) { option.selected = false; });
        Array.prototype.forEach.call(productSelect.options, function (option) { option.selected = false; });
        targetIdHidden.value = '';
        targetIdPreview.value = '';
        targetHelpEl.textContent = 'Global seçimde hedef ID boş kalır.';
        return;
      }

      syncTargetIdValue();
    }

    function syncTargetIdValue() {
      var target = targetEl.value;
      var selectedValues = [];

      if (target === 'category') {
        selectedValues = getSelectedValues(categorySelect);
        targetHelpEl.textContent = 'Seçilen kategori ID değerleri target_id alanına virgülle ayrılmış olarak hazırlanır.';
      } else if (target === 'product') {
        selectedValues = getSelectedValues(productSelect);
        targetHelpEl.textContent = 'Seçilen ürün ID değerleri target_id alanına virgülle ayrılmış olarak hazırlanır.';
      } else {
        targetHelpEl.textContent = 'Global seçimde hedef ID boş kalır.';
      }

      var serialized = selectedValues.join(',');
      targetIdHidden.value = serialized;
      targetIdPreview.value = serialized;
    }

    function filterProductOptions() {
      if (!productSearch) {
        return;
      }

      var query = productSearch.value.toLocaleLowerCase('tr-TR');
      Array.prototype.forEach.call(productSelect.options, function (option) {
        var text = (option.text || '').toLocaleLowerCase('tr-TR');
        option.hidden = query !== '' && text.indexOf(query) === -1;
      });
    }

    typeEl.addEventListener('change', syncTypeField);
    targetEl.addEventListener('change', syncTargetField);
    categorySelect.addEventListener('change', syncTargetIdValue);
    productSelect.addEventListener('change', syncTargetIdValue);

    if (productSearch) {
      productSearch.addEventListener('input', filterProductOptions);
    }

    syncTypeField();
    syncTargetField();
    filterProductOptions();
  })();
</script>
