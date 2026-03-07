<?php

namespace App\Services;

use App\DTO\Marketing\CouponDTO;
use App\Models\CategoryModel;
use App\Models\CouponModel;
use App\Models\CouponTargetModel;
use App\Models\ProductsModel;

class CouponService
{
    public function __construct(
        private ?CouponModel $couponModel = null,
        private ?CouponTargetModel $couponTargetModel = null,
        private ?CategoryModel $categoryModel = null,
        private ?ProductsModel $productsModel = null
    ) {
        $this->couponModel = $this->couponModel ?? new CouponModel();
        $this->couponTargetModel = $this->couponTargetModel ?? new CouponTargetModel();
        $this->categoryModel = $this->categoryModel ?? new CategoryModel();
        $this->productsModel = $this->productsModel ?? new ProductsModel();
    }

    public function listCoupons(): array
    {
        $coupons = $this->couponModel->listForAdmin();
        $couponIds = array_values(array_filter(array_map(static fn ($row) => (string) ($row['id'] ?? ''), $coupons)));
        $targets = $this->couponTargetModel->getTargetsByCouponIds($couponIds);

        $targetsByCoupon = [];
        foreach ($targets as $target) {
            $couponId = (string) ($target['coupon_id'] ?? '');
            if ($couponId === '') {
                continue;
            }
            if (! isset($targetsByCoupon[$couponId])) {
                $targetsByCoupon[$couponId] = ['category' => 0, 'product' => 0];
            }
            $type = (string) ($target['target_type'] ?? '');
            if ($type === 'category') {
                $targetsByCoupon[$couponId]['category']++;
            } elseif ($type === 'product') {
                $targetsByCoupon[$couponId]['product']++;
            }
        }

        $items = [];
        $nowTs = time();
        $activeCount = 0;
        $passiveCount = 0;
        $expiringSoonCount = 0;

        foreach ($coupons as $coupon) {
            $id = (string) ($coupon['id'] ?? '');
            $isActive = (int) ($coupon['is_active'] ?? 0) === 1;
            if ($isActive) {
                $activeCount++;
            } else {
                $passiveCount++;
            }

            $endsAt = trim((string) ($coupon['ends_at'] ?? ''));
            if ($isActive && $endsAt !== '') {
                $endsAtTs = strtotime($endsAt);
                if ($endsAtTs !== false && $endsAtTs >= $nowTs && $endsAtTs <= ($nowTs + 7 * 86400)) {
                    $expiringSoonCount++;
                }
            }

            $items[] = [
                'id' => $id,
                'code' => (string) ($coupon['code'] ?? ''),
                'coupon_kind' => (string) ($coupon['coupon_kind'] ?? 'discount'),
                'discount_type' => (string) ($coupon['discount_type'] ?? 'none'),
                'discount_value' => $coupon['discount_value'] !== null ? (float) $coupon['discount_value'] : null,
                'min_cart_amount' => $coupon['min_cart_amount'] !== null ? (float) $coupon['min_cart_amount'] : null,
                'max_usage_total' => $coupon['max_usage_total'] !== null ? (int) $coupon['max_usage_total'] : null,
                'max_usage_per_user' => $coupon['max_usage_per_user'] !== null ? (int) $coupon['max_usage_per_user'] : null,
                'starts_at' => (string) ($coupon['starts_at'] ?? ''),
                'ends_at' => (string) ($coupon['ends_at'] ?? ''),
                'is_active' => $isActive,
                'is_first_order_only' => (int) ($coupon['is_first_order_only'] ?? 0) === 1,
                'target_counts' => $targetsByCoupon[$id] ?? ['category' => 0, 'product' => 0],
            ];
        }

        return [
            'items' => $items,
            'summary' => [
                'total' => count($items),
                'active' => $activeCount,
                'passive' => $passiveCount,
                'expiring_soon' => $expiringSoonCount,
            ],
        ];
    }

