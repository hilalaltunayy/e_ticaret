<?php

namespace App\Services;

use App\Models\BlockTypeModel;
use App\Models\BlockInstanceModel;
use App\Models\PageVersionModel;

class PageBuilderService
{
    public function __construct(
        private ?PageService $pageService = null,
        private ?PageVersionService $pageVersionService = null,
        private ?BlockInstanceModel $blockInstanceModel = null,
        private ?BlockTypeModel $blockTypeModel = null,
        private ?PageVersionModel $pageVersionModel = null
    ) {
        $this->pageService = $this->pageService ?? new PageService();
        $this->pageVersionService = $this->pageVersionService ?? new PageVersionService();
        $this->blockInstanceModel = $this->blockInstanceModel ?? new BlockInstanceModel();
        $this->blockTypeModel = $this->blockTypeModel ?? new BlockTypeModel();
        $this->pageVersionModel = $this->pageVersionModel ?? new PageVersionModel();
    }

    public function getPageOverview(string $pageCode): ?array
    {
        $page = $this->pageService->findPageByCode($pageCode);

        if (! is_array($page)) {
            return null;
        }

        return [
            'page' => $page,
            'publishedVersion' => $this->pageVersionService->findPublishedVersionForPage($page['id']),
            'drafts' => $this->pageVersionService->getDraftListByPageCode($pageCode),
        ];
    }

    public function getVersionOverview(string $versionId): ?array
    {
        $version = $this->pageVersionService->findVersionDetail($versionId);

        if (! is_array($version)) {
            return null;
        }

        return [
            'version' => $version,
            'blocks' => $this->blockTablesReady() ? $this->blockInstanceModel->findDetailedByPageVersion($versionId) : [],
        ];
    }

    public function getBuilderData(string $pageCode): ?array
    {
        $page = $this->pageService->findPageByCode($pageCode);

        if (! is_array($page)) {
            return null;
        }

        $draft = $this->createDraftIfNotExists($page['id']);

        if (! is_array($draft)) {
            return null;
        }

        $builderData = [
            'page' => $page,
            'draft' => $draft,
            'publishedVersion' => $this->pageVersionService->findPublishedVersionForPage($page['id']),
            'blockTypes' => $this->filterBlockTypesForPageCode(
                $this->blockTypeTablesReady() ? $this->blockTypeModel->findActiveOrdered() : [],
                (string) ($page['code'] ?? '')
            ),
            'blocks' => $this->decorateBlocks($this->blockTablesReady() ? $this->blockInstanceModel->findDetailedByPageVersion($draft['id']) : []),
            'builderPolicy' => $this->getBuilderPolicyForPageCode((string) ($page['code'] ?? '')),
            'builderOptions' => $this->builderOptions(),
        ];

        if (($page['code'] ?? '') === 'product_list') {
            $productListLayout = $this->ensureProductListLayoutBlock((string) $draft['id']);
            $builderData['productListLayoutBlock'] = $productListLayout;
            $builderData['productListConfig'] = $this->normalizeProductListConfig(
                is_array($productListLayout)
                    ? $this->decodeJson((string) ($productListLayout['config_json'] ?? ''))
                    : []
            );
        }

        return $builderData;
    }

    public function createDraftIfNotExists(string $pageId): ?array
    {
        if (! $this->pageVersionService->tablesReady()) {
            return null;
        }

        $draft = $this->pageVersionModel->findLatestEditableByPageId($pageId);

        if (is_array($draft)) {
            return $draft;
        }

        $versionId = $this->pageVersionModel->insert([
            'page_id' => $pageId,
            'version_no' => $this->pageVersionModel->getNextVersionNo($pageId),
            'name' => 'Draft 1',
            'status' => 'DRAFT',
            'created_by' => $this->actorId(),
            'notes' => 'Sprint 2 builder icin otomatik olusturulan draft.',
        ], true);

        if (! $versionId) {
            return null;
        }

        return $this->pageVersionModel->find((string) $versionId);
    }

    public function createDraft(string $pageId, ?string $sourceVersionId = null): array
    {
        if (! $this->pageVersionService->tablesReady()) {
            return ['success' => false, 'error' => 'Draft tablolari hazir degil.'];
        }

        $versionNo = $this->pageVersionModel->getNextVersionNo($pageId);
        $sourceVersion = $sourceVersionId !== null && $sourceVersionId !== '' ? $this->pageVersionService->findVersionDetail($sourceVersionId) : null;
        $draftName = $sourceVersionId !== null && is_array($sourceVersion)
            ? 'Kopya ' . trim((string) ($sourceVersion['name'] ?? ('Draft ' . ($sourceVersion['version_no'] ?? 1))))
            : 'Draft ' . $versionNo;

        $db = db_connect();
        $db->transStart();

        $newVersionId = $this->pageVersionModel->insert([
            'page_id' => $pageId,
            'version_no' => $versionNo,
            'name' => $draftName,
            'status' => 'DRAFT',
            'created_by' => $this->actorId(),
            'notes' => $sourceVersionId !== null && is_array($sourceVersion)
                ? trim((string) ($sourceVersion['notes'] ?? ''))
                : 'Yeni draft olusturuldu.',
            'scheduled_publish_at' => null,
            'published_at' => null,
            'archived_at' => null,
        ], true);

        if ($newVersionId && $sourceVersionId !== null && $sourceVersionId !== '' && $this->blockTablesReady()) {
            foreach ($this->blockInstanceModel->findByPageVersion($sourceVersionId) as $block) {
                $this->blockInstanceModel->insert([
                    'owner_type' => 'PAGE',
                    'owner_version_id' => (string) $newVersionId,
                    'block_type_id' => $block['block_type_id'],
                    'zone' => $block['zone'],
                    'position_x' => (int) $block['position_x'],
                    'position_y' => (int) $block['position_y'],
                    'width' => (int) $block['width'],
                    'height' => (int) $block['height'],
                    'order_index' => (int) $block['order_index'],
                    'config_json' => $block['config_json'],
                    'is_visible' => (int) $block['is_visible'],
                ]);
            }
        }

        $db->transComplete();

        if (! $db->transStatus() || ! $newVersionId) {
            return ['success' => false, 'error' => 'Yeni draft olusturulamadi.'];
        }

        return ['success' => true, 'version_id' => (string) $newVersionId];
    }

