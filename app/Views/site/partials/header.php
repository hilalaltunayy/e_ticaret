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
                    <?php
                    $isLoggedIn = (bool) session()->get('isLoggedIn');
                    $currentPath = trim((string) service('uri')->getPath(), '/');
                    $digitalBooksPath = 'yardim/dijital-kitaplarim';
                    $digitalMenuInjected = false;
                    ?>
                    <?php foreach (($headerMenuItems ?? []) as $item): ?>
                        <?php $label = (string) ($item['label'] ?? 'Menu'); ?>
                        <?php if (mb_strtolower($label, 'UTF-8') === 'hesabim'): ?>
                            <?php if ($isLoggedIn && ! $digitalMenuInjected): ?>
                                <a
                                    href="<?= esc(base_url($digitalBooksPath)) ?>"
                                    class="storefront-menu-link<?= $currentPath === $digitalBooksPath ? ' is-active' : '' ?>"
                                >
                                    Dijital Kitaplarim
                                </a>
                                <?php $digitalMenuInjected = true; ?>
                            <?php endif; ?>
                            <div class="storefront-menu-dropdown">
                                <a
                                    href="<?= esc((string) ($item['url'] ?? '#')) ?>"
                                    class="storefront-menu-link storefront-menu-link--has-submenu<?= ! empty($item['active']) ? ' is-active' : '' ?>"
                                    aria-haspopup="true"
                                >
                                    <?= esc($label) ?>
                                    <i class="ti ti-chevron-down storefront-menu-caret" aria-hidden="true"></i>
                                </a>
                                <div class="storefront-submenu" role="menu" aria-label="Hesabim alt menusu">
                                    <a href="<?= esc(base_url('yardim/dijital-kitaplarim')) ?>" class="storefront-submenu-link" role="menuitem">
                                        Dijital Kitaplarim
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a
                                href="<?= esc((string) ($item['url'] ?? '#')) ?>"
                                class="storefront-menu-link<?= ! empty($item['active']) ? ' is-active' : '' ?><?= ! empty($item['is_auth']) ? ' storefront-menu-link--cta' : '' ?>"
                            >
                                <?= esc($label) ?>
                            </a>
                        <?php endif; ?>
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
