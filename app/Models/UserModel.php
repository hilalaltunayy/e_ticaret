<?php

namespace App\Models;

class UserModel extends BaseUuidModel
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id', 'username', 'email', 'password', 'role', 'status',
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $deletedField   = 'deleted_at';

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function countCreatedBetween(string $start, string $end): int
    {
        return (int) $this->builder()
            ->where('created_at >=', $start)
            ->where('created_at <=', $end)
            ->countAllResults();
    }
}
