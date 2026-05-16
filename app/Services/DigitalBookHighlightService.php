<?php

namespace App\Services;

use App\Models\DigitalBookHighlightModel;

class DigitalBookHighlightService
{
    private DigitalBookHighlightModel $highlightModel;
    private DigitalBookReaderService $readerService;

    public function __construct()
    {
        $this->highlightModel = new DigitalBookHighlightModel();
        $this->readerService = new DigitalBookReaderService();
    }

    public function listHighlights(string $userId, string $productId, ?int $pageNo = null): array
    {
        $builder = $this->highlightModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->orderBy('created_at', 'ASC');

        if ($pageNo !== null && $pageNo > 0) {
            $builder->where('page_no', $pageNo);
        }

        return $builder->findAll();
    }

    public function createHighlight(string $userId, string $productId, array $payload): array
    {
        $pageNo = (int) ($payload['page_no'] ?? 0);
        $selectedText = $this->normalizeSelectedText((string) ($payload['selected_text'] ?? ''));
        $startOffset = is_numeric($payload['start_offset'] ?? null) ? (int) $payload['start_offset'] : null;
        $endOffset = is_numeric($payload['end_offset'] ?? null) ? (int) $payload['end_offset'] : null;
        $color = strtolower(trim((string) ($payload['color'] ?? 'yellow')));

        if ($pageNo < 1) {
            return ['success' => false, 'status' => 422, 'error' => 'invalid_page'];
        }

        if ($selectedText === '' || mb_strlen($selectedText) > 1200) {
            return ['success' => false, 'status' => 422, 'error' => 'invalid_selected_text'];
        }
        if ($startOffset === null || $endOffset === null || $startOffset < 0 || $endOffset <= $startOffset) {
            return ['success' => false, 'status' => 422, 'error' => 'invalid_offsets'];
        }

        $reader = $this->readerService->buildReaderPayload($productId, $pageNo);
        $totalPages = (int) ($reader['total_pages'] ?? 0);
        if ($totalPages < 1 || $pageNo > $totalPages) {
            return ['success' => false, 'status' => 422, 'error' => 'page_out_of_range'];
        }

        $pageContent = (string) ($reader['content'] ?? '');
        if ($pageContent === '') {
            return ['success' => false, 'status' => 422, 'error' => 'selected_text_not_in_page'];
        }
        $contentLength = mb_strlen($pageContent);
        if ($endOffset > $contentLength) {
            return ['success' => false, 'status' => 422, 'error' => 'offset_out_of_range'];
        }

        $offsetSlice = mb_substr($pageContent, $startOffset, $endOffset - $startOffset);
        if ($this->normalizeSelectedText($offsetSlice) !== $selectedText) {
            return ['success' => false, 'status' => 422, 'error' => 'selected_text_not_in_page'];
        }

        $existing = $this->findDuplicateHighlight($userId, $productId, $pageNo, $selectedText, $startOffset, $endOffset);
        if (is_array($existing)) {
            return [
                'success' => false,
                'status' => 409,
                'error' => 'duplicate_highlight',
                'highlight' => $existing,
            ];
        }
        if ($this->hasOverlappingHighlight($userId, $productId, $pageNo, $startOffset, $endOffset)) {
            return ['success' => false, 'status' => 409, 'error' => 'overlapping_highlight'];
        }

        $allowedColors = ['yellow', 'green', 'blue', 'pink'];
        if (! in_array($color, $allowedColors, true)) {
            $color = 'yellow';
        }

        $row = [
            'user_id' => $userId,
            'product_id' => $productId,
            'page_no' => $pageNo,
            'selected_text' => $selectedText,
            'start_offset' => $startOffset,
            'end_offset' => $endOffset,
            'color' => $color,
        ];

        $inserted = $this->highlightModel->insert($row, true);
        if ($inserted === false) {
            return ['success' => false, 'status' => 500, 'error' => 'insert_failed'];
        }

        $id = is_string($inserted) ? $inserted : (string) $inserted;
        $created = $this->highlightModel->find($id);

        return ['success' => true, 'status' => 201, 'highlight' => $created];
    }

    public function deleteHighlight(string $userId, string $productId, string $highlightId): array
    {
        $highlight = $this->highlightModel
            ->where('id', $highlightId)
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (! is_array($highlight)) {
            return ['success' => false, 'status' => 404, 'error' => 'highlight_not_found'];
        }

        $deleted = $this->highlightModel->delete($highlightId);
        if ($deleted === false) {
            return ['success' => false, 'status' => 500, 'error' => 'delete_failed'];
        }

        return ['success' => true, 'status' => 200];
    }

    private function normalizeSelectedText(string $text): string
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return '';
        }

        $normalized = preg_replace('/\s+/u', ' ', $trimmed);

        return is_string($normalized) ? trim($normalized) : $trimmed;
    }

    private function findDuplicateHighlight(
        string $userId,
        string $productId,
        int $pageNo,
        string $selectedText,
        int $startOffset,
        int $endOffset
    ): ?array
    {
        $matches = $this->highlightModel
            ->withDeleted()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('page_no', $pageNo)
            ->where('start_offset', $startOffset)
            ->where('end_offset', $endOffset)
            ->findAll();

        foreach ($matches as $row) {
            $rowText = $this->normalizeSelectedText((string) ($row['selected_text'] ?? ''));
            if ($rowText !== $selectedText) {
                continue;
            }

            if (! empty($row['deleted_at'])) {
                $this->highlightModel->update((string) ($row['id'] ?? ''), [
                    'deleted_at' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'selected_text' => $selectedText,
                    'start_offset' => $startOffset,
                    'end_offset' => $endOffset,
                ]);

                return $this->highlightModel->find((string) ($row['id'] ?? ''));
            }

            return $row;
        }

        return null;
    }

    private function hasOverlappingHighlight(string $userId, string $productId, int $pageNo, int $startOffset, int $endOffset): bool
    {
        $rows = $this->highlightModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('page_no', $pageNo)
            ->findAll();

        foreach ($rows as $row) {
            $existingStart = is_numeric($row['start_offset'] ?? null) ? (int) $row['start_offset'] : null;
            $existingEnd = is_numeric($row['end_offset'] ?? null) ? (int) $row['end_offset'] : null;
            if ($existingStart === null || $existingEnd === null) {
                continue;
            }

            $overlap = ! ($endOffset <= $existingStart || $startOffset >= $existingEnd);
            if ($overlap) {
                return true;
            }
        }

        return false;
    }
}
