<?php

namespace App\Controllers;

use App\Services\CheckoutService;
use App\Services\CheckoutStorefrontBindingService;
use App\Services\StorefrontHomeService;

class Checkout extends BaseController
{
    private CheckoutService $checkoutService;
    private CheckoutStorefrontBindingService $checkoutStorefrontBindingService;
    private StorefrontHomeService $storefrontHomeService;

    public function __construct()
    {
        $this->checkoutService = new CheckoutService();
        $this->checkoutStorefrontBindingService = new CheckoutStorefrontBindingService();
        $this->storefrontHomeService = new StorefrontHomeService();
    }

    public function index()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Odeme adimina gecmek icin giris yapmalisiniz.');
        }

        $checkoutView = $this->checkoutService->buildCheckoutViewModel(
            $userId,
            is_array(session()->get('user')) ? session()->get('user') : []
        );

        if ((int) ($checkoutView['cartView']['item_count'] ?? 0) <= 0) {
            return redirect()->to(base_url('yardim/sepetim'))->with('error', 'Odeme adimina gecmeden once sepetinizde urun bulunmalidir.');
        }

        return view('site/checkout/index', [
            'title' => 'Odeme Bilgileri',
            'checkoutView' => $checkoutView,
            'checkoutBuilderBinding' => $this->checkoutStorefrontBindingService->getPublishedBinding(),
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ]);
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
