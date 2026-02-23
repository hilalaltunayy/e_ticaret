<?php

namespace App\Models;

class CategoryModel extends BaseUuidModel
{
    protected $table         = 'categories';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id','category_name'
    ];

    // categories tablosunda created_at vs yoksa timestamps kapalı kalsın:
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    public function getAllForAdmin(): array
    {
        return $this->orderBy('category_name', 'ASC')->findAll();
    }

    public function findByName(string $name): ?array
    {
        $normalized = trim($name);
        if ($normalized === '') {
            return null;
        }

        $normalizedLower = function_exists('mb_strtolower')
            ? mb_strtolower($normalized, 'UTF-8')
            : strtolower($normalized);

        return $this->builder()
            ->select('id, category_name')
            ->where('LOWER(category_name)', $normalizedLower)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    public function createCategory(string $name): string|int
    {
        $normalized = trim($name);
        if ($normalized === '') {
            return 0;
        }

        $insertId = $this->insert(['category_name' => $normalized], true);

        return $insertId === false ? 0 : $insertId;
    }

    public function findOrCreateByName(string $name): string|int
    {
        $existing = $this->findByName($name);
        if ($existing && isset($existing['id'])) {
            return $existing['id'];
        }

        return $this->createCategory($name);
    }
}
