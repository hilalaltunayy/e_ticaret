<?php

namespace App\Controllers;

use App\Services\StorefrontHomeService;

class StorefrontController extends BaseController
{
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
}
