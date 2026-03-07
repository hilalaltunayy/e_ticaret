<?php

namespace App\Services;

use App\DTO\Marketing\CampaignDTO;
use App\Models\CampaignModel;
use App\Models\CampaignTargetModel;
use App\Models\CategoryModel;
use App\Models\ProductsModel;

class CampaignService
{
    public function __construct(
        private ?CampaignModel $campaignModel = null,
        private ?CampaignTargetModel $campaignTargetModel = null,
        private ?CategoryModel $categoryModel = null,
        private ?ProductsModel $productsModel = null
    ) {
        $this->campaignModel = $this->campaignModel ?? new CampaignModel();
        $this->campaignTargetModel = $this->campaignTargetModel ?? new CampaignTargetModel();
        $this->categoryModel = $this->categoryModel ?? new CategoryModel();
        $this->productsModel = $this->productsModel ?? new ProductsModel();
    }

    public function listCampaigns(): array
    {
        $db = db_connect();
        if (! $db->tableExists('campaigns')) {
            return ['items' => [], 'summary' => $this->emptySummary()];
        }

        $campaigns = $this->campaignModel->listForAdmin();
        $campaignIds = array_values(array_filter(array_map(static fn ($row) => (string) ($row['id'] ?? ''), $campaigns)));
        $targets = $db->tableExists('campaign_targets')
            ? $this->campaignTargetModel->getTargetsByCampaignIds($campaignIds)
            : [];

        $targetsByCampaign = [];
        foreach ($targets as $target) {
            $campaignId = (string) ($target['campaign_id'] ?? '');
            if ($campaignId === '') {
                continue;
            }
            if (! isset($targetsByCampaign[$campaignId])) {
                $targetsByCampaign[$campaignId] = ['category' => 0, 'product' => 0];
            }
            $targetType = (string) ($target['target_type'] ?? '');
            if ($targetType === 'category') {
                $targetsByCampaign[$campaignId]['category']++;
            } elseif ($targetType === 'product') {
                $targetsByCampaign[$campaignId]['product']++;
            }
        }

        $items = [];
        $summary = $this->emptySummary();
        $nowTs = time();

        foreach ($campaigns as $campaign) {
            $campaignType = (string) ($campaign['campaign_type'] ?? 'cart_discount');
            $isActive = (int) ($campaign['is_active'] ?? 0) === 1;
            $summary['total']++;
            if ($isActive) {
                $summary['active']++;
            } else {
                $summary['passive']++;
            }

            $endsAt = trim((string) ($campaign['ends_at'] ?? ''));
            if ($isActive && $endsAt !== '') {
                $endsAtTs = strtotime($endsAt);
                if ($endsAtTs !== false && $endsAtTs >= $nowTs && $endsAtTs <= ($nowTs + 7 * 86400)) {
                    $summary['expiring_soon']++;
                }
            }

            $id = (string) ($campaign['id'] ?? '');
            $items[] = [
                'id' => $id,
                'name' => (string) ($campaign['name'] ?? ''),
                'slug' => (string) ($campaign['slug'] ?? ''),
                'campaign_type' => $campaignType,
                'discount_type' => (string) ($campaign['discount_type'] ?? 'percent'),
                'discount_value' => $campaign['discount_value'] !== null ? (float) $campaign['discount_value'] : null,
                'min_cart_amount' => $campaign['min_cart_amount'] !== null ? (float) $campaign['min_cart_amount'] : null,
                'starts_at' => (string) ($campaign['starts_at'] ?? ''),
                'ends_at' => (string) ($campaign['ends_at'] ?? ''),
                'priority' => (int) ($campaign['priority'] ?? 0),
                'stop_further_rules' => (int) ($campaign['stop_further_rules'] ?? 0) === 1,
                'is_active' => $isActive,
                'target_counts' => $targetsByCampaign[$id] ?? ['category' => 0, 'product' => 0],
            ];
        }

        return [
            'items' => $items,
            'summary' => $summary,
        ];
    }

