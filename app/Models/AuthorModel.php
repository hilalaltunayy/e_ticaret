<?php

namespace App\Models;

class AuthorModel extends BaseUuidModel
{
    protected $table         = 'authors';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id','name','bio','created_at','updated_at','deleted_at'
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $deletedField   = 'deleted_at';

    public function getAllForAdmin(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }

    public function getLatestForAdmin(int $limit = 5): array
    {
        $fields = $this->db->getFieldNames($this->table);

        if (in_array('created_at', $fields, true)) {
            return $this->orderBy('created_at', 'DESC')
                ->orderBy('id', 'DESC')
                ->findAll($limit);
        }

        return $this->orderBy('id', 'DESC')->findAll($limit);
    }

    public function findByName(string $name): ?array
    {
        $normalized = trim($name);
        if ($normalized === '') {
            return null;
        }

        return $this->where('name', $normalized)->first();
    }

    public function createAuthor(string $name): string|int
    {
        $normalized = trim($name);
        if ($normalized === '') {
            return 0;
        }

        $insertId = $this->insert(['name' => $normalized], true);

        return $insertId === false ? 0 : $insertId;
    }

    public function findOrCreateByName(string $name): string|int
    {
        $existing = $this->findByName($name);
        if ($existing && isset($existing['id'])) {
            return $existing['id'];
        }

        return $this->createAuthor($name);
    }
}