    public function getCouponForEdit(string $couponId): ?array
    {
        $coupon = $this->couponModel->find($couponId);
        if (! is_array($coupon)) {
            return null;
        }

        $targets = $this->couponTargetModel->getTargetsByCouponId($couponId);
        $categoryIds = [];
        $productIds = [];
        foreach ($targets as $target) {
            $targetType = (string) ($target['target_type'] ?? '');
            $targetId = trim((string) ($target['target_id'] ?? ''));
            if ($targetId === '') {
                continue;
            }
            if ($targetType === 'category') {
                $categoryIds[] = $targetId;
            } elseif ($targetType === 'product') {
                $productIds[] = $targetId;
            }
        }

        return [
            'id' => (string) ($coupon['id'] ?? ''),
            'code' => (string) ($coupon['code'] ?? ''),
            'coupon_kind' => (string) ($coupon['coupon_kind'] ?? 'discount'),
            'discount_type' => (string) ($coupon['discount_type'] ?? 'none'),
            'discount_value' => $coupon['discount_value'] !== null ? (string) $coupon['discount_value'] : '',
            'min_cart_amount' => $coupon['min_cart_amount'] !== null ? (string) $coupon['min_cart_amount'] : '',
            'max_usage_total' => $coupon['max_usage_total'] !== null ? (string) $coupon['max_usage_total'] : '',
            'max_usage_per_user' => $coupon['max_usage_per_user'] !== null ? (string) $coupon['max_usage_per_user'] : '',
            'starts_at' => $this->toDateTimeLocal((string) ($coupon['starts_at'] ?? '')),
            'ends_at' => $this->toDateTimeLocal((string) ($coupon['ends_at'] ?? '')),
            'is_active' => (int) ($coupon['is_active'] ?? 1),
            'is_first_order_only' => (int) ($coupon['is_first_order_only'] ?? 0),
            'category_ids' => array_values(array_unique($categoryIds)),
            'product_ids' => array_values(array_unique($productIds)),
        ];
    }

    public function getCouponFormMeta(): array
    {
        return [
            'categories' => $this->categoryModel->getAllForAdmin(),
            'products' => $this->productsModel->getAllActivePrintedProductsForSelect(),
        ];
    }

    public function createCoupon(CouponDTO $dto, ?string $actorId = null): array
    {
        $errors = $this->validateCouponDTO($dto, null);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $normalized = $this->normalizeCouponData($dto, $actorId, true);
        $db = db_connect();
        $db->transStart();

        $couponId = $this->couponModel->insert($normalized, true);
        if ($couponId === false) {
            $db->transRollback();
            return ['success' => false, 'errors' => ['Kupon kaydedilemedi.']];
        }

        $couponId = (string) $couponId;
        if (! $this->replaceTargets($couponId, $dto)) {
            $db->transRollback();
            return ['success' => false, 'errors' => ['Kupon kısıtları kaydedilemedi.']];
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return ['success' => false, 'errors' => ['Kupon kaydı sırasında işlem tamamlanamadı.']];
        }

        return ['success' => true, 'id' => $couponId];
    }

    public function updateCoupon(string $couponId, CouponDTO $dto, ?string $actorId = null): array
    {
        $existing = $this->couponModel->find($couponId);
        if (! is_array($existing)) {
            return ['success' => false, 'errors' => ['Kupon bulunamadı.']];
        }

        $errors = $this->validateCouponDTO($dto, $couponId);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $normalized = $this->normalizeCouponData($dto, $actorId, false);
        $db = db_connect();
        $db->transStart();

        $updated = $this->couponModel->update($couponId, $normalized);
        if (! $updated) {
            $db->transRollback();
            return ['success' => false, 'errors' => ['Kupon güncellenemedi.']];
        }

        if (! $this->replaceTargets($couponId, $dto)) {
            $db->transRollback();
            return ['success' => false, 'errors' => ['Kupon kısıtları güncellenemedi.']];
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return ['success' => false, 'errors' => ['Kupon güncelleme işlemi tamamlanamadı.']];
        }

        return ['success' => true];
    }

    public function toggleCouponStatus(string $couponId, ?string $actorId = null): bool
    {
        $coupon = $this->couponModel->find($couponId);
        if (! is_array($coupon)) {
            return false;
        }

        $current = (int) ($coupon['is_active'] ?? 0);
        $next = $current === 1 ? 0 : 1;

        return (bool) $this->couponModel->update($couponId, [
            'is_active' => $next,
            'updated_by' => $this->cleanActorId($actorId),
        ]);
    }

    public function deleteCoupon(string $couponId, ?string $actorId = null): bool
    {
        $coupon = $this->couponModel->find($couponId);
        if (! is_array($coupon)) {
            return false;
        }

        $updated = $this->couponModel->update($couponId, [
            'updated_by' => $this->cleanActorId($actorId),
        ]);
        if (! $updated) {
            return false;
        }

        $db = db_connect();
        $db->transStart();
        $this->couponTargetModel->where('coupon_id', $couponId)->delete();
        $this->couponModel->delete($couponId);
        $db->transComplete();

        return (bool) $db->transStatus();
    }