    public function getCampaignFormMeta(): array
    {
        return [
            'categories' => $this->categoryModel->getAllForAdmin(),
            'products' => $this->productsModel->getAllActivePrintedProductsForSelect(),
        ];
    }

    public function getCampaignForEdit(string $campaignId): ?array
    {
        $campaign = $this->campaignModel->find($campaignId);
        if (! is_array($campaign)) {
            return null;
        }

        $targets = $this->campaignTargetModel->getTargetsByCampaignId($campaignId);
        $categoryIds = [];
        $productIds = [];
        foreach ($targets as $target) {
            $targetId = trim((string) ($target['target_id'] ?? ''));
            if ($targetId === '') {
                continue;
            }
            $targetType = (string) ($target['target_type'] ?? '');
            if ($targetType === 'category') {
                $categoryIds[] = $targetId;
            } elseif ($targetType === 'product') {
                $productIds[] = $targetId;
            }
        }

        return [
            'id' => (string) ($campaign['id'] ?? ''),
            'name' => (string) ($campaign['name'] ?? ''),
            'slug' => (string) ($campaign['slug'] ?? ''),
            'campaign_type' => (string) ($campaign['campaign_type'] ?? 'cart_discount'),
            'discount_type' => (string) ($campaign['discount_type'] ?? 'percent'),
            'discount_value' => $campaign['discount_value'] !== null ? (string) $campaign['discount_value'] : '',
            'min_cart_amount' => $campaign['min_cart_amount'] !== null ? (string) $campaign['min_cart_amount'] : '',
            'starts_at' => $this->toDateTimeLocal((string) ($campaign['starts_at'] ?? '')),
            'ends_at' => $this->toDateTimeLocal((string) ($campaign['ends_at'] ?? '')),
            'priority' => (string) ((int) ($campaign['priority'] ?? 0)),
            'stop_further_rules' => (int) ($campaign['stop_further_rules'] ?? 0),
            'is_active' => (int) ($campaign['is_active'] ?? 1),
            'category_ids' => array_values(array_unique($categoryIds)),
            'product_ids' => array_values(array_unique($productIds)),
        ];
    }

    public function createCampaign(CampaignDTO $dto, ?string $actorId = null): array
    {
        $errors = $this->validateCampaignDTO($dto, null);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $normalized = $this->normalizeCampaignData($dto, $actorId, true);
        $db = db_connect();
        $db->transStart();

        $campaignId = $this->campaignModel->insert($normalized, true);
        if ($campaignId === false) {
            $db->transRollback();
            return ['success' => false, 'errors' => ['Kampanya kaydedilemedi.']];
        }

        $campaignId = (string) $campaignId;
        if (! $this->replaceTargets($campaignId, $dto)) {
            $db->transRollback();
            return ['success' => false, 'errors' => ['Kampanya hedefleri kaydedilemedi.']];
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return ['success' => false, 'errors' => ['Kampanya kaydı sırasında işlem tamamlanamadı.']];
        }

        return ['success' => true, 'id' => $campaignId];
    }

    public function updateCampaign(string $campaignId, CampaignDTO $dto, ?string $actorId = null): array
    {
        $existing = $this->campaignModel->find($campaignId);
        if (! is_array($existing)) {
            return ['success' => false, 'errors' => ['Kampanya bulunamadı.']];
        }

        $errors = $this->validateCampaignDTO($dto, $campaignId);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $normalized = $this->normalizeCampaignData($dto, $actorId, false);
        $db = db_connect();
        $db->transStart();

        $updated = $this->campaignModel->update($campaignId, $normalized);
        if (! $updated) {
            $db->transRollback();
            return ['success' => false, 'errors' => ['Kampanya güncellenemedi.']];
        }

        if (! $this->replaceTargets($campaignId, $dto)) {
            $db->transRollback();
            return ['success' => false, 'errors' => ['Kampanya hedefleri güncellenemedi.']];
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return ['success' => false, 'errors' => ['Kampanya güncelleme işlemi tamamlanamadı.']];
        }

        return ['success' => true];
    }

