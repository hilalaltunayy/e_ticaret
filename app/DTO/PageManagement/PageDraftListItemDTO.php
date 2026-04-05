<?php

namespace App\DTO\PageManagement;

class PageDraftListItemDTO
{
    public function __construct(
        public string $id,
        public string $pageId,
        public int $versionNo,
        public string $name,
        public string $status,
        public ?string $updatedAt,
        public ?string $createdAt
    ) {
    }
}
