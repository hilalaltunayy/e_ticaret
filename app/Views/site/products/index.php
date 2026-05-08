<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$title = $title ?? 'Kitap Koleksiyonu';
$currentType = $type ?? '';
$currentCat = $selectedCat ?? null;
$bookList = $products ?? [];
$categories = $categories ?? [];
$currentTypeLabel = $currentType !== '' ? ucfirst((string) $currentType) : 'Tum urunler';
$allProductsUrl = $currentType !== '' ? base_url("products/list/$currentType/all") : base_url('products/selection');
$productListBinding = is_array($productListBinding ?? null) ? $productListBinding : [];
$productListPresenter = is_array($productListBinding['presenter'] ?? null) ? $productListBinding['presenter'] : [];
$builderHero = is_array($productListPresenter['hero'] ?? null) ? $productListPresenter['hero'] : [];
$builderBanner = is_array($productListPresenter['banner'] ?? null) ? $productListPresenter['banner'] : [];
$builderEmptyState = is_array($productListPresenter['emptyState'] ?? null) ? $productListPresenter['emptyState'] : [];
$builderFooterDescription = is_array($productListPresenter['footerDescription'] ?? null) ? $productListPresenter['footerDescription'] : [];
$hasPublishedProductListBinding = ! empty($productListBinding['hasPublishedConfig']);
$showHero = ! $hasPublishedProductListBinding || ! array_key_exists('enabled', $builderHero) || ! empty($builderHero['enabled']);
$showBreadcrumb = ! $hasPublishedProductListBinding || ! array_key_exists('showBreadcrumb', $builderHero) || ! empty($builderHero['showBreadcrumb']);
$heroSubtitle = trim((string) ($builderHero['subtitle'] ?? ''));
$showBuilderBanner = ! empty($builderBanner['enabled']);
$bannerTone = trim((string) ($builderBanner['tone'] ?? 'soft'));
$showFooterDescription = ! empty($builderFooterDescription['enabled']);
?>
<style>
    .pc-container {
        background: linear-gradient(180deg, #f8fbff 0%, #f3f7fc 100%) !important;
    }
    .storefront-main .pc-container {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0;
    }
    .storefront-main .pc-content {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        padding: 1.1rem 1.25rem 0;
    }
    .storefront-main .pc-content.products-page {
        padding-top: 0.35rem;
    }
    .products-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        width: 100%;
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 0 2.25rem;
    }
    .products-hero {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(248, 251, 255, 0.96) 100%);
        border: 1px solid rgba(15, 23, 42, 0.07);
        border-radius: 24px;
        padding: 1rem 1.35rem;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
    }
    .products-hero-meta {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .products-hero-title {
        margin: 0.55rem 0 0;
        color: #2c3e50;
        font-size: clamp(1.45rem, 1.8vw, 1.9rem);
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .products-hero-subtitle {
        margin: 0.55rem 0 0;
        max-width: 720px;
        color: #64748b;
        font-size: 0.98rem;
        line-height: 1.7;
    }
    .products-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 0.65rem;
        flex-wrap: wrap;
    }
    .category-btn {
        background-color: rgba(255, 255, 255, 0.92);
        color: #334155;
        border-radius: 999px;
        transition: all 0.25s ease;
        border: 1px solid rgba(148, 163, 184, 0.25);
        font-weight: 700;
        min-width: 120px;
        padding: 0.8rem 1.1rem;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.04);
    }
    .category-btn:hover, .active-bordo {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #1d4ed8 0%, #38bdf8 100%) !important;
        border-color: transparent;
        color: white !important;
    }
    .products-grid {
        margin-top: 0.1rem;
        align-items: stretch;
    }
    .products-builder-banner,
    .products-builder-footer {
        border-radius: 22px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
        padding: 1rem 1.15rem;
    }
    .products-builder-banner h2,
    .products-builder-footer h2 {
        margin: 0;
        color: #1f2937;
        font-size: 1.05rem;
        font-weight: 800;
    }
    .products-builder-banner p,
    .products-builder-footer p {
        margin: 0.45rem 0 0;
        color: #64748b;
        line-height: 1.7;
    }
    .products-builder-banner--soft {
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.95) 0%, rgba(255, 255, 255, 0.96) 100%);
    }
    .products-builder-banner--light {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.96) 100%);
    }
    .products-builder-banner--dark {
        background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
        border-color: rgba(15, 23, 42, 0.15);
    }
    .products-builder-banner--dark h2,
    .products-builder-banner--dark p {
        color: #f8fafc;
    }
    .products-builder-banner--accent {
        background: linear-gradient(135deg, rgba(29, 78, 216, 0.95) 0%, rgba(56, 189, 248, 0.92) 100%);
        border-color: transparent;
    }
    .products-builder-banner--accent h2,
    .products-builder-banner--accent p {
        color: #eff6ff;
    }
    .book-card {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 22px;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        background: #fff;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
    }
    .book-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 36px rgba(15, 23, 42, 0.10) !important;
        border-color: rgba(59, 130, 246, 0.20);
    }
    .book-card-media {
        position: relative;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        padding: 1rem 1rem 0.75rem;
    }
    .book-card-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.75rem;
        border-radius: 999px;
        background: rgba(239, 246, 255, 0.98);
        color: #1e3a8a;
        border: 1px solid rgba(59, 130, 246, 0.12);
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .book-card-image {
        width: 100%;
        aspect-ratio: 4 / 3;
        object-fit: cover;
        border-radius: 18px;
        display: block;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        background: #f8fafc;
    }
    .book-card-body {
        padding: 1.15rem;
    }
    .book-card-title {
        color: #1f2937;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.45;
        margin-bottom: 0.45rem;
        min-height: 3rem;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }
    .book-card-author {
        color: #64748b;
        font-size: 0.92rem;
        margin-bottom: 1rem;
        min-height: 1.4rem;
    }
    .book-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid rgba(226, 232, 240, 0.9);
    }
    .price-wrap {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    .price-label {
        color: #9ca3af;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
    }
    .price-tag {
        color: #0f172a;
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .product-detail-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.85rem 1rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #1d4ed8 0%, #38bdf8 100%);
        color: #fff;
        font-size: 0.92rem;
        font-weight: 700;
        text-decoration: none;
        border: none;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.18);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        white-space: nowrap;
    }
    .product-card-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .product-card-actions form {
        margin: 0;
    }
    .product-add-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.78rem 0.92rem;
        border-radius: 14px;
        border: 1px solid rgba(29, 78, 216, 0.14);
        background: rgba(239, 246, 255, 0.92);
        color: #1d4ed8;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        min-height: 46px;
    }
    .product-add-btn:hover {
        color: #0f52ba;
        transform: translateY(-1px);
    }
    .product-detail-btn:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(37, 99, 235, 0.22);
    }
    .products-empty {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 24px;
        padding: 3rem 1.5rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }
    @media (max-width: 991.98px) {
        .storefront-main .pc-content {
            padding: 0.9rem 1rem 0;
        }
        .storefront-main .pc-content.products-page {
            padding-top: 0.3rem;
        }
        .products-hero {
            padding: 0.95rem 1rem;
            border-radius: 20px;
        }
        .book-card-image {
            aspect-ratio: 5 / 4;
        }
    }
    @media (max-width: 767.98px) {
        .storefront-main .pc-content {
            padding: 0.75rem 0.85rem 0;
        }
        .storefront-main .pc-content.products-page {
            padding-top: 0.2rem;
        }
        .products-shell {
            gap: 1.1rem;
            padding-bottom: 1.5rem;
        }
        .products-toolbar {
            align-items: center;
        }
        .book-card-footer {
            flex-direction: column;
            align-items: stretch;
        }
        .product-detail-btn {
            width: 100%;
        }
    }
