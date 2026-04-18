<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title ?? 'Kitap Dunyasi') ?></title>
<link rel="stylesheet" href="<?= base_url('assets/admin/fonts/inter/inter.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/fonts/tabler-icons.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/css/style.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/css/style-preset.css') ?>">
<script src="<?= base_url('assets/admin/js/icon/custom-font.js') ?>"></script>
<style>
    :root {
        --storefront-ink: #111936;
        --storefront-muted: #5b6b89;
        --storefront-surface: #ffffff;
        --storefront-shell: #f5f7fb;
        --storefront-soft: #f0f4ff;
        --storefront-accent: #1677ff;
        --storefront-accent-dark: #0f52ba;
        --storefront-success: #2ca87f;
        --storefront-border: rgba(17, 25, 54, 0.08);
        --storefront-shadow: 0 20px 55px rgba(17, 25, 54, 0.08);
        --storefront-panel: linear-gradient(135deg, #eef4ff 0%, #ffffff 46%, #f6f9ff 100%);
        --storefront-hero: linear-gradient(135deg, rgba(22, 119, 255, 0.10), rgba(255, 255, 255, 0.94) 52%, rgba(44, 168, 127, 0.10));
    }

    * {
        box-sizing: border-box;
    }

    body.storefront-body {
        font-family: Inter, sans-serif;
        background:
            radial-gradient(circle 640px at 12% 180px, rgba(22, 119, 255, 0.10), transparent 68%),
            radial-gradient(circle 520px at 88% 160px, rgba(44, 168, 127, 0.09), transparent 66%),
            var(--storefront-shell);
        color: var(--storefront-ink);
        min-height: 100vh;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .storefront-container {
        width: min(1480px, calc(100% - 40px));
        margin: 0 auto;
    }

    .storefront-header {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 0 !important;
        height: auto !important;
        position: sticky;
        top: 0;
        z-index: 1030;
        background: rgba(245, 247, 251, 0.94);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid var(--storefront-border);
        box-shadow: 0 10px 24px rgba(17, 25, 54, 0.04);
    }

    .storefront-header::before,
    .storefront-header::after {
        content: none !important;
        display: none !important;
    }

    .storefront-topbar {
        padding: 0 0 14px;
    }

    .storefront-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: var(--storefront-ink);
        text-decoration: none;
        white-space: nowrap;
        min-width: 0;
    }

    .storefront-brand-icon {
        width: 26px;
        height: 26px;
        color: var(--storefront-accent);
        flex: 0 0 auto;
    }

    .storefront-brand-text {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        font-size: 1.08rem;
        font-weight: 800;
        letter-spacing: -0.01em;
        color: var(--storefront-accent-dark);
    }

    .storefront-topbar-grid {
        display: grid;
        grid-template-columns: 190px minmax(360px, 1fr) auto;
        align-items: center;
        gap: 18px;
    }

    .storefront-search {
        position: relative;
    }

    .storefront-search .form-control {
        min-height: 54px;
        border-radius: 18px;
        padding-left: 52px;
        padding-right: 18px;
        border: 1px solid rgba(148, 163, 184, 0.28);
        box-shadow: 0 12px 24px rgba(17, 25, 54, 0.05);
        background: rgba(255, 255, 255, 0.96);
        font-size: 0.96rem;
    }

    .storefront-search .search-icon {
        position: absolute;
        top: 50%;
        left: 18px;
        transform: translateY(-50%);
        color: var(--storefront-muted);
        font-size: 1.1rem;
    }

    .storefront-menu {
        display: flex;
        flex-wrap: nowrap;
        gap: 6px;
        justify-content: flex-end;
        align-items: center;
        min-width: 0;
        overflow: hidden;
    }

    .storefront-menu-link,
    .storefront-category-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .storefront-menu-link {
        padding: 10px 11px;
        border-radius: 12px;
        color: var(--storefront-ink);
        background: transparent;
        border: 1px solid transparent;
        font-size: 0.86rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .storefront-menu-link:hover,
    .storefront-menu-link.is-active {
        color: var(--storefront-accent-dark);
        background: rgba(22, 119, 255, 0.08);
        border-color: rgba(22, 119, 255, 0.16);
    }

    .storefront-menu-link.storefront-menu-link--cta {
        background: linear-gradient(135deg, var(--storefront-accent), #3b8cff);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 12px 24px rgba(22, 119, 255, 0.22);
    }

    .storefront-menu-link.storefront-menu-link--cta:hover {
        color: #fff;
        background: linear-gradient(135deg, #0f69eb, #2f83ff);
    }

    .storefront-categorybar {
        border-top: 1px solid rgba(148, 163, 184, 0.14);
        padding: 12px 0 14px;
    }

    .storefront-category-list {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        justify-content: center;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: none;
        width: max-content;
        min-width: 100%;
        margin: 0 auto;
    }

    .storefront-category-list::-webkit-scrollbar {
        display: none;
    }

    .storefront-category-link {
        padding: 10px 13px;
        border-radius: 12px;
        color: var(--storefront-ink);
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid rgba(148, 163, 184, 0.12);
        font-size: 0.85rem;
        font-weight: 700;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .storefront-category-link:hover {
        border-color: rgba(22, 119, 255, 0.24);
        color: var(--storefront-accent-dark);
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(17, 25, 54, 0.06);
    }

    .storefront-main {
        flex: 1 0 auto;
        padding: 34px 0 56px;
    }

    .storefront-hero-card,
    .storefront-panel-card {
        border: 1px solid var(--storefront-border);
        border-radius: 28px;
        background: var(--storefront-surface);
        box-shadow: var(--storefront-shadow);
        overflow: hidden;
    }

    .storefront-hero-card {
        background: var(--storefront-hero);
    }

    .storefront-section {
        margin-bottom: 30px;
    }

    .storefront-section-inner {
        padding: 32px;
    }

    .storefront-section-title {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 10px;
        letter-spacing: -0.02em;
    }

    .storefront-section-subtitle {
        color: var(--storefront-muted);
        margin-bottom: 0;
        max-width: 760px;
    }

    .storefront-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .storefront-hero-meta {
        display: grid;
        gap: 14px;
    }

    .storefront-info-card {
        border: 1px solid rgba(22, 119, 255, 0.12);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 28px rgba(17, 25, 54, 0.06);
    }

    .storefront-primary-btn,
    .storefront-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 48px;
        padding: 0 20px;
        border-radius: 14px;
        text-decoration: none;
        font-weight: 700;
    }

    .storefront-primary-btn {
        background: linear-gradient(135deg, var(--storefront-accent), #3d8eff);
        color: #fff;
        border: 1px solid transparent;
        box-shadow: 0 14px 24px rgba(22, 119, 255, 0.22);
    }

    .storefront-secondary-btn {
        background: rgba(255, 255, 255, 0.9);
        color: var(--storefront-ink);
        border: 1px solid rgba(148, 163, 184, 0.18);
    }

    .storefront-grid {
        display: grid;
        gap: 22px;
    }

    .storefront-grid.products {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }

    .storefront-grid.categories {
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    }

    .storefront-grid.authors {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }

    .storefront-product-card,
    .storefront-category-card,
    .storefront-author-card,
    .storefront-notice-card,
    .storefront-newsletter-card,
    .storefront-empty-state {
        border: 1px solid var(--storefront-border);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 16px 40px rgba(17, 25, 54, 0.06);
    }

    .storefront-product-card {
        padding: 18px;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .storefront-product-card:hover,
    .storefront-category-card:hover,
    .storefront-author-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 22px 44px rgba(17, 25, 54, 0.09);
    }

    .storefront-product-cover,
    .storefront-category-icon,
    .storefront-author-avatar {
        border-radius: 18px;
        background: linear-gradient(135deg, #edf3ff, #f8fbff);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--storefront-accent-dark);
        font-weight: 800;
        overflow: hidden;
    }

    .storefront-product-cover {
        height: 238px;
        border: 1px solid rgba(148, 163, 184, 0.12);
    }

    .storefront-product-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .storefront-category-card,
    .storefront-author-card {
        padding: 20px;
        height: 100%;
    }

    .storefront-category-icon,
    .storefront-author-avatar {
        width: 56px;
        height: 56px;
        font-size: 1.1rem;
        margin-bottom: 14px;
    }

    .storefront-meta-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--storefront-accent-dark);
        background: rgba(22, 119, 255, 0.10);
    }

    .storefront-muted {
        color: var(--storefront-muted);
    }

    .storefront-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--storefront-accent-dark);
    }

    .storefront-price {
        font-size: 1rem;
        font-weight: 800;
        color: var(--storefront-ink);
    }

    .storefront-link-inline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: var(--storefront-accent-dark);
        text-decoration: none;
    }

    .storefront-link-inline:hover {
        color: var(--storefront-accent);
    }

    .storefront-footer {
        border-top: 1px solid var(--storefront-border);
        padding: 26px 0 38px;
        color: var(--storefront-muted);
        background: rgba(255, 255, 255, 0.55);
    }

    .storefront-empty-state {
        padding: 34px;
        text-align: center;
    }

    .storefront-notice-card,
    .storefront-newsletter-card {
        padding: 28px;
    }

    .storefront-newsletter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 18px;
    }

    .storefront-newsletter-form .form-control {
        flex: 1 1 280px;
        min-height: 50px;
        border-radius: 14px;
    }

    .storefront-divider {
        height: 1px;
        margin: 24px 0;
        background: linear-gradient(90deg, rgba(17, 25, 54, 0), rgba(17, 25, 54, 0.08), rgba(17, 25, 54, 0));
    }

    .storefront-footer-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: var(--storefront-ink);
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .storefront-topbar-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .storefront-menu {
            justify-content: flex-start;
            flex-wrap: wrap;
            overflow: visible;
        }
    }

    @media (max-width: 767.98px) {
        .storefront-container {
            width: min(100% - 20px, 1480px);
        }

        .storefront-topbar {
            padding-top: 0;
        }

        .storefront-section-inner {
            padding: 22px;
        }

        .storefront-main {
            padding-top: 20px;
        }

        .storefront-brand {
            gap: 10px;
        }

        .storefront-brand-text {
            font-size: 0.98rem;
        }
    }
</style>
