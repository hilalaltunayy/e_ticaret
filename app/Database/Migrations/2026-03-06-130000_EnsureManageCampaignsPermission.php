<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureManageCampaignsPermission extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('permissions') || ! $this->db->tableExists('roles') || ! $this->db->tableExists('role_permissions')) {
            return;
        }

        $perm = $this->db->table('permissions')
            ->select('id')
            ->where('code', 'manage_campaigns')
            ->get()
            ->getRowArray();

        $permissionId = is_array($perm) ? (string) ($perm['id'] ?? '') : '';
        if ($permissionId === '') {
            $permissionId = $this->uuidV4();
            $this->db->table('permissions')->insert([
                'id' => $permissionId,
                'code' => 'manage_campaigns',
                'description' => 'Manage campaigns and coupons',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
            ]);
        } else {
            $this->db->table('permissions')
                ->where('id', $permissionId)
                ->update([
                    'description' => 'Manage campaigns and coupons',
                    'deleted_at' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

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

        $exists = $this->db->table('role_permissions')
            ->select('id')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->get()
            ->getRowArray();

        if (is_array($exists) && isset($exists['id'])) {
            $this->db->table('role_permissions')
                ->where('id', (string) $exists['id'])
                ->update([
                    'deleted_at' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            return;
        }

        $this->db->table('role_permissions')->insert([
            'id' => $this->uuidV4(),
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ]);
    }

    public function down()
    {
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}

