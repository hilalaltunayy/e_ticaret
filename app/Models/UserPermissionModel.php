<?php

namespace App\Models;

class UserPermissionModel extends BaseUuidModel
{
    protected $table = 'user_permissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'user_id',
        'permission_id',
        'allowed',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getEffectivePermissions(string $userId, string $roleName): array
    {
        $roleName = trim(strtolower($roleName));
        if ($roleName === 'admin') {
            $all = $this->db->table('permissions')
                ->select('code')
                ->where('deleted_at', null)
                ->get()
                ->getResultArray();

            $codes = array_map(static fn (array $r): string => (string) ($r['code'] ?? ''), $all);
            $codes = array_values(array_filter($codes, static fn (string $c): bool => $c !== ''));
            sort($codes);
            return $codes;
        }

        $roleRows = $this->db->table('role_permissions rp')
            ->select('p.code')
            ->join('roles r', 'r.id = rp.role_id', 'inner')
            ->join('permissions p', 'p.id = rp.permission_id', 'inner')
            ->where('r.name', $roleName)
            ->where('r.deleted_at', null)
            ->where('rp.deleted_at', null)
            ->where('p.deleted_at', null)
            ->get()
            ->getResultArray();

        $effective = [];
        foreach ($roleRows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            if ($code !== '') {
                $effective[$code] = true;
            }
        }

        $overrides = $this->db->table('user_permissions up')
            ->select('p.code, up.allowed')
            ->join('permissions p', 'p.id = up.permission_id', 'inner')
            ->where('up.user_id', $userId)
            ->where('up.deleted_at', null)
            ->where('p.deleted_at', null)
            ->get()
            ->getResultArray();

        foreach ($overrides as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            if ($code === '') {
                continue;
            }

            if ((int) ($row['allowed'] ?? 0) === 1) {
                $effective[$code] = true;
            } else {
                unset($effective[$code]);
            }
        }

        $codes = array_keys($effective);
        sort($codes);

        return $codes;
    }

    public function isAllowed(string $userId, string $permCode, string $roleName = ''): bool
    {
        $permCode = trim($permCode);
        if ($permCode === '') {
            return false;
        }

        $role = trim(strtolower($roleName));
        if ($role === '') {
            $userRow = $this->db->table('users')
                ->select('role')
                ->where('id', $userId)
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();
            $role = strtolower(trim((string) ($userRow['role'] ?? '')));
        }

        if ($role === 'admin') {
            return true;
        }

        $effective = $this->getEffectivePermissions($userId, $role);
        return in_array($permCode, $effective, true);
    }

    public function setOverride(string $userId, string $permCode, bool $allowed): void
    {
        $permRow = $this->db->table('permissions')
            ->select('id')
            ->where('code', $permCode)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (! is_array($permRow) || ! isset($permRow['id'])) {
            throw new \DomainException('İzin kodu bulunamadı.');
        }

        $permissionId = (string) $permRow['id'];

        $existing = $this->withDeleted()
            ->where('user_id', $userId)
            ->where('permission_id', $permissionId)
            ->first();

        $payload = [
            'user_id' => $userId,
            'permission_id' => $permissionId,
            'allowed' => $allowed ? 1 : 0,
            'deleted_at' => null,
        ];

        if (is_array($existing) && isset($existing['id'])) {
            $this->update((string) $existing['id'], $payload);
            return;
        }

        $this->insert($payload);
    }
}