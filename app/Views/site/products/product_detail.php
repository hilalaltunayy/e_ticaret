<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('product_media');

$productName = is_object($product ?? null) ? (string) ($product->product_name ?? '') : (string) ($product['product_name'] ?? '');
$productPrice = is_object($product ?? null) ? (float) ($product->price ?? 0) : (float) ($product['price'] ?? 0);
$productStock = is_object($product ?? null) ? (int) ($product->stock ?? 0) : (int) ($product['stock'] ?? 0);
$productType = is_object($product ?? null) ? (string) ($product->type ?? '') : (string) ($product['type'] ?? '');
$productAuthor = is_object($product ?? null) ? (string) ($product->author ?? '') : (string) ($product['author'] ?? '');
$productCategory = is_object($product ?? null) ? (string) ($product->category_name ?? '') : (string) ($product['category_name'] ?? '');
$productDescription = is_object($product ?? null) ? (string) ($product->description ?? '') : (string) ($product['description'] ?? '');
$productImageUrl = is_object($product ?? null)
    ? (string) ($product->image_url ?? product_image_url((string) ($product->image ?? '')))
    : product_image_url((string) ($product['image'] ?? ''));

$productTypeLabel = match ($productType) {
    'basili' => 'Basili Kitap',
    'dijital' => 'Dijital Urun',
    'paket' => 'Paket Urun',
    default => 'Urun',
};

$productStockLabel = $productType === 'dijital'
    ? 'Dijital teslimat'
    : ($productStock > 0 ? 'Stokta mevcut' : 'Stok bilgisi sinirli');

$productStockToneClass = $productType === 'dijital' || $productStock > 0
    ? 'book-detail-stock--available'
    : 'book-detail-stock--limited';

?>

