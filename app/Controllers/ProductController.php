<?php

namespace App\Controllers;

use App\Services\ProductsService;
use App\Services\StorefrontHomeService;

class ProductController extends BaseController
{
    protected ProductsService $productsService;
    protected StorefrontHomeService $storefrontHomeService;

    public function __construct()
    {
        $this->productsService = new ProductsService();
        $this->storefrontHomeService = new StorefrontHomeService();
    }

    public function index()
    {
        $products = $this->productsService->getActiveProducts();

        return view('site/products/index', array_merge($this->storefrontViewData(), [
            'products' => $products,
            'categories' => [],
            'selectedCat' => 'all',
            'type' => '',
            'title' => 'Tum Urunler',
        ]));
    }

    public function detail($id)
    {
        $product = $this->productsService->getProductById($id);

        if ($product === null) {
            return view('site/storefront/fallback_page', array_merge($this->storefrontViewData(), [
                'title' => 'Urun bulunamadi',
                'pageTitle' => 'Urun bulunamadi',
                'pageDescription' => 'Aradiginiz urun su anda goruntulenemiyor. Dilerseniz urun listesine donerek incelemeye devam edebilirsiniz.',
                'primaryActionUrl' => base_url('products/selection'),
                'primaryActionLabel' => 'Urun Listesine Don',
                'secondaryActionUrl' => base_url('/'),
                'secondaryActionLabel' => 'Ana Sayfaya Don',
            ]));
        }

        return view('site/products/product_detail', array_merge($this->storefrontViewData(), [
            'product' => $product,
        ]));
    }

    public function selection()
    {
        return $this->index();
    }

    public function listByType($type)
    {
        $products = $this->productsService->getProductsByType((string) $type);
        $categories = $this->productsService->getCategoriesByType((string) $type);

        return view('site/products/index', array_merge($this->storefrontViewData(), [
            'products' => $products,
            'categories' => $categories,
            'selectedCat' => 'all',
            'type' => (string) $type,
            'title' => $this->resolveTypeTitle((string) $type),
        ]));
    }

    public function listByCategory($type, $categoryId = null)
    {
        $categories = $this->productsService->getCategoriesByType((string) $type);
        $products = $this->productsService->getFilteredProducts((string) $type, $categoryId);

        return view('site/products/index', array_merge($this->storefrontViewData(), [
            'type' => (string) $type,
            'categories' => $categories,
            'products' => $products,
            'selectedCat' => $categoryId ?? 'all',
            'title' => $this->resolveTypeTitle((string) $type),
        ]));
    }

    private function storefrontViewData(): array
    {
        return [
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ];
    }

    private function resolveTypeTitle(string $type): string
    {
        return match ($type) {
            'basili' => 'Basili Kitaplar',
            'dijital' => 'Dijital Kitaplar',
            'paket' => 'Ortak Paketler',
            default => 'Tum Urunler',
        };
    }
}
