<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureReviewRatingPermissions extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('permissions') || ! $this->db->tableExists('roles') || ! $this->db->tableExists('role_permissions')) {
            return;
        }

        $permissions = [
            'create_review' => 'Yorum yapma erisimi',
            'rate_product' => 'Urun puanlama erisimi',
            'delete_reviews' => 'Yorum silme erisimi',
        ];

        $permissionIds = [];
        foreach ($permissions as $code => $description) {
            $permissionIds[$code] = $this->ensurePermission($code, $description);
        }

        $userRoleId = $this->findRoleId('user');
        $adminRoleId = $this->findRoleId('admin');

        if ($userRoleId !== '') {
            $this->ensureRolePermission($userRoleId, (string) ($permissionIds['create_review'] ?? ''));
            $this->ensureRolePermission($userRoleId, (string) ($permissionIds['rate_product'] ?? ''));
        }

        if ($adminRoleId !== '') {
            $this->ensureRolePermission($adminRoleId, (string) ($permissionIds['delete_reviews'] ?? ''));
        }
    }

    public function down()
    {
    }

    private function findRoleId(string $roleName): string
    {
        $row = $this->db->table('roles')
            ->select('id')
            ->where('name', $roleName)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        return is_array($row) ? trim((string) ($row['id'] ?? '')) : '';
    }

    private function ensurePermission(string $code, string $description): string
    {
        $existing = $this->db->table('permissions')
            ->select('id')
            ->where('code', $code)
            ->get()
            ->getRowArray();

        $permissionId = is_array($existing) ? trim((string) ($existing['id'] ?? '')) : '';
        $now = date('Y-m-d H:i:s');

        if ($permissionId === '') {
            $permissionId = $this->uuidV4();
            $this->db->table('permissions')->insert([
                'id' => $permissionId,
                'code' => $code,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            return $permissionId;
        }

        $this->db->table('permissions')
            ->where('id', $permissionId)
            ->update([
                'description' => $description,
                'deleted_at' => null,
                'updated_at' => $now,
            ]);

        return $permissionId;
    }

    private function ensureRolePermission(string $roleId, string $permissionId): void
    {
        if ($roleId === '' || $permissionId === '') {
            return;
        }

        $existing = $this->db->table('role_permissions')
            ->select('id')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->get()
            ->getRowArray();

        $now = date('Y-m-d H:i:s');

        if (is_array($existing) && isset($existing['id'])) {
            $this->db->table('role_permissions')
                ->where('id', (string) $existing['id'])
                ->update([
                    'deleted_at' => null,
                    'updated_at' => $now,
                ]);

            return;
        }

        $this->db->table('role_permissions')->insert([
            'id' => $this->uuidV4(),
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