    public function findActiveCouponByCode(string $code): ?array
    {
        $normalizedCode = strtoupper(trim($code));
        if ($normalizedCode === '') {
            return null;
        }

        $coupon = $this->couponModel
            ->where('code', $normalizedCode)
            ->where('is_active', 1)
            ->first();

        if (! is_array($coupon)) {
            return null;
        }

        if (! $this->isDateRangeActive($coupon, date('Y-m-d H:i:s'))) {
            return null;
        }

        return $coupon;
    }

    /**
     * @param array{subtotal?:float|int, shipping_fee?:float|int, user_order_count?:int, category_ids?:array, product_ids?:array} $cart
     * @return array{valid:bool,message:string,coupon:?array,meta:array}
     */
    public function validateCouponForCart(string $code, array $cart, ?string $userId = null): array
    {
        $coupon = $this->findActiveCouponByCode($code);
        if (! is_array($coupon)) {
            return [
                'valid' => false,
                'message' => 'Kupon bulunamadı veya aktif değil.',
                'coupon' => null,
                'meta' => [],
            ];
        }

        $subtotal = (float) ($cart['subtotal'] ?? 0);
        $minCartAmount = (float) ($coupon['min_cart_amount'] ?? 0);
        if ($minCartAmount > 0 && $subtotal < $minCartAmount) {
            return [
                'valid' => false,
                'message' => 'Minimum sepet tutarı sağlanmıyor.',
                'coupon' => null,
                'meta' => ['required_min_cart_amount' => $minCartAmount],
            ];
        }

        if ((int) ($coupon['is_first_order_only'] ?? 0) === 1) {
            $userOrderCount = (int) ($cart['user_order_count'] ?? 0);
            if ($userOrderCount > 0) {
                return [
                    'valid' => false,
                    'message' => 'Bu kupon sadece ilk sipariş için kullanılabilir.',
                    'coupon' => null,
                    'meta' => [],
                ];
            }
        }

        $targetCheck = $this->checkTargetEligibility((string) ($coupon['id'] ?? ''), $cart);
        if (! $targetCheck['ok']) {
            return [
                'valid' => false,
                'message' => 'Kupon hedef kısıtları sepete uymuyor.',
                'coupon' => null,
                'meta' => $targetCheck,
            ];
        }

        // TODO: checkout entegrasyonunda max_usage_total, max_usage_per_user ve coupon_redemptions kontrolü eklenecek.
        // TODO: userId null değilse kullanıcı bazlı redemption limiti uygulanacak.
        $preview = $this->calculateCouponDiscount($coupon, $cart);

        return [
            'valid' => true,
            'message' => 'Kupon geçerli.',
            'coupon' => $coupon,
            'meta' => ['preview' => $preview, 'targets' => $targetCheck],
        ];
    }

    /**
     * @param array{subtotal?:float|int, shipping_fee?:float|int} $cart
     * @return array{discount_amount:float,free_shipping:bool,applied_code:string,notes:array}
     */
    public function calculateCouponDiscount(array $coupon, array $cart): array
    {
        $subtotal = max(0, (float) ($cart['subtotal'] ?? 0));
        $shippingFee = max(0, (float) ($cart['shipping_fee'] ?? 0));
        $couponKind = (string) ($coupon['coupon_kind'] ?? 'discount');
        $discountType = (string) ($coupon['discount_type'] ?? 'none');
        $discountValue = (float) ($coupon['discount_value'] ?? 0);

        $discountAmount = 0.0;
        $freeShipping = false;

        if ($couponKind === 'free_shipping') {
            $freeShipping = true;
        } elseif ($discountType === 'percent') {
            $discountAmount = round(($subtotal * $discountValue) / 100, 2);
        } elseif ($discountType === 'fixed') {
            $discountAmount = $discountValue;
        }

        $discountAmount = max(0, min($discountAmount, $subtotal));

        return [
            'discount_amount' => $discountAmount,
            'free_shipping' => $freeShipping,
            'applied_code' => (string) ($coupon['code'] ?? ''),
            'notes' => [
                'coupon_kind' => $couponKind,
                'shipping_fee_snapshot' => $shippingFee,
                'discount_type' => $discountType,
            ],
        ];
    }

