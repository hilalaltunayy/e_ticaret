<?php

namespace App\Database\Seeds;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class InitialAuthSeeder extends Seeder
{
    public function run()
    {
        $users = new UserModel();
        $roles = new RoleModel();
        $perms = new PermissionModel();
        $rp = new RolePermissionModel();

        $roleAdminId = $this->firstOrCreateRole($roles, 'admin', 'System administrator');
        $roleSecId = $this->firstOrCreateRole($roles, 'secretary', 'Secretary');
        $this->firstOrCreateRole($roles, 'user', 'Regular user');

        $permManageProductsId = $this->firstOrCreatePerm($perms, 'manage_products', 'Manage products');
        $permManageOrdersId = $this->firstOrCreatePerm($perms, 'manage_orders', 'Manage orders');
        $permManageShippingId = $this->firstOrCreatePerm($perms, 'manage_shipping', 'Manage shipping automation');

        $this->firstOrCreateRolePerm($rp, $roleAdminId, $permManageProductsId);
        $this->firstOrCreateRolePerm($rp, $roleAdminId, $permManageOrdersId);
        $this->firstOrCreateRolePerm($rp, $roleAdminId, $permManageShippingId);

        $this->firstOrCreateRolePerm($rp, $roleSecId, $permManageOrdersId);

        $existingAdmin = $users->where('email', 'admin@site.com')->first();
        if (! $existingAdmin) {
            $users->insert([
                'username' => 'admin',
                'email' => 'admin@site.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active',
            ]);
        }

        $existingSec = $users->where('email', 'secretary@site.com')->first();
        if (! $existingSec) {
            $users->insert([
                'username' => 'secretary',
                'email' => 'secretary@site.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'secretary',
                'status' => 'active',
            ]);
        }

        $existingUser = $users->where('email', 'user@site.com')->first();
        if (! $existingUser) {
            $users->insert([
                'username' => 'user',
                'email' => 'user@site.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'user',
                'status' => 'active',
            ]);
        }
    }

    private function firstOrCreateRole(RoleModel $roles, string $name, string $desc): string
    {
        $row = $roles->where('name', $name)->first();
        if ($row) {
            return (string) $row['id'];
        }

        $roles->insert(['name' => $name, 'description' => $desc]);

        return (string) $roles->getInsertID();
    }

    private function firstOrCreatePerm(PermissionModel $perms, string $code, string $desc): string
    {
        $row = $perms->where('code', $code)->first();
        if ($row) {
            return (string) $row['id'];
        }

        $perms->insert(['code' => $code, 'description' => $desc]);

        return (string) $perms->getInsertID();
    }

    private function firstOrCreateRolePerm(RolePermissionModel $rp, string $roleId, string $permId): void
    {
        $row = $rp->where('role_id', $roleId)->where('permission_id', $permId)->first();
        if ($row) {
            return;
        }

        $rp->insert([
            'role_id' => $roleId,
            'permission_id' => $permId,
        ]);
    }
}