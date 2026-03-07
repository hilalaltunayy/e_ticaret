<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CampaignAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $user = $session->get('user');

        if (! is_array($user)) {
            return redirect()->to(base_url('login'));
        }

        $userId = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));
        $role = strtolower(trim((string) ($user['role'] ?? '')));
        if ($userId === '' || $role !== 'admin') {
            return $this->deny($request);
        }

        if (! $this->hasCampaignPermission($userId, $role)) {
            return $this->deny($request);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function hasCampaignPermission(string $userId, string $role): bool
    {
        foreach ($this->candidatePermissionCodes() as $permissionCode) {
            if ($this->hasPermissionCode($userId, $role, $permissionCode)) {
                return true;
            }
        }

        return false;
    }

    private function hasPermissionCode(string $userId, string $role, string $permissionCode): bool
    {
        $db = db_connect();

        $permRow = $db->table('permissions')
            ->select('id')
            ->where('code', $permissionCode)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (! is_array($permRow) || ! isset($permRow['id'])) {
            return false;
        }
        $permissionId = (string) $permRow['id'];

        $override = $db->table('user_permissions')
            ->select('allowed')
            ->where('user_id', $userId)
            ->where('permission_id', $permissionId)
            ->where('deleted_at', null)
            ->orderBy('updated_at', 'DESC')
            ->get()
            ->getRowArray();

        if (is_array($override)) {
            return (int) ($override['allowed'] ?? 0) === 1;
        }

        $roleRow = $db->table('roles')
            ->select('id')
            ->where('name', $role)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (! is_array($roleRow) || ! isset($roleRow['id'])) {
            return false;
        }

        $rolePerm = $db->table('role_permissions')
            ->select('id')
            ->where('role_id', (string) $roleRow['id'])
            ->where('permission_id', $permissionId)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        return is_array($rolePerm);
    }

    /**
     * Keep old code for coupon routes while allowing the new engine permission.
     *
     * @return string[]
     */
    private function candidatePermissionCodes(): array
    {
        return ['manage_campaigns_engine', 'manage_campaigns'];
    }

    private function deny(RequestInterface $request): ResponseInterface
    {
        $accept = strtolower($request->getHeaderLine('Accept'));
        $xrw = strtolower($request->getHeaderLine('X-Requested-With'));
        $wantsJson = str_contains($accept, 'application/json') || $xrw === 'xmlhttprequest';
        $response = service('response');

        if ($wantsJson) {
            return $response
                ->setStatusCode(403)
                ->setJSON(['ok' => false, 'message' => 'forbidden']);
        }

        return $response
            ->setStatusCode(403, 'Forbidden')
            ->setContentType('text/html', 'utf-8')
            ->setBody(view('errors/html/error_403'));
    }
}
