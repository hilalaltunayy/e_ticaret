<div class="storefront-header" role="banner">
    <div class="storefront-container storefront-topbar">
        <div class="storefront-topbar-grid">
            <div>
                <a href="<?= base_url('/') ?>" class="storefront-brand" aria-label="Kitap Dunyasi">
                    <svg class="pc-icon pc-brand-icon storefront-brand-icon" aria-hidden="true">
                        <use xlink:href="#custom-archive-book"></use>
                    </svg>
                    <span class="pc-brand-text storefront-brand-text">Kitap Dunyasi</span>
                </a>
            </div>
            <div>
                <form action="<?= base_url('/') ?>" method="get" class="storefront-search">
                    <i class="ti ti-search search-icon"></i>
                    <input
                        type="search"
                        name="q"
                        class="form-control"
                        placeholder="Kitap, yazar veya kategori ara"
                        value="<?= esc($searchQuery ?? '') ?>"
                    >
                </form>
            </div>
            <div>
                <nav class="storefront-menu" aria-label="Kullanici menusu">
                    <?php foreach (($headerMenuItems ?? []) as $item): ?>
                        <?php $label = (string) ($item['label'] ?? 'Menu'); ?>
                        <a
                            href="<?= esc((string) ($item['url'] ?? '#')) ?>"
                            class="storefront-menu-link<?= ! empty($item['active']) ? ' is-active' : '' ?><?= $label === 'Giris Yap' ? ' storefront-menu-link--cta' : '' ?>"
                        >
                            <?= esc($label) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    </div>

    <div class="storefront-categorybar">
        <div class="storefront-container">
            <nav class="storefront-category-list" aria-label="Kategori menusu">
                <?php foreach (($categoryNavItems ?? []) as $item): ?>
                    <a href="<?= esc((string) ($item['url'] ?? '#')) ?>" class="storefront-category-link">
                        <?= esc((string) ($item['label'] ?? 'Kategori')) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
</div>
