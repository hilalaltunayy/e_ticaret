<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\SettingsPermissionsService;
use DomainException;
use Throwable;

class SettingsPermissionsController extends BaseController
{
    public function __construct(private ?SettingsPermissionsService $service = null)
    {
        $this->service = $this->service ?? new SettingsPermissionsService();
    }

    public function index()
    {
        $secretaries = $this->service->listSecretaries();
        $selectedUserId = trim((string) ($this->request->getGet('user_id') ?? ''));
        if ($selectedUserId === '' && $secretaries !== []) {
            $selectedUserId = (string) ($secretaries[0]['id'] ?? '');
        }

        $matrix = [];
        $error = null;
        if ($selectedUserId !== '') {
            try {
                $matrix = $this->service->getMatrix($selectedUserId);
            } catch (DomainException $e) {
                $error = $e->getMessage();
            }
        }

        return view('admin/settings/permissions', [
            'title' => 'Yetkilendirme',
            'secretaries' => $secretaries,
            'selectedUserId' => $selectedUserId,
            'matrix' => $matrix,
            'error' => $error,
        ]);
    }

    public function update()
    {
        $userId = trim((string) $this->request->getPost('user_id'));
        $permCode = trim((string) $this->request->getPost('perm_code'));
        $allowedRaw = $this->request->getPost('allowed');
        $allowed = in_array((string) $allowedRaw, ['1', 'true', 'on'], true);

        try {
            $this->service->setOverride($userId, $permCode, $allowed);

            $currentUser = session()->get('user');
            $currentUserId = is_array($currentUser) ? (string) ($currentUser['id'] ?? ($currentUser['user_id'] ?? '')) : '';
            if ($currentUserId !== '' && $currentUserId === $userId) {
                session()->remove('permissions');
            }

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Yetki güncellendi.',
            ]);
        } catch (DomainException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Yetki güncellenemedi.',
            ]);
        }
    }
}