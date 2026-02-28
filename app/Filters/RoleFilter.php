<?php

namespace App\Filters;

use App\Models\UserPermissionModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $user = $session->get('user');

        if (! $user) {
            return redirect()->to(base_url('login'));
        }

        $role = is_array($user) ? strtolower(trim((string) ($user['role'] ?? ''))) : '';
        $userId = is_array($user) ? trim((string) ($user['id'] ?? ($user['user_id'] ?? ''))) : '';

        if ($userId === '' || $role === '') {
            return redirect()->to(base_url('login'));
        }

        $allowedRoles = [];
        $requiredPerm = null;

        if (! empty($arguments)) {
            $flat = implode(',', $arguments);
            $parts = preg_split('/\|/', $flat) ?: [];

            foreach ($parts as $p) {
                $p = trim((string) $p);

                if (str_starts_with($p, 'role:')) {
                    $p = trim(substr($p, 5));
                }

                if (str_starts_with($p, 'perm:')) {
                    $requiredPerm = trim(substr($p, 5));
                    continue;
                }

                foreach (explode(',', $p) as $r) {
                    $r = strtolower(trim($r));
                    if ($r !== '') {
                        $allowedRoles[] = $r;
                    }
                }
            }
        }

        if ($allowedRoles !== [] && ! in_array($role, $allowedRoles, true)) {
            return $this->deny($request);
        }

        if ($requiredPerm !== null && $requiredPerm !== '') {
            if ($role === 'admin') {
                return null;
            }

            $upm = new UserPermissionModel();
            $allowed = $upm->isAllowed($userId, $requiredPerm, $role);
            if (! $allowed) {
                return $this->deny($request);
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
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