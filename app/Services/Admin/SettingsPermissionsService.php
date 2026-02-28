<?php

namespace App\Services\Admin;

use App\Models\PermissionModel;
use App\Models\UserModel;
use App\Models\UserPermissionModel;
use DomainException;

class SettingsPermissionsService
{
    public function __construct(
        private ?PermissionModel $permissionModel = null,
        private ?UserModel $userModel = null,
        private ?UserPermissionModel $userPermissionModel = null,
    ) {
        $this->permissionModel = $this->permissionModel ?? new PermissionModel();
        $this->userModel = $this->userModel ?? new UserModel();
        $this->userPermissionModel = $this->userPermissionModel ?? new UserPermissionModel();
    }

    public function listAssignablePermissions(): array
    {
        $rows = $this->permissionModel
            ->select('id, code, description')
            ->orderBy('code', 'ASC')
            ->findAll();

        $result = [];
        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            if ($code === '') {
                continue;
            }

            $module = $this->moduleFromCode($code);
            $result[] = [
                'id' => (string) ($row['id'] ?? ''),
                'code' => $code,
                'description' => (string) ($row['description'] ?? ''),
                'module' => $module,
            ];
        }

        return $result;
    }

    public function listSecretaries(): array
    {
        return $this->userModel
            ->select('id, username, email, role, status')
            ->where('role', 'secretary')
            ->orderBy('username', 'ASC')
            ->findAll();
    }

    public function getMatrix(string $userId): array
    {
        if (! $this->isValidUuid($userId)) {
            throw new DomainException('Geçersiz kullanıcı seçimi.');
        }

        $user = $this->userModel->find($userId);
        if (! is_array($user)) {
            throw new DomainException('Kullanıcı bulunamadı.');
        }

        $roleName = (string) ($user['role'] ?? '');
        $effective = $this->userPermissionModel->getEffectivePermissions($userId, $roleName);
        $effectiveSet = array_fill_keys($effective, true);

        $overrideRows = db_connect()->table('user_permissions up')
            ->select('p.code, up.allowed')
            ->join('permissions p', 'p.id = up.permission_id', 'inner')
            ->where('up.user_id', $userId)
            ->where('up.deleted_at', null)
            ->where('p.deleted_at', null)
            ->get()
            ->getResultArray();

        $overrideMap = [];
        foreach ($overrideRows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $overrideMap[$code] = (int) ($row['allowed'] ?? 0);
        }

        $permissions = $this->listAssignablePermissions();
        $matrix = [];
        foreach ($permissions as $perm) {
            $code = $perm['code'];
            $matrix[] = [
                'module' => $perm['module'],
                'code' => $code,
                'description' => $perm['description'],
                'effective' => isset($effectiveSet[$code]),
                'override' => array_key_exists($code, $overrideMap),
                'override_allowed' => array_key_exists($code, $overrideMap) ? ($overrideMap[$code] === 1) : null,
            ];
        }

        return $matrix;
    }

    public function setOverride(string $userId, string $permCode, bool $allowed): void
    {
        if (! $this->isValidUuid($userId)) {
            throw new DomainException('Geçersiz kullanıcı seçimi.');
        }

        $permCode = trim($permCode);
        if (! preg_match('/^[a-z0-9_]{3,100}$/', $permCode)) {
            throw new DomainException('Geçersiz izin kodu.');
        }

        $user = $this->userModel->find($userId);
        if (! is_array($user) || (($user['role'] ?? '') === 'admin')) {
            throw new DomainException('Bu kullanıcı için yetki güncellenemez.');
        }

        $this->userPermissionModel->setOverride($userId, $permCode, $allowed);
    }

    private function moduleFromCode(string $code): string
    {
        if (str_starts_with($code, 'manage_')) {
            return substr($code, 7);
        }

        $parts = explode('_', $code);
        return $parts[0] ?? 'genel';
    }

    private function isValidUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }
}
