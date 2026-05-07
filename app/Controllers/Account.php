<?php

namespace App\Controllers;

use App\Services\AccountService;
use App\Services\StorefrontHomeService;

class Account extends BaseController
{
    private AccountService $accountService;
    private StorefrontHomeService $storefrontHomeService;

    public function __construct()
    {
        $this->accountService = new AccountService();
        $this->storefrontHomeService = new StorefrontHomeService();
    }

    public function index()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Hesap sayfanızı görüntülemek için giriş yapmalısınız.');
        }

        return view('site/account/index', [
            'title' => 'Hesabım',
            'accountOverview' => $this->accountService->getAccountOverview($userId),
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ]);
    }

    public function updateProfile()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Profilinizi güncellemek için giriş yapmalısınız.');
        }

        $result = $this->accountService->updateProfile($userId, [
            'username' => $this->request->getPost('username'),
        ]);

        $redirect = redirect()->to(base_url('yardim/hesabim'));
        if (! empty($result['errors']) && is_array($result['errors'])) {
            return $redirect->withInput()->with('errors', $result['errors'])->with('error', $result['message']);
        }

        return $redirect->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function updatePassword()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Şifrenizi güncellemek için giriş yapmalısınız.');
        }

        $result = $this->accountService->updatePassword(
            $userId,
            (string) $this->request->getPost('current_password'),
            (string) $this->request->getPost('new_password'),
            (string) $this->request->getPost('new_password_confirmation')
        );

        return redirect()->to(base_url('yardim/hesabim#security'))
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function getCurrentUserId(): ?string
    {
        if (! session()->get('isLoggedIn')) {
            return null;
        }

        $fromUser = trim((string) (session()->get('user')['id'] ?? ''));
        if ($fromUser !== '') {
            return $fromUser;
        }

        $fromLegacy = trim((string) session()->get('user_id'));
        return $fromLegacy !== '' ? $fromLegacy : null;
    }
}
