<?php

namespace App\Services;

class DigitalBookReaderService
{
    private const CHARS_PER_PAGE = 1400;
    private DigitalBookContentService $digitalBookContentService;

    public function __construct()
    {
        $this->digitalBookContentService = new DigitalBookContentService();
    }

    public function buildReaderPayload(string $productId, int $requestedPage): array
    {
        $productId = trim($productId);
        if ($productId === '') {
            return $this->emptyPayload();
        }

        $db = db_connect();
        if (! $db->tableExists('products')) {
            return $this->emptyPayload();
        }

        $product = $db->table('products')
            ->select('id, product_name, author, description, image')
            ->where('id', $productId)
            ->where('deleted_at', null)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! is_array($product)) {
            return $this->emptyPayload();
        }

        $storedContent = $this->digitalBookContentService->getContentForProduct($productId);
        $contentSourceText = trim((string) $storedContent) !== ''
            ? (string) $storedContent
            : (string) ($product['description'] ?? '');

        $pages = $this->chunkText($contentSourceText);
        $totalPages = count($pages);
        $currentPage = $this->normalizePage($requestedPage, $totalPages);
        $content = $totalPages > 0 ? $pages[$currentPage - 1] : '';

        helper('product_media');

        return [
            'book' => [
                'product_id' => (string) ($product['id'] ?? ''),
                'title' => trim((string) ($product['product_name'] ?? '')) ?: 'Dijital Kitap',
                'author' => trim((string) ($product['author'] ?? '')) ?: 'Yazar belirtilmedi',
                'image_url' => product_image_url((string) ($product['image'] ?? '')),
            ],
            'content' => $content,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'has_prev' => $currentPage > 1,
            'has_next' => $totalPages > 0 && $currentPage < $totalPages,
            'is_empty' => $totalPages === 0,
        ];
    }

    private function chunkText(string $text): array
    {
        $text = trim(strip_tags($text));
        if ($text === '') {
            return [];
        }

        $compact = preg_replace('/\s+/u', ' ', $text);
        $compact = is_string($compact) ? trim($compact) : $text;
        if ($compact === '') {
            return [];
        }

        $pages = str_split($compact, self::CHARS_PER_PAGE);

        return array_values(array_filter(array_map(static fn(string $chunk): string => trim($chunk), $pages), static fn(string $chunk): bool => $chunk !== ''));
    }

    private function normalizePage(int $requestedPage, int $totalPages): int
    {
        if ($totalPages <= 0) {
            return 1;
        }

        if ($requestedPage < 1) {
            return 1;
        }

        if ($requestedPage > $totalPages) {
            return $totalPages;
        }

        return $requestedPage;
    }

    private function emptyPayload(): array
    {
        return [
            'book' => [
                'product_id' => '',
                'title' => 'Dijital Kitap',
                'author' => 'Yazar belirtilmedi',
                'image_url' => '',
            ],
            'content' => '',
            'current_page' => 1,
            'total_pages' => 0,
            'has_prev' => false,
            'has_next' => false,
            'is_empty' => true,
        ];
    }
}
