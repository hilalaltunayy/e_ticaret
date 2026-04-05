<?php

namespace App\Services;

use App\DTO\PageManagement\PageDraftListItemDTO;
use App\Models\PageModel;
use App\Models\PageVersionModel;

class PageVersionService
{
    public function __construct(
        private ?PageModel $pageModel = null,
        private ?PageVersionModel $pageVersionModel = null
    ) {
        $this->pageModel = $this->pageModel ?? new PageModel();
        $this->pageVersionModel = $this->pageVersionModel ?? new PageVersionModel();
    }

    public function getDraftListByPageCode(string $pageCode): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $page = $this->pageModel->findByCode($pageCode);

        if (! is_array($page)) {
            return [];
        }

        $items = [];

        foreach ($this->pageVersionModel->findWorkingVersionsByPageId($page['id']) as $draft) {
            $items[] = new PageDraftListItemDTO(
                (string) $draft['id'],
                (string) $draft['page_id'],
                (int) $draft['version_no'],
                (string) $draft['name'],
                (string) $draft['status'],
                $draft['updated_at'] ?? null,
                $draft['created_at'] ?? null
            );
        }

        return $items;
    }

    public function findVersionDetail(string $versionId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->pageVersionModel
            ->select('page_versions.*, pages.code AS page_code, pages.name AS page_name')
            ->join('pages', 'pages.id = page_versions.page_id', 'left')
            ->where('page_versions.id', $versionId)
            ->first();

        return is_array($row) ? $row : null;
    }

    public function findPublishedVersionForPage(string $pageId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        // Sprint 1 notu: Aynı page için aynı anda yalnızca bir adet PUBLISHED version olmalı.
        return $this->pageVersionModel->findPublishedByPageId($pageId);
    }

    public function tablesReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('pages') && $db->tableExists('page_versions');
    }
}
