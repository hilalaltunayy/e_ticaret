<?php

namespace App\Models;

class PageModel extends BaseUuidModel
{
    protected $table = 'pages';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'code',
        'name',
        'status',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByCode(string $code): ?array
    {
        $row = $this->where('code', $code)->first();

        return is_array($row) ? $row : null;
    }

    public function findAllOrdered(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }
}
