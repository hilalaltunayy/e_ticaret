<?php

namespace App\DTO\PageManagement;

class PageListItemDTO
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $status,
        public int $draftCount,
        public ?string $publishedVersionId,
        public ?string $publishedVersionName,
        public ?string $publishedAt
    ) {
    }
}
