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
  <div class="col-md-4">
    <label class="form-label">Kupon Kodu</label>
    <input type="text" name="code" class="form-control" required value="<?= esc((string) $val('code', '')) ?>" placeholder="ORN: INDIRIM10">
  </div>
  <div class="col-md-4">
    <label class="form-label">Kupon Türü</label>
    <select name="coupon_kind" id="coupon_kind" class="form-select" required>
      <option value="discount" <?= (string) $val('coupon_kind', '') === 'discount' ? 'selected' : '' ?>>İndirim</option>
      <option value="free_shipping" <?= (string) $val('coupon_kind', '') === 'free_shipping' ? 'selected' : '' ?>>Ücretsiz Kargo</option>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">İndirim Tipi</label>
    <select name="discount_type" id="discount_type" class="form-select">
      <option value="percent" <?= (string) $val('discount_type', '') === 'percent' ? 'selected' : '' ?>>Yüzde</option>
      <option value="fixed" <?= (string) $val('discount_type', '') === 'fixed' ? 'selected' : '' ?>>Sabit Tutar</option>
      <option value="none" <?= (string) $val('discount_type', '') === 'none' ? 'selected' : '' ?>>Yok</option>
    </select>
  </div>

  <div class="col-md-3">
    <label class="form-label">İndirim Değeri</label>
    <input type="number" step="0.01" min="0" name="discount_value" id="discount_value" class="form-control" value="<?= esc((string) $val('discount_value', '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Minimum Sepet</label>
    <input type="number" step="0.01" min="0" name="min_cart_amount" class="form-control" value="<?= esc((string) $val('min_cart_amount', '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Toplam Kullanım Limiti</label>
    <input type="number" min="0" name="max_usage_total" class="form-control" value="<?= esc((string) $val('max_usage_total', '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Kullanıcı Başı Limit</label>
    <input type="number" min="0" name="max_usage_per_user" class="form-control" value="<?= esc((string) $val('max_usage_per_user', '')) ?>">
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
    <label class="form-label">Durum</label>
    <select name="is_active" class="form-select">
      <option value="1" <?= (string) $val('is_active', 1) === '1' ? 'selected' : '' ?>>Aktif</option>
      <option value="0" <?= (string) $val('is_active', 1) === '0' ? 'selected' : '' ?>>Pasif</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Yeni Müşteri Kısıtı</label>
    <select name="is_first_order_only" class="form-select">
      <option value="0" <?= (string) $val('is_first_order_only', 0) === '0' ? 'selected' : '' ?>>Hayır</option>
      <option value="1" <?= (string) $val('is_first_order_only', 0) === '1' ? 'selected' : '' ?>>Evet</option>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">Kategori Kısıtları</label>
    <select name="category_ids[]" class="form-select" multiple size="8">
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
  <div class="col-md-6">
    <label class="form-label">Ürün Kısıtları</label>
    <select name="product_ids[]" class="form-select" multiple size="8">
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
    var kindEl = document.getElementById('coupon_kind');
    var typeEl = document.getElementById('discount_type');
    var valueEl = document.getElementById('discount_value');
    if (!kindEl || !typeEl || !valueEl) return;

    function syncByKind() {
      if (kindEl.value === 'free_shipping') {
        typeEl.value = 'none';
        valueEl.value = '0';
      } else {
        if (typeEl.value === 'none') {
          typeEl.value = 'percent';
        }
      }
    }

    kindEl.addEventListener('change', syncByKind);
    syncByKind();
  })();
</script>