    public function toggleCampaignStatus(string $campaignId, ?string $actorId = null): bool
    {
        $campaign = $this->campaignModel->find($campaignId);
        if (! is_array($campaign)) {
            return false;
        }

        $current = (int) ($campaign['is_active'] ?? 0);
        $next = $current === 1 ? 0 : 1;

        return (bool) $this->campaignModel->update($campaignId, [
            'is_active' => $next,
            'updated_by' => $this->cleanActorId($actorId),
        ]);
    }

    public function deleteCampaign(string $campaignId, ?string $actorId = null): bool
    {
        $campaign = $this->campaignModel->find($campaignId);
        if (! is_array($campaign)) {
            return false;
        }

        $updated = $this->campaignModel->update($campaignId, [
            'updated_by' => $this->cleanActorId($actorId),
        ]);
        if (! $updated) {
            return false;
        }

        $db = db_connect();
        $db->transStart();
        $this->campaignTargetModel->where('campaign_id', $campaignId)->delete();
        $this->campaignModel->delete($campaignId);
        $db->transComplete();

        return (bool) $db->transStatus();
    }

    /**
     * @return string[]
     */
    private function validateCampaignDTO(CampaignDTO $dto, ?string $ignoreCampaignId): array
    {
        $errors = [];

        $name = trim($dto->name);
        if ($name === '') {
            $errors[] = 'Kampanya adı zorunludur.';
        }

        if (! in_array($dto->campaign_type, ['category_discount', 'product_discount', 'cart_discount'], true)) {
            $errors[] = 'Kampanya türü geçersiz.';
        }

        if (! in_array($dto->discount_type, ['percent', 'fixed'], true)) {
            $errors[] = 'İndirim türü geçersiz.';
        }

        if ($dto->discount_value === null) {
            $errors[] = 'İndirim değeri zorunludur.';
        } elseif ($dto->discount_type === 'percent' && ($dto->discount_value < 0 || $dto->discount_value > 100)) {
            $errors[] = 'Yüzde indirim değeri 0 ile 100 arasında olmalıdır.';
        } elseif ($dto->discount_type === 'fixed' && $dto->discount_value < 0) {
            $errors[] = 'Sabit indirim değeri negatif olamaz.';
        }

        if ($dto->min_cart_amount !== null && $dto->min_cart_amount < 0) {
            $errors[] = 'Minimum sepet tutarı negatif olamaz.';
        }

        if ($dto->priority < 0) {
            $errors[] = 'Öncelik negatif olamaz.';
        }

        $startsAt = $this->normalizeDateTime($dto->starts_at);
        $endsAt = $this->normalizeDateTime($dto->ends_at);
        if ($dto->starts_at !== null && $startsAt === null) {
            $errors[] = 'Başlangıç tarihi geçersiz.';
        }
        if ($dto->ends_at !== null && $endsAt === null) {
            $errors[] = 'Bitiş tarihi geçersiz.';
        }
        if ($startsAt !== null && $endsAt !== null && strtotime($endsAt) < strtotime($startsAt)) {
            $errors[] = 'Bitiş tarihi başlangıç tarihinden küçük olamaz.';
        }

        $slug = $this->normalizeSlug($dto->slug !== '' ? $dto->slug : $name);
        if ($slug === '') {
            $errors[] = 'Slug üretilemedi. Lütfen geçerli bir kampanya adı veya slug giriniz.';
        } else {
            $existing = $this->campaignModel->withDeleted()->where('slug', $slug)->first();
            if (is_array($existing)) {
                $existingId = (string) ($existing['id'] ?? '');
                if ($ignoreCampaignId === null || $existingId !== $ignoreCampaignId) {
                    $errors[] = 'Bu slug zaten kullanılıyor.';
                }
            }
        }

        $categorySet = [];
        foreach ($this->categoryModel->getAllForAdmin() as $category) {
            $id = trim((string) ($category['id'] ?? ''));
            if ($id !== '') {
                $categorySet[$id] = true;
            }
        }

        $productSet = [];
        foreach ($this->productsModel->getAllActivePrintedProductsForSelect() as $product) {
            $id = trim((string) ($product['id'] ?? ''));
            if ($id !== '') {
                $productSet[$id] = true;
            }
        }

        foreach ($dto->category_ids as $categoryId) {
            if (! isset($categorySet[$categoryId])) {
                $errors[] = 'Geçersiz kategori seçimi var.';
                break;
            }
        }
        foreach ($dto->product_ids as $productId) {
            if (! isset($productSet[$productId])) {
                $errors[] = 'Geçersiz ürün seçimi var.';
                break;
            }
        }

        if ($dto->campaign_type === 'category_discount' && $dto->category_ids === []) {
            $errors[] = 'Kategori indirimi için en az bir hedef kategori seçilmelidir.';
        }
        if ($dto->campaign_type === 'product_discount' && $dto->product_ids === []) {
            $errors[] = 'Ürün indirimi için en az bir hedef ürün seçilmelidir.';
        }

        return $errors;
    }

