<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php $favorites = is_array($favorites ?? null) ? $favorites : []; ?>
<style>
    .favorites-page {
        max-width: 1240px;
        margin: 0 auto;
        padding: 1.25rem 1.1rem 2rem;
    }
    .favorites-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
    .favorites-title {
        margin: 0;
        color: #0f172a;
        font-weight: 800;
        font-size: clamp(1.35rem, 2vw, 1.85rem);
    }
    .favorites-subtitle {
        margin: 0.45rem 0 0;
        color: #64748b;
        font-size: 0.95rem;
        max-width: 720px;
        line-height: 1.6;
    }
    .favorites-discover-btn {
        white-space: nowrap;
        min-height: 42px;
        padding: 0.55rem 1rem;
        border-radius: 10px;
    }
    .favorites-alerts {
        margin-bottom: 1rem;
    }
    .favorites-empty {
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
    }
    .favorites-empty-title {
        margin: 0 0 0.6rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
    }
    .favorites-empty-text {
        margin: 0 0 1.1rem;
        color: #64748b;
        line-height: 1.65;
    }
    .favorites-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 360px));
        gap: 1.25rem;
        justify-content: start;
    }
    .favorite-card {
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 14px 26px rgba(15, 23, 42, 0.06);
        background: #fff;
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }
    .favorite-card-image {
        width: 100%;
        height: 240px;
        object-fit: cover;
        display: block;
        background: #f8fafc;
    }
    .favorite-card-body {
        padding: 1.15rem 1.15rem 1rem;
        display: flex;
        flex-direction: column;
        min-height: 0;
        height: 100%;
    }
    .favorite-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.55rem;
        margin-bottom: 0.65rem;
    }
    .favorite-type-badge {
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        border: 1px solid rgba(37, 99, 235, 0.2);
        font-weight: 700;
    }
    .favorite-stock-badge {
        font-weight: 600;
        border: 1px solid transparent;
    }
    .favorite-stock-badge.is-in {
        background: rgba(22, 163, 74, 0.1);
        color: #166534;
        border-color: rgba(22, 163, 74, 0.2);
    }
    .favorite-stock-badge.is-out {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
        border-color: rgba(245, 158, 11, 0.22);
    }
    .favorite-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.04rem;
        line-height: 1.45;
        font-weight: 800;
        min-height: 3rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .favorite-author {
        margin: 0.4rem 0 0.9rem;
        color: #64748b;
        font-size: 0.9rem;
    }
    .favorite-price-block {
        margin-bottom: 1rem;
    }
    .favorite-old-price {
        color: #dc2626;
        font-size: 0.88rem;
        text-decoration: line-through;
    }
    .favorite-current-price {
        color: #0f172a;
        font-weight: 800;
        font-size: 1.13rem;
        line-height: 1.2;
    }
    .favorite-price-drop {
        margin-top: 0.15rem;
        color: #16a34a;
        font-size: 0.83rem;
        font-weight: 700;
    }
    .favorite-actions {
        margin-top: auto;
    }
    .favorite-actions-primary {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .favorite-actions-primary form {
        margin: 0;
        flex: 1 1 0;
    }
    .favorite-actions-primary .btn,
    .favorite-actions-primary .favorite-detail-link {
        width: 100%;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-weight: 700;
    }
    .favorite-disabled-btn {
        border: 1px solid rgba(148, 163, 184, 0.3);
        background: #f8fafc;
        color: #64748b;
        cursor: not-allowed;
    }
    .favorite-actions-secondary {
        margin-top: 0.6rem;
    }
    .favorite-actions-secondary form {
        margin: 0;
    }
    .favorite-actions-secondary .btn {
        width: 100%;
        min-height: 38px;
        border-radius: 10px;
    }
    @media (max-width: 991.98px) {
        .favorites-page {
            padding-inline: 0.95rem;
        }
    }
    @media (max-width: 767.98px) {
        .favorites-header {
            flex-direction: column;
            align-items: stretch;
        }
        .favorites-discover-btn {
            width: 100%;
        }
    }
    @media (max-width: 575.98px) {
        .favorites-page {
            padding-inline: 0.8rem;
        }
        .favorites-grid {
            grid-template-columns: 1fr;
        }
        .favorite-card-image {
            height: 220px;
        }
        .favorite-actions-primary form,
        .favorite-actions-primary a {
            flex: 1 1 100%;
        }
    }
</style>

<section class="favorites-page">
    <header class="favorites-header">
        <div>
            <h1 class="favorites-title">Favorilerim</h1>
            <p class="favorites-subtitle">Begendiginiz kitaplari burada saklayabilir, fiyat ve stok durumlarini takip edebilirsiniz.</p>
        </div>
        <a href="<?= base_url('products/selection') ?>" class="btn btn-outline-primary favorites-discover-btn">Urunleri Kesfet</a>
    </header>

    <div class="favorites-alerts">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success mb-2"><?= esc((string) session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger mb-0"><?= esc((string) session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
    </div>

    <?php if ($favorites === []): ?>
        <div class="favorites-empty">
            <div class="card-body py-5 text-center">
                <h2 class="favorites-empty-title">Henuz favori urununuz yok.</h2>
                <p class="favorites-empty-text">Begendiginiz urunleri favorilere ekleyerek daha sonra kolayca ulasabilirsiniz.</p>
                <a href="<?= base_url('products/selection') ?>" class="btn btn-primary px-4">Urunleri Kesfet</a>
            </div>
        </div>
    <?php else: ?>
        <div class="favorites-grid">
            <?php foreach ($favorites as $favorite): ?>
                <?php
                $stockText = (string) ($favorite['stock_message'] ?? '');
                $isInStock = stripos($stockText, 'yok') === false;
                ?>
                <article class="favorite-card">
                    <img src="<?= esc((string) ($favorite['image_url'] ?? '')) ?>" class="favorite-card-image" alt="<?= esc((string) ($favorite['product_name'] ?? 'Urun')) ?>">
                    <div class="favorite-card-body">
                        <div class="favorite-card-meta">
                            <span class="badge favorite-type-badge"><?= esc((string) ($favorite['type'] ?? 'urun')) ?></span>
                            <span class="badge favorite-stock-badge <?= $isInStock ? 'is-in' : 'is-out' ?>">
                                <?= esc($stockText !== '' ? $stockText : 'Stok bilgisi') ?>
                            </span>
                        </div>

                        <h2 class="favorite-title"><?= esc((string) ($favorite['product_name'] ?? '')) ?></h2>
                        <?php if (trim((string) ($favorite['author'] ?? '')) !== ''): ?>
                            <p class="favorite-author">Yazar: <?= esc((string) $favorite['author']) ?></p>
                        <?php endif; ?>

                        <div class="favorite-price-block">
                            <?php if (!empty($favorite['is_price_dropped'])): ?>
                                <div class="favorite-old-price"><?= number_format((float) ($favorite['favorited_price'] ?? 0), 2, ',', '.') ?> TL</div>
                                <div class="favorite-current-price"><?= number_format((float) ($favorite['current_price'] ?? 0), 2, ',', '.') ?> TL</div>
                                <div class="favorite-price-drop">Fiyat dustu</div>
                            <?php else: ?>
                                <div class="favorite-current-price"><?= number_format((float) ($favorite['current_price'] ?? 0), 2, ',', '.') ?> TL</div>
                            <?php endif; ?>
                        </div>

                        <div class="favorite-actions">
                            <div class="favorite-actions-primary">
                                <a href="<?= esc((string) ($favorite['detail_url'] ?? '#')) ?>" class="btn btn-outline-primary favorite-detail-link">Urune Git</a>

                                <?php if (!empty($favorite['can_add_to_cart'])): ?>
                                    <form action="<?= base_url('favorites/add-to-cart') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= esc((string) ($favorite['product_id'] ?? '')) ?>">
                                        <button type="submit" class="btn btn-primary">Sepete Ekle</button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn favorite-disabled-btn" disabled>Sepete eklenemez</button>
                                <?php endif; ?>
                            </div>

                            <div class="favorite-actions-secondary">
                                <form action="<?= base_url('favorites/remove') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= esc((string) ($favorite['product_id'] ?? '')) ?>">
                                    <button type="submit" class="btn btn-outline-danger">Favorilerden Kaldir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