    /**
     * @return string[]
     */
    private function validateCouponDTO(CouponDTO $dto, ?string $ignoreCouponId): array
    {
        $errors = [];
        $code = strtoupper(trim($dto->code));
        if ($code === '') {
            $errors[] = 'Kupon kodu zorunludur.';
        } elseif (! preg_match('/^[A-Z0-9_\-]{3,64}$/', $code)) {
            $errors[] = 'Kupon kodu 3-64 karakter olmalı ve sadece harf, rakam, alt çizgi, tire içermelidir.';
        }

        if (! in_array($dto->coupon_kind, ['discount', 'free_shipping'], true)) {
            $errors[] = 'Kupon türü geçersiz.';
        }

        if ($dto->coupon_kind === 'discount' && ! in_array($dto->discount_type, ['percent', 'fixed'], true)) {
            $errors[] = 'İndirim kuponunda indirim tipi percent veya fixed olmalıdır.';
        }

        if ($dto->coupon_kind === 'free_shipping' && ! in_array($dto->discount_type, ['none', ''], true)) {
            $errors[] = 'Ücretsiz kargo kuponunda indirim tipi none olmalıdır.';
        }

        if ($dto->coupon_kind === 'free_shipping' && $dto->discount_value !== null && (float) $dto->discount_value !== 0.0) {
            $errors[] = 'Ücretsiz kargo kuponunda indirim değeri 0 olmalıdır.';
        }

        if ($dto->discount_type === 'percent') {
            $value = $dto->discount_value ?? 0;
            if ($value < 0 || $value > 100) {
                $errors[] = 'Yüzde indirim 0 ile 100 arasında olmalıdır.';
            }
        }

        if ($dto->discount_type === 'fixed') {
            $value = $dto->discount_value ?? 0;
            if ($value < 0) {
                $errors[] = 'Sabit indirim negatif olamaz.';
            }
        }

        if ($dto->min_cart_amount !== null && $dto->min_cart_amount < 0) {
            $errors[] = 'Minimum sepet tutarı negatif olamaz.';
        }

        if ($dto->max_usage_total !== null && $dto->max_usage_total < 0) {
            $errors[] = 'Toplam kullanım limiti negatif olamaz.';
        }

        if ($dto->max_usage_per_user !== null && $dto->max_usage_per_user < 0) {
            $errors[] = 'Kullanıcı başı kullanım limiti negatif olamaz.';
        }

        $startsAt = $this->normalizeDateTime($dto->starts_at);
        $endsAt = $this->normalizeDateTime($dto->ends_at);
        if ($startsAt !== null && $endsAt !== null && strtotime($endsAt) < strtotime($startsAt)) {
            $errors[] = 'Bitiş tarihi başlangıç tarihinden küçük olamaz.';
        }

        $existing = $this->couponModel->where('code', $code)->first();
        if (is_array($existing)) {
            $existingId = (string) ($existing['id'] ?? '');
            if ($ignoreCouponId === null || $existingId !== $ignoreCouponId) {
                $errors[] = 'Bu kupon kodu zaten kullanılıyor.';
            }
        }

        $categorySet = [];
        foreach ($this->categoryModel->getAllForAdmin() as $category) {
            $categoryId = trim((string) ($category['id'] ?? ''));
            if ($categoryId !== '') {
                $categorySet[$categoryId] = true;
            }
        }
        foreach ($dto->category_ids as $categoryId) {
            if (! isset($categorySet[$categoryId])) {
                $errors[] = 'Geçersiz kategori seçimi var.';
                break;
            }
        }

        $productSet = [];
        foreach ($this->productsModel->getAllActivePrintedProductsForSelect() as $product) {
            $productId = trim((string) ($product['id'] ?? ''));
            if ($productId !== '') {
                $productSet[$productId] = true;
            }
        }
        foreach ($dto->product_ids as $productId) {
            if (! isset($productSet[$productId])) {
                $errors[] = 'Geçersiz ürün seçimi var.';
                break;
            }
        }

        return $errors;
    }

