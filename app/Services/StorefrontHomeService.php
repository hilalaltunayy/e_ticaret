<?php

namespace App\Services;

use App\Models\AuthorModel;
use App\Models\BlockInstanceModel;
use App\Models\CategoryModel;
use App\Models\PageVersionModel;
use App\Models\ProductsModel;

class StorefrontHomeService
{
    private const CATEGORY_NAV_ITEMS = [
        'Dijital',
        'Basili',
        'Roman',
        'Siir',
        'Hikaye',
        'Cocuk',
        'Kisisel Gelisim',
        'Akademik',
        'Cok Satanlar',
        'Yeni Gelenler',
        'Kampanyalar',
    ];

    public function __construct(
        private ?PageService $pageService = null,
        private ?PageVersionModel $pageVersionModel = null,
        private ?BlockInstanceModel $blockInstanceModel = null,
        private ?ProductsModel $productsModel = null,
        private ?CategoryModel $categoryModel = null,
        private ?AuthorModel $authorModel = null
    ) {
        $this->pageService = $this->pageService ?? new PageService();
        $this->pageVersionModel = $this->pageVersionModel ?? new PageVersionModel();
        $this->blockInstanceModel = $this->blockInstanceModel ?? new BlockInstanceModel();
        $this->productsModel = $this->productsModel ?? new ProductsModel();
        $this->categoryModel = $this->categoryModel ?? new CategoryModel();
        $this->authorModel = $this->authorModel ?? new AuthorModel();
    }

    public function getHomePageData(string $searchQuery = ''): array
    {
        $page = $this->pageService->findPageByCode('home');
        $publishedVersion = null;
        $blocks = [];

        if (is_array($page) && isset($page['id'])) {
            $publishedVersion = $this->pageVersionModel->findPublishedByPageId((string) $page['id']);
        }

        if (is_array($publishedVersion) && isset($publishedVersion['id'])) {
            $blocks = $this->normalizeBlocks(
                $this->blockInstanceModel->findDetailedByPageVersion((string) $publishedVersion['id']),
                $searchQuery
            );
        }

        return [
            'title' => 'Kitap Dunyasi',
            'searchQuery' => $searchQuery,
            'page' => $page,
            'publishedVersion' => $publishedVersion,
            'blocks' => $blocks,
            'hasPublishedHome' => ! empty($blocks),
            'headerMenuItems' => $this->buildHeaderMenuItems(),
            'categoryNavItems' => $this->buildCategoryNavItems(),
        ];
    }

    private function normalizeBlocks(array $blocks, string $searchQuery): array
    {
        $normalized = [];

        foreach ($blocks as $block) {
            if ((int) ($block['is_visible'] ?? 1) !== 1) {
                continue;
            }

            $code = trim((string) ($block['block_type_code'] ?? ''));
            $config = $this->decodeConfig($block['config_json'] ?? null);
            $imageUrl = $this->resolveMediaUrl($config['image_path'] ?? null);

            if ($imageUrl !== null) {
                $config['image_url'] = $imageUrl;
            }

            $item = [
                'id' => (string) ($block['id'] ?? ''),
                'code' => $code,
                'name' => (string) ($block['block_type_name'] ?? $code),
                'config' => $config,
                'summary' => $this->buildSummary($config),
            ];

            switch ($code) {
                case 'hero_banner':
                case 'campaign_banner':
                case 'slider':
                    $item['template'] = 'banner';
                    break;

                case 'best_sellers':
                case 'featured_products':
                    $item['template'] = 'product_showcase';
                    $item['products'] = $this->resolveProducts($config, $code, $searchQuery);
                    break;

                case 'category_grid':
                    $item['template'] = 'category_grid';
                    $item['categories'] = $this->resolveCategories($config, $searchQuery);
                    break;

                case 'author_showcase':
                    $item['template'] = 'author_showcase';
                    $item['authors'] = $this->resolveAuthors($config, $searchQuery);
                    break;

                case 'newsletter':
                    $item['template'] = 'newsletter';
                    break;

                case 'notice':
                    $item['template'] = 'notice';
                    break;

                default:
                    $item['template'] = 'generic';
                    break;
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    private function resolveProducts(array $config, string $code, string $searchQuery): array
    {
        $limit = $this->sanitizeLimit($config['item_limit'] ?? null, $code === 'best_sellers' ? 8 : 6, 12);
        $mode = trim((string) ($config['mode'] ?? 'auto'));
        $allProducts = array_values(array_filter(
            $this->productsModel->getActiveProducts(),
            static fn (array $product): bool => ((int) ($product['is_active'] ?? 0)) === 1
        ));

        if ($mode === 'manual' && ! empty($config['selected_product_ids']) && is_array($config['selected_product_ids'])) {
            $selectedIds = array_map('strval', $config['selected_product_ids']);
            $allProducts = array_values(array_filter(
                $allProducts,
                static fn (array $product): bool => in_array((string) ($product['id'] ?? ''), $selectedIds, true)
            ));
        }

        $sortType = trim((string) ($config['sort_type'] ?? 'latest'));
        usort($allProducts, function (array $left, array $right) use ($sortType): int {
            return match ($sortType) {
                'price_asc' => (float) ($left['price'] ?? 0) <=> (float) ($right['price'] ?? 0),
                'price_desc' => (float) ($right['price'] ?? 0) <=> (float) ($left['price'] ?? 0),
                default => strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? '')),
            };
        });

        if ($searchQuery !== '') {
            $needle = function_exists('mb_strtolower') ? mb_strtolower($searchQuery, 'UTF-8') : strtolower($searchQuery);
            $allProducts = array_values(array_filter($allProducts, function (array $product) use ($needle): bool {
                $haystack = trim((string) (($product['product_name'] ?? '') . ' ' . ($product['author'] ?? '')));
                $normalized = function_exists('mb_strtolower') ? mb_strtolower($haystack, 'UTF-8') : strtolower($haystack);

                return str_contains($normalized, $needle);
            }));
        }

        $items = array_slice($allProducts, 0, $limit);

        return array_map(function (array $product): array {
            $price = (float) ($product['price'] ?? 0);
            $image = trim((string) ($product['image'] ?? ''));
            $type = trim((string) ($product['type'] ?? ''));

            return [
                'id' => (string) ($product['id'] ?? ''),
                'name' => (string) ($product['product_name'] ?? 'Urun'),
                'author' => (string) ($product['author'] ?? 'Yazar belirtilmedi'),
                'price' => $price,
                'price_label' => number_format($price, 2, ',', '.') . ' TL',
                'type' => $type,
                'type_label' => $type !== '' ? ucfirst($type) : 'Kitap',
                'detail_url' => base_url('products/detail/' . (string) ($product['id'] ?? '')),
                'image_url' => $image !== '' ? base_url('assets/images/books/' . $image) : null,
                'initial' => strtoupper(substr((string) ($product['product_name'] ?? 'K'), 0, 1)),
            ];
        }, $items);
    }

    private function resolveCategories(array $config, string $searchQuery): array
    {
        $limit = $this->sanitizeLimit($config['item_limit'] ?? null, 6, 8);
        $categories = $this->categoryModel->getAllForAdmin();

        if ($searchQuery !== '') {
            $needle = function_exists('mb_strtolower') ? mb_strtolower($searchQuery, 'UTF-8') : strtolower($searchQuery);
            $categories = array_values(array_filter($categories, function (array $category) use ($needle): bool {
                $name = (string) ($category['category_name'] ?? '');
                $normalized = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);

                return str_contains($normalized, $needle);
            }));
        }

        return array_map(function (array $category): array {
            $name = (string) ($category['category_name'] ?? 'Kategori');

            return [
                'id' => (string) ($category['id'] ?? ''),
                'name' => $name,
                'url' => base_url('products'),
                'initial' => strtoupper(substr($name, 0, 1)),
            ];
        }, array_slice($categories, 0, $limit));
    }

