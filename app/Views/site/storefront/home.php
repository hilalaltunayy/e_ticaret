<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<div class="storefront-container">
    <?php if (! empty($blocks)): ?>
        <?php foreach ($blocks as $block): ?>
            <?php
            $config = is_array($block['config'] ?? null) ? $block['config'] : [];
            $title = trim((string) ($config['title'] ?? $block['name'] ?? ''));
            $subtitle = trim((string) ($config['subtitle'] ?? $block['summary'] ?? ''));
            $buttonText = trim((string) ($config['button_text'] ?? ''));
            $buttonLink = trim((string) ($config['button_link'] ?? '/'));
            $blockImageUrl = trim((string) ($config['image_url'] ?? ''));
            ?>
            <section class="storefront-section">
                <?php if (($block['template'] ?? '') === 'banner'): ?>
                    <div class="storefront-hero-card">
                        <div class="storefront-section-inner">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="storefront-eyebrow">
                                        <i class="ti ti-layout-grid-add"></i>
                                        <?= esc((string) ($block['name'] ?? 'Banner')) ?>
                                    </span>
                                    <h1 class="storefront-section-title mt-3 mb-3"><?= esc($title !== '' ? $title : 'Kitap Dunyasi vitrini') ?></h1>
                                    <?php if ($subtitle !== ''): ?>
                                        <p class="storefront-section-subtitle"><?= esc($subtitle) ?></p>
                                    <?php endif; ?>
                                    <div class="storefront-hero-actions">
                                        <a href="<?= esc($buttonLink !== '' ? $buttonLink : '/') ?>" class="storefront-primary-btn">
                                            <i class="ti ti-arrow-right"></i>
                                            <?= esc($buttonText !== '' ? $buttonText : 'Kesfet') ?>
                                        </a>
                                        <a href="<?= base_url('login') ?>" class="storefront-secondary-btn">Giris Yap</a>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="storefront-info-card">
                                        <div class="storefront-section-inner">
                                            <div class="storefront-hero-meta">
                                                <?php if ($blockImageUrl !== ''): ?>
                                                    <div class="storefront-product-cover">
                                                        <img src="<?= esc($blockImageUrl) ?>" alt="<?= esc($title !== '' ? $title : (string) ($block['name'] ?? 'Banner')) ?>">
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="storefront-muted text-uppercase small fw-semibold mb-2">Published Home</div>
                                                    <div class="h4 mb-2"><?= esc((string) ($block['name'] ?? 'Banner Blogu')) ?></div>
                                                    <p class="storefront-muted mb-0">
                                                        Bu alan admin page management uzerinde publish edilen <code>home</code> blok verisinden gelir.
                                                    </p>
                                                </div>
                                                <div class="storefront-divider"></div>
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="small storefront-muted">Yayinlanan ana sayfa icerigi dogrudan bu vitrinde gosterilir.</div>
                                                    <a href="<?= esc($buttonLink !== '' ? $buttonLink : '/') ?>" class="storefront-link-inline">
                                                        Icerige git
                                                        <i class="ti ti-arrow-up-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif (($block['template'] ?? '') === 'product_showcase'): ?>
                    <div class="storefront-panel-card">
                        <div class="storefront-section-inner">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                                <div>
                                    <span class="storefront-eyebrow mb-2">
                                        <i class="ti ti-shopping-bag"></i>
                                        Vitrin Alani
                                    </span>
                                    <h2 class="storefront-section-title"><?= esc($title !== '' ? $title : 'Urun vitrini') ?></h2>
                                    <?php if ($subtitle !== ''): ?>
                                        <p class="storefront-section-subtitle"><?= esc($subtitle) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="storefront-muted small align-self-lg-center">
                                    <?= esc((string) ($block['name'] ?? 'Urun Blogu')) ?>
                                </div>
                            </div>

                            <?php if (! empty($block['products'])): ?>
                                <div class="storefront-grid products">
                                    <?php foreach ($block['products'] as $product): ?>
                                        <article class="storefront-product-card">
                                            <div class="storefront-product-cover">
                                                <?php if (! empty($product['image_url'])): ?>
                                                    <img src="<?= esc((string) $product['image_url']) ?>" alt="<?= esc((string) ($product['name'] ?? 'Urun')) ?>">
                                                <?php else: ?>
                                                    <span><?= esc((string) ($product['initial'] ?? 'K')) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <span class="storefront-meta-badge"><?= esc((string) ($product['type_label'] ?? 'Kitap')) ?></span>
                                                <span class="storefront-price"><?= esc((string) ($product['price_label'] ?? '0,00 TL')) ?></span>
                                            </div>
                                            <div>
                                                <h3 class="h5 mb-1"><?= esc((string) ($product['name'] ?? 'Urun')) ?></h3>
                                                <p class="storefront-muted mb-0"><?= esc((string) ($product['author'] ?? 'Yazar')) ?></p>
                                            </div>
                                            <a href="<?= esc((string) ($product['detail_url'] ?? '#')) ?>" class="storefront-secondary-btn mt-auto">Detayi Gor</a>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="storefront-empty-state">
                                    <div class="h5 mb-2">Eslesen urun bulunamadi</div>
                                    <p class="storefront-muted mb-0">Published block ayarlari yuklu, ancak gecerli urun verisi veya arama sonucu bulunamadi.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif (($block['template'] ?? '') === 'category_grid'): ?>
                    <div class="storefront-panel-card">
                        <div class="storefront-section-inner">
                            <span class="storefront-eyebrow mb-2">
                                <i class="ti ti-category"></i>
                                Kategori Gezintisi
                            </span>
                            <h2 class="storefront-section-title"><?= esc($title !== '' ? $title : 'Kategoriler') ?></h2>
                            <?php if ($subtitle !== ''): ?>
                                <p class="storefront-section-subtitle mb-4"><?= esc($subtitle) ?></p>
                            <?php endif; ?>
                            <div class="storefront-grid categories">
                                <?php foreach (($block['categories'] ?? []) as $category): ?>
                                    <a href="<?= esc((string) ($category['url'] ?? '#')) ?>" class="storefront-category-card text-decoration-none text-reset">
                                        <?php if ($blockImageUrl !== ''): ?>
                                            <div class="storefront-product-cover mb-3">
                                                <img src="<?= esc($blockImageUrl) ?>" alt="<?= esc((string) ($category['name'] ?? 'Kategori')) ?>">
                                            </div>
                                        <?php else: ?>
                                            <div class="storefront-category-icon"><?= esc((string) ($category['initial'] ?? 'K')) ?></div>
                                        <?php endif; ?>
                                        <h3 class="h6 mb-1"><?= esc((string) ($category['name'] ?? 'Kategori')) ?></h3>
                                        <div class="storefront-muted small">Listeleme akisi sonraki sprintte derinlesecek.</div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php elseif (($block['template'] ?? '') === 'author_showcase'): ?>
                    <div class="storefront-panel-card">
                        <div class="storefront-section-inner">
                            <span class="storefront-eyebrow mb-2">
                                <i class="ti ti-user-star"></i>
                                Yazar Seckisi
                            </span>
                            <h2 class="storefront-section-title"><?= esc($title !== '' ? $title : 'Yazarlar') ?></h2>
                            <?php if ($subtitle !== ''): ?>
                                <p class="storefront-section-subtitle mb-4"><?= esc($subtitle) ?></p>
                            <?php endif; ?>
                            <div class="storefront-grid authors">
                                <?php foreach (($block['authors'] ?? []) as $author): ?>
                                    <article class="storefront-author-card">
                                        <?php if ($blockImageUrl !== ''): ?>
                                            <div class="storefront-author-avatar">
                                                <img src="<?= esc($blockImageUrl) ?>" alt="<?= esc((string) ($author['name'] ?? 'Yazar')) ?>" class="w-100 h-100 object-fit-cover">
                                            </div>
                                        <?php else: ?>
                                            <div class="storefront-author-avatar"><?= esc((string) ($author['initial'] ?? 'Y')) ?></div>
                                        <?php endif; ?>
                                        <h3 class="h5 mb-2"><?= esc((string) ($author['name'] ?? 'Yazar')) ?></h3>
                                        <p class="storefront-muted mb-0">
                                            <?= esc((string) (($author['bio'] ?? '') !== '' ? $author['bio'] : 'Yazar vitrini sonraki sprintte daha zenginlestirilecek.')) ?>
                                        </p>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php elseif (($block['template'] ?? '') === 'notice'): ?>
                    <div class="storefront-notice-card">
                        <div class="d-flex align-items-start gap-3">
                            <div class="storefront-category-icon mb-0"><i class="ti ti-info-circle"></i></div>
                            <div>
                                <span class="storefront-eyebrow mb-2">
                                    <i class="ti ti-bell-ringing"></i>
                                    Duyuru
                                </span>
                                <h2 class="h4 mb-2"><?= esc($title !== '' ? $title : 'Bilgilendirme') ?></h2>
                                <p class="storefront-muted mb-0"><?= esc((string) ($config['content'] ?? $subtitle ?? '')) ?></p>
                            </div>
                        </div>
                    </div>
                <?php elseif (($block['template'] ?? '') === 'newsletter'): ?>
                    <div class="storefront-newsletter-card">
                        <span class="storefront-eyebrow mb-2">
                            <i class="ti ti-mail-share"></i>
                            E-Bulten
                        </span>
                        <h2 class="storefront-section-title"><?= esc($title !== '' ? $title : 'Bultene Katil') ?></h2>
                        <?php if ($subtitle !== ''): ?>
                            <p class="storefront-section-subtitle"><?= esc($subtitle) ?></p>
                        <?php endif; ?>
                        <form class="storefront-newsletter-form" action="#" method="post" onsubmit="return false;">
                            <input
                                type="email"
                                class="form-control"
                                placeholder="<?= esc((string) ($config['input_placeholder'] ?? 'E-posta adresiniz')) ?>"
                            >
                            <button type="button" class="storefront-primary-btn"><?= esc($buttonText !== '' ? $buttonText : 'Kayit Ol') ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="storefront-panel-card">
                        <div class="storefront-section-inner">
                            <span class="storefront-eyebrow">
                                <i class="ti ti-layout-list"></i>
                                <?= esc((string) ($block['name'] ?? 'Block')) ?>
                            </span>
                            <h2 class="storefront-section-title mt-3"><?= esc($title !== '' ? $title : 'Published block') ?></h2>
                            <?php if ($subtitle !== ''): ?>
                                <p class="storefront-section-subtitle"><?= esc($subtitle) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="storefront-section">
            <div class="storefront-hero-card">
                <div class="storefront-section-inner">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-7">
                            <span class="storefront-eyebrow">
                                <i class="ti ti-home"></i>
                                Ana Sayfa
                            </span>
                            <h1 class="storefront-section-title mt-3 mb-3">Kitap Dunyasi ana sayfasi hazir</h1>
                            <p class="storefront-section-subtitle">
                                Public shell aktif. Admin page management tarafinda <code>home</code> icin published version olusturuldugunda icerik burada gorunur.
                            </p>
                            <div class="storefront-hero-actions">
                                <a href="<?= base_url('login') ?>" class="storefront-primary-btn">Giris Yap</a>
                                <a href="<?= base_url('dashboard_anasayfa') ?>" class="storefront-secondary-btn">Eski Dashboard</a>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="storefront-empty-state">
                                <div class="h5 mb-2">Published home bulunamadi</div>
                                <p class="storefront-muted mb-0">Bu durum admin builder zincirini bozmaz; yalnız public runtime icin yayinlanmis veri bekleniyor.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
