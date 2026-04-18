<?php

namespace App\Controllers;

use App\Services\StorefrontHomeService;

class StorefrontController extends BaseController
{
    private const PLACEHOLDER_PAGES = [
        'favorilerim' => [
            'title' => 'Favorilerim',
            'heading' => 'Favorilerim',
            'description' => 'Favori urunlerinizi tek bir yerde gormek icin bu alan yakinda kullanima acilacak.',
        ],
        'sepetim' => [
            'title' => 'Sepetim',
            'heading' => 'Sepetim',
            'description' => 'Sepet adimlari hazirlaniyor. Bu surecte urunleri incelemeye devam edebilirsiniz.',
        ],
        'siparislerim' => [
            'title' => 'Siparislerim',
            'heading' => 'Siparislerim',
            'description' => 'Siparis takip ekrani hazirlaniyor. Bu sayfa kisa sure icinde aktif olacak.',
        ],
        'hesabim' => [
            'title' => 'Hesabim',
            'heading' => 'Hesabim',
            'description' => 'Hesap yonetimi ekrani hazirlaniyor. Bu arada ana sayfadan urunleri inceleyebilirsiniz.',
        ],
    ];

    public function __construct(
        private ?StorefrontHomeService $storefrontHomeService = null
    ) {
        $this->storefrontHomeService = $this->storefrontHomeService ?? new StorefrontHomeService();
    }

    public function home()
    {
        $searchQuery = trim((string) $this->request->getGet('q'));
        $data = $this->storefrontHomeService->getHomePageData($searchQuery);

        return view('site/storefront/home', $data);
    }

    public function placeholder(string $slug)
    {
        $page = self::PLACEHOLDER_PAGES[$slug] ?? null;

        if ($page === null) {
            return redirect()->to(base_url('/'));
        }

        return view('site/storefront/fallback_page', [
            'title' => $page['title'],
            'pageTitle' => $page['heading'],
            'pageDescription' => $page['description'],
            'primaryActionUrl' => base_url('products/selection'),
            'primaryActionLabel' => 'Urunleri Incele',
            'secondaryActionUrl' => base_url('/'),
            'secondaryActionLabel' => 'Ana Sayfaya Don',
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ]);
    }
}