    public function duplicateDraft(string $versionId): array
    {
        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version)) {
            return ['success' => false, 'error' => 'Kopyalanacak version bulunamadi.'];
        }

        if ((string) ($version['status'] ?? '') === 'ARCHIVED') {
            return ['success' => false, 'error' => 'Arsivlenmis version kopyalanamaz.'];
        }

        return $this->createDraft((string) $version['page_id'], $versionId);
    }

    public function archiveDraft(string $versionId): array
    {
        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version)) {
            return ['success' => false, 'error' => 'Arsivlenecek version bulunamadi.'];
        }

        $status = (string) ($version['status'] ?? '');
        if ($status === 'PUBLISHED') {
            return ['success' => false, 'error' => 'Canlidaki version dogrudan arsivlenemez. Once canlidan cekin.'];
        }

        if (! in_array($status, ['DRAFT', 'SCHEDULED'], true)) {
            return ['success' => false, 'error' => 'Yalnizca draft veya scheduled version arsivlenebilir.'];
        }

        $updated = $this->pageVersionModel->update($versionId, [
            'status' => 'ARCHIVED',
            'scheduled_publish_at' => null,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Version arsivlenemedi.'];
        }

        return ['success' => true];
    }

    public function unpublishVersion(string $versionId): array
    {
        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version)) {
            return ['success' => false, 'error' => 'Canlidan cekilecek version bulunamadi.'];
        }

        if ((string) ($version['status'] ?? '') !== 'PUBLISHED') {
            return ['success' => false, 'error' => 'Yalnizca published version canlidan cekilebilir.'];
        }

        $updated = $this->pageVersionModel->update($versionId, [
            'status' => 'ARCHIVED',
            'archived_at' => date('Y-m-d H:i:s'),
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Version canlidan cekilemedi.'];
        }

        return ['success' => true];
    }

    public function updateProductListConfig(string $versionId, array $input): array
    {
        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version)) {
            return ['success' => false, 'error' => 'Urun listeleme taslagi bulunamadi.'];
        }

        if ((string) ($version['page_code'] ?? '') !== 'product_list') {
            return ['success' => false, 'error' => 'Bu ayarlar yalnizca product_list sayfasinda kullanilir.'];
        }

        if (! in_array((string) ($version['status'] ?? ''), ['DRAFT', 'SCHEDULED'], true)) {
            return ['success' => false, 'error' => 'Yalnizca draft veya scheduled version duzenlenebilir.'];
        }

        $layoutBlock = $this->ensureProductListLayoutBlock($versionId);
        if (! is_array($layoutBlock)) {
            return ['success' => false, 'error' => 'Product list layout blogu hazirlanamadi.'];
        }

        $config = $this->buildProductListConfigPayload($input);
        $updated = $this->blockInstanceModel->update((string) $layoutBlock['id'], [
            'config_json' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Product list ayarlari kaydedilemedi.'];
        }

        return ['success' => true];
    }

    public function addBlock(string $versionId, string $blockTypeId, array $input): array
    {
        if (! $this->blockTablesReady() || ! $this->blockTypeTablesReady()) {
            return ['success' => false, 'error' => 'Builder tabloları hazir degil.'];
        }

        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version) || ! in_array((string) ($version['status'] ?? ''), ['DRAFT', 'SCHEDULED'], true)) {
            return ['success' => false, 'error' => 'Yalnizca draft veya scheduled version uzerinde blok eklenebilir.'];
        }

        $blockType = $this->blockTypeModel->find($blockTypeId);
        if (! is_array($blockType)) {
            return ['success' => false, 'error' => 'Gecersiz block type secildi.'];
        }

        if (! $this->isBlockAllowedForPageCode((string) ($version['page_code'] ?? ''), (string) ($blockType['code'] ?? ''))) {
            return ['success' => false, 'error' => 'Bu block tipi secilen sayfa turu icin kullanilamaz.'];
        }

        $config = $this->buildConfigPayload((string) ($blockType['code'] ?? ''), $input, (string) ($blockType['default_config_json'] ?? ''));
        if (! ($config['success'] ?? false)) {
            return $config;
        }

        $inserted = $this->blockInstanceModel->insert([
            'owner_type' => 'PAGE',
            'owner_version_id' => $versionId,
            'block_type_id' => $blockTypeId,
            'zone' => 'main',
            'position_x' => 0,
            'position_y' => 0,
            'width' => 12,
            'height' => 1,
            'order_index' => $this->blockInstanceModel->getNextOrderIndex($versionId),
            'config_json' => $config['config_json'] ?? null,
            'is_visible' => 1,
        ], true);

        if (! $inserted) {
            return ['success' => false, 'error' => 'Block kaydi eklenemedi.'];
        }

        return ['success' => true];
    }

    public function deleteBlock(string $blockId): array
    {
        $block = $this->blockInstanceModel->findByIdDetailed($blockId);
        if (! is_array($block) || ($block['owner_type'] ?? '') !== 'PAGE') {
            return ['success' => false, 'error' => 'Silinecek block bulunamadi.'];
        }

        $deleted = $this->blockInstanceModel->delete($blockId);

        if (! $deleted) {
            return ['success' => false, 'error' => 'Block silinemedi.'];
        }

        return ['success' => true];
    }

    public function updateBlockConfig(string $blockId, array $input): array
    {
        if (! $this->blockTablesReady() || ! $this->blockTypeTablesReady()) {
            return ['success' => false, 'error' => 'Builder tablolari hazir degil.'];
        }

        $block = $this->blockInstanceModel->findByIdDetailed($blockId);
        if (! is_array($block) || ($block['owner_type'] ?? '') !== 'PAGE') {
            return ['success' => false, 'error' => 'Guncellenecek block bulunamadi.'];
        }

        $version = $this->pageVersionService->findVersionDetail((string) ($block['owner_version_id'] ?? ''));
        if (! is_array($version) || ! in_array((string) ($version['status'] ?? ''), ['DRAFT', 'SCHEDULED'], true)) {
            return ['success' => false, 'error' => 'Yalnizca draft veya scheduled version uzerindeki blocklar guncellenebilir.'];
        }

        $blockType = $this->blockTypeModel->find((string) ($block['block_type_id'] ?? ''));
        if (! is_array($blockType)) {
            return ['success' => false, 'error' => 'Block type bilgisi bulunamadi.'];
        }

        $config = $this->buildConfigPayload((string) ($blockType['code'] ?? ''), $input, (string) ($blockType['default_config_json'] ?? ''));
        if (! ($config['success'] ?? false)) {
            return $config;
        }

        $updated = $this->blockInstanceModel->update($blockId, [
            'config_json' => $config['config_json'] ?? null,
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Block ayarlari kaydedilemedi.'];
        }

        return ['success' => true];
    }

    public function reorderBlock(string $blockId, string $direction): array
    {
        $block = $this->blockInstanceModel->findByIdDetailed($blockId);
        if (! is_array($block) || ($block['owner_type'] ?? '') !== 'PAGE') {
            return ['success' => false, 'error' => 'Siralanacak block bulunamadi.'];
        }

        $blocks = $this->blockInstanceModel->findByPageVersion((string) $block['owner_version_id']);
        $index = null;

        foreach ($blocks as $i => $candidate) {
            if (($candidate['id'] ?? '') === $blockId) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return ['success' => false, 'error' => 'Block siralamasi okunamadi.'];
        }

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;
        if (! isset($blocks[$targetIndex])) {
            return ['success' => true];
        }

        $current = $blocks[$index];
        $target = $blocks[$targetIndex];

        $db = db_connect();
        $db->transStart();
        $this->blockInstanceModel->update($current['id'], ['order_index' => $target['order_index']]);
        $this->blockInstanceModel->update($target['id'], ['order_index' => $current['order_index']]);
        $db->transComplete();

        if (! $db->transStatus()) {
            return ['success' => false, 'error' => 'Block sirasi guncellenemedi.'];
        }

        return ['success' => true];
    }

    public function reorderBlocks(string $versionId, array $orderedBlockIds): array
    {
        $blocks = $this->blockInstanceModel->findByPageVersion($versionId);
        if ($blocks === []) {
            return ['success' => false, 'error' => 'Siralanacak block bulunamadi.'];
        }

        $currentIds = array_values(array_map(static fn (array $block): string => (string) ($block['id'] ?? ''), $blocks));
        $orderedIds = array_values(array_filter(array_map(static fn ($id): string => trim((string) $id), $orderedBlockIds)));

        sort($currentIds);
        $sortedOrderedIds = $orderedIds;
        sort($sortedOrderedIds);

        if ($currentIds !== $sortedOrderedIds) {
            return ['success' => false, 'error' => 'Gecerli bir block sirasi gonderilmedi.'];
        }

        $orderIndexes = array_values(array_map(static fn (array $block): int => (int) ($block['order_index'] ?? 0), $blocks));
        sort($orderIndexes);

        $db = db_connect();
        $db->transStart();

        foreach ($orderedIds as $index => $blockId) {
            $this->blockInstanceModel->update($blockId, ['order_index' => $orderIndexes[$index] ?? $index]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return ['success' => false, 'error' => 'Block sirasi guncellenemedi.'];
        }

        return ['success' => true];
    }

    public function updateDraftMeta(string $versionId, array $input): array
    {
        if (! $this->pageVersionService->tablesReady()) {
            return ['success' => false, 'error' => 'Draft tablolari hazir degil.'];
        }

        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version)) {
            return ['success' => false, 'error' => 'Guncellenecek draft bulunamadi.'];
        }

        if (! in_array((string) ($version['status'] ?? ''), ['DRAFT', 'SCHEDULED'], true)) {
            return ['success' => false, 'error' => 'Yalnizca draft veya scheduled kayitlar bu ekranda guncellenebilir.'];
        }

        $name = trim((string) ($input['draft_name'] ?? ''));
        if ($name === '') {
            return ['success' => false, 'error' => 'Draft adi zorunlu.'];
        }

        $notes = trim((string) ($input['draft_notes'] ?? ''));

        $updated = $this->pageVersionModel->update($versionId, [
            'name' => $name,
            'notes' => $notes,
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Draft bilgileri kaydedilemedi.'];
        }

        return ['success' => true];
    }

    public function publishDraft(string $versionId): array
    {
        if (! $this->pageVersionService->tablesReady()) {
            return ['success' => false, 'error' => 'Draft tablolari hazir degil.'];
        }

        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version)) {
            return ['success' => false, 'error' => 'Publish edilecek draft bulunamadi.'];
        }

        $status = (string) ($version['status'] ?? '');
        if ($status === 'PUBLISHED') {
            return ['success' => true];
        }

        if (! in_array($status, ['DRAFT', 'SCHEDULED'], true)) {
            return ['success' => false, 'error' => 'Yalnizca draft veya scheduled kayit publish edilebilir.'];
        }

        $pageId = (string) ($version['page_id'] ?? '');
        $currentPublished = $this->pageVersionModel->findPublishedByPageId($pageId);
        $now = date('Y-m-d H:i:s');

        $db = db_connect();
        $db->transStart();

        if (is_array($currentPublished) && ($currentPublished['id'] ?? '') !== $versionId) {
            $this->pageVersionModel->update((string) $currentPublished['id'], [
                'status' => 'ARCHIVED',
                'archived_at' => $now,
            ]);
        }

        $this->pageVersionModel->update($versionId, [
            'status' => 'PUBLISHED',
            'published_at' => $now,
            'scheduled_publish_at' => null,
            'archived_at' => null,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return ['success' => false, 'error' => 'Draft publish islemi tamamlanamadi.'];
        }

        return ['success' => true];
    }

    public function scheduleDraft(string $versionId, string $scheduledAt): array
    {
        if (! $this->pageVersionService->tablesReady()) {
            return ['success' => false, 'error' => 'Draft tablolari hazir degil.'];
        }

        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version)) {
            return ['success' => false, 'error' => 'Planlanacak draft bulunamadi.'];
        }

        $status = (string) ($version['status'] ?? '');
        if ($status === 'PUBLISHED') {
            return ['success' => false, 'error' => 'Canlidaki version yeniden schedule edilemez.'];
        }

        if ($status === 'ARCHIVED') {
            return ['success' => false, 'error' => 'Arsivlenmis version schedule edilemez.'];
        }

        $normalizedDate = $this->normalizeScheduleDate($scheduledAt);
        if (! is_array($normalizedDate) || ! ($normalizedDate['success'] ?? false)) {
            return ['success' => false, 'error' => (string) ($normalizedDate['error'] ?? 'Planlama tarihi gecersiz.')];
        }

        $updated = $this->pageVersionModel->update($versionId, [
            'status' => 'SCHEDULED',
            'scheduled_publish_at' => $normalizedDate['value'],
            'published_at' => null,
            'archived_at' => null,
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Schedule bilgisi kaydedilemedi.'];
        }

        return ['success' => true];
    }

    public function unscheduleDraft(string $versionId): array
    {
        if (! $this->pageVersionService->tablesReady()) {
            return ['success' => false, 'error' => 'Draft tablolari hazir degil.'];
        }

        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version)) {
            return ['success' => false, 'error' => 'Planlamasi kaldirilacak draft bulunamadi.'];
        }

        if ((string) ($version['status'] ?? '') === 'PUBLISHED') {
            return ['success' => false, 'error' => 'Canlidaki version icin planlama kaldirma kullanilamaz.'];
        }

        if ((string) ($version['status'] ?? '') !== 'SCHEDULED') {
            return ['success' => true];
        }

        $updated = $this->pageVersionModel->update($versionId, [
            'status' => 'DRAFT',
            'scheduled_publish_at' => null,
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Planlama kaldirilamadi.'];
        }

        return ['success' => true];
    }

    private function blockTablesReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('block_instances');
    }

    private function blockTypeTablesReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('block_types');
    }

    private function actorId(): ?string
    {
        $user = session()->get('user') ?? [];
        $id = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));

        return $id === '' ? null : $id;
    }

    private function decorateBlocks(array $blocks): array
    {
        foreach ($blocks as &$block) {
            $config = $this->decodeJson((string) ($block['config_json'] ?? ''));
            $block['config_summary'] = $this->summarizeConfig(
                (string) ($block['block_type_code'] ?? ''),
                (string) ($block['config_json'] ?? '')
            );
            $block['config_data'] = $config;
            $block['config_data_json'] = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $blocks;
    }

    private function buildConfigPayload(string $blockTypeCode, array $input, string $defaultConfigJson): array
    {
        $defaultConfig = $this->decodeJson($defaultConfigJson);
        $config = $defaultConfig;

        switch ($blockTypeCode) {
            case 'hero_banner':
                $title = trim((string) ($input['hero_title'] ?? ''));
                if ($title === '') {
                    return ['success' => false, 'error' => 'Hero Banner icin baslik zorunlu.'];
                }

                $buttonConfig = $this->buildButtonConfig('hero', $input, 'Simdi Kesfet', '/');
                $config = [
                    'title' => $title,
                    'subtitle' => trim((string) ($input['hero_subtitle'] ?? '')),
                    'variant' => $this->sanitizeEnum((string) ($input['hero_variant'] ?? 'light'), ['light', 'dark', 'soft', 'accent'], 'light'),
                    'image_path' => trim((string) ($input['hero_image_path'] ?? '')),
                ];
                $config = array_merge($config, $buttonConfig);
                break;

            case 'best_sellers':
                $bestSellersMode = $this->sanitizeEnum((string) ($input['best_sellers_mode'] ?? 'auto'), ['auto', 'manual'], 'auto');
                $config = [
                    'title' => trim((string) ($input['best_sellers_title'] ?? 'Cok Satanlar')),
                    'mode' => $bestSellersMode,
                    'data_source' => $bestSellersMode === 'auto' ? 'top_selling' : 'manual',
                    'item_limit' => $this->sanitizeInt($input['best_sellers_item_limit'] ?? 8, 1, 24, 8),
                    'sort_type' => $this->sanitizeEnum((string) ($input['best_sellers_sort_type'] ?? 'sales_desc'), ['sales_desc', 'price_desc', 'price_asc', 'latest'], 'sales_desc'),
                    'show_badge' => $this->sanitizeBool($input['best_sellers_show_badge'] ?? null),
                    'card_style' => $this->sanitizeEnum((string) ($input['best_sellers_card_style'] ?? 'classic'), ['classic', 'compact', 'minimal'], 'classic'),
                    'selected_product_ids' => $this->sanitizeIdList((string) ($input['best_sellers_selected_product_ids'] ?? '')),
                ];
                break;

            case 'featured_products':
                $featuredMode = $this->sanitizeEnum((string) ($input['featured_products_mode'] ?? 'auto'), ['auto', 'manual'], 'auto');
                $config = [
                    'title' => trim((string) ($input['featured_products_title'] ?? 'One Cikan Urunler')),
                    'mode' => $featuredMode,
                    'data_source' => $featuredMode === 'auto' ? 'featured' : 'manual',
                    'item_limit' => $this->sanitizeInt($input['featured_products_item_limit'] ?? 6, 1, 24, 6),
                    'variant' => $this->sanitizeEnum((string) ($input['featured_products_variant'] ?? 'grid'), ['grid', 'carousel', 'compact'], 'grid'),
                    'selected_product_ids' => $this->sanitizeIdList((string) ($input['featured_products_selected_product_ids'] ?? '')),
                ];
                break;

            case 'campaign_banner':
                $buttonConfig = $this->buildButtonConfig('campaign_banner', $input, 'Kampanyayi Gor', '/campaigns');
                $config = [
                    'title' => trim((string) ($input['campaign_banner_title'] ?? 'Haftanin Firsati')),
                    'subtitle' => trim((string) ($input['campaign_banner_subtitle'] ?? '')),
                    'variant' => $this->sanitizeEnum((string) ($input['campaign_banner_variant'] ?? 'dark'), ['light', 'dark', 'soft', 'accent'], 'dark'),
                    'image_path' => trim((string) ($input['campaign_banner_image_path'] ?? '')),
                ];
                $config = array_merge($config, $buttonConfig);
                break;

            case 'author_showcase':
                $config = [
                    'title' => trim((string) ($input['author_showcase_title'] ?? 'Yazar Seckisi')),
                    'item_limit' => $this->sanitizeInt($input['author_showcase_item_limit'] ?? 4, 1, 24, 4),
                    'layout_type' => $this->sanitizeEnum((string) ($input['author_showcase_layout_type'] ?? 'grid'), ['grid', 'list', 'spotlight'], 'grid'),
                    'subtitle' => trim((string) ($input['author_showcase_subtitle'] ?? '')),
                    'image_path' => trim((string) ($input['author_showcase_image_path'] ?? '')),
                ];
                break;

            case 'slider':
                $buttonConfig = $this->buildButtonConfig('slider', $input, 'Detaya Git', '/');
                $config = [
                    'title' => trim((string) ($input['slider_title'] ?? 'Slider')),
                    'subtitle' => trim((string) ($input['slider_subtitle'] ?? '')),
                    'variant' => $this->sanitizeEnum((string) ($input['slider_variant'] ?? 'light'), ['light', 'dark', 'soft', 'accent'], 'light'),
                    'image_path' => trim((string) ($input['slider_image_path'] ?? '')),
                ];
                $config = array_merge($config, $buttonConfig);
                break;

            case 'newsletter':
                $buttonConfig = $this->buildButtonConfig('newsletter', $input, 'Hemen Basla', '/');
                $config = [
                    'title' => trim((string) ($input['newsletter_title'] ?? 'Bultene Katil')),
                    'subtitle' => trim((string) ($input['newsletter_subtitle'] ?? 'Kampanya ve yeni urun duyurulari icin kayit alani.')),
                    'input_placeholder' => trim((string) ($input['newsletter_input_placeholder'] ?? 'E-posta adresiniz')),
                    'variant' => $this->sanitizeEnum((string) ($input['newsletter_variant'] ?? 'primary'), ['primary', 'light', 'soft'], 'primary'),
                    'show_icon' => $this->sanitizeBool($input['newsletter_show_icon'] ?? null),
                ];
                $config = array_merge($config, $buttonConfig);
                break;

            case 'notice':
                $config = [
                    'title' => trim((string) ($input['notice_title'] ?? 'Bilgilendirme')),
                    'content' => trim((string) ($input['notice_content'] ?? 'Kisa duyuru veya operasyonel bilgilendirme alani.')),
                    'notice_type' => $this->sanitizeEnum((string) ($input['notice_notice_type'] ?? 'info'), ['info', 'success', 'warning', 'danger'], 'info'),
                    'tone' => $this->sanitizeEnum((string) ($input['notice_tone'] ?? 'soft'), ['soft', 'solid'], 'soft'),
                    'show_icon' => $this->sanitizeBool($input['notice_show_icon'] ?? null),
                ];
                break;

            case 'category_grid':
                $config = [
                    'title' => trim((string) ($input['category_grid_title'] ?? 'Kategori Grid')),
                    'item_limit' => $this->sanitizeInt($input['category_grid_item_limit'] ?? 4, 1, 12, 4),
                    'grid_type' => $this->sanitizeEnum((string) ($input['category_grid_grid_type'] ?? '4_col'), ['2_col', '3_col', '4_col', 'masonry'], '4_col'),
                    'label' => trim((string) ($input['category_grid_label'] ?? '')),
                    'image_path' => trim((string) ($input['category_grid_image_path'] ?? '')),
                ];
                break;
        }

        return [
            'success' => true,
            'config_json' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    private function summarizeConfig(string $blockTypeCode, string $configJson): string
    {
        $config = $this->decodeJson($configJson);

        return match ($blockTypeCode) {
            'hero_banner' => trim((string) ($config['title'] ?? 'Hero Banner')) . ' / ' . trim((string) ($config['variant'] ?? 'light')),
            'best_sellers' => trim((string) ($config['title'] ?? 'Cok Satanlar')) . ' / ' . trim((string) ($config['mode'] ?? 'auto')) . ' / limit ' . (string) ($config['item_limit'] ?? 8),
            'featured_products' => trim((string) ($config['title'] ?? 'One Cikan Urunler')) . ' / ' . trim((string) ($config['mode'] ?? 'auto')) . ' / ' . ($this->countSelectedItems($config['selected_product_ids'] ?? []) > 0 ? $this->countSelectedItems($config['selected_product_ids'] ?? []) . ' urun' : trim((string) ($config['variant'] ?? 'grid'))),
            'campaign_banner' => trim((string) ($config['title'] ?? 'Kampanya Banner')) . ' / ' . trim((string) ($config['variant'] ?? 'dark')),
            'author_showcase' => trim((string) ($config['title'] ?? 'Yazar Seckisi')) . ' / ' . trim((string) ($config['layout_type'] ?? 'grid')),
            'slider' => trim((string) ($config['title'] ?? 'Slider')) . ' / ' . trim((string) ($config['variant'] ?? 'light')),
            'newsletter' => trim((string) ($config['title'] ?? 'Bultene Katil')) . ' / ' . trim((string) ($config['button_text'] ?? 'Hemen Basla')),
            'notice' => trim((string) ($config['title'] ?? 'Bilgilendirme')) . ' / ' . trim((string) ($config['notice_type'] ?? 'info')),
            'category_grid' => trim((string) ($config['title'] ?? 'Kategori Grid')) . ' / ' . trim((string) ($config['grid_type'] ?? '4_col')),
            default => trim((string) ($config['title'] ?? $config['content'] ?? ($config['subtitle'] ?? 'Varsayilan ayarlar'))),
        };
    }

    private function builderOptions(): array
    {
        return [
            'hero_variants' => ['light' => 'Light', 'dark' => 'Dark', 'soft' => 'Soft', 'accent' => 'Accent'],
            'best_sellers_sort_types' => [
                'sales_desc' => 'Cok satanlar',
                'price_desc' => 'Fiyat yuksekten',
                'price_asc' => 'Fiyat dusukten',
                'latest' => 'Yeni eklenenler',
            ],
            'data_modes' => ['auto' => 'Auto', 'manual' => 'Manual'],
            'best_sellers_card_styles' => ['classic' => 'Classic', 'compact' => 'Compact', 'minimal' => 'Minimal'],
            'featured_variants' => ['grid' => 'Grid', 'carousel' => 'Carousel', 'compact' => 'Compact'],
            'campaign_variants' => ['light' => 'Light', 'dark' => 'Dark', 'soft' => 'Soft', 'accent' => 'Accent'],
            'slider_variants' => ['light' => 'Light', 'dark' => 'Dark', 'soft' => 'Soft', 'accent' => 'Accent'],
            'newsletter_variants' => ['primary' => 'Primary', 'light' => 'Light', 'soft' => 'Soft'],
            'notice_types' => ['info' => 'Info', 'success' => 'Success', 'warning' => 'Warning', 'danger' => 'Danger'],
            'notice_tones' => ['soft' => 'Soft', 'solid' => 'Solid'],
            'author_layout_types' => ['grid' => 'Grid', 'list' => 'List', 'spotlight' => 'Spotlight'],
            'category_grid_types' => ['2_col' => '2 Kolon', '3_col' => '3 Kolon', '4_col' => '4 Kolon', 'masonry' => 'Masonry'],
            'tone_options' => ['light' => 'Light', 'dark' => 'Dark', 'soft' => 'Soft', 'accent' => 'Accent', 'neutral' => 'Neutral'],
            'spacing_options' => ['compact' => 'Compact', 'normal' => 'Normal', 'spacious' => 'Spacious'],
            'align_options' => ['left' => 'Left', 'center' => 'Center'],
            'cta_presets' => [
                'buy_now' => 'Satin Al',
                'inspect' => 'Incele',
                'discover_now' => 'Simdi Kesfet',
                'go_detail' => 'Detaya Git',
                'view_campaign' => 'Kampanyayi Gor',
                'go_category' => 'Kategoriye Git',
                'start_now' => 'Hemen Basla',
                'custom' => 'Ozel Metin',
            ],
            'link_types' => [
                'page' => 'Page',
                'category' => 'Category',
                'campaign' => 'Campaign',
                'product' => 'Product',
                'custom_url' => 'Custom URL',
            ],
            'link_targets' => [
                'page' => [
                    '/home' => 'Ana Sayfa',
                    '/cart' => 'Sepet',
                    '/checkout' => 'Checkout',
                ],
                'category' => [
                    '/kategori/cocuk-kitaplari' => 'Cocuk Kitaplari',
                    '/kategori/roman' => 'Roman',
                    '/kategori/kisisel-gelisim' => 'Kisisel Gelisim',
                ],
                'campaign' => [
                    '/kampanyalar/yaz-firsatlari' => 'Yaz Firsatlari',
                    '/kampanyalar/haftanin-secimi' => 'Haftanin Secimi',
                ],
                'product' => [
                    '/urun/ornek-urun-1' => 'Ornek Urun 1',
                    '/urun/ornek-urun-2' => 'Ornek Urun 2',
                ],
            ],
        ];
    }

    private function buildButtonConfig(string $prefix, array $input, string $defaultText, string $defaultLink): array
    {
        $presetKey = trim((string) ($input[$prefix . '_button_text_preset'] ?? ''));
        $customText = trim((string) ($input[$prefix . '_button_text_custom'] ?? ''));
        $presetMap = $this->builderOptions()['cta_presets'] ?? [];
        $buttonText = $presetKey !== '' && $presetKey !== 'custom' && isset($presetMap[$presetKey])
            ? (string) $presetMap[$presetKey]
            : $customText;
        $buttonText = $buttonText === '' ? trim((string) ($input[$prefix . '_button_text'] ?? $defaultText)) : $buttonText;

        $linkType = $this->sanitizeEnum(
            trim((string) ($input[$prefix . '_button_link_type'] ?? 'custom_url')),
            ['page', 'category', 'campaign', 'product', 'custom_url'],
            'custom_url'
        );
        $targetValue = trim((string) ($input[$prefix . '_button_link_target'] ?? ''));
        $customUrl = trim((string) ($input[$prefix . '_button_link_custom_url'] ?? ''));
        $buttonLink = $linkType === 'custom_url' ? $customUrl : $targetValue;
        $buttonLink = $buttonLink === '' ? trim((string) ($input[$prefix . '_button_link'] ?? $defaultLink)) : $buttonLink;

        return [
            'button_text' => $buttonText,
            'button_text_preset' => $presetKey === '' ? 'custom' : $presetKey,
            'button_link' => $buttonLink,
            'button_link_type' => $linkType,
            'button_link_target' => $targetValue,
            'button_link_custom_url' => $customUrl,
        ];
    }

    private function decodeJson(string $json): array
    {
        if (trim($json) === '') {
            return [];
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function sanitizeEnum(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private function sanitizeInt(mixed $value, int $min, int $max, int $fallback): int
    {
        $value = (int) $value;

        if ($value < $min || $value > $max) {
            return $fallback;
        }

        return $value;
    }

    private function sanitizeBool(mixed $value): bool
    {
        return $value !== null && $value !== '' && $value !== '0';
    }

    private function sanitizeIdList(string $value): array
    {
        $parts = array_filter(array_map('trim', explode(',', $value)), static fn ($item) => $item !== '');

        return array_values($parts);
    }

    private function countSelectedItems(mixed $value): int
    {
        return is_array($value) ? count($value) : 0;
    }

    private function normalizeScheduleDate(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return ['success' => false, 'error' => 'Planlama tarihi zorunlu.'];
        }

        $formats = ['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date instanceof \DateTime) {
                return ['success' => true, 'value' => $date->format('Y-m-d H:i:s')];
            }
        }

        return ['success' => false, 'error' => 'Planlama tarihi formati gecersiz.'];
    }

    private function filterBlockTypesForPageCode(array $blockTypes, string $pageCode): array
    {
        $allowedCodes = $this->getAllowedBlocksForPageCode($pageCode);

        return array_values(array_filter($blockTypes, static function (array $blockType) use ($allowedCodes): bool {
            return in_array((string) ($blockType['code'] ?? ''), $allowedCodes, true);
        }));
    }

    private function isBlockAllowedForPageCode(string $pageCode, string $blockCode): bool
    {
        return in_array($blockCode, $this->getAllowedBlocksForPageCode($pageCode), true);
    }

    private function getAllowedBlocksForPageCode(string $pageCode): array
    {
        return match ($pageCode) {
            'home' => [
                'hero_banner',
                'slider',
                'campaign_banner',
                'best_sellers',
                'featured_products',
                'category_grid',
                'author_showcase',
                'newsletter',
                'notice',
            ],
            'product_list' => [
                'product_list_layout',
            ],
            'product_detail', 'cart', 'checkout' => [
                'notice',
            ],
            default => [
                'notice',
            ],
        };
    }

    private function getBuilderPolicyForPageCode(string $pageCode): array
    {
        return match ($pageCode) {
            'home' => [
                'mode' => 'full',
                'title' => 'Tam block kutuphanesi',
                'message' => 'Ana sayfa builder akisi slider, banner, urun vitrinleri ve newsletter gibi zengin bloklarla calisir.',
            ],
            'product_list' => [
                'mode' => 'page_specific',
                'title' => 'Product list icin ozel sayfa yonetimi',
                'message' => 'Bu sayfa generic block builder yerine hazir urun listeleme sablonu uzerinden kontrollu section ayarlariyla yonetilir.',
            ],
            'product_detail' => [
                'mode' => 'page_specific',
                'title' => 'Product detail icin kontrollu sayfa yonetimi',
                'message' => 'Urun detay sayfasi, urun veri ve sepet mantigina dokunmadan kontrollu section ayarlariyla yonetilir.',
            ],
            'cart' => [
                'mode' => 'page_specific',
                'title' => 'Cart icin kontrollu sayfa yonetimi',
                'message' => 'Sepet sayfasi, fiyat ve stok mantigina dokunmadan kontrollu section ayarlariyla yonetilir.',
            ],
            'checkout' => [
                'mode' => 'transition',
                'title' => 'Checkout icin gecis modu',
                'message' => 'Odeme sayfasi ileride daha kontrollu bir builder ile yonetilecek. Bu sprintte block kutuphanesi bilerek sinirlandirildi.',
            ],
            default => [
                'mode' => 'limited',
                'title' => 'Kisitli block kutuphanesi',
                'message' => 'Bu sayfa turu icin yalnizca guvenli ve temel block seti gosterilir.',
            ],
        };
    }

    private function ensureProductListLayoutBlock(string $versionId): ?array
    {
        if (! $this->blockTablesReady() || ! $this->blockTypeTablesReady()) {
            return null;
        }

        foreach ($this->blockInstanceModel->findDetailedByPageVersion($versionId) as $block) {
            if (($block['block_type_code'] ?? '') === 'product_list_layout') {
                return $block;
            }
        }

        $blockType = $this->blockTypeModel->findByCode('product_list_layout');
        if (! is_array($blockType)) {
            return null;
        }

        $newId = $this->blockInstanceModel->insert([
            'owner_type' => 'PAGE',
            'owner_version_id' => $versionId,
            'block_type_id' => $blockType['id'],
            'zone' => 'main',
            'position_x' => 0,
            'position_y' => 0,
            'width' => 12,
            'height' => 1,
            'order_index' => 0,
            'config_json' => (string) ($blockType['default_config_json'] ?? json_encode($this->defaultProductListConfig(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'is_visible' => 1,
        ], true);

        if (! $newId) {
            return null;
        }

        return $this->blockInstanceModel->findByIdDetailed((string) $newId);
    }

    private function defaultProductListConfig(): array
    {
        return [
            'sections' => [
                'sayfa_ust_alani' => ['active' => true, 'order' => 1],
                'filtre_alani' => ['active' => true, 'order' => 2],
                'siralama_sonuc_cubugu' => ['active' => true, 'order' => 3],
                'urun_listesi_gorunumu' => ['active' => true, 'order' => 4],
                'bilgilendirme_kampanya_alani' => ['active' => true, 'order' => 5],
                'bos_sonuc_alani' => ['active' => true, 'order' => 6],
                'alt_aciklama_alani' => ['active' => false, 'order' => 7],
            ],
            'sayfa_basligi' => 'Kategori Sayfasi',
            'sayfa_alt_basligi' => 'One cikan urunleri ve filtreleri duzenleyin',
            'breadcrumb_goster' => true,
            'ust_banner_goster' => true,
            'banner_gorseli' => '',
            'banner_basligi' => 'Secili Kategori',
            'banner_alt_metni' => 'Listeleme sayfasinin ust alanini yonetin',
            'banner_tonu' => 'soft',
            'filtreler_goster' => true,
            'filtre_konumu' => 'left',
            'filtre_ozeti_goster' => true,
            'filtre_basligi' => 'Filtreler',
            'siralama_cubugu_goster' => true,
            'sonuc_sayisi_goster' => true,
            'aktif_filtre_etiketleri_goster' => true,
            'varsayilan_grid_yogunlugu' => '3',
            'kart_varyanti' => 'classic',
            'grid_yogunlugu' => '3',
            'rozetleri_goster' => true,
            'favori_butonu_goster' => true,
            'hizli_aksiyonlari_goster' => false,
            'bilgilendirme_alani_goster' => true,
            'bilgilendirme_basligi' => 'Kargo Bilgisi',
            'bilgilendirme_metni' => '250 TL ve uzeri siparislerde ucretsiz kargo.',
            'bilgilendirme_tonu' => 'info',
            'bilgilendirme_gorseli' => '',
            'bos_sonuc_basligi' => 'Sonuc bulunamadi',
            'bos_sonuc_aciklamasi' => 'Filtreleri degistirerek tekrar deneyin.',
            'bos_sonuc_tonu' => 'warning',
            'bos_sonuc_gorseli' => '',
            'alt_aciklama_goster' => false,
            'alt_aciklama_basligi' => 'Listeleme Aciklamasi',
            'alt_aciklama_metni' => 'Bu alan kategoriye ait aciklayici metinler icin kullanilir.',
        ];
    }

    private function buildProductListConfigPayload(array $input): array
    {
        $defaults = $this->defaultProductListConfig();

        return [
            'sections' => [
                'sayfa_ust_alani' => [
                    'active' => $this->sanitizeBool($input['section_sayfa_ust_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_sayfa_ust_alani_order'] ?? 1, 1, 7, 1),
                ],
                'filtre_alani' => [
                    'active' => $this->sanitizeBool($input['section_filtre_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_filtre_alani_order'] ?? 2, 1, 7, 2),
                ],
                'siralama_sonuc_cubugu' => [
                    'active' => $this->sanitizeBool($input['section_siralama_sonuc_cubugu_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_siralama_sonuc_cubugu_order'] ?? 3, 1, 7, 3),
                ],
                'urun_listesi_gorunumu' => [
                    'active' => $this->sanitizeBool($input['section_urun_listesi_gorunumu_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_urun_listesi_gorunumu_order'] ?? 4, 1, 7, 4),
                ],
                'bilgilendirme_kampanya_alani' => [
                    'active' => $this->sanitizeBool($input['section_bilgilendirme_kampanya_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_bilgilendirme_kampanya_alani_order'] ?? 5, 1, 7, 5),
                ],
                'bos_sonuc_alani' => [
                    'active' => $this->sanitizeBool($input['section_bos_sonuc_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_bos_sonuc_alani_order'] ?? 6, 1, 7, 6),
                ],
                'alt_aciklama_alani' => [
                    'active' => $this->sanitizeBool($input['section_alt_aciklama_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_alt_aciklama_alani_order'] ?? 7, 1, 7, 7),
                ],
            ],
            'sayfa_basligi' => trim((string) ($input['sayfa_basligi'] ?? $defaults['sayfa_basligi'])),
            'sayfa_alt_basligi' => trim((string) ($input['sayfa_alt_basligi'] ?? $defaults['sayfa_alt_basligi'])),
            'breadcrumb_goster' => $this->sanitizeBool($input['breadcrumb_goster'] ?? null),
            'ust_banner_goster' => $this->sanitizeBool($input['ust_banner_goster'] ?? null),
            'banner_gorseli' => trim((string) ($input['banner_gorseli'] ?? '')),
            'banner_basligi' => trim((string) ($input['banner_basligi'] ?? $defaults['banner_basligi'])),
            'banner_alt_metni' => trim((string) ($input['banner_alt_metni'] ?? $defaults['banner_alt_metni'])),
            'banner_tonu' => $this->sanitizeEnum((string) ($input['banner_tonu'] ?? 'soft'), ['light', 'dark', 'soft', 'accent'], 'soft'),
            'filtreler_goster' => $this->sanitizeBool($input['filtreler_goster'] ?? null),
            'filtre_konumu' => $this->sanitizeEnum((string) ($input['filtre_konumu'] ?? 'left'), ['left', 'top'], 'left'),
            'filtre_ozeti_goster' => $this->sanitizeBool($input['filtre_ozeti_goster'] ?? null),
            'filtre_basligi' => trim((string) ($input['filtre_basligi'] ?? $defaults['filtre_basligi'])),
            'siralama_cubugu_goster' => $this->sanitizeBool($input['siralama_cubugu_goster'] ?? null),
            'sonuc_sayisi_goster' => $this->sanitizeBool($input['sonuc_sayisi_goster'] ?? null),
            'aktif_filtre_etiketleri_goster' => $this->sanitizeBool($input['aktif_filtre_etiketleri_goster'] ?? null),
            'varsayilan_grid_yogunlugu' => $this->sanitizeEnum((string) ($input['varsayilan_grid_yogunlugu'] ?? '3'), ['2', '3', '4'], '3'),
            'kart_varyanti' => $this->sanitizeEnum((string) ($input['kart_varyanti'] ?? 'classic'), ['classic', 'minimal', 'elevated'], 'classic'),
            'grid_yogunlugu' => $this->sanitizeEnum((string) ($input['grid_yogunlugu'] ?? '3'), ['2', '3', '4'], '3'),
            'rozetleri_goster' => $this->sanitizeBool($input['rozetleri_goster'] ?? null),
            'favori_butonu_goster' => $this->sanitizeBool($input['favori_butonu_goster'] ?? null),
            'hizli_aksiyonlari_goster' => $this->sanitizeBool($input['hizli_aksiyonlari_goster'] ?? null),
            'bilgilendirme_alani_goster' => $this->sanitizeBool($input['bilgilendirme_alani_goster'] ?? null),
            'bilgilendirme_basligi' => trim((string) ($input['bilgilendirme_basligi'] ?? $defaults['bilgilendirme_basligi'])),
            'bilgilendirme_metni' => trim((string) ($input['bilgilendirme_metni'] ?? $defaults['bilgilendirme_metni'])),
            'bilgilendirme_tonu' => $this->sanitizeEnum((string) ($input['bilgilendirme_tonu'] ?? 'info'), ['info', 'success', 'warning', 'danger'], 'info'),
            'bilgilendirme_gorseli' => trim((string) ($input['bilgilendirme_gorseli'] ?? '')),
            'bos_sonuc_basligi' => trim((string) ($input['bos_sonuc_basligi'] ?? $defaults['bos_sonuc_basligi'])),
            'bos_sonuc_aciklamasi' => trim((string) ($input['bos_sonuc_aciklamasi'] ?? $defaults['bos_sonuc_aciklamasi'])),
            'bos_sonuc_tonu' => $this->sanitizeEnum((string) ($input['bos_sonuc_tonu'] ?? 'warning'), ['info', 'success', 'warning', 'danger'], 'warning'),
            'bos_sonuc_gorseli' => trim((string) ($input['bos_sonuc_gorseli'] ?? '')),
            'alt_aciklama_goster' => $this->sanitizeBool($input['alt_aciklama_goster'] ?? null),
            'alt_aciklama_basligi' => trim((string) ($input['alt_aciklama_basligi'] ?? $defaults['alt_aciklama_basligi'])),
            'alt_aciklama_metni' => trim((string) ($input['alt_aciklama_metni'] ?? $defaults['alt_aciklama_metni'])),
        ];
    }

    private function normalizeProductListConfig(array $config): array
    {
        $defaults = $this->defaultProductListConfig();

        $legacyMap = [
            'page_title' => 'sayfa_basligi',
            'page_subtitle' => 'sayfa_alt_basligi',
            'show_breadcrumb' => 'breadcrumb_goster',
            'show_top_banner' => 'ust_banner_goster',
            'banner_image' => 'banner_gorseli',
            'banner_title' => 'banner_basligi',
            'banner_subtitle' => 'banner_alt_metni',
            'show_filters' => 'filtreler_goster',
            'filter_position' => 'filtre_konumu',
            'show_filter_summary' => 'filtre_ozeti_goster',
            'show_sort_bar' => 'siralama_cubugu_goster',
            'show_result_count' => 'sonuc_sayisi_goster',
            'default_grid_density' => 'varsayilan_grid_yogunlugu',
            'card_variant' => 'kart_varyanti',
            'grid_density' => 'grid_yogunlugu',
            'show_badges' => 'rozetleri_goster',
            'show_favorite_button' => 'favori_butonu_goster',
            'show_quick_actions' => 'hizli_aksiyonlari_goster',
            'show_notice' => 'bilgilendirme_alani_goster',
            'notice_title' => 'bilgilendirme_basligi',
            'notice_text' => 'bilgilendirme_metni',
            'notice_tone' => 'bilgilendirme_tonu',
            'notice_image' => 'bilgilendirme_gorseli',
            'empty_title' => 'bos_sonuc_basligi',
            'empty_description' => 'bos_sonuc_aciklamasi',
            'empty_notice_tone' => 'bos_sonuc_tonu',
        ];

        foreach ($legacyMap as $oldKey => $newKey) {
            if (! array_key_exists($newKey, $config) && array_key_exists($oldKey, $config)) {
                $config[$newKey] = $config[$oldKey];
            }
        }

        $config['sections'] = is_array($config['sections'] ?? null) ? $config['sections'] : [];
        foreach ($defaults['sections'] as $key => $sectionDefaults) {
            $current = is_array($config['sections'][$key] ?? null) ? $config['sections'][$key] : [];
            $config['sections'][$key] = array_merge($sectionDefaults, $current);
        }

        unset($defaults['sections']);

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }

        return $config;
    }
}