    private function normalizeCouponData(CouponDTO $dto, ?string $actorId, bool $includeCreatedBy): array
    {
        $code = strtoupper(trim($dto->code));
        $startsAt = $this->normalizeDateTime($dto->starts_at);
        $endsAt = $this->normalizeDateTime($dto->ends_at);

        $couponKind = $dto->coupon_kind;
        $discountType = $dto->discount_type;
        $discountValue = $dto->discount_value;

        if ($couponKind === 'free_shipping') {
            $discountType = 'none';
            $discountValue = 0;
        }

        if ($couponKind === 'discount' && ! in_array($discountType, ['percent', 'fixed'], true)) {
            $discountType = 'percent';
        }

        if ($discountType === 'none') {
            $discountValue = 0;
        }

        $payload = [
            'code' => $code,
            'coupon_kind' => $couponKind,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'min_cart_amount' => $dto->min_cart_amount,
            'max_usage_total' => $dto->max_usage_total,
            'max_usage_per_user' => $dto->max_usage_per_user,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_active' => $dto->is_active === 1 ? 1 : 0,
            'is_first_order_only' => $dto->is_first_order_only === 1 ? 1 : 0,
            'updated_by' => $this->cleanActorId($actorId),
        ];

        if ($includeCreatedBy) {
            $payload['created_by'] = $this->cleanActorId($actorId);
        }

        return $payload;
    }

    private function replaceTargets(string $couponId, CouponDTO $dto): bool
    {
        $this->couponTargetModel->where('coupon_id', $couponId)->delete();

        foreach ($dto->category_ids as $categoryId) {
            $ok = $this->couponTargetModel->insert([
                'coupon_id' => $couponId,
                'target_type' => 'category',
                'target_id' => $categoryId,
            ], true);
            if ($ok === false) {
                return false;
            }
        }

        foreach ($dto->product_ids as $productId) {
            $ok = $this->couponTargetModel->insert([
                'coupon_id' => $couponId,
                'target_type' => 'product',
                'target_id' => $productId,
            ], true);
            if ($ok === false) {
                return false;
            }
        }

        return true;
    }

    private function cleanActorId(?string $actorId): ?string
    {
        $value = trim((string) $actorId);
        return $value === '' ? null : $value;
    }

    private function normalizeDateTime(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return null;
        }
        return date('Y-m-d H:i:s', $ts);
    }

    private function toDateTimeLocal(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return '';
        }
        return date('Y-m-d\TH:i', $ts);
    }

    private function isDateRangeActive(array $coupon, string $now): bool
    {
        $nowTs = strtotime($now);
        if ($nowTs === false) {
            return false;
        }

        $startsAt = trim((string) ($coupon['starts_at'] ?? ''));
        if ($startsAt !== '') {
            $startsAtTs = strtotime($startsAt);
            if ($startsAtTs !== false && $startsAtTs > $nowTs) {
                return false;
            }
        }

        $endsAt = trim((string) ($coupon['ends_at'] ?? ''));
        if ($endsAt !== '') {
            $endsAtTs = strtotime($endsAt);
            if ($endsAtTs !== false && $endsAtTs < $nowTs) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array{subtotal?:float|int, category_ids?:array, product_ids?:array} $cart
     * @return array{ok:bool,required_categories:array,required_products:array}
     */
    private function checkTargetEligibility(string $couponId, array $cart): array
    {
        $targets = $this->couponTargetModel->getTargetsByCouponId($couponId);
        if ($targets === []) {
            return ['ok' => true, 'required_categories' => [], 'required_products' => []];
        }

        $requiredCategories = [];
        $requiredProducts = [];
        foreach ($targets as $target) {
            $targetType = (string) ($target['target_type'] ?? '');
            $targetId = trim((string) ($target['target_id'] ?? ''));
            if ($targetId === '') {
                continue;
            }
            if ($targetType === 'category') {
                $requiredCategories[] = $targetId;
            } elseif ($targetType === 'product') {
                $requiredProducts[] = $targetId;
            }
        }

        $cartCategoryIds = $this->normalizeIdArray($cart['category_ids'] ?? []);
        $cartProductIds = $this->normalizeIdArray($cart['product_ids'] ?? []);

        $categoryOk = $requiredCategories === [] || count(array_intersect($requiredCategories, $cartCategoryIds)) > 0;
        $productOk = $requiredProducts === [] || count(array_intersect($requiredProducts, $cartProductIds)) > 0;

        return [
            'ok' => $categoryOk && $productOk,
            'required_categories' => array_values(array_unique($requiredCategories)),
            'required_products' => array_values(array_unique($requiredProducts)),
        ];
    }

    /**
     * @param mixed $raw
     * @return string[]
     */
    private function normalizeIdArray($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $ids = [];
        foreach ($raw as $item) {
            $id = trim((string) $item);
            if ($id !== '') {
                $ids[] = $id;
            }
        }
        return array_values(array_unique($ids));
    }
}