    private function normalizeCampaignData(CampaignDTO $dto, ?string $actorId, bool $includeCreatedBy): array
    {
        $name = trim($dto->name);
        $slug = $this->normalizeSlug($dto->slug !== '' ? $dto->slug : $name);

        $payload = [
            'name' => $name,
            'slug' => $slug,
            'campaign_type' => $dto->campaign_type,
            'discount_type' => $dto->discount_type,
            'discount_value' => $dto->discount_value,
            'min_cart_amount' => $dto->min_cart_amount,
            'starts_at' => $this->normalizeDateTime($dto->starts_at),
            'ends_at' => $this->normalizeDateTime($dto->ends_at),
            'priority' => max(0, $dto->priority),
            'stop_further_rules' => $dto->stop_further_rules === 1 ? 1 : 0,
            'is_active' => $dto->is_active === 1 ? 1 : 0,
            'updated_by' => $this->cleanActorId($actorId),
        ];

        if ($includeCreatedBy) {
            $payload['created_by'] = $this->cleanActorId($actorId);
        }

        return $payload;
    }

    private function replaceTargets(string $campaignId, CampaignDTO $dto): bool
    {
        $this->campaignTargetModel->where('campaign_id', $campaignId)->delete();

        if ($dto->campaign_type === 'cart_discount') {
            return true;
        }

        if ($dto->campaign_type === 'category_discount') {
            foreach (array_values(array_unique($dto->category_ids)) as $categoryId) {
                $ok = $this->campaignTargetModel->insert([
                    'campaign_id' => $campaignId,
                    'target_type' => 'category',
                    'target_id' => $categoryId,
                ], true);
                if ($ok === false) {
                    return false;
                }
            }
            return true;
        }

        foreach (array_values(array_unique($dto->product_ids)) as $productId) {
            $ok = $this->campaignTargetModel->insert([
                'campaign_id' => $campaignId,
                'target_type' => 'product',
                'target_id' => $productId,
            ], true);
            if ($ok === false) {
                return false;
            }
        }

        return true;
    }

    private function normalizeDateTime(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    private function toDateTimeLocal(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return '';
        }
        return date('Y-m-d\TH:i', $timestamp);
    }

    private function normalizeSlug(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii === false || $ascii === '') {
            $ascii = $value;
        }

        $slug = strtolower($ascii);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        $slug = preg_replace('/-+/', '-', $slug) ?? '';

        return $slug;
    }

    private function cleanActorId(?string $actorId): ?string
    {
        $value = trim((string) $actorId);
        return $value === '' ? null : $value;
    }

    private function emptySummary(): array
    {
        return [
            'total' => 0,
            'active' => 0,
            'passive' => 0,
            'expiring_soon' => 0,
        ];
    }
}

