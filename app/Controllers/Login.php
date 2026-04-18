<?php

namespace App\Controllers;

use App\Models\UserPermissionModel;
use App\Services\AuthService;

class Login extends BaseController
{
    public function index()
    {
        return view('auth/login');
    }

    public function auth()
    {
        $session = session();
        $now = time();

        $lastAttemptTime = $session->get('last_attempt_time') ?? 0;
        $waitTime = $session->get('current_wait_time') ?? 0;
        $remainingTime = ($lastAttemptTime + $waitTime) - $now;

        if ($remainingTime > 0) {
            return redirect()->back()->with('error', "Cok fazla hatali deneme! Lutfen {$remainingTime} saniye bekleyin.");
        }

        $email = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $authService = new AuthService();
        $user = $authService->attemptLogin($email, $password);

        if ($user) {
            $session->remove(['login_errors', 'last_attempt_time', 'current_wait_time']);

            $userId = (string) ($user['id'] ?? '');
            $role = strtolower(trim((string) ($user['role'] ?? '')));
            $permissions = [];

            if ($userId !== '' && $role !== '') {
                $permissions = (new UserPermissionModel())->getEffectivePermissions($userId, $role);
            }

            $session->set('isLoggedIn', true);
            $session->set('user', [
                'id' => $userId,
                'role' => $role,
                'email' => (string) ($user['email'] ?? ''),
                'name' => (string) ($user['username'] ?? ''),
            ]);
            $session->set('user_id', $userId);
            $session->set('role', $role);
            $session->set('permissions', $permissions);

            if ($role === 'admin') {
                return redirect()->to(base_url('admin/dashboard'));
            }

            if ($role === 'secretary') {
                return redirect()->to(base_url('admin/dashboard'));
            }

            return redirect()->to(base_url('dashboard_anasayfa'));
        }

        $errorCount = ($session->get('login_errors') ?? 0) + 1;
        $session->set('login_errors', $errorCount);
        $session->set('last_attempt_time', $now);

        $newWaitTime = 0;
        if ($errorCount === 3) {
            $newWaitTime = 30;
        } elseif ($errorCount >= 4) {
            $newWaitTime = 60;
        }
        $session->set('current_wait_time', $newWaitTime);

        $errorMessage = 'Hatali e-posta veya sifre!';
        if ($newWaitTime > 0) {
            $errorMessage = "Hatali giris! 3 deneme hakkiniz doldu, {$newWaitTime} saniye engellendiniz.";
        }

        return redirect()->back()->with('error', $errorMessage);
    }
}
