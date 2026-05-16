<?php

namespace App\Services;

use App\Models\DigitalBookContentModel;

class DigitalBookContentService
{
    private DigitalBookContentModel $model;

    public function __construct()
    {
        $this->model = new DigitalBookContentModel();
    }

    public function getContentForProduct(string $productId): ?string
    {
        $productId = trim($productId);
        if ($productId === '') {
            return null;
        }

        if (! db_connect()->tableExists('digital_book_contents')) {
            return null;
        }

        $row = $this->model
            ->where('product_id', $productId)
            ->where('deleted_at', null)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (! is_array($row)) {
            return null;
        }

        return (string) ($row['content_text'] ?? '');
    }

    public function hasContent(string $productId): bool
    {
        $content = $this->getContentForProduct($productId);

        return trim((string) $content) !== '';
    }

    public function saveContentForProduct(string $productId, string $contentText, string $sourceType = 'manual'): bool
    {
        $productId = trim($productId);
        $sourceType = trim($sourceType) !== '' ? trim($sourceType) : 'manual';
        if ($productId === '') {
            return false;
        }

        if (! db_connect()->tableExists('digital_book_contents')) {
            return false;
        }

        $existing = $this->model
            ->where('product_id', $productId)
            ->where('deleted_at', null)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        $data = [
            'product_id' => $productId,
            'content_text' => $contentText,
            'source_type' => $sourceType,
            'deleted_at' => null,
        ];

        if (is_array($existing) && ! empty($existing['id'])) {
            return (bool) $this->model->update((string) $existing['id'], $data);
        }

        return (bool) $this->model->insert($data);
    }
}

