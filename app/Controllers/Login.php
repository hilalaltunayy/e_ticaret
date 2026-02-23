<?php namespace App\Controllers;

class Login extends BaseController
{
    public function index()
    {
        // Login sayfasını gösterir
        return view('auth/login');
    }

    public function auth()
{
    $session = session();
    $now = time();

    // 1) Bekleme cezası kontrol
    $lastAttemptTime = $session->get('last_attempt_time') ?? 0;
    $waitTime        = $session->get('current_wait_time') ?? 0;
    $remainingTime   = ($lastAttemptTime + $waitTime) - $now;

    if ($remainingTime > 0) {
        return redirect()->back()->with('error', "Çok fazla hatalı deneme! Lütfen {$remainingTime} saniye bekleyin.");
    }

    // 2) Form verileri
    $email    = (string) $this->request->getPost('email');
    $password = (string) $this->request->getPost('password');

    $authService = new \App\Services\AuthService();
    $user = $authService->attemptLogin($email, $password);

    if ($user) {
        // Başarılı: sayaçları temizle
        $session->remove(['login_errors', 'last_attempt_time', 'current_wait_time']);

        $session->set('isLoggedIn', true);

        $session->set('user', [
            'id'    => (string) ($user['id'] ?? ''),
            'role'  => (string) ($user['role'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'name'  => (string) ($user['username'] ?? ''),
        ]);

        // RoleFilter'ın beklediği alanlar
        $session->set('user_id', (string) ($user['id'] ?? ''));
        $session->set('role', (string) ($user['role'] ?? ''));

        // ✅ DİKKAT: burası URL değil, route olmalı.
        // Site anasayfan neyse oraya yönlendir:
        return redirect()->to(base_url('dashboard_anasayfa'));
    }

    // 3) Hatalı giriş: cezalandırma
    $errorCount = ($session->get('login_errors') ?? 0) + 1;
    $session->set('login_errors', $errorCount);
    $session->set('last_attempt_time', $now);

    $newWaitTime = 0;
    if ($errorCount == 3) {
        $newWaitTime = 30;
    } elseif ($errorCount >= 4) {
        $newWaitTime = 60;
    }
    $session->set('current_wait_time', $newWaitTime);

    $errorMessage = 'Hatalı e-posta veya şifre!';
    if ($newWaitTime > 0) {
        $errorMessage = "Hatalı giriş! 3 deneme hakkınız doldu, {$newWaitTime} saniye engellendiniz.";
    }

    // ✅ En alttaki bozuk satırın doğru hali:
    return redirect()->back()->with('error', $errorMessage);
}

}