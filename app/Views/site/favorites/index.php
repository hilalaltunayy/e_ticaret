<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php $favorites = is_array($favorites ?? null) ? $favorites : []; ?>
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <h1 class="h4 mb-0">Favorilerim</h1>
        <a href="<?= base_url('products/selection') ?>" class="btn btn-outline-primary btn-sm">
            Ürünleri Keşfet
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if ($favorites === []): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body py-5 text-center">
                <p class="text-muted mb-3">Henüz favori ürününüz yok.</p>
                <a href="<?= base_url('products/selection') ?>" class="btn btn-primary">Ürünleri Keşfet</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($favorites as $favorite): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?= esc((string) ($favorite['image_url'] ?? '')) ?>" class="card-img-top" alt="<?= esc((string) ($favorite['product_name'] ?? 'Ürün')) ?>" style="object-fit:cover;aspect-ratio:16/9;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="badge bg-light text-dark"><?= esc((string) ($favorite['type'] ?? 'ürün')) ?></span>
                                <small class="text-muted"><?= esc((string) ($favorite['stock_message'] ?? '')) ?></small>
                            </div>
                            <h2 class="h6 mb-1"><?= esc((string) ($favorite['product_name'] ?? '')) ?></h2>
                            <p class="text-muted small mb-2"><?= esc((string) ($favorite['author'] ?? '')) ?></p>

                            <div class="mb-3">
                                <?php if (!empty($favorite['is_price_dropped'])): ?>
                                    <div class="small text-danger text-decoration-line-through">
                                        <?= number_format((float) ($favorite['favorited_price'] ?? 0), 2, ',', '.') ?> TL
                                    </div>
                                    <div class="fw-semibold"><?= number_format((float) ($favorite['current_price'] ?? 0), 2, ',', '.') ?> TL</div>
                                    <div class="small text-success">Fiyat düştü</div>
                                <?php else: ?>
                                    <div class="fw-semibold"><?= number_format((float) ($favorite['current_price'] ?? 0), 2, ',', '.') ?> TL</div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto d-flex flex-wrap gap-2">
                                <a href="<?= esc((string) ($favorite['detail_url'] ?? '#')) ?>" class="btn btn-outline-primary btn-sm">Ürüne Git</a>

                                <?php if (!empty($favorite['can_add_to_cart'])): ?>
                                    <form action="<?= base_url('favorites/add-to-cart') ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= esc((string) ($favorite['product_id'] ?? '')) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Sepete Ekle</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark align-self-center">Stokta yok</span>
                                <?php endif; ?>

                                <form action="<?= base_url('favorites/remove') ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= esc((string) ($favorite['product_id'] ?? '')) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Favorilerden Kaldır</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
