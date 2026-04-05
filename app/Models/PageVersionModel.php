<?php

namespace App\Models;

class PageVersionModel extends BaseUuidModel
{
    protected $table = 'page_versions';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'page_id',
        'version_no',
        'name',
        'slug',
        'status',
        'created_by',
        'notes',
        'published_at',
        'scheduled_publish_at',
        'archived_at',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByPageIdOrdered(string $pageId): array
    {
        return $this->where('page_id', $pageId)
            ->orderBy('version_no', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->findAll();
    }

    public function findDraftsByPageId(string $pageId): array
    {
        return $this->where('page_id', $pageId)
            ->where('status', 'DRAFT')
            ->orderBy('updated_at', 'DESC')
            ->orderBy('version_no', 'DESC')
            ->findAll();
    }

    public function findEditableVersionsByPageId(string $pageId): array
    {
        return $this->where('page_id', $pageId)
            ->whereIn('status', ['DRAFT', 'SCHEDULED'])
            ->orderBy('updated_at', 'DESC')
            ->orderBy('version_no', 'DESC')
            ->findAll();
    }

    public function findWorkingVersionsByPageId(string $pageId): array
    {
        return $this->where('page_id', $pageId)
            ->whereIn('status', ['DRAFT', 'SCHEDULED', 'PUBLISHED'])
            ->orderBy('version_no', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->findAll();
    }

    public function findPublishedByPageId(string $pageId): ?array
    {
        $row = $this->where('page_id', $pageId)
            ->where('status', 'PUBLISHED')
            ->orderBy('published_at', 'DESC')
            ->first();

        return is_array($row) ? $row : null;
    }

    public function findLatestDraftByPageId(string $pageId): ?array
    {
        $row = $this->where('page_id', $pageId)
            ->where('status', 'DRAFT')
            ->orderBy('version_no', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->first();

        return is_array($row) ? $row : null;
    }

    public function findLatestEditableByPageId(string $pageId): ?array
    {
        $row = $this->where('page_id', $pageId)
            ->whereIn('status', ['DRAFT', 'SCHEDULED'])
            ->orderBy('version_no', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->first();

        return is_array($row) ? $row : null;
    }

    public function getNextVersionNo(string $pageId): int
    {
        $row = $this->selectMax('version_no')
            ->where('page_id', $pageId)
            ->first();

        return ((int) ($row['version_no'] ?? 0)) + 1;
    }
}
