<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureSecretaryOperationPermissions extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('permissions') || ! $this->db->tableExists('roles') || ! $this->db->tableExists('role_permissions')) {
            return;
        }

        $permissions = [
            'manage_dashboard' => 'Manage dashboard',
            'manage_stock' => 'Manage stock',
            'manage_notifications' => 'Manage notifications',
            'manage_customers' => 'Manage customers',
        ];

        $role = $this->db->table('roles')
            ->select('id')
            ->where('name', 'admin')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();
        $roleId = is_array($role) ? (string) ($role['id'] ?? '') : '';
        if ($roleId === '') {
            return;
        }

        foreach ($permissions as $code => $description) {
            $permissionId = $this->ensurePermission($code, $description);
            if ($permissionId === '') {
                continue;
            }

            $this->ensureAdminRolePermission($roleId, $permissionId);
        }
    }

    public function down()
    {
    }

    private function ensurePermission(string $code, string $description): string
    {
        $perm = $this->db->table('permissions')
            ->select('id')
            ->where('code', $code)
            ->get()
            ->getRowArray();

        $permissionId = is_array($perm) ? (string) ($perm['id'] ?? '') : '';
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

    private function ensureAdminRolePermission(string $roleId, string $permissionId): void
    {
        $exists = $this->db->table('role_permissions')
            ->select('id')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->get()
            ->getRowArray();

        $now = date('Y-m-d H:i:s');

        if (is_array($exists) && isset($exists['id'])) {
            $this->db->table('role_permissions')
                ->where('id', (string) $exists['id'])
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