    private function resolveAuthors(array $config, string $searchQuery): array
    {
        $limit = $this->sanitizeLimit($config['item_limit'] ?? null, 4, 6);
        $authors = $this->authorModel->getLatestForAdmin($limit * 2);

        if ($searchQuery !== '') {
            $needle = function_exists('mb_strtolower') ? mb_strtolower($searchQuery, 'UTF-8') : strtolower($searchQuery);
            $authors = array_values(array_filter($authors, function (array $author) use ($needle): bool {
                $name = (string) ($author['name'] ?? '');
                $normalized = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);

                return str_contains($normalized, $needle);
            }));
        }

        return array_map(function (array $author): array {
            $name = (string) ($author['name'] ?? 'Yazar');

            return [
                'id' => (string) ($author['id'] ?? ''),
                'name' => $name,
                'bio' => trim((string) ($author['bio'] ?? '')),
                'initial' => strtoupper(substr($name, 0, 1)),
            ];
        }, array_slice($authors, 0, $limit));
    }

    private function decodeConfig(mixed $configJson): array
    {
        if (! is_string($configJson) || trim($configJson) === '') {
            return [];
        }

        $decoded = json_decode($configJson, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function buildSummary(array $config): string
    {
        foreach (['subtitle', 'content', 'label'] as $key) {
            $value = trim((string) ($config[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function resolveMediaUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $value));
        if ($path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path) === 1 || str_starts_with($path, '//') || str_starts_with($path, 'data:')) {
            return $path;
        }

        $normalized = ltrim($path, '/');
        $candidates = [
            [
                'disk' => rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized),
                'url' => base_url($normalized),
            ],
        ];

        if (! str_contains($normalized, '/')) {
            $candidates[] = [
                'disk' => rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $normalized,
                'url' => base_url('uploads/' . $normalized),
            ];
            $candidates[] = [
                'disk' => rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'json' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $normalized,
                'url' => base_url('assets/admin/json/uploads/' . $normalized),
            ];
            $candidates[] = [
                'disk' => rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'json' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $normalized,
                'url' => base_url('assets/json/uploads/' . $normalized),
            ];
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate['disk'])) {
                return $candidate['url'];
            }
        }

        return null;
    }

    private function sanitizeLimit(mixed $value, int $fallback, int $max): int
    {
        $limit = (int) $value;
        if ($limit <= 0) {
            return $fallback;
        }

        return min($limit, $max);
    }

    private function buildHeaderMenuItems(): array
    {
        return [
            ['label' => 'Anasayfa', 'url' => base_url('/'), 'active' => true],
            ['label' => 'Favorilerim', 'url' => '#'],
            ['label' => 'Sepetim', 'url' => '#'],
            ['label' => 'Siparislerim', 'url' => '#'],
            ['label' => 'Hesabim', 'url' => '#'],
            ['label' => 'Giris Yap', 'url' => base_url('login')],
        ];
    }

    private function buildCategoryNavItems(): array
    {
        return array_map(static function (string $label): array {
            return [
                'label' => $label,
                'url' => '#',
            ];
        }, self::CATEGORY_NAV_ITEMS);
    }
}