</style>

<div class="pc-container">
    <div class="pc-content products-page">
        <div class="products-shell">
            <?php if ($showHero): ?>
                <section class="products-hero">
                    <div class="products-hero-meta">
                        <i class="ti ti-book-2"></i>
                        <span><?= esc($currentTypeLabel) ?> koleksiyonu</span>
                    </div>
                    <div class="products-toolbar">
                        <div>
                            <?php if ($showBreadcrumb): ?>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-2">
                                        <li class="breadcrumb-item"><a href="<?= base_url('products/selection') ?>" style="color: #E67E22; font-weight: 700;">Urun Secimi</a></li>
                                        <li class="breadcrumb-item active" style="color: #7c5c46;"><?= esc($currentTypeLabel) ?></li>
                                    </ol>
                                </nav>
                            <?php endif; ?>
                            <h1 class="products-hero-title"><?= esc($title) ?></h1>
                            <?php if ($heroSubtitle !== ''): ?>
                                <p class="products-hero-subtitle"><?= esc($heroSubtitle) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success mb-0"><?= esc((string) session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger mb-0"><?= esc((string) session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <?php if ($showBuilderBanner): ?>
                <section class="products-builder-banner products-builder-banner--<?= esc($bannerTone) ?>">
                    <?php if (trim((string) ($builderBanner['title'] ?? '')) !== ''): ?>
                        <h2><?= esc((string) $builderBanner['title']) ?></h2>
                    <?php endif; ?>
                    <?php if (trim((string) ($builderBanner['subtitle'] ?? '')) !== ''): ?>
                        <p><?= esc((string) $builderBanner['subtitle']) ?></p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <div class="row g-4 products-grid">
            <?php if ($currentCat === null): ?>
                <div class="col-12">
                    <div class="products-empty text-center">
                        <h4 class="text-muted mb-0">Lutfen once listelemek istediginiz kategoriyi yukaridan secin.</h4>
                    </div>
                </div>
            <?php elseif (empty($bookList)): ?>
                <div class="col-12">
                    <div class="products-empty text-center">
                        <?php if (trim((string) ($builderEmptyState['title'] ?? '')) !== ''): ?>
                            <h4 class="text-muted mb-2"><?= esc((string) $builderEmptyState['title']) ?></h4>
                        <?php endif; ?>
                        <p class="text-muted mb-0">
                            <?= esc((string) ($builderEmptyState['description'] ?? 'Bu kategoride henuz bir urun bulunamadi.')) ?>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($bookList as $product): ?>
                    <div class="col-sm-6 col-xl-3">
                        <article class="card book-card h-100">
                            <div class="book-card-media">
                                <span class="book-card-badge">
                                    <i class="ti ti-stack-2"></i>
                                    <?= esc((string) ($product->type ?? 'urun')) ?>
                                </span>
                                <img
                                    src="<?= esc((string) ($product->image_url ?? '')) ?>"
                                    class="book-card-image"
                                    alt="<?= esc($product->product_name) ?>"
                                >
                            </div>
                            <div class="book-card-body d-flex flex-column">
                                <h2 class="book-card-title"><?= esc($product->product_name) ?></h2>
                                <p class="book-card-author"><?= esc($product->author ?? 'Yazar Bilinmiyor') ?></p>
                                 <div class="book-card-footer">
                                     <div class="price-wrap">
                                         <span class="price-label">Fiyat</span>
                                         <span class="price-tag"><?= number_format((float) $product->price, 2) ?> TL</span>
                                     </div>
                                     <div class="product-card-actions">
                                         <form action="<?= base_url('favorites/toggle') ?>" method="post" class="d-inline">
                                             <?= csrf_field() ?>
                                             <input type="hidden" name="product_id" value="<?= esc((string) $product->id) ?>">
                                             <button type="submit" class="product-detail-btn" title="Favori">
                                                 <i class="ti ti-heart"></i>
                                             </button>
                                         </form>
                                         <form action="<?= base_url('cart/add') ?>" method="post" class="d-inline">
                                             <?= csrf_field() ?>
                                             <input type="hidden" name="product_id" value="<?= esc((string) $product->id) ?>">
                                             <button type="submit" class="product-add-btn" title="Sepete Ekle">
                                                 <i class="ti ti-shopping-cart-plus"></i>
                                             </button>
                                         </form>
                                         <a href="<?= esc((string) ($product->detail_url ?? base_url('products/detail/' . $product->id))) ?>" class="product-detail-btn">
                                             <span>Detayi Gor</span>
                                             <i class="ti ti-arrow-up-right"></i>
                                         </a>
                                     </div>
                                 </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>

            <?php if ($showFooterDescription): ?>
                <section class="products-builder-footer">
                    <?php if (trim((string) ($builderFooterDescription['title'] ?? '')) !== ''): ?>
                        <h2><?= esc((string) $builderFooterDescription['title']) ?></h2>
                    <?php endif; ?>
                    <?php if (trim((string) ($builderFooterDescription['text'] ?? '')) !== ''): ?>
                        <p><?= esc((string) $builderFooterDescription['text']) ?></p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
