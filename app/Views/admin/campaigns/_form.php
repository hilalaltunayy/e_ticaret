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

$categoryIdsRaw = $val('category_ids', []);
$productIdsRaw = $val('product_ids', []);
$categoryIds = is_array($categoryIdsRaw) ? $categoryIdsRaw : [];
$productIds = is_array($productIdsRaw) ? $productIdsRaw : [];
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

<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Kampanya Adı</label>
    <input type="text" name="name" class="form-control" required value="<?= esc((string) $val('name', '')) ?>" placeholder="Örn: Bahar İndirimi">
  </div>
  <div class="col-md-6">
    <label class="form-label">Slug (Opsiyonel)</label>
    <input type="text" name="slug" class="form-control" value="<?= esc((string) $val('slug', '')) ?>" placeholder="orn-bahar-indirimi">
  </div>

  <div class="col-md-4">
    <label class="form-label">Kampanya Türü</label>
    <select name="campaign_type" id="campaign_type" class="form-select" required>
      <option value="category_discount" <?= (string) $val('campaign_type', 'cart_discount') === 'category_discount' ? 'selected' : '' ?>>Kategori İndirimi</option>
      <option value="product_discount" <?= (string) $val('campaign_type', 'cart_discount') === 'product_discount' ? 'selected' : '' ?>>Ürün İndirimi</option>
      <option value="cart_discount" <?= (string) $val('campaign_type', 'cart_discount') === 'cart_discount' ? 'selected' : '' ?>>Sepet İndirimi</option>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">İndirim Türü</label>
    <select name="discount_type" id="discount_type" class="form-select" required>
      <option value="percent" <?= (string) $val('discount_type', 'percent') === 'percent' ? 'selected' : '' ?>>Yüzde</option>
      <option value="fixed" <?= (string) $val('discount_type', 'percent') === 'fixed' ? 'selected' : '' ?>>Sabit Tutar</option>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">İndirim Değeri</label>
    <input type="number" step="0.01" min="0" name="discount_value" class="form-control" required value="<?= esc((string) $val('discount_value', '')) ?>">
  </div>

  <div class="col-md-3">
    <label class="form-label">Minimum Sepet</label>
    <input type="number" step="0.01" min="0" name="min_cart_amount" class="form-control" value="<?= esc((string) $val('min_cart_amount', '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Başlangıç</label>
    <input type="datetime-local" name="starts_at" class="form-control" value="<?= esc((string) $val('starts_at', '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Bitiş</label>
    <input type="datetime-local" name="ends_at" class="form-control" value="<?= esc((string) $val('ends_at', '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Öncelik</label>
    <input type="number" min="0" name="priority" class="form-control" value="<?= esc((string) $val('priority', '0')) ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">Sonraki kuralları durdur</label>
    <select name="stop_further_rules" class="form-select">
      <option value="0" <?= (string) $val('stop_further_rules', 0) === '0' ? 'selected' : '' ?>>Hayır</option>
      <option value="1" <?= (string) $val('stop_further_rules', 0) === '1' ? 'selected' : '' ?>>Evet</option>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Durum</label>
    <select name="is_active" class="form-select">
      <option value="1" <?= (string) $val('is_active', 1) === '1' ? 'selected' : '' ?>>Aktif</option>
      <option value="0" <?= (string) $val('is_active', 1) === '0' ? 'selected' : '' ?>>Pasif</option>
    </select>
  </div>

  <div class="col-md-6" id="category_target_wrap">
    <label class="form-label">Hedef Kategoriler</label>
    <select id="category_ids" name="category_ids[]" class="form-select" multiple size="8">
      <?php foreach ($categories as $category): ?>
        <?php $categoryId = trim((string) ($category['id'] ?? '')); ?>
        <?php if ($categoryId === '') {
    continue;
} ?>
        <option value="<?= esc($categoryId) ?>" <?= in_array($categoryId, $categoryIds, true) ? 'selected' : '' ?>>
          <?= esc((string) ($category['category_name'] ?? '-')) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-6" id="product_target_wrap">
    <label class="form-label">Hedef Ürünler</label>
    <select id="product_ids" name="product_ids[]" class="form-select" multiple size="8">
      <?php foreach ($products as $product): ?>
        <?php $productId = trim((string) ($product['id'] ?? '')); ?>
        <?php if ($productId === '') {
    continue;
} ?>
        <option value="<?= esc($productId) ?>" <?= in_array($productId, $productIds, true) ? 'selected' : '' ?>>
          <?= esc((string) ($product['product_name'] ?? '-')) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<script>
  (function () {
    var typeEl = document.getElementById('campaign_type');
    var categoryWrap = document.getElementById('category_target_wrap');
    var productWrap = document.getElementById('product_target_wrap');
    var categorySelect = document.getElementById('category_ids');
    var productSelect = document.getElementById('product_ids');
    if (!typeEl || !categoryWrap || !productWrap || !categorySelect || !productSelect) return;

    function syncTargetFields() {
      var type = typeEl.value;
      var showCategory = type === 'category_discount';
      var showProduct = type === 'product_discount';

      categoryWrap.classList.toggle('d-none', !showCategory);
      productWrap.classList.toggle('d-none', !showProduct);

      categorySelect.disabled = !showCategory;
      productSelect.disabled = !showProduct;
    }

    typeEl.addEventListener('change', syncTargetFields);
    syncTargetFields();
  })();
</script>

