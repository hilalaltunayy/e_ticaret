<?php

namespace App\Services;

use App\DTO\PageManagement\PageListItemDTO;
use App\Models\PageModel;
use App\Models\PageVersionModel;

class PageService
{
    public function __construct(
        private ?PageModel $pageModel = null,
        private ?PageVersionModel $pageVersionModel = null
    ) {
        $this->pageModel = $this->pageModel ?? new PageModel();
        $this->pageVersionModel = $this->pageVersionModel ?? new PageVersionModel();
    }

    public function listPageItems(): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $items = [];

        foreach ($this->pageModel->findAllOrdered() as $page) {
            $draftCount = count($this->pageVersionModel->findEditableVersionsByPageId($page['id']));
            $publishedVersion = $this->pageVersionModel->findPublishedByPageId($page['id']);

            $items[] = new PageListItemDTO(
                $page['id'],
                (string) $page['code'],
                (string) $page['name'],
                (string) $page['status'],
                $draftCount,
                $publishedVersion['id'] ?? null,
                $publishedVersion['name'] ?? null,
                $publishedVersion['published_at'] ?? null
            );
        }

        return $items;
    }

    public function findPageByCode(string $code): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        return $this->pageModel->findByCode($code);
    }

    public function tablesReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('pages') && $db->tableExists('page_versions');
    }
}