<style>
    .book-detail-page {
        max-width: 1220px;
        margin: 0 auto;
        padding: 1.25rem 1.25rem 2.5rem;
    }
    .book-detail-breadcrumb {
        margin-bottom: 1rem;
    }
    .book-detail-breadcrumb .breadcrumb {
        margin: 0;
        gap: 0.15rem;
    }
    .book-detail-breadcrumb .breadcrumb-item,
    .book-detail-breadcrumb .breadcrumb-item a {
        color: #64748b;
        font-size: 0.92rem;
        text-decoration: none;
    }
    .book-detail-breadcrumb .breadcrumb-item.active {
        color: #0f172a;
        font-weight: 600;
    }
    .book-detail-hero {
        display: grid;
        grid-template-columns: minmax(280px, 420px) minmax(0, 1fr);
        gap: 1.75rem;
        align-items: start;
    }
    .book-detail-cover-card,
    .book-detail-info-card,
    .book-detail-content-card {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 251, 255, 0.98) 100%);
        border: 1px solid rgba(15, 23, 42, 0.07);
        border-radius: 26px;
        box-shadow: 0 20px 42px rgba(15, 23, 42, 0.06);
    }
    .book-detail-cover-card {
        position: relative;
        padding: 1.1rem;
    }
    .book-detail-favorite {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 44px;
        height: 44px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.25);
        background: rgba(255, 255, 255, 0.96);
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        cursor: pointer;
    }
    .book-detail-cover-frame {
        padding: 0.5rem;
        border-radius: 22px;
        background: linear-gradient(180deg, #eef5ff 0%, #ffffff 100%);
    }
    .book-detail-cover-image {
        width: 100%;
        aspect-ratio: 4 / 5.35;
        object-fit: cover;
        border-radius: 20px;
        display: block;
        background: #f8fafc;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
    }
    .book-detail-info-card {
        padding: 1.6rem;
    }
    .book-detail-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-bottom: 0.95rem;
    }
    .book-detail-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .book-detail-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.9rem, 2.6vw, 2.7rem);
        line-height: 1.12;
        letter-spacing: -0.03em;
        font-weight: 800;
    }
    .book-detail-author {
        margin-top: 0.7rem;
        color: #475569;
        font-size: 1rem;
    }
    .book-detail-author strong {
        color: #0f172a;
        font-weight: 700;
    }
    .book-detail-price-row {
        margin-top: 1.35rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1.1rem 1.2rem;
        border-radius: 22px;
        background: linear-gradient(135deg, rgba(239, 246, 255, 0.95) 0%, rgba(255, 255, 255, 0.95) 100%);
        border: 1px solid rgba(59, 130, 246, 0.10);
    }
    .book-detail-price-block {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .book-detail-price-label {
        color: #64748b;
        font-size: 0.84rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }
    .book-detail-price-value {
        color: #0f172a;
        font-size: clamp(1.7rem, 2vw, 2.1rem);
        font-weight: 800;
        line-height: 1;
    }
    .book-detail-stock {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.88rem;
    }
    .book-detail-stock--available {
        background: rgba(22, 163, 74, 0.10);
        color: #166534;
    }
    .book-detail-stock--limited {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
    }
    .book-detail-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin-top: 1.35rem;
    }
    .book-detail-meta-card {
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;
        padding: 0.95rem 1rem;
        background: rgba(255, 255, 255, 0.88);
    }
    .book-detail-meta-label {
        display: block;
        color: #64748b;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    .book-detail-meta-value {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.5;
    }
    .book-detail-actions {
        margin-top: 1.5rem;
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }
    .book-detail-primary-btn,
    .book-detail-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.95rem 1.2rem;
        border-radius: 16px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .book-detail-primary-btn {
        min-width: 220px;
        border: none;
        background: linear-gradient(135deg, #1d4ed8 0%, #38bdf8 100%);
        color: #fff;
        box-shadow: 0 14px 26px rgba(37, 99, 235, 0.18);
    }
    .book-detail-primary-btn:hover {
        color: #fff;
        transform: translateY(-1px);
    }
    .book-detail-secondary-btn {
        border: 1px solid rgba(148, 163, 184, 0.25);
        background: rgba(255, 255, 255, 0.92);
        color: #334155;
    }
    .book-detail-secondary-btn:hover {
        color: #0f172a;
        transform: translateY(-1px);
    }
    .book-detail-content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(280px, 0.95fr);
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    .book-detail-content-card {
        padding: 1.35rem 1.45rem;
    }
    .book-detail-section-title {
        margin: 0 0 1rem;
        color: #0f172a;
        font-size: 1.15rem;
        font-weight: 800;
    }
    .book-detail-description {
        color: #475569;
        line-height: 1.82;
        font-size: 0.98rem;
        white-space: pre-line;
    }
    .book-detail-notes {
        display: grid;
        gap: 1rem;
    }
    .book-detail-note {
        padding: 1rem 1.05rem;
        border-radius: 18px;
        background: rgba(248, 250, 252, 0.92);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }
    .book-detail-note-title {
        color: #0f172a;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    .book-detail-note-text {
        color: #64748b;
        line-height: 1.65;
        font-size: 0.92rem;
        margin: 0;
    }
    @media (max-width: 991.98px) {
        .book-detail-page {
            padding: 1rem 1rem 2rem;
        }
        .book-detail-hero,
        .book-detail-content-grid {
            grid-template-columns: 1fr;
        }
        .book-detail-cover-image {
            aspect-ratio: 4 / 4.8;
        }
    }
    @media (max-width: 767.98px) {
        .book-detail-page {
            padding: 0.9rem 0.85rem 1.75rem;
        }
        .book-detail-info-card,
        .book-detail-content-card {
            padding: 1.1rem;
        }
        .book-detail-meta-grid {
            grid-template-columns: 1fr;
        }
        .book-detail-price-row,
        .book-detail-actions {
            align-items: stretch;
        }
        .book-detail-primary-btn,
        .book-detail-secondary-btn {
            width: 100%;
        }
    }
</style>

<div class="book-detail-page">
    <nav class="book-detail-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Anasayfa</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('products/selection') ?>">Kitaplar</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= esc($productName) ?></li>
        </ol>
    </nav>

    <section class="book-detail-hero">
        <div class="book-detail-cover-card">
            <button type="button" class="book-detail-favorite" aria-label="Favorilere ekle">
                <i class="ti ti-heart"></i>
            </button>

            <div class="book-detail-cover-frame">
                <img src="<?= esc($productImageUrl) ?>" alt="<?= esc($productName) ?>" class="book-detail-cover-image">
            </div>

            <div class="book-detail-cover-meta">
        </div>

        <div class="book-detail-info-card">
            <div class="book-detail-badge-row">
                <span class="book-detail-badge">
                    <i class="ti ti-book-2"></i>
                    <?= esc($productTypeLabel) ?>
                </span>
                <?php if ($productCategory !== ''): ?>
                    <span class="book-detail-badge">
                        <i class="ti ti-category"></i>
                        <?= esc($productCategory) ?>
                    </span>
                <?php endif; ?>
            </div>

            <h1 class="book-detail-title"><?= esc($productName) ?></h1>
            <p class="book-detail-author">
                Yazar:
                <strong><?= esc($productAuthor !== '' ? $productAuthor : 'Belirtilmemis') ?></strong>
            </p>

            <div class="book-detail-price-row">
                <div class="book-detail-price-block">
                    <span class="book-detail-price-label">Satis Fiyati</span>
                    <span class="book-detail-price-value"><?= number_format($productPrice, 2, ',', '.') ?> TL</span>
                </div>
                <span class="book-detail-stock <?= esc($productStockToneClass) ?>">
                    <i class="ti ti-package"></i>
                    <?= esc($productStockLabel) ?>
                </span>
            </div>

            <div class="book-detail-meta-grid">
                <div class="book-detail-meta-card">
                    <span class="book-detail-meta-label">Tur</span>
                    <span class="book-detail-meta-value"><?= esc($productTypeLabel) ?></span>
                </div>
                <div class="book-detail-meta-card">
                    <span class="book-detail-meta-label">Kategori</span>
                    <span class="book-detail-meta-value"><?= esc($productCategory !== '' ? $productCategory : 'Kategori bilgisi yok') ?></span>
                </div>
            </div>

            <div class="book-detail-actions">
                <a href="#" class="book-detail-primary-btn" aria-label="Sepete ekle">
                    <i class="ti ti-shopping-cart-plus"></i>
                    <span>Sepete Ekle</span>
                </a>
                <a href="<?= base_url('products/selection') ?>" class="book-detail-secondary-btn">
                    <i class="ti ti-arrow-left"></i>
                    <span>Listeye Don</span>
                </a>
            </div>
        </div>
    </section>

    <section class="book-detail-content-grid">
        <div class="book-detail-content-card">
            <h2 class="book-detail-section-title">Aciklama</h2>
            <div class="book-detail-description">
                <?= esc(trim($productDescription) !== '' ? $productDescription : 'Bu urun icin henuz detayli bir aciklama eklenmemis.') ?>
            </div>
        </div>

        <aside class="book-detail-content-card">
            <h2 class="book-detail-section-title">Urun Bilgileri</h2>
            <div class="book-detail-notes">
                <div class="book-detail-note">
                    <div class="book-detail-note-title">Temel Bilgi</div>
                    <p class="book-detail-note-text">
                        Bu sayfa kitap urununun temel bilgilerini tek bakista gormeniz icin duzenlendi.
                    </p>
                </div>
                <div class="book-detail-note">
                    <div class="book-detail-note-title">Ek Notlar</div>
                    <p class="book-detail-note-text">
                        Stok, kategori ve urun tipi bilgileri mevcut kayittan alinmakta olup ekrandaki ozet kartlarda gosterilir.
                    </p>
                </div>
            </div>
        </aside>
    </section>
</div>
<?= $this->endSection() ?>
