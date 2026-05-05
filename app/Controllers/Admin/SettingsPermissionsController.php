<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AuditLogger;
use App\Services\Admin\SettingsPermissionsService;
use Config\Services;
use DomainException;
use Throwable;

class SettingsPermissionsController extends BaseController
{
    public function __construct(
        private ?SettingsPermissionsService $service = null,
        private ?AuditLogger $auditLogger = null
    ) {
        $this->service = $this->service ?? new SettingsPermissionsService();
        $this->auditLogger = $this->auditLogger ?? new AuditLogger();
    }

    public function index()
    {
        $secretaries = $this->service->listSecretaries();
        $selectedUserId = trim((string) ($this->request->getGet('user_id') ?? ''));
        if ($selectedUserId === '') {
            $selectedUserId = trim((string) (session()->getFlashdata('selectedSecretaryId') ?? ''));
        }
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
            'validation' => session('validation'),
            'openSecretaryModal' => (bool) session('openSecretaryModal'),
        ]);
    }

    public function createSecretary()
    {
        $returnUserId = trim((string) $this->request->getPost('return_user_id'));
        $redirectUrl = site_url('admin/settings/permissions');
        if ($returnUserId !== '') {
            $redirectUrl .= '?user_id=' . rawurlencode($returnUserId);
        }

        $post = [
            'username' => trim((string) $this->request->getPost('username')),
            'email' => trim((string) $this->request->getPost('email')),
            'password' => (string) $this->request->getPost('password'),
            'password_confirm' => (string) $this->request->getPost('password_confirm'),
            'status' => trim((string) $this->request->getPost('status')),
        ];

        $validator = Services::validation();
        $validator->setRules([
            'username' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|max_length[100]|is_unique[users.email]',
            'password' => 'required|min_length[6]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
            'status' => 'required|in_list[active,suspended]',
        ]);

        if (! $validator->run($post)) {
            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('validation', $validator)
                ->with('error', 'Lutfen form alanlarini kontrol edin.')
                ->with('openSecretaryModal', true);
        }

        try {
            $secretaryId = $this->service->createSecretary($post);

            return redirect()
                ->to(site_url('admin/settings/permissions') . '?user_id=' . rawurlencode($secretaryId))
                ->with('success', 'Yeni sekreter basariyla olusturuldu.')
                ->with('selectedSecretaryId', $secretaryId);
        } catch (DomainException $e) {
            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('error', $e->getMessage())
                ->with('openSecretaryModal', true);
        } catch (Throwable) {
            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('error', 'Sekreter olusturulamadi.')
                ->with('openSecretaryModal', true);
        }
    }

    public function update()
    {
        $userId = trim((string) $this->request->getPost('user_id'));
        $permCode = trim((string) $this->request->getPost('perm_code'));
        $allowedRaw = $this->request->getPost('allowed');
        $allowed = in_array((string) $allowedRaw, ['1', 'true', 'on'], true);

        try {
            $beforePermission = $this->permissionState($userId, $permCode);
            $this->service->setOverride($userId, $permCode, $allowed);
            $afterPermission = $this->permissionState($userId, $permCode);

            $currentUser = session()->get('user');
            $currentUserId = is_array($currentUser) ? (string) ($currentUser['id'] ?? ($currentUser['user_id'] ?? '')) : '';
            if ($currentUserId !== '' && $currentUserId === $userId) {
                session()->remove('permissions');
            }

            $this->auditLogger->log('permission.update', 'permission', $userId, [
                'target_user_id' => $userId,
                'target_role' => $this->targetRole($userId),
                'permission_code' => $permCode,
                'before' => $beforePermission,
                'after' => $afterPermission,
            ]);

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

    private function permissionState(string $userId, string $permCode): array
    {
        $state = [
            'effective' => null,
            'override' => null,
            'override_allowed' => null,
        ];

        try {
            foreach ($this->service->getMatrix($userId) as $row) {
                if ((string) ($row['code'] ?? '') !== $permCode) {
                    continue;
                }

                return [
                    'effective' => (bool) ($row['effective'] ?? false),
                    'override' => (bool) ($row['override'] ?? false),
                    'override_allowed' => array_key_exists('override_allowed', $row) && $row['override_allowed'] !== null
                        ? (bool) $row['override_allowed']
                        : null,
                ];
            }
        } catch (DomainException) {
        }

        return $state;
    }

    private function targetRole(string $userId): ?string
    {
        foreach ($this->service->listSecretaries() as $secretary) {
            if ((string) ($secretary['id'] ?? '') === $userId) {
                return (string) ($secretary['role'] ?? 'secretary');
            }
        }

        return null;
    }
}
