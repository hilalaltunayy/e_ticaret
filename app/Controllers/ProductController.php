<?php

namespace App\Controllers;

use App\Services\ProductsService;
use App\Services\FavoriteService;
use App\Services\ProductDetailStorefrontBindingService;
use App\Services\ProductListStorefrontBindingService;
use App\Services\ReviewService;
use App\Services\StorefrontHomeService;
use App\Models\UserPermissionModel;

class ProductController extends BaseController
{
    protected ProductsService $productsService;
    protected FavoriteService $favoriteService;
    protected ProductDetailStorefrontBindingService $productDetailStorefrontBindingService;
    protected ProductListStorefrontBindingService $productListStorefrontBindingService;
    protected StorefrontHomeService $storefrontHomeService;
    protected ReviewService $reviewService;

    public function __construct()
    {
        $this->productsService = new ProductsService();
        $this->favoriteService = new FavoriteService();
        $this->productDetailStorefrontBindingService = new ProductDetailStorefrontBindingService();
        $this->productListStorefrontBindingService = new ProductListStorefrontBindingService();
        $this->storefrontHomeService = new StorefrontHomeService();
        $this->reviewService = new ReviewService();
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
            'productListBinding' => $this->resolveProductListBinding($products),
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

        $productId = (string) ($product->id ?? '');
        $approvedReviews = $this->reviewService->getApprovedReviewsForProduct($productId);
        $reviewSummary = $this->reviewService->getReviewSummaryForProduct($productId);
        $currentUserId = $this->resolveCurrentUserId();
        $isLoggedIn = $currentUserId !== '' && (bool) session()->get('isLoggedIn');
        $currentUserRole = $this->resolveCurrentUserRole();
        $permissionModel = new UserPermissionModel();
        $canUseReviewPermissions = $isLoggedIn
            && $permissionModel->isAllowed($currentUserId, 'create_review', $currentUserRole)
            && $permissionModel->isAllowed($currentUserId, 'rate_product', $currentUserRole);
        $hasSubmittedReview = $isLoggedIn
            ? $this->reviewService->hasUserActiveReviewForProduct($currentUserId, $productId)
            : false;
        $canSubmitReview = $canUseReviewPermissions && ! $hasSubmittedReview
            ? $this->reviewService->canUserReviewProduct($currentUserId, $productId)
            : false;

        return view('site/products/product_detail', array_merge($this->storefrontViewData(), [
            'product' => $product,
            'similarProducts' => $this->productsService->getSimilarProductsByProduct($product, 4),
            'isFavorited' => $this->resolveFavoriteState($productId),
            'productDetailBinding' => $this->productDetailStorefrontBindingService->getPublishedBinding(),
            'approvedReviews' => $approvedReviews,
            'reviewSummary' => $reviewSummary,
            'reviewUiState' => [
                'isLoggedIn' => $isLoggedIn,
                'canSubmitReview' => $canSubmitReview,
                'hasSubmittedReview' => $hasSubmittedReview,
            ],
        ]));
    }

    public function submitReview($id)
    {
        $product = $this->productsService->getProductById($id);
        if ($product === null) {
            return redirect()->to(base_url('products/selection'))->with('error', 'Urun bulunamadi.');
        }

        if (! session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Yorum gonderebilmek icin giris yapmalisiniz.');
        }

        $userId = $this->resolveCurrentUserId();
        if ($userId === '') {
            return redirect()->back()->withInput()->with('error', 'Kullanici oturumu bulunamadi.');
        }

        $role = $this->resolveCurrentUserRole();
        $permissionModel = new UserPermissionModel();
        $canCreateReview = $permissionModel->isAllowed($userId, 'create_review', $role);
        $canRateProduct = $permissionModel->isAllowed($userId, 'rate_product', $role);
        if (! $canCreateReview || ! $canRateProduct) {
            return redirect()->back()->withInput()->with('error', 'Yorum gonderme yetkiniz bulunmuyor.');
        }

        $result = $this->reviewService->createReview($userId, (string) $id, [
            'rating' => $this->request->getPost('rating'),
            'title' => $this->request->getPost('title'),
            'comment' => $this->request->getPost('comment'),
        ]);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()->withInput()->with('error', (string) ($result['message'] ?? 'Yorum gonderilemedi.'));
        }

        return redirect()->to(base_url('products/detail/' . rawurlencode((string) $id)))
            ->with('success', 'Yorumunuz moderasyon onayından sonra yayınlanacaktır.');
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
            'productListBinding' => $this->resolveProductListBinding($products),
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
            'productListBinding' => $this->resolveProductListBinding($products),
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

    private function resolveFavoriteState(string $productId): bool
    {
        if ($productId === '' || !session()->get('isLoggedIn')) {
            return false;
        }

        $user = session()->get('user');
        $userId = is_array($user) ? trim((string) ($user['id'] ?? '')) : '';
        if ($userId === '') {
            $userId = trim((string) session()->get('user_id'));
        }

        if ($userId === '') {
            return false;
        }

        return $this->favoriteService->isFavorite($userId, $productId);
    }

    private function resolveCurrentUserId(): string
    {
        $user = session()->get('user');
        $userId = is_array($user) ? trim((string) ($user['id'] ?? '')) : '';
        if ($userId === '') {
            $userId = trim((string) session()->get('user_id'));
        }

        return $userId;
    }

    private function resolveCurrentUserRole(): string
    {
        $user = session()->get('user');
        $role = is_array($user) ? strtolower(trim((string) ($user['role'] ?? ''))) : '';
        if ($role === '') {
            $role = strtolower(trim((string) session()->get('role')));
        }

        return $role !== '' ? $role : 'user';
    }

    private function resolveProductListBinding(array $products): array
    {
        return $this->productListStorefrontBindingService->getPublishedBinding([
            'productCount' => count($products),
        ]);
    }
}
