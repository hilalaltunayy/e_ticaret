<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$selectedBlockTypeId = old('block_type_id');
$draftName = trim((string) ($draft['name'] ?? ('Draft ' . (string) ($draft['version_no'] ?? 1))));
$draftStatus = trim((string) ($draft['status'] ?? 'DRAFT'));
$blockCount = count($blocks);
$builderPolicy = is_array($builderPolicy ?? null) ? $builderPolicy : ['mode' => 'limited', 'title' => 'Kisitli block kutuphanesi', 'message' => 'Bu sayfa turu icin block kutuphanesi sinirlandirildi.'];
$scheduledPublishValue = trim((string) ($draft['scheduled_publish_at'] ?? ''));
$scheduledPublishInputValue = $scheduledPublishValue !== '' ? date('Y-m-d\TH:i', strtotime($scheduledPublishValue)) : '';
$editBlockId = trim((string) old('edit_block_id'));
$editBlockTypeCode = trim((string) old('edit_block_type_code'));
$draftMetaVersionId = trim((string) old('draft_meta_version_id'));
$variantBadgeClasses = [
    'light' => 'bg-light-primary',
    'dark' => 'bg-dark',
    'soft' => 'bg-light-secondary',
    'accent' => 'bg-light-warning',
    'neutral' => 'bg-light',
];
?>

<div class="page-header">
    <div class="page-block">
        <div class="row">
            <div class="col-12">
                <div class="page-header-title">
                    <h2 class="mb-0"><?= esc($page['name']) ?> Builder</h2>
                </div>
            </div>
            <div class="col-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yonetim</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/pages') ?>">Sayfa Yonetimi</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/pages/' . $page['code']) ?>"><?= esc($page['name']) ?></a></li>
                    <li class="breadcrumb-item" aria-current="page"><?= esc($page['name']) ?> Builder</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-xl-7">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avtar avtar-l bg-light-primary">
                                <i class="ti ti-layout-dashboard fs-4"></i>
                            </div>
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="badge bg-light-primary"><?= esc($draftStatus) ?></span>
                                    <span class="badge bg-light-secondary"><?= esc($page['code']) ?></span>
                                    <span class="badge bg-light-success"><?= esc((string) $blockCount) ?> blok</span>
                                </div>
                                <h4 class="mb-1"><?= esc($page['name']) ?> Builder</h4>
                                <p class="text-muted mb-0">Sayfa akisina uygun bloklar ekle, sira duzenle ve draft yerlesimini yonet.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="row g-3">
                            <div class="col-sm-4 col-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small mb-1">Draft</div>
                                    <div class="fw-semibold"><?= esc($draftName) ?></div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small mb-1">Version</div>
                                    <div class="fw-semibold">#<?= esc((string) ($draft['version_no'] ?? 1)) ?></div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-12">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small mb-1">Durum</div>
                                    <div class="fw-semibold"><?= esc($draftStatus) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="<?= site_url('admin/pages/' . $page['code']) ?>" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Detay
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#draftMetaOffcanvas" aria-controls="draftMetaOffcanvas">
                        <i class="ti ti-edit-circle me-1"></i> Taslak Islemleri
                    </button>
                    <a href="<?= site_url('admin/pages/' . $page['code'] . '/drafts') ?>" class="btn btn-outline-primary">
                        <i class="ti ti-stack-2 me-1"></i> Draftlar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-4 col-xl-5">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">Block Kutuphanesi</h5>
                        <p class="text-muted mb-0">Bu draft icin yeni blok ekle ve temel ayarlari yap.</p>
                    </div>
                    <span class="badge bg-light-primary">Builder</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ti ti-alert-circle mt-1"></i>
                            <div><?= esc(session()->getFlashdata('error')) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ti ti-circle-check mt-1"></i>
                            <div><?= esc(session()->getFlashdata('success')) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <div class="fw-semibold mb-1">Kullanim notu</div>
                    <div class="small">Form alanlari block tipine gore degisir. Bu sprintte agir JS yerine temiz, hizli ve anlasilir bir builder deneyimi hedeflenir.</div>
                </div>

                <div class="alert <?= $builderPolicy['mode'] === 'full' ? 'alert-light border' : 'alert-warning' ?>">
                    <div class="fw-semibold mb-1"><?= esc((string) ($builderPolicy['title'] ?? 'Block politikasi')) ?></div>
                    <div class="small mb-0"><?= esc((string) ($builderPolicy['message'] ?? 'Bu sayfa turu icin block seti sinirlandirildi.')) ?></div>
                </div>

                <form action="<?= site_url('admin/pages/builder/blocks') ?>" method="post" id="pageBuilderForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                    <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">

                    <div class="border rounded p-3 mb-3">
                        <label for="block_type_id" class="form-label fw-semibold">Block Type</label>
                        <select name="block_type_id" id="block_type_id" class="form-select" required>
                            <option value="">Block seciniz</option>
                            <?php foreach ($blockTypes as $blockType): ?>
                                <option
                                    value="<?= esc($blockType['id']) ?>"
                                    data-block-code="<?= esc($blockType['code']) ?>"
                                    <?= $selectedBlockTypeId === $blockType['id'] ? 'selected' : '' ?>
                                >
                                    <?= esc($blockType['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Bu listede yalnizca <code><?= esc($page['code']) ?></code> sayfasi icin izinli block tipleri gosterilir.</div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" data-block-form="hero_banner">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Hero Banner</h6>
                            <span class="badge bg-light-warning">Prominent</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="hero_title" class="form-control" value="<?= esc(old('hero_title')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <textarea name="hero_subtitle" rows="3" class="form-control"><?= esc(old('hero_subtitle')) ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3" data-cta-group>
                                <label class="form-label">CTA Metni</label>
                                <select name="hero_button_text_preset" class="form-select" data-cta-select>
                                    <?php foreach ($builderOptions['cta_presets'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('hero_button_text_preset', 'discover_now') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="hero_button_text_custom" class="form-control mt-2 d-none" value="<?= esc(old('hero_button_text_custom', old('hero_button_text'))) ?>" placeholder="Ozel CTA metni" data-cta-custom>
                                <input type="hidden" name="hero_button_text" value="<?= esc(old('hero_button_text', 'Simdi Kesfet')) ?>" data-cta-output>
                            </div>
                            <div class="col-md-6 mb-3" data-link-group>
                                <label class="form-label">Yonlendirme</label>
                                <select name="hero_button_link_type" class="form-select" data-link-type>
                                    <?php foreach ($builderOptions['link_types'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('hero_button_link_type', 'page') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="hero_button_link_target" class="form-select mt-2 d-none" data-link-target data-selected-value="<?= esc(old('hero_button_link_target')) ?>"></select>
                                <input type="text" name="hero_button_link_custom_url" class="form-control mt-2 d-none" value="<?= esc(old('hero_button_link_custom_url', old('hero_button_link'))) ?>" placeholder="https://ornek.com" data-link-custom-url>
                                <input type="hidden" name="hero_button_link" value="<?= esc(old('hero_button_link', '/')) ?>" data-link-output>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Variant</label>
                                <select name="hero_variant" class="form-select">
                                    <?php foreach ($builderOptions['hero_variants'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('hero_variant', 'light') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-0">
                                <label class="form-label">Media</label>
                                <div class="card border shadow-none mb-0" data-media-group data-placeholder-label="Hero banner gorseli">
                                    <div class="card-body">
                                        <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                            <img src="" alt="Hero create media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                                        </div>
                                        <div class="border rounded text-center p-4" data-media-placeholder>
                                            <i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i>
                                            <div class="fw-semibold mb-1">Gorsel yukleyin</div>
                                            <div class="small text-muted">Olusturma asamasinda da banner gorselini secin.</div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            <button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda Kirp</button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                                        </div>
                                        <input type="file" class="d-none" accept="image/*" data-media-file>
                                        <div class="mt-3">
                                            <div class="small text-muted mb-1">Gelismis alan</div>
                                            <input type="text" name="hero_image_path" class="form-control" value="<?= esc(old('hero_image_path')) ?>" placeholder="/uploads/banner.jpg" data-media-path>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" data-block-form="best_sellers">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Best Sellers</h6>
                            <span class="badge bg-light-success">Commerce</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="best_sellers_title" class="form-control" value="<?= esc(old('best_sellers_title')) ?>">
                        </div>
                        <div class="mb-3" data-mode-group>
                            <label class="form-label">Mode</label>
                            <select name="best_sellers_mode" class="form-select" data-mode-select>
                                <?php foreach ($builderOptions['data_modes'] as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= old('best_sellers_mode', 'auto') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="alert alert-light border mb-3" data-mode-panel="auto">
                            <div class="small text-muted">Auto modda sistem `top_selling` kaynagini baz alir ve cok satan urunleri item limit kadar getirir.</div>
                        </div>
                        <div class="border rounded p-3 mb-3 d-none" data-mode-panel="manual">
                            <label class="form-label">Secili Urunler (MVP)</label>
                            <input type="text" name="best_sellers_selected_product_ids" class="form-control" value="<?= esc(old('best_sellers_selected_product_ids')) ?>" placeholder="101, 205, 330">
                            <div class="form-text">Bu sprintte urun secici yerine ID listesi altyapisi hazirlaniyor.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Limit</label>
                                <input type="number" name="best_sellers_item_limit" class="form-control" min="1" max="24" value="<?= esc(old('best_sellers_item_limit', '8')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Type</label>
                                <select name="best_sellers_sort_type" class="form-select">
                                    <?php foreach ($builderOptions['best_sellers_sort_types'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('best_sellers_sort_type', 'sales_desc') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Card Style</label>
                                <select name="best_sellers_card_style" class="form-select">
                                    <?php foreach ($builderOptions['best_sellers_card_styles'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('best_sellers_card_style', 'classic') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="best_sellers_show_badge" name="best_sellers_show_badge" <?= old('best_sellers_show_badge', '1') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="best_sellers_show_badge">Badge goster</label>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-light border mb-0">
                            <div class="small text-muted">Urun secimi bu sprintte sabit placeholder mantigiyla ilerler. Card style ve siralama preview karakterini etkiler.</div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" data-block-form="featured_products">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Featured Products</h6>
                            <span class="badge bg-light-primary">Showcase</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="featured_products_title" class="form-control" value="<?= esc(old('featured_products_title')) ?>">
                        </div>
                        <div class="mb-3" data-mode-group>
                            <label class="form-label">Mode</label>
                            <select name="featured_products_mode" class="form-select" data-mode-select>
                                <?php foreach ($builderOptions['data_modes'] as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= old('featured_products_mode', 'auto') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="alert alert-light border mb-3" data-mode-panel="auto">
                            <div class="small text-muted">Auto modda one cikan urun akisi kullanilir. Manual moda gecince secili urun listesi tutulur.</div>
                        </div>
                        <div class="border rounded p-3 mb-3 d-none" data-mode-panel="manual">
                            <label class="form-label">Secili Urunler (MVP)</label>
                            <input type="text" name="featured_products_selected_product_ids" class="form-control" value="<?= esc(old('featured_products_selected_product_ids')) ?>" placeholder="88, 144, 302">
                            <div class="form-text">Gercek product picker sonraki sprintte eklenecek. Bu alan config altyapisini hazirlar.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Limit</label>
                                <input type="number" name="featured_products_item_limit" class="form-control" min="1" max="24" value="<?= esc(old('featured_products_item_limit', '6')) ?>">
                            </div>
                            <div class="col-md-6 mb-0">
                                <label class="form-label">Variant</label>
                                <select name="featured_products_variant" class="form-select">
                                    <?php foreach ($builderOptions['featured_variants'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('featured_products_variant', 'grid') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" data-block-form="campaign_banner">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Campaign Banner</h6>
                            <span class="badge bg-light-danger">Campaign</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="campaign_banner_title" class="form-control" value="<?= esc(old('campaign_banner_title')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <textarea name="campaign_banner_subtitle" rows="3" class="form-control"><?= esc(old('campaign_banner_subtitle')) ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3" data-cta-group>
                                <label class="form-label">CTA Metni</label>
                                <select name="campaign_banner_button_text_preset" class="form-select" data-cta-select>
                                    <?php foreach ($builderOptions['cta_presets'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('campaign_banner_button_text_preset', 'view_campaign') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="campaign_banner_button_text_custom" class="form-control mt-2 d-none" value="<?= esc(old('campaign_banner_button_text_custom', old('campaign_banner_button_text'))) ?>" placeholder="Ozel CTA metni" data-cta-custom>
                                <input type="hidden" name="campaign_banner_button_text" value="<?= esc(old('campaign_banner_button_text', 'Kampanyayi Gor')) ?>" data-cta-output>
                            </div>
                            <div class="col-md-6 mb-3" data-link-group>
                                <label class="form-label">Yonlendirme</label>
                                <select name="campaign_banner_button_link_type" class="form-select" data-link-type>
                                    <?php foreach ($builderOptions['link_types'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('campaign_banner_button_link_type', 'campaign') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="campaign_banner_button_link_target" class="form-select mt-2 d-none" data-link-target data-selected-value="<?= esc(old('campaign_banner_button_link_target')) ?>"></select>
                                <input type="text" name="campaign_banner_button_link_custom_url" class="form-control mt-2 d-none" value="<?= esc(old('campaign_banner_button_link_custom_url', old('campaign_banner_button_link'))) ?>" placeholder="https://ornek.com" data-link-custom-url>
                                <input type="hidden" name="campaign_banner_button_link" value="<?= esc(old('campaign_banner_button_link', '/campaigns')) ?>" data-link-output>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Variant</label>
                            <select name="campaign_banner_variant" class="form-select">
                                <?php foreach ($builderOptions['campaign_variants'] as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= old('campaign_banner_variant', 'dark') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Media</label>
                            <div class="card border shadow-none mb-0" data-media-group data-placeholder-label="Kampanya gorseli">
                                <div class="card-body">
                                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                        <img src="" alt="Campaign create media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                                    </div>
                                    <div class="border rounded text-center p-4" data-media-placeholder>
                                        <i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i>
                                        <div class="fw-semibold mb-1">Kampanya gorseli secin</div>
                                        <div class="small text-muted">Olusturma aninda kampanya afisi veya banner eklenebilir.</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda Kirp</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                                    </div>
                                    <input type="file" class="d-none" accept="image/*" data-media-file>
                                    <div class="mt-3">
                                        <div class="small text-muted mb-1">Gelismis alan</div>
                                        <input type="text" name="campaign_banner_image_path" class="form-control" value="<?= esc(old('campaign_banner_image_path')) ?>" placeholder="/uploads/campaign.jpg" data-media-path>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" data-block-form="author_showcase">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Author Showcase</h6>
                            <span class="badge bg-light-info">Editorial</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="author_showcase_title" class="form-control" value="<?= esc(old('author_showcase_title')) ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Limit</label>
                                <input type="number" name="author_showcase_item_limit" class="form-control" min="1" max="24" value="<?= esc(old('author_showcase_item_limit', '4')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Layout Type</label>
                                <select name="author_showcase_layout_type" class="form-select">
                                    <?php foreach ($builderOptions['author_layout_types'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('author_showcase_layout_type', 'grid') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kisa Aciklama</label>
                            <textarea name="author_showcase_subtitle" rows="3" class="form-control"><?= esc(old('author_showcase_subtitle')) ?></textarea>
                        </div>
                        <div>
                            <label class="form-label">Author Media</label>
                            <div class="card border shadow-none mb-0" data-media-group data-placeholder-label="Yazar gorseli">
                                <div class="card-body">
                                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                        <img src="" alt="Author create media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                                    </div>
                                    <div class="border rounded text-center p-4" data-media-placeholder>
                                        <i class="ti ti-user-circle fs-2 text-muted d-block mb-2"></i>
                                        <div class="fw-semibold mb-1">Yazar gorseli secin</div>
                                        <div class="small text-muted">Showcase alaninin gorsel karakterini belirlemek icin kullanilir.</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                                    </div>
                                    <input type="file" class="d-none" accept="image/*" data-media-file>
                                    <div class="mt-3">
                                        <div class="small text-muted mb-1">Gelismis alan</div>
                                        <input type="text" name="author_showcase_image_path" class="form-control" value="<?= esc(old('author_showcase_image_path')) ?>" placeholder="/uploads/author.jpg" data-media-path>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" data-block-form="category_grid">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Category Grid</h6>
                            <span class="badge bg-light-secondary">Discovery</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="category_grid_title" class="form-control" value="<?= esc(old('category_grid_title')) ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Item Limit</label>
                                <input type="number" name="category_grid_item_limit" class="form-control" min="1" max="12" value="<?= esc(old('category_grid_item_limit', '4')) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Grid Type</label>
                                <select name="category_grid_grid_type" class="form-select">
                                    <?php foreach ($builderOptions['category_grid_types'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('category_grid_grid_type', '4_col') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category Label</label>
                                <input type="text" name="category_grid_label" class="form-control" value="<?= esc(old('category_grid_label')) ?>" placeholder="Orn. Editorden Secmeler">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Grid Media</label>
                            <div class="card border shadow-none mb-0" data-media-group data-placeholder-label="Kategori grid gorseli">
                                <div class="card-body">
                                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                        <img src="" alt="Category create media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                                    </div>
                                    <div class="border rounded text-center p-4" data-media-placeholder>
                                        <i class="ti ti-category-plus fs-2 text-muted d-block mb-2"></i>
                                        <div class="fw-semibold mb-1">Grid gorseli secin</div>
                                        <div class="small text-muted">Kategori bloklarinin gorsel karakterini destekleyen placeholder alani.</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                                    </div>
                                    <input type="file" class="d-none" accept="image/*" data-media-file>
                                    <div class="mt-3">
                                        <div class="small text-muted mb-1">Gelismis alan</div>
                                        <input type="text" name="category_grid_image_path" class="form-control" value="<?= esc(old('category_grid_image_path')) ?>" placeholder="/uploads/category-grid.jpg" data-media-path>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" data-block-form="newsletter">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Newsletter</h6>
                            <span class="badge bg-light-primary">Engagement</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="newsletter_title" class="form-control" value="<?= esc(old('newsletter_title')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="newsletter_subtitle" rows="3" class="form-control"><?= esc(old('newsletter_subtitle')) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Input Placeholder</label>
                            <input type="text" name="newsletter_input_placeholder" class="form-control" value="<?= esc(old('newsletter_input_placeholder', 'E-posta adresiniz')) ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3" data-cta-group>
                                <label class="form-label">CTA Metni</label>
                                <select name="newsletter_button_text_preset" class="form-select" data-cta-select>
                                    <?php foreach ($builderOptions['cta_presets'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('newsletter_button_text_preset', 'start_now') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="newsletter_button_text_custom" class="form-control mt-2 d-none" value="<?= esc(old('newsletter_button_text_custom', old('newsletter_button_text'))) ?>" placeholder="Ozel CTA metni" data-cta-custom>
                                <input type="hidden" name="newsletter_button_text" value="<?= esc(old('newsletter_button_text', 'Hemen Basla')) ?>" data-cta-output>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Variant</label>
                                <select name="newsletter_variant" class="form-select">
                                    <?php foreach ($builderOptions['newsletter_variants'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('newsletter_variant', 'primary') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="newsletter_show_icon" name="newsletter_show_icon" <?= old('newsletter_show_icon', '1') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="newsletter_show_icon">Icon goster</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" data-block-form="notice">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Notice</h6>
                            <span class="badge bg-light-warning">Alert</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="notice_title" class="form-control" value="<?= esc(old('notice_title')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="notice_content" rows="3" class="form-control"><?= esc(old('notice_content')) ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Notice Type</label>
                                <select name="notice_notice_type" class="form-select">
                                    <?php foreach ($builderOptions['notice_types'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('notice_notice_type', 'info') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tone</label>
                                <select name="notice_tone" class="form-select">
                                    <?php foreach ($builderOptions['notice_tones'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('notice_tone', 'soft') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="notice_show_icon" name="notice_show_icon" <?= old('notice_show_icon', '1') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notice_show_icon">Icon goster</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" data-block-form="slider">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Slider</h6>
                            <span class="badge bg-light-primary">Carousel</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="slider_title" class="form-control" value="<?= esc(old('slider_title')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <textarea name="slider_subtitle" rows="3" class="form-control"><?= esc(old('slider_subtitle')) ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3" data-cta-group>
                                <label class="form-label">CTA Metni</label>
                                <select name="slider_button_text_preset" class="form-select" data-cta-select>
                                    <?php foreach ($builderOptions['cta_presets'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('slider_button_text_preset', 'go_detail') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="slider_button_text_custom" class="form-control mt-2 d-none" value="<?= esc(old('slider_button_text_custom', old('slider_button_text'))) ?>" placeholder="Ozel CTA metni" data-cta-custom>
                                <input type="hidden" name="slider_button_text" value="<?= esc(old('slider_button_text', 'Detaya Git')) ?>" data-cta-output>
                            </div>
                            <div class="col-md-6 mb-3" data-link-group>
                                <label class="form-label">Yonlendirme</label>
                                <select name="slider_button_link_type" class="form-select" data-link-type>
                                    <?php foreach ($builderOptions['link_types'] as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= old('slider_button_link_type', 'page') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="slider_button_link_target" class="form-select mt-2 d-none" data-link-target data-selected-value="<?= esc(old('slider_button_link_target')) ?>"></select>
                                <input type="text" name="slider_button_link_custom_url" class="form-control mt-2 d-none" value="<?= esc(old('slider_button_link_custom_url', old('slider_button_link'))) ?>" placeholder="https://ornek.com" data-link-custom-url>
                                <input type="hidden" name="slider_button_link" value="<?= esc(old('slider_button_link', '/')) ?>" data-link-output>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Variant</label>
                            <select name="slider_variant" class="form-select">
                                <?php foreach ($builderOptions['slider_variants'] as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= old('slider_variant', 'light') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Media</label>
                            <div class="card border shadow-none mb-0" data-media-group data-placeholder-label="Slider gorseli">
                                <div class="card-body">
                                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                        <img src="" alt="Slider create media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                                    </div>
                                    <div class="border rounded text-center p-4" data-media-placeholder>
                                        <i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i>
                                        <div class="fw-semibold mb-1">Slider gorseli secin</div>
                                        <div class="small text-muted">Olusturma tarafinda da media preview ile ilerleyin.</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda Kirp</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                                    </div>
                                    <input type="file" class="d-none" accept="image/*" data-media-file>
                                    <div class="mt-3">
                                        <div class="small text-muted mb-1">Gelismis alan</div>
                                        <input type="text" name="slider_image_path" class="form-control" value="<?= esc(old('slider_image_path')) ?>" placeholder="/uploads/slider.jpg" data-media-path>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-secondary d-none mb-3" data-block-form="fallback">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ti ti-info-circle mt-1"></i>
                            <div>Secilen block tipi bu sprintte varsayilan ayarlariyla eklenir. Ileriki sprintlerde daha detayli ayarlar acilabilir.</div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Canvas'a Block Ekle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xxl-8 col-xl-7">
        <div class="card mb-4 h-100">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">Canvas Ozeti</h5>
                        <p class="text-muted mb-0">Kutuphane ile draft canvas arasindaki akisi daha rahat yonetin.</p>
                    </div>
                    <span class="badge bg-light-primary">Live Draft</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Toplam Blok</div>
                            <div class="fw-semibold"><?= esc((string) $blockCount) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Akis</div>
                            <div class="fw-semibold">Sirali Builder Canvas</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Duzenleme</div>
                            <div class="fw-semibold">Sag Panel Offcanvas</div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-light border mb-0 mt-3">
                    <div class="small text-muted mb-0">Block kutuphanesi ust alanda arac paneli gibi kalir. Asil sayfa akisi ise asagidaki genis canvas bolumunde devam eder.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">Builder Canvas</h5>
                        <p class="text-muted mb-0">Bloklar sayfa akisina uygun sirayla burada yer alir.</p>
                    </div>
                    <span class="badge bg-light-primary"><?= esc((string) $blockCount) ?> blok</span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($blocks === []): ?>
                    <div class="text-center py-5">
                        <div class="avtar avtar-xl bg-light-primary mx-auto mb-3">
                            <i class="ti ti-layout-grid-add fs-2"></i>
                        </div>
                        <h5 class="mb-2">Canvas henuz bos</h5>
                        <p class="text-muted mb-3">Soldaki block kutuphanesinden bir blok secerek bu draft icin ilk yerlesimi olustur.</p>
                        <span class="badge bg-light-secondary">Ilk blok eklendiginde sayfa akisi burada gorunur</span>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($blocks as $index => $block): ?>
                            <?php
                            $blockTypeCode = (string) ($block['block_type_code'] ?? '');
                            $config = is_array($block['config_data'] ?? null) ? $block['config_data'] : [];
                            $titleText = trim((string) ($config['title'] ?? $block['block_type_name'] ?? 'Block'));
                            $subtitleText = trim((string) ($config['subtitle'] ?? ''));
                            $variantText = trim((string) ($config['variant'] ?? 'light'));
                            $itemLimit = (int) ($config['item_limit'] ?? 0);
                            $isDarkVariant = $variantText === 'dark';
                            $showIcon = ! array_key_exists('show_icon', $config) || (bool) $config['show_icon'];
                            $dataMode = trim((string) ($config['mode'] ?? 'auto'));
                            $selectedProductCount = is_array($config['selected_product_ids'] ?? null) ? count($config['selected_product_ids']) : 0;
                            $variantBadgeClass = $variantBadgeClasses[$variantText] ?? 'bg-light';
                            $surfaceClass = match ($variantText) {
                                'dark' => 'bg-dark text-white',
                                'soft' => 'bg-light-secondary',
                                'accent' => 'bg-light-warning',
                                default => 'bg-light-primary',
                            };
                            ?>
                            <div class="card border shadow-none mb-0">
                                <div class="card-body">
                                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3">
                                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                                            <div class="avtar avtar-s bg-light-secondary">
                                                <span class="fw-semibold"><?= esc((string) ($index + 1)) ?></span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <h6 class="mb-0"><?= esc($block['block_type_name'] ?? 'Block') ?></h6>
                                                    <span class="badge bg-light-primary"><?= esc($blockTypeCode ?: '-') ?></span>
                                                    <span class="badge bg-light-secondary"><?= esc($block['zone']) ?></span>
                                                    <span class="badge bg-light-success">Visible</span>
                                                    <?php if (in_array($blockTypeCode, ['best_sellers', 'featured_products'], true)): ?>
                                                        <span class="badge <?= $dataMode === 'manual' ? 'bg-light-warning' : 'bg-light-info' ?>"><?= esc($dataMode) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-muted mb-2 small">Bu blok sayfa akisinda <?= esc((string) ($index + 1)) ?>. sirada yer aliyor.</p>
                                                <div class="border rounded p-3 bg-light mb-3">
                                                    <div class="text-muted small mb-1">Kisa ozet</div>
                                                    <div class="fw-medium"><?= esc($block['config_summary'] ?? 'Varsayilan ayarlar') ?></div>
                                                </div>

                                                <div class="border rounded p-3">
                                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                                        <div class="small fw-semibold text-muted">Mini Preview</div>
                                                        <span class="badge <?= esc($variantBadgeClass, 'attr') ?>"><?= esc($variantText !== '' ? $variantText : 'default') ?></span>
                                                    </div>

                                                    <?php if ($blockTypeCode === 'hero_banner'): ?>
                                                        <div class="card <?= esc($surfaceClass, 'attr') ?> border-0 mb-0">
                                                            <div class="card-body">
                                                                <div class="row g-3 align-items-center">
                                                                    <div class="col-md-8">
                                                                        <h6 class="mb-1"><?= esc($titleText !== '' ? $titleText : 'Hero Banner') ?></h6>
                                                                        <p class="small mb-3 <?= $isDarkVariant ? 'text-white-50' : 'text-muted' ?>"><?= esc($subtitleText !== '' ? $subtitleText : 'One cikan mesaj ve call to action alani.') ?></p>
                                                                        <span class="btn btn-sm <?= $isDarkVariant ? 'btn-light' : 'btn-primary' ?> disabled"><?= esc((string) ($config['button_text'] ?? 'Hemen Kesfet')) ?></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="card border shadow-none mb-0">
                                                                            <div class="card-body text-center">
                                                                                <?php if (trim((string) ($config['image_path'] ?? '')) !== ''): ?>
                                                                                    <div class="ratio ratio-16x9 mb-2">
                                                                                        <img src="<?= esc((string) $config['image_path'], 'attr') ?>" alt="Hero media" class="img-fluid rounded object-fit-cover">
                                                                                    </div>
                                                                                    <span class="small text-muted text-truncate d-block"><?= esc((string) $config['image_path']) ?></span>
                                                                                <?php else: ?>
                                                                                    <i class="ti ti-photo fs-3 d-block mb-2"></i>
                                                                                    <span class="small text-muted">Gorsel alani</span>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php elseif ($blockTypeCode === 'campaign_banner'): ?>
                                                        <div class="card <?= esc($surfaceClass, 'attr') ?> border-0 mb-0">
                                                            <div class="card-body">
                                                                <div class="row g-3 align-items-center">
                                                                    <div class="col-md-8">
                                                                        <div class="badge bg-light text-dark mb-2">Campaign</div>
                                                                        <h6 class="mb-1"><?= esc($titleText !== '' ? $titleText : 'Kampanya Banner') ?></h6>
                                                                        <p class="small mb-0 <?= $isDarkVariant ? 'text-white-50' : 'text-muted' ?>"><?= esc($subtitleText !== '' ? $subtitleText : 'Kisa kampanya mesaji burada gorunur.') ?></p>
                                                                    </div>
                                                                    <div class="col-md-4 text-md-end">
                                                                        <?php if (trim((string) ($config['image_path'] ?? '')) !== ''): ?>
                                                                            <div class="ratio ratio-16x9 mb-2">
                                                                                <img src="<?= esc((string) $config['image_path'], 'attr') ?>" alt="Campaign media" class="img-fluid rounded object-fit-cover">
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        <span class="btn btn-sm <?= $isDarkVariant ? 'btn-light' : 'btn-danger' ?> disabled"><?= esc((string) ($config['button_text'] ?? 'Detay')) ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php elseif ($blockTypeCode === 'best_sellers'): ?>
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <span class="badge <?= $dataMode === 'manual' ? 'bg-light-warning' : 'bg-light-info' ?>">
                                                                <?= $dataMode === 'manual' ? esc($selectedProductCount . ' secili urun') : 'Otomatik veri akisi' ?>
                                                            </span>
                                                            <span class="small text-muted"><?= esc((string) ($config['sort_type'] ?? 'sales_desc')) ?></span>
                                                        </div>
                                                        <div class="row g-2">
                                                            <?php for ($i = 0; $i < min(max($itemLimit, 3), 4); $i++): ?>
                                                                <div class="col-md-3 col-6">
                                                                    <div class="card border shadow-none mb-0 h-100">
                                                                        <div class="card-body p-3">
                                                                            <div class="<?= ($config['card_style'] ?? 'classic') === 'minimal' ? 'bg-light-secondary' : (($config['card_style'] ?? 'classic') === 'compact' ? 'bg-light-success' : 'bg-light') ?> rounded-2 p-3 text-center mb-2">
                                                                                <i class="ti ti-book-2 text-primary"></i>
                                                                            </div>
                                                                            <div class="small fw-semibold text-truncate"><?= esc($titleText !== '' ? $titleText : 'Cok Satanlar') ?></div>
                                                                            <div class="small text-muted"><?= esc((string) ($config['card_style'] ?? 'classic')) ?> / Urun <?= esc((string) ($i + 1)) ?></div>
                                                                            <?php if (! empty($config['show_badge'])): ?><span class="badge bg-light-warning mt-2">Cok Satan</span><?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endfor; ?>
                                                        </div>
                                                    <?php elseif ($blockTypeCode === 'featured_products'): ?>
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <span class="badge <?= $dataMode === 'manual' ? 'bg-light-warning' : 'bg-light-info' ?>">
                                                                <?= $dataMode === 'manual' ? esc($selectedProductCount . ' secili urun') : 'Auto source: featured' ?>
                                                            </span>
                                                            <span class="small text-muted"><?= esc($variantText !== '' ? $variantText : 'grid') ?></span>
                                                        </div>
                                                        <div class="row g-2">
                                                            <?php for ($i = 0; $i < 4; $i++): ?>
                                                                <div class="col-md-3 col-6">
                                                                    <div class="card border shadow-none mb-0 h-100">
                                                                        <div class="card-body p-3">
                                                                            <div class="bg-light-primary rounded-2 p-3 text-center mb-2">
                                                                                <i class="ti ti-photo text-primary"></i>
                                                                            </div>
                                                                            <div class="small fw-semibold text-truncate"><?= esc($titleText !== '' ? $titleText : 'One Cikanlar') ?></div>
                                                                            <div class="small text-muted"><?= esc($variantText !== '' ? $variantText : 'grid') ?> gorunumu</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endfor; ?>
                                                        </div>
                                                    <?php elseif ($blockTypeCode === 'author_showcase'): ?>
                                                        <div class="row g-2">
                                                            <?php for ($i = 0; $i < min(max($itemLimit, 2), 3); $i++): ?>
                                                                <div class="col-md-4">
                                                                    <div class="card border shadow-none mb-0 h-100">
                                                                        <div class="card-body p-3 text-center">
                                                                            <?php if (trim((string) ($config['image_path'] ?? '')) !== ''): ?>
                                                                                <div class="ratio ratio-1x1 mx-auto mb-2">
                                                                                    <img src="<?= esc((string) $config['image_path'], 'attr') ?>" alt="Author media" class="img-fluid rounded-circle object-fit-cover">
                                                                                </div>
                                                                            <?php else: ?>
                                                                                <div class="avtar avtar-l bg-light-info mx-auto mb-2">
                                                                                    <i class="ti ti-user"></i>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            <div class="small fw-semibold"><?= esc($titleText !== '' ? $titleText : 'Yazar Seckisi') ?></div>
                                                                            <div class="small text-muted"><?= esc((string) ($config['layout_type'] ?? 'grid')) ?></div>
                                                                            <?php if (trim((string) ($config['subtitle'] ?? '')) !== ''): ?>
                                                                                <div class="small text-muted mt-1 text-truncate"><?= esc((string) $config['subtitle']) ?></div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endfor; ?>
                                                        </div>
                                                    <?php elseif ($blockTypeCode === 'category_grid'): ?>
                                                        <div class="row g-2">
                                                            <?php for ($i = 0; $i < min(max((int) ($config['item_limit'] ?? 4), 2), 4); $i++): ?>
                                                                <div class="col-md-3 col-6">
                                                                    <div class="border rounded p-3 text-center bg-light h-100">
                                                                        <?php if (trim((string) ($config['image_path'] ?? '')) !== ''): ?>
                                                                            <div class="ratio ratio-1x1 mb-2">
                                                                                <img src="<?= esc((string) $config['image_path'], 'attr') ?>" alt="Category media" class="img-fluid rounded object-fit-cover">
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <i class="ti ti-category-2 d-block mb-2 text-primary"></i>
                                                                        <?php endif; ?>
                                                                        <span class="small fw-semibold"><?= esc((string) ($config['label'] ?? 'Kategori')) ?> <?= esc((string) ($i + 1)) ?></span>
                                                                        <div class="small text-muted"><?= esc((string) ($config['grid_type'] ?? '4_col')) ?></div>
                                                                    </div>
                                                                </div>
                                                            <?php endfor; ?>
                                                        </div>
                                                    <?php elseif ($blockTypeCode === 'newsletter'): ?>
                                                        <div class="card <?= $variantText === 'light' ? 'bg-light' : ($variantText === 'soft' ? 'bg-light-primary' : 'bg-primary text-white') ?> border-0 mb-0">
                                                            <div class="card-body">
                                                                <div class="row g-3 align-items-center">
                                                                    <div class="col-md-7">
                                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                                            <?php if ($showIcon): ?><i class="ti ti-mail fs-5"></i><?php endif; ?>
                                                                            <h6 class="mb-0"><?= esc($titleText !== '' ? $titleText : 'Bultene Katil') ?></h6>
                                                                        </div>
                                                                        <p class="small <?= $variantText === 'primary' ? 'text-white-50' : 'text-muted' ?> mb-0"><?= esc($subtitleText !== '' ? $subtitleText : 'Kampanya ve yeni urun duyurulari icin kayit alani.') ?></p>
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <div class="input-group input-group-sm">
                                                                            <?php if ($showIcon): ?><span class="input-group-text"><i class="ti ti-mail"></i></span><?php endif; ?>
                                                                            <input type="text" class="form-control" value="<?= esc((string) ($config['input_placeholder'] ?? 'email@example.com')) ?>" disabled>
                                                                            <button class="btn <?= $variantText === 'primary' ? 'btn-light' : 'btn-primary' ?>" type="button" disabled><?= esc((string) ($config['button_text'] ?? 'Hemen Basla')) ?></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php elseif ($blockTypeCode === 'notice'): ?>
                                                        <div class="alert alert-<?= esc((string) ($config['notice_type'] ?? 'warning')) ?> mb-0 <?= ($config['tone'] ?? 'soft') === 'solid' ? 'border-0' : '' ?>">
                                                            <div class="d-flex align-items-start gap-2">
                                                                <?php if ($showIcon): ?><i class="ti ti-info-circle mt-1"></i><?php endif; ?>
                                                                <div>
                                                                    <div class="fw-semibold"><?= esc($titleText !== '' ? $titleText : 'Bilgilendirme') ?></div>
                                                                    <div class="small mb-0"><?= esc((string) ($config['content'] ?? $subtitleText ?: 'Kisa duyuru veya operasyonel notice alani.')) ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php elseif ($blockTypeCode === 'slider'): ?>
                                                        <div class="card bg-light border-0 mb-0">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <div class="fw-semibold"><?= esc($titleText !== '' ? $titleText : 'Slider') ?></div>
                                                                    <div class="d-flex gap-1">
                                                                        <span class="badge bg-light-secondary">1</span>
                                                                        <span class="badge bg-primary">2</span>
                                                                        <span class="badge bg-light-secondary">3</span>
                                                                    </div>
                                                                </div>
                                                                <div class="row g-2">
                                                                    <div class="col-8">
                                                                        <?php if (trim((string) ($config['image_path'] ?? '')) !== ''): ?>
                                                                            <div class="ratio ratio-16x9">
                                                                                <img src="<?= esc((string) $config['image_path'], 'attr') ?>" alt="Slider media" class="img-fluid rounded object-fit-cover">
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="bg-light-primary rounded p-4 text-center">
                                                                                <i class="ti ti-carousel-horizontal text-primary fs-4"></i>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="d-flex flex-column gap-2">
                                                                            <div class="bg-light rounded p-2"></div>
                                                                            <div class="bg-light rounded p-2"></div>
                                                                            <div class="bg-light rounded p-2"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="card bg-light border-0 mb-0">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <div class="avtar avtar-m bg-light-secondary">
                                                                        <i class="ti ti-layout-grid"></i>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold"><?= esc($titleText !== '' ? $titleText : ($block['block_type_name'] ?? 'Block Preview')) ?></div>
                                                                        <div class="small text-muted">Bu block tipi icin sade builder preview gosterilir.</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary page-builder-edit-btn"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#blockEditOffcanvas"
                                                data-block-id="<?= esc($block['id']) ?>"
                                                data-block-name="<?= esc($block['block_type_name'] ?? 'Block') ?>"
                                                data-block-code="<?= esc($block['block_type_code'] ?? '') ?>"
                                                data-block-summary="<?= esc($block['config_summary'] ?? 'Varsayilan ayarlar') ?>"
                                                data-block-config="<?= esc($block['config_data_json'] ?? '{}', 'attr') ?>"
                                            >
                                                <i class="ti ti-edit me-1"></i> Duzenle
                                            </button>

                                            <form action="<?= site_url('admin/pages/builder/blocks/reorder') ?>" method="post">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                                                <input type="hidden" name="block_id" value="<?= esc($block['id']) ?>">
                                                <input type="hidden" name="direction" value="up">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Yukari">
                                                    <i class="ti ti-arrow-up"></i>
                                                </button>
                                            </form>

                                            <form action="<?= site_url('admin/pages/builder/blocks/reorder') ?>" method="post">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                                                <input type="hidden" name="block_id" value="<?= esc($block['id']) ?>">
                                                <input type="hidden" name="direction" value="down">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Asagi">
                                                    <i class="ti ti-arrow-down"></i>
                                                </button>
                                            </form>

                                            <form action="<?= site_url('admin/pages/builder/blocks/delete') ?>" method="post">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                                                <input type="hidden" name="block_id" value="<?= esc($block['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="draftMetaOffcanvas" aria-labelledby="draftMetaOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-1" id="draftMetaOffcanvasLabel">Taslak Islemleri</h5>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge bg-light-primary"><?= esc($draftStatus) ?></span>
                <span class="badge bg-light-secondary"><?= esc($page['code']) ?></span>
                <span class="badge bg-light-success"><?= esc((string) $blockCount) ?> blok</span>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php if (session()->getFlashdata('draft_error')): ?>
            <div class="alert alert-danger" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="ti ti-alert-circle mt-1"></i>
                    <div><?= esc(session()->getFlashdata('draft_error')) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="ti ti-circle-check mt-1"></i>
                    <div><?= esc(session()->getFlashdata('success')) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border shadow-none mb-3">
            <div class="card-body">
                <div class="text-muted small mb-2">Draft Ozeti</div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Sayfa</div>
                            <div class="fw-semibold"><?= esc($page['name']) ?></div>
                            <div class="small text-muted"><?= esc($page['code']) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Draft</div>
                            <div class="fw-semibold"><?= esc($draftName) ?></div>
                            <div class="small text-muted">Version #<?= esc((string) ($draft['version_no'] ?? 1)) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Durum</div>
                            <div class="fw-semibold"><?= esc($draftStatus) ?></div>
                            <div class="small text-muted"><?= esc((string) $blockCount) ?> blok</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Son Guncelleme</div>
                            <div class="fw-semibold"><?= esc((string) ($draft['updated_at'] ?? '-')) ?></div>
                            <div class="small text-muted">Olusturma: <?= esc((string) ($draft['created_at'] ?? '-')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="<?= site_url('admin/pages/builder/draft/update') ?>" method="post" id="draftMetaForm">
            <?= csrf_field() ?>
            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
            <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">
            <input type="hidden" name="draft_meta_version_id" value="<?= esc($draftMetaVersionId !== '' ? $draftMetaVersionId : (string) ($draft['id'] ?? '')) ?>">

            <div class="card border shadow-none mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Temel Ayarlar</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Draft Adi</label>
                        <input type="text" name="draft_name" class="form-control" value="<?= esc(old('draft_name', $draftName)) ?>" placeholder="Orn. Nisan Kampanya Taslagi">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Kisa Not</label>
                        <textarea name="draft_notes" rows="4" class="form-control" placeholder="Orn. Banner gorselleri guncellenecek"><?= esc(old('draft_notes', (string) ($draft['notes'] ?? ''))) ?></textarea>
                        <div class="form-text">Bu not yalnizca admin tarafinda taslak takibi icin kullanilir.</div>
                    </div>
                </div>
            </div>

            <div class="card border shadow-none mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Canliya Al</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border mb-3">
                        <div class="small text-muted mb-0">Bu draft canliya alindiginda ayni sayfadaki mevcut published version otomatik olarak arsivlenir.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success" form="draftMetaForm" formaction="<?= site_url('admin/pages/builder/draft/publish') ?>" <?= $draftStatus === 'PUBLISHED' ? 'disabled' : '' ?>>
                            <i class="ti ti-broadcast me-1"></i> <?= $draftStatus === 'PUBLISHED' ? 'Canlida' : 'Canliya Al' ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border shadow-none mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Schedule</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Planlanan Tarih</label>
                        <input type="datetime-local" name="scheduled_publish_at" class="form-control" value="<?= esc(old('scheduled_publish_at', $scheduledPublishInputValue)) ?>">
                    </div>
                    <div class="alert alert-light border mb-3">
                        <div class="small text-muted mb-0">Planlama yapildiginda version `SCHEDULED` durumuna gecer. Otomatik zaman gelince publish motoru sonraki sprintte tamamlanacak.</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary" form="draftMetaForm" formaction="<?= site_url('admin/pages/builder/draft/schedule') ?>" <?= $draftStatus === 'PUBLISHED' ? 'disabled' : '' ?>>
                            <i class="ti ti-calendar-event me-1"></i> Schedule Et
                        </button>
                        <?php if ($draftStatus === 'SCHEDULED'): ?>
                            <button type="submit" class="btn btn-outline-warning" form="draftMetaForm" formaction="<?= site_url('admin/pages/builder/draft/unschedule') ?>">
                                <i class="ti ti-calendar-off me-1"></i> Planlamayi Kaldir
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if ($scheduledPublishValue !== ''): ?>
                        <div class="small text-muted mt-3">Mevcut plan: <?= esc($scheduledPublishValue) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border shadow-none mb-0">
                <div class="card-header">
                    <h6 class="mb-0">Draft Yasam Dongusu</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="<?= site_url('admin/pages/drafts/create') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="ti ti-copy-plus me-1"></i> Yeni Draft Olustur
                            </button>
                        </form>
                        <form action="<?= site_url('admin/pages/drafts/duplicate') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                            <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">
                            <button type="submit" class="btn btn-outline-info w-100" <?= $draftStatus === 'ARCHIVED' ? 'disabled' : '' ?>>
                                <i class="ti ti-copy me-1"></i> Taslagi Kopyala
                            </button>
                        </form>
                        <form action="<?= site_url('admin/pages/drafts/archive') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                            <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">
                            <button type="submit" class="btn btn-outline-warning w-100" <?= ! in_array($draftStatus, ['DRAFT', 'SCHEDULED'], true) ? 'disabled' : '' ?>>
                                <i class="ti ti-archive me-1"></i> Taslagi Arsivle
                            </button>
                        </form>
                        <?php if (is_array($publishedVersion) && ! empty($publishedVersion['id'])): ?>
                            <form action="<?= site_url('admin/pages/drafts/unpublish') ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                                <input type="hidden" name="version_id" value="<?= esc($publishedVersion['id']) ?>">
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="ti ti-plug-connected-x me-1"></i> Canli Surumu Geri Cek
                                </button>
                            </form>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-danger" disabled>
                            <i class="ti ti-trash me-1"></i> Guvenli Silme (Yakinda)
                        </button>
                    </div>
                    <div class="alert alert-light border mt-3 mb-0">
                        <div class="small text-muted mb-0">Silme aksiyonu bu sprintte bilerek pasif tutuldu. Riskli hard delete acilmadi.</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Kapat</button>
            <button type="submit" class="btn btn-primary" form="draftMetaForm">
                <i class="ti ti-device-floppy me-1"></i> Kaydet
            </button>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end w-75" tabindex="-1" id="blockEditOffcanvas" aria-labelledby="blockEditOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-1" id="blockEditOffcanvasLabel">Block Duzenle</h5>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge bg-light-primary" id="editBlockCodeBadge">-</span>
                <span class="badge bg-light-secondary">Draft</span>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="ti ti-alert-circle mt-1"></i>
                    <div><?= esc(session()->getFlashdata('error')) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border shadow-none mb-3">
            <div class="card-body">
                <div class="text-muted small mb-1">Secili Block</div>
                <div class="fw-semibold mb-1" id="editBlockName">Block seciniz</div>
                <div class="text-muted small" id="editBlockSummary">Secilen block'in mevcut ayarlari burada duzenlenir.</div>
            </div>
        </div>

        <form action="<?= site_url('admin/pages/builder/blocks/update') ?>" method="post" id="blockEditForm">
            <?= csrf_field() ?>
            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
            <input type="hidden" name="block_id" id="edit_block_id" value="<?= esc($editBlockId) ?>">
            <input type="hidden" name="edit_block_id" value="<?= esc($editBlockId) ?>" id="edit_block_state_id">
            <input type="hidden" name="edit_block_type_code" value="<?= esc($editBlockTypeCode) ?>" id="edit_block_type_code">

            <div class="border rounded p-3 mb-3 d-none" data-edit-block-form="hero_banner">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Hero Banner</h6>
                    <span class="badge bg-light-warning">Prominent</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="hero_title" class="form-control" value="<?= esc(old('hero_title')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Subtitle</label>
                    <textarea name="hero_subtitle" rows="3" class="form-control"><?= esc(old('hero_subtitle')) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3" data-cta-group>
                        <label class="form-label">CTA Metni</label>
                        <select name="hero_button_text_preset" class="form-select" data-cta-select>
                            <?php foreach ($builderOptions['cta_presets'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('hero_button_text_preset', 'discover_now') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="hero_button_text_custom" class="form-control mt-2 d-none" value="<?= esc(old('hero_button_text_custom', old('hero_button_text'))) ?>" placeholder="Ozel CTA metni" data-cta-custom>
                        <input type="hidden" name="hero_button_text" value="<?= esc(old('hero_button_text', 'Simdi Kesfet')) ?>" data-cta-output>
                    </div>
                    <div class="col-lg-6 col-12 mb-3" data-link-group>
                        <label class="form-label">Yonlendirme</label>
                        <select name="hero_button_link_type" class="form-select" data-link-type>
                            <?php foreach ($builderOptions['link_types'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('hero_button_link_type', 'page') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="hero_button_link_target" class="form-select mt-2 d-none" data-link-target data-selected-value="<?= esc(old('hero_button_link_target')) ?>"></select>
                        <input type="text" name="hero_button_link_custom_url" class="form-control mt-2 d-none" value="<?= esc(old('hero_button_link_custom_url', old('hero_button_link'))) ?>" placeholder="https://ornek.com" data-link-custom-url>
                        <input type="hidden" name="hero_button_link" value="<?= esc(old('hero_button_link', '/')) ?>" data-link-output>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3">
                        <label class="form-label">Variant</label>
                        <select name="hero_variant" class="form-select">
                            <?php foreach ($builderOptions['hero_variants'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('hero_variant', 'light') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-6 col-12 mb-0">
                        <label class="form-label">Media</label>
                        <div class="card border shadow-none mb-2" data-media-group data-placeholder-label="Hero banner gorseli">
                            <div class="card-body">
                                <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                    <img src="" alt="Hero media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                                </div>
                                <div class="border rounded text-center p-4" data-media-placeholder>
                                    <i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i>
                                    <div class="fw-semibold mb-1">Gorsel yukleyin</div>
                                    <div class="small text-muted">Hero alani icin banner veya kapak gorseli secin.</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda Kirp</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                                </div>
                                <input type="file" class="d-none" accept="image/*" data-media-file>
                                <div class="mt-3">
                                    <label class="form-label small text-muted">Gelismis Media Path</label>
                                    <input type="text" name="hero_image_path" class="form-control" value="<?= esc(old('hero_image_path')) ?>" placeholder="/uploads/banner.jpg" data-media-path>
                                    <div class="form-text">Gercek upload sonraki sprintte eklenecek. Bu alanda path veya secilen dosya adi tutulur.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3 d-none" data-edit-block-form="best_sellers">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Best Sellers</h6>
                    <span class="badge bg-light-success">Commerce</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="best_sellers_title" class="form-control" value="<?= esc(old('best_sellers_title')) ?>">
                </div>
                <div class="mb-3" data-mode-group>
                    <label class="form-label">Mode</label>
                    <select name="best_sellers_mode" class="form-select" data-mode-select>
                        <?php foreach ($builderOptions['data_modes'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('best_sellers_mode', 'auto') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="alert alert-light border mb-3" data-mode-panel="auto">
                    <div class="small text-muted">Auto mod `top_selling` akisini kullanir. Manual mod secili urun listesiyle calisir.</div>
                </div>
                <div class="border rounded p-3 mb-3 d-none" data-mode-panel="manual">
                    <label class="form-label">Secili Urunler (MVP)</label>
                    <input type="text" name="best_sellers_selected_product_ids" class="form-control" value="<?= esc(old('best_sellers_selected_product_ids')) ?>" placeholder="101, 205, 330">
                    <div class="form-text">Bu sprintte ID listesiyle hazirlik yapiliyor.</div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3">
                        <label class="form-label">Item Limit</label>
                        <input type="number" name="best_sellers_item_limit" class="form-control" min="1" max="24" value="<?= esc(old('best_sellers_item_limit', '8')) ?>">
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label class="form-label">Sort Type</label>
                        <select name="best_sellers_sort_type" class="form-select">
                            <?php foreach ($builderOptions['best_sellers_sort_types'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('best_sellers_sort_type', 'sales_desc') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3">
                        <label class="form-label">Card Style</label>
                        <select name="best_sellers_card_style" class="form-select">
                            <?php foreach ($builderOptions['best_sellers_card_styles'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('best_sellers_card_style', 'classic') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-6 col-12 mb-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_best_sellers_show_badge" name="best_sellers_show_badge" <?= old('best_sellers_show_badge', '1') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="edit_best_sellers_show_badge">Badge goster</label>
                        </div>
                    </div>
                </div>
                <div class="alert alert-light border mb-0">
                    <div class="small text-muted">Preview stili bu secime gore degisir. Urun secici sonraki sprintte genisletilebilir.</div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3 d-none" data-edit-block-form="featured_products">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Featured Products</h6>
                    <span class="badge bg-light-primary">Showcase</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="featured_products_title" class="form-control" value="<?= esc(old('featured_products_title')) ?>">
                </div>
                <div class="mb-3" data-mode-group>
                    <label class="form-label">Mode</label>
                    <select name="featured_products_mode" class="form-select" data-mode-select>
                        <?php foreach ($builderOptions['data_modes'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('featured_products_mode', 'auto') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="alert alert-light border mb-3" data-mode-panel="auto">
                    <div class="small text-muted">Auto mod one cikan urun akisini kullanir. Manual mod secili urun havuzunu baz alir.</div>
                </div>
                <div class="border rounded p-3 mb-3 d-none" data-mode-panel="manual">
                    <label class="form-label">Secili Urunler (MVP)</label>
                    <input type="text" name="featured_products_selected_product_ids" class="form-control" value="<?= esc(old('featured_products_selected_product_ids')) ?>" placeholder="88, 144, 302">
                    <div class="form-text">Product picker sonraki sprintte baglanacak.</div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3">
                        <label class="form-label">Item Limit</label>
                        <input type="number" name="featured_products_item_limit" class="form-control" min="1" max="24" value="<?= esc(old('featured_products_item_limit', '6')) ?>">
                    </div>
                    <div class="col-lg-6 col-12 mb-0">
                        <label class="form-label">Variant</label>
                        <select name="featured_products_variant" class="form-select">
                            <?php foreach ($builderOptions['featured_variants'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('featured_products_variant', 'grid') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3 d-none" data-edit-block-form="campaign_banner">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Campaign Banner</h6>
                    <span class="badge bg-light-danger">Campaign</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="campaign_banner_title" class="form-control" value="<?= esc(old('campaign_banner_title')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Subtitle</label>
                    <textarea name="campaign_banner_subtitle" rows="3" class="form-control"><?= esc(old('campaign_banner_subtitle')) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3" data-cta-group>
                        <label class="form-label">CTA Metni</label>
                        <select name="campaign_banner_button_text_preset" class="form-select" data-cta-select>
                            <?php foreach ($builderOptions['cta_presets'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('campaign_banner_button_text_preset', 'view_campaign') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="campaign_banner_button_text_custom" class="form-control mt-2 d-none" value="<?= esc(old('campaign_banner_button_text_custom', old('campaign_banner_button_text'))) ?>" placeholder="Ozel CTA metni" data-cta-custom>
                        <input type="hidden" name="campaign_banner_button_text" value="<?= esc(old('campaign_banner_button_text', 'Kampanyayi Gor')) ?>" data-cta-output>
                    </div>
                    <div class="col-lg-6 col-12 mb-3" data-link-group>
                        <label class="form-label">Yonlendirme</label>
                        <select name="campaign_banner_button_link_type" class="form-select" data-link-type>
                            <?php foreach ($builderOptions['link_types'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('campaign_banner_button_link_type', 'campaign') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="campaign_banner_button_link_target" class="form-select mt-2 d-none" data-link-target data-selected-value="<?= esc(old('campaign_banner_button_link_target')) ?>"></select>
                        <input type="text" name="campaign_banner_button_link_custom_url" class="form-control mt-2 d-none" value="<?= esc(old('campaign_banner_button_link_custom_url', old('campaign_banner_button_link'))) ?>" placeholder="https://ornek.com" data-link-custom-url>
                        <input type="hidden" name="campaign_banner_button_link" value="<?= esc(old('campaign_banner_button_link', '/campaigns')) ?>" data-link-output>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Variant</label>
                    <select name="campaign_banner_variant" class="form-select">
                        <?php foreach ($builderOptions['campaign_variants'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('campaign_banner_variant', 'dark') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mt-3">
                    <label class="form-label">Media</label>
                    <div class="card border shadow-none mb-2" data-media-group data-placeholder-label="Kampanya gorseli">
                        <div class="card-body">
                            <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                <img src="" alt="Campaign media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                            </div>
                            <div class="border rounded text-center p-4" data-media-placeholder>
                                <i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i>
                                <div class="fw-semibold mb-1">Gorsel yukleyin</div>
                                <div class="small text-muted">Kampanya karti icin gorsel veya afis alani.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-primary" data-media-browse>Degistir</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                            </div>
                            <input type="file" class="d-none" accept="image/*" data-media-file>
                            <div class="mt-3">
                                <label class="form-label small text-muted">Gelismis Media Path</label>
                                <input type="text" name="campaign_banner_image_path" class="form-control" value="<?= esc(old('campaign_banner_image_path')) ?>" placeholder="/uploads/campaign.jpg" data-media-path>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3 d-none" data-edit-block-form="author_showcase">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Author Showcase</h6>
                    <span class="badge bg-light-info">Editorial</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="author_showcase_title" class="form-control" value="<?= esc(old('author_showcase_title')) ?>">
                </div>
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3">
                        <label class="form-label">Item Limit</label>
                        <input type="number" name="author_showcase_item_limit" class="form-control" min="1" max="24" value="<?= esc(old('author_showcase_item_limit', '4')) ?>">
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label class="form-label">Layout Type</label>
                        <select name="author_showcase_layout_type" class="form-select">
                            <?php foreach ($builderOptions['author_layout_types'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('author_showcase_layout_type', 'grid') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kisa Aciklama</label>
                    <textarea name="author_showcase_subtitle" rows="3" class="form-control"><?= esc(old('author_showcase_subtitle')) ?></textarea>
                </div>
                <div>
                    <label class="form-label">Author Media</label>
                    <div class="card border shadow-none mb-0" data-media-group data-placeholder-label="Yazar gorseli">
                        <div class="card-body">
                            <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                <img src="" alt="Author media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                            </div>
                            <div class="border rounded text-center p-4" data-media-placeholder>
                                <i class="ti ti-user-circle fs-2 text-muted d-block mb-2"></i>
                                <div class="fw-semibold mb-1">Yazar gorseli secin</div>
                                <div class="small text-muted">Avatar veya editoryal kapak hissi icin kullanilir.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-primary" data-media-browse>Degistir</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                            </div>
                            <input type="file" class="d-none" accept="image/*" data-media-file>
                            <div class="mt-3">
                                <label class="form-label small text-muted">Gelismis Media Path</label>
                                <input type="text" name="author_showcase_image_path" class="form-control" value="<?= esc(old('author_showcase_image_path')) ?>" placeholder="/uploads/author.jpg" data-media-path>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3 d-none" data-edit-block-form="category_grid">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Category Grid</h6>
                    <span class="badge bg-light-secondary">Discovery</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="category_grid_title" class="form-control" value="<?= esc(old('category_grid_title')) ?>">
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <label class="form-label">Item Limit</label>
                        <input type="number" name="category_grid_item_limit" class="form-control" min="1" max="12" value="<?= esc(old('category_grid_item_limit', '4')) ?>">
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <label class="form-label">Grid Type</label>
                        <select name="category_grid_grid_type" class="form-select">
                            <?php foreach ($builderOptions['category_grid_types'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('category_grid_grid_type', '4_col') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-12 mb-3">
                        <label class="form-label">Category Label</label>
                        <input type="text" name="category_grid_label" class="form-control" value="<?= esc(old('category_grid_label')) ?>" placeholder="Orn. Editorden Secmeler">
                    </div>
                </div>
                <div>
                    <label class="form-label">Grid Media</label>
                    <div class="card border shadow-none mb-0" data-media-group data-placeholder-label="Kategori grid gorseli">
                        <div class="card-body">
                            <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                <img src="" alt="Category grid media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                            </div>
                            <div class="border rounded text-center p-4" data-media-placeholder>
                                <i class="ti ti-category-plus fs-2 text-muted d-block mb-2"></i>
                                <div class="fw-semibold mb-1">Grid gorseli secin</div>
                                <div class="small text-muted">Kategori kutularinin gorsel dilini guclendiren placeholder alan.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-primary" data-media-browse>Degistir</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                            </div>
                            <input type="file" class="d-none" accept="image/*" data-media-file>
                            <div class="mt-3">
                                <label class="form-label small text-muted">Gelismis Media Path</label>
                                <input type="text" name="category_grid_image_path" class="form-control" value="<?= esc(old('category_grid_image_path')) ?>" placeholder="/uploads/category-grid.jpg" data-media-path>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3 d-none" data-edit-block-form="newsletter">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Newsletter</h6>
                    <span class="badge bg-light-primary">Engagement</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="newsletter_title" class="form-control" value="<?= esc(old('newsletter_title')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="newsletter_subtitle" rows="3" class="form-control"><?= esc(old('newsletter_subtitle')) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Input Placeholder</label>
                    <input type="text" name="newsletter_input_placeholder" class="form-control" value="<?= esc(old('newsletter_input_placeholder', 'E-posta adresiniz')) ?>">
                </div>
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3" data-cta-group>
                        <label class="form-label">CTA Metni</label>
                        <select name="newsletter_button_text_preset" class="form-select" data-cta-select>
                            <?php foreach ($builderOptions['cta_presets'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('newsletter_button_text_preset', 'start_now') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="newsletter_button_text_custom" class="form-control mt-2 d-none" value="<?= esc(old('newsletter_button_text_custom', old('newsletter_button_text'))) ?>" placeholder="Ozel CTA metni" data-cta-custom>
                        <input type="hidden" name="newsletter_button_text" value="<?= esc(old('newsletter_button_text', 'Hemen Basla')) ?>" data-cta-output>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">Variant</label>
                        <select name="newsletter_variant" class="form-select">
                            <?php foreach ($builderOptions['newsletter_variants'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('newsletter_variant', 'primary') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_newsletter_show_icon" name="newsletter_show_icon" <?= old('newsletter_show_icon', '1') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="edit_newsletter_show_icon">Icon goster</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3 d-none" data-edit-block-form="notice">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Notice</h6>
                    <span class="badge bg-light-warning">Alert</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="notice_title" class="form-control" value="<?= esc(old('notice_title')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="notice_content" rows="3" class="form-control"><?= esc(old('notice_content')) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <label class="form-label">Notice Type</label>
                        <select name="notice_notice_type" class="form-select">
                            <?php foreach ($builderOptions['notice_types'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('notice_notice_type', 'info') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <label class="form-label">Tone</label>
                        <select name="notice_tone" class="form-select">
                            <?php foreach ($builderOptions['notice_tones'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('notice_tone', 'soft') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-12 mb-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_notice_show_icon" name="notice_show_icon" <?= old('notice_show_icon', '1') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="edit_notice_show_icon">Icon goster</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3 d-none" data-edit-block-form="slider">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Slider</h6>
                    <span class="badge bg-light-primary">Carousel</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="slider_title" class="form-control" value="<?= esc(old('slider_title')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Subtitle</label>
                    <textarea name="slider_subtitle" rows="3" class="form-control"><?= esc(old('slider_subtitle')) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3" data-cta-group>
                        <label class="form-label">CTA Metni</label>
                        <select name="slider_button_text_preset" class="form-select" data-cta-select>
                            <?php foreach ($builderOptions['cta_presets'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('slider_button_text_preset', 'go_detail') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="slider_button_text_custom" class="form-control mt-2 d-none" value="<?= esc(old('slider_button_text_custom', old('slider_button_text'))) ?>" placeholder="Ozel CTA metni" data-cta-custom>
                        <input type="hidden" name="slider_button_text" value="<?= esc(old('slider_button_text', 'Detaya Git')) ?>" data-cta-output>
                    </div>
                    <div class="col-lg-6 col-12 mb-3" data-link-group>
                        <label class="form-label">Yonlendirme</label>
                        <select name="slider_button_link_type" class="form-select" data-link-type>
                            <?php foreach ($builderOptions['link_types'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= old('slider_button_link_type', 'page') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="slider_button_link_target" class="form-select mt-2 d-none" data-link-target data-selected-value="<?= esc(old('slider_button_link_target')) ?>"></select>
                        <input type="text" name="slider_button_link_custom_url" class="form-control mt-2 d-none" value="<?= esc(old('slider_button_link_custom_url', old('slider_button_link'))) ?>" placeholder="https://ornek.com" data-link-custom-url>
                        <input type="hidden" name="slider_button_link" value="<?= esc(old('slider_button_link', '/')) ?>" data-link-output>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Variant</label>
                    <select name="slider_variant" class="form-select">
                        <?php foreach ($builderOptions['slider_variants'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('slider_variant', 'light') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Media</label>
                    <div class="card border shadow-none mb-2" data-media-group data-placeholder-label="Slider gorseli">
                        <div class="card-body">
                            <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap>
                                <img src="" alt="Slider media preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image>
                            </div>
                            <div class="border rounded text-center p-4" data-media-placeholder>
                                <i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i>
                                <div class="fw-semibold mb-1">Slider gorseli secin</div>
                                <div class="small text-muted">Ilk asamada gorsel yolu veya dosya adi placeholder olarak saklanir.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-media-crop disabled>Yakinda Kirp</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button>
                            </div>
                            <input type="file" class="d-none" accept="image/*" data-media-file>
                            <div class="mt-3">
                                <label class="form-label small text-muted">Gelismis Media Path</label>
                                <input type="text" name="slider_image_path" class="form-control" value="<?= esc(old('slider_image_path')) ?>" placeholder="/uploads/slider.jpg" data-media-path>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-secondary d-none mb-3" data-edit-block-form="fallback">
                <div class="d-flex align-items-start gap-2">
                    <i class="ti ti-info-circle mt-1"></i>
                    <div>Bu block tipi icin bu sprintte sinirli duzenleme desteklenir. Kaydet islemi mevcut taslak uzerinde guvenli sekilde calisir.</div>
                </div>
            </div>

            <div class="alert alert-light border">
                <div class="d-flex align-items-start gap-2">
                    <i class="ti ti-photo mt-1"></i>
                    <div>
                        <div class="fw-semibold mb-1">Media Placeholder</div>
                        <div class="small text-muted mb-0">Gorsel alanlari simdilik path veya placeholder mantigiyla yonetilir. Ayrik media manager sonraki sprintlere birakildi.</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Kapat</button>
            <button type="submit" class="btn btn-primary" form="blockEditForm">
                <i class="ti ti-device-floppy me-1"></i> Kaydet
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    var linkTargetOptions = <?= json_encode($builderOptions['link_targets'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var ctaPresetLabels = <?= json_encode($builderOptions['cta_presets'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var addForm = document.getElementById('pageBuilderForm');
    var addSelect = document.getElementById('block_type_id');
    var editForm = document.getElementById('blockEditForm');
    var draftOffcanvasEl = document.getElementById('draftMetaOffcanvas');
    var editOffcanvasEl = document.getElementById('blockEditOffcanvas');
    var editButtons = document.querySelectorAll('.page-builder-edit-btn');
    var editBlockIdInput = document.getElementById('edit_block_id');
    var editBlockStateIdInput = document.getElementById('edit_block_state_id');
    var editBlockTypeCodeInput = document.getElementById('edit_block_type_code');
    var editBlockName = document.getElementById('editBlockName');
    var editBlockSummary = document.getElementById('editBlockSummary');
    var editBlockCodeBadge = document.getElementById('editBlockCodeBadge');
    var mediaGroups = document.querySelectorAll('[data-media-group]');
    var oldEditState = {
      blockId: <?= json_encode($editBlockId) ?>,
      blockTypeCode: <?= json_encode($editBlockTypeCode) ?>,
      values: {
        hero_title: <?= json_encode(old('hero_title')) ?>,
        hero_subtitle: <?= json_encode(old('hero_subtitle')) ?>,
        hero_button_text: <?= json_encode(old('hero_button_text')) ?>,
        hero_button_text_preset: <?= json_encode(old('hero_button_text_preset')) ?>,
        hero_button_text_custom: <?= json_encode(old('hero_button_text_custom')) ?>,
        hero_button_link: <?= json_encode(old('hero_button_link')) ?>,
        hero_button_link_type: <?= json_encode(old('hero_button_link_type')) ?>,
        hero_button_link_target: <?= json_encode(old('hero_button_link_target')) ?>,
        hero_button_link_custom_url: <?= json_encode(old('hero_button_link_custom_url')) ?>,
        hero_variant: <?= json_encode(old('hero_variant')) ?>,
        hero_image_path: <?= json_encode(old('hero_image_path')) ?>,
        best_sellers_title: <?= json_encode(old('best_sellers_title')) ?>,
        best_sellers_mode: <?= json_encode(old('best_sellers_mode')) ?>,
        best_sellers_item_limit: <?= json_encode(old('best_sellers_item_limit')) ?>,
        best_sellers_sort_type: <?= json_encode(old('best_sellers_sort_type')) ?>,
        best_sellers_show_badge: <?= json_encode(old('best_sellers_show_badge')) ?>,
        best_sellers_card_style: <?= json_encode(old('best_sellers_card_style')) ?>,
        best_sellers_selected_product_ids: <?= json_encode(old('best_sellers_selected_product_ids')) ?>,
        featured_products_title: <?= json_encode(old('featured_products_title')) ?>,
        featured_products_mode: <?= json_encode(old('featured_products_mode')) ?>,
        featured_products_item_limit: <?= json_encode(old('featured_products_item_limit')) ?>,
        featured_products_variant: <?= json_encode(old('featured_products_variant')) ?>,
        featured_products_selected_product_ids: <?= json_encode(old('featured_products_selected_product_ids')) ?>,
        campaign_banner_title: <?= json_encode(old('campaign_banner_title')) ?>,
        campaign_banner_subtitle: <?= json_encode(old('campaign_banner_subtitle')) ?>,
        campaign_banner_button_text: <?= json_encode(old('campaign_banner_button_text')) ?>,
        campaign_banner_button_text_preset: <?= json_encode(old('campaign_banner_button_text_preset')) ?>,
        campaign_banner_button_text_custom: <?= json_encode(old('campaign_banner_button_text_custom')) ?>,
        campaign_banner_button_link: <?= json_encode(old('campaign_banner_button_link')) ?>,
        campaign_banner_button_link_type: <?= json_encode(old('campaign_banner_button_link_type')) ?>,
        campaign_banner_button_link_target: <?= json_encode(old('campaign_banner_button_link_target')) ?>,
        campaign_banner_button_link_custom_url: <?= json_encode(old('campaign_banner_button_link_custom_url')) ?>,
        campaign_banner_variant: <?= json_encode(old('campaign_banner_variant')) ?>,
        campaign_banner_image_path: <?= json_encode(old('campaign_banner_image_path')) ?>,
        author_showcase_title: <?= json_encode(old('author_showcase_title')) ?>,
        author_showcase_item_limit: <?= json_encode(old('author_showcase_item_limit')) ?>,
        author_showcase_layout_type: <?= json_encode(old('author_showcase_layout_type')) ?>,
        author_showcase_subtitle: <?= json_encode(old('author_showcase_subtitle')) ?>,
        author_showcase_image_path: <?= json_encode(old('author_showcase_image_path')) ?>,
        newsletter_title: <?= json_encode(old('newsletter_title')) ?>,
        newsletter_subtitle: <?= json_encode(old('newsletter_subtitle')) ?>,
        newsletter_input_placeholder: <?= json_encode(old('newsletter_input_placeholder')) ?>,
        newsletter_button_text: <?= json_encode(old('newsletter_button_text')) ?>,
        newsletter_button_text_preset: <?= json_encode(old('newsletter_button_text_preset')) ?>,
        newsletter_button_text_custom: <?= json_encode(old('newsletter_button_text_custom')) ?>,
        newsletter_variant: <?= json_encode(old('newsletter_variant')) ?>,
        newsletter_show_icon: <?= json_encode(old('newsletter_show_icon')) ?>,
        notice_title: <?= json_encode(old('notice_title')) ?>,
        notice_content: <?= json_encode(old('notice_content')) ?>,
        notice_notice_type: <?= json_encode(old('notice_notice_type')) ?>,
        notice_tone: <?= json_encode(old('notice_tone')) ?>,
        notice_show_icon: <?= json_encode(old('notice_show_icon')) ?>,
        category_grid_title: <?= json_encode(old('category_grid_title')) ?>,
        category_grid_item_limit: <?= json_encode(old('category_grid_item_limit')) ?>,
        category_grid_grid_type: <?= json_encode(old('category_grid_grid_type')) ?>,
        category_grid_label: <?= json_encode(old('category_grid_label')) ?>,
        category_grid_image_path: <?= json_encode(old('category_grid_image_path')) ?>,
        slider_title: <?= json_encode(old('slider_title')) ?>,
        slider_subtitle: <?= json_encode(old('slider_subtitle')) ?>,
        slider_button_text: <?= json_encode(old('slider_button_text')) ?>,
        slider_button_text_preset: <?= json_encode(old('slider_button_text_preset')) ?>,
        slider_button_text_custom: <?= json_encode(old('slider_button_text_custom')) ?>,
        slider_button_link: <?= json_encode(old('slider_button_link')) ?>,
        slider_button_link_type: <?= json_encode(old('slider_button_link_type')) ?>,
        slider_button_link_target: <?= json_encode(old('slider_button_link_target')) ?>,
        slider_button_link_custom_url: <?= json_encode(old('slider_button_link_custom_url')) ?>,
        slider_variant: <?= json_encode(old('slider_variant')) ?>,
        slider_image_path: <?= json_encode(old('slider_image_path')) ?>
      }
    };
    var oldDraftMetaVersionId = <?= json_encode($draftMetaVersionId) ?>;

    function toggleScopedSections(container, attributeName, blockCode) {
      if (!container) {
        return;
      }

      var sections = container.querySelectorAll('[' + attributeName + ']');
      var hasDirectSection = !!container.querySelector('[' + attributeName + '="' + blockCode + '"]');

      sections.forEach(function (section) {
        var target = section.getAttribute(attributeName);
        var isFallback = target === 'fallback';
        var show = blockCode !== '' && (target === blockCode || (isFallback && !hasDirectSection));
        section.classList.toggle('d-none', !show);
      });
    }

    function setFieldValue(form, name, value) {
      var field = form ? form.querySelector('[name="' + name + '"]') : null;
      if (!field) {
        return;
      }

      if (field.type === 'checkbox') {
        field.checked = value === true || value === 1 || value === '1';
        return;
      }

      field.value = value == null ? '' : value;
    }

    function syncCtaGroup(group) {
      if (!group) {
        return;
      }

      var select = group.querySelector('[data-cta-select]');
      var customInput = group.querySelector('[data-cta-custom]');
      var output = group.querySelector('[data-cta-output]');
      var preset = select ? select.value : '';
      var label = ctaPresetLabels[preset] || '';
      var isCustom = preset === 'custom';

      if (customInput) {
        customInput.classList.toggle('d-none', !isCustom);
      }
      if (output) {
        output.value = isCustom ? (customInput ? customInput.value.trim() : '') : label;
      }
    }

    function populateLinkTargets(targetSelect, type, selectedValue) {
      if (!targetSelect) {
        return;
      }

      var options = linkTargetOptions[type] || {};
      targetSelect.innerHTML = '';
      Object.keys(options).forEach(function (value) {
        var option = document.createElement('option');
        option.value = value;
        option.textContent = options[value];
        if (selectedValue === value) {
          option.selected = true;
        }
        targetSelect.appendChild(option);
      });
    }

    function syncLinkGroup(group) {
      if (!group) {
        return;
      }

      var typeSelect = group.querySelector('[data-link-type]');
      var targetSelect = group.querySelector('[data-link-target]');
      var customInput = group.querySelector('[data-link-custom-url]');
      var output = group.querySelector('[data-link-output]');
      var type = typeSelect ? typeSelect.value : 'custom_url';
      var selectedTarget = targetSelect ? targetSelect.getAttribute('data-selected-value') || targetSelect.value : '';

      if (targetSelect) {
        populateLinkTargets(targetSelect, type, selectedTarget);
      }

      var isCustom = type === 'custom_url';
      if (targetSelect) {
        targetSelect.classList.toggle('d-none', isCustom || !linkTargetOptions[type]);
      }
      if (customInput) {
        customInput.classList.toggle('d-none', !isCustom);
      }
      if (output) {
        output.value = isCustom ? (customInput ? customInput.value.trim() : '') : (targetSelect ? targetSelect.value : '');
      }
    }

    function syncFormEnhancements(container) {
      if (!container) {
        return;
      }

      container.querySelectorAll('[data-cta-group]').forEach(function (group) {
        syncCtaGroup(group);
      });
      container.querySelectorAll('[data-link-group]').forEach(function (group) {
        syncLinkGroup(group);
      });
      container.querySelectorAll('[data-mode-group]').forEach(function (group) {
        syncModeGroup(group);
      });
    }

    function syncModeGroup(group) {
      if (!group) {
        return;
      }

      var select = group.querySelector('[data-mode-select]');
      if (!select) {
        return;
      }

      var wrapper = group.parentElement;
      if (!wrapper) {
        return;
      }

      wrapper.querySelectorAll('[data-mode-panel]').forEach(function (panel) {
        panel.classList.toggle('d-none', panel.getAttribute('data-mode-panel') !== select.value);
      });
    }

    function detectSectionKey(node) {
      var names = Array.from(node.querySelectorAll('[name]')).map(function (field) {
        return field.getAttribute('name') || '';
      }).join(' ');

      if (node.querySelector('[data-media-group]') || /image_path|selected_product_ids/.test(names)) {
        return 'advanced';
      }

      if (/button_|subtitle|content|input_placeholder/.test(names)) {
        return 'content';
      }

      if (/variant|layout_type|grid_type|card_style|tone|notice_type|show_badge|show_icon|sort_type|align|spacing/.test(names)) {
        return 'appearance';
      }

      return 'basic';
    }

    function createSectionCard(title, targetId, expanded) {
      var wrapper = document.createElement('div');
      wrapper.className = 'card border shadow-none mb-3';
      wrapper.innerHTML =
        '<div class="card-header py-2">' +
          '<a class="d-flex align-items-center justify-content-between text-decoration-none fw-semibold" data-bs-toggle="collapse" href="#' + targetId + '" role="button" aria-expanded="' + (expanded ? 'true' : 'false') + '">' +
            '<span>' + title + '</span>' +
            '<span class="badge bg-light-secondary">Ayar</span>' +
          '</a>' +
        '</div>' +
        '<div id="' + targetId + '" class="collapse' + (expanded ? ' show' : '') + '">' +
          '<div class="card-body pt-3"></div>' +
        '</div>';

      return wrapper;
    }

    function enhanceEditSections() {
      document.querySelectorAll('[data-edit-block-form]').forEach(function (formSection, index) {
        if (formSection.getAttribute('data-sections-enhanced') === '1') {
          return;
        }

        var children = Array.from(formSection.children);
        if (children.length <= 2) {
          return;
        }

        var header = children.shift();
        var buckets = {
          basic: [],
          appearance: [],
          content: [],
          advanced: []
        };

        children.forEach(function (child) {
          if (!(child instanceof HTMLElement)) {
            return;
          }
          buckets[detectSectionKey(child)].push(child);
        });

        formSection.innerHTML = '';
        formSection.appendChild(header);

        [
          ['basic', 'Temel Ayarlar', true],
          ['content', 'Icerik Ayarlari', true],
          ['appearance', 'Gorunum Ayarlari', false],
          ['advanced', 'Gelismis Ayarlar', false]
        ].forEach(function (item) {
          var key = item[0];
          if (buckets[key].length === 0) {
            return;
          }

          var card = createSectionCard(item[1], 'builder-section-' + index + '-' + key, item[2]);
          var body = card.querySelector('.card-body');
          buckets[key].forEach(function (node) {
            body.appendChild(node);
          });
          formSection.appendChild(card);
        });

        formSection.setAttribute('data-sections-enhanced', '1');
      });
    }

    function fillEditForm(config, blockCode) {
      if (!editForm) {
        return;
      }

      setFieldValue(editForm, 'hero_title', config.title);
      setFieldValue(editForm, 'hero_subtitle', config.subtitle);
      setFieldValue(editForm, 'hero_button_text', config.button_text);
      setFieldValue(editForm, 'hero_button_text_preset', config.button_text_preset || 'custom');
      setFieldValue(editForm, 'hero_button_text_custom', config.button_text_preset && config.button_text_preset !== 'custom' ? '' : (config.button_text || ''));
      setFieldValue(editForm, 'hero_button_link', config.button_link);
      setFieldValue(editForm, 'hero_button_link_type', config.button_link_type || 'custom_url');
      var heroTarget = editForm.querySelector('[name="hero_button_link_target"]');
      if (heroTarget) {
        heroTarget.setAttribute('data-selected-value', config.button_link_target || '');
      }
      setFieldValue(editForm, 'hero_button_link_custom_url', config.button_link_custom_url || config.button_link || '');
      setFieldValue(editForm, 'hero_variant', config.variant || 'light');
      setFieldValue(editForm, 'hero_image_path', config.image_path);

      setFieldValue(editForm, 'best_sellers_title', config.title);
      setFieldValue(editForm, 'best_sellers_mode', config.mode || 'auto');
      setFieldValue(editForm, 'best_sellers_item_limit', config.item_limit || 8);
      setFieldValue(editForm, 'best_sellers_sort_type', config.sort_type || 'sales_desc');
      setFieldValue(editForm, 'best_sellers_show_badge', config.show_badge);
      setFieldValue(editForm, 'best_sellers_card_style', config.card_style || 'classic');
      setFieldValue(editForm, 'best_sellers_selected_product_ids', Array.isArray(config.selected_product_ids) ? config.selected_product_ids.join(', ') : '');

      setFieldValue(editForm, 'featured_products_title', config.title);
      setFieldValue(editForm, 'featured_products_mode', config.mode || 'auto');
      setFieldValue(editForm, 'featured_products_item_limit', config.item_limit || 6);
      setFieldValue(editForm, 'featured_products_variant', config.variant || 'grid');
      setFieldValue(editForm, 'featured_products_selected_product_ids', Array.isArray(config.selected_product_ids) ? config.selected_product_ids.join(', ') : '');

      setFieldValue(editForm, 'campaign_banner_title', config.title);
      setFieldValue(editForm, 'campaign_banner_subtitle', config.subtitle);
      setFieldValue(editForm, 'campaign_banner_button_text', config.button_text);
      setFieldValue(editForm, 'campaign_banner_button_text_preset', config.button_text_preset || 'custom');
      setFieldValue(editForm, 'campaign_banner_button_text_custom', config.button_text_preset && config.button_text_preset !== 'custom' ? '' : (config.button_text || ''));
      setFieldValue(editForm, 'campaign_banner_button_link', config.button_link);
      setFieldValue(editForm, 'campaign_banner_button_link_type', config.button_link_type || 'custom_url');
      var campaignTarget = editForm.querySelector('[name="campaign_banner_button_link_target"]');
      if (campaignTarget) {
        campaignTarget.setAttribute('data-selected-value', config.button_link_target || '');
      }
      setFieldValue(editForm, 'campaign_banner_button_link_custom_url', config.button_link_custom_url || config.button_link || '');
      setFieldValue(editForm, 'campaign_banner_variant', config.variant || 'dark');
      setFieldValue(editForm, 'campaign_banner_image_path', config.image_path);

      setFieldValue(editForm, 'author_showcase_title', config.title);
      setFieldValue(editForm, 'author_showcase_item_limit', config.item_limit || 4);
      setFieldValue(editForm, 'author_showcase_layout_type', config.layout_type || 'grid');
      setFieldValue(editForm, 'author_showcase_subtitle', config.subtitle);
      setFieldValue(editForm, 'author_showcase_image_path', config.image_path);

      setFieldValue(editForm, 'newsletter_title', config.title);
      setFieldValue(editForm, 'newsletter_subtitle', config.subtitle);
      setFieldValue(editForm, 'newsletter_input_placeholder', config.input_placeholder || 'E-posta adresiniz');
      setFieldValue(editForm, 'newsletter_button_text', config.button_text);
      setFieldValue(editForm, 'newsletter_button_text_preset', config.button_text_preset || 'custom');
      setFieldValue(editForm, 'newsletter_button_text_custom', config.button_text_preset && config.button_text_preset !== 'custom' ? '' : (config.button_text || ''));
      setFieldValue(editForm, 'newsletter_variant', config.variant || 'primary');
      setFieldValue(editForm, 'newsletter_show_icon', !Object.prototype.hasOwnProperty.call(config, 'show_icon') || config.show_icon);

      setFieldValue(editForm, 'notice_title', config.title);
      setFieldValue(editForm, 'notice_content', config.content || config.subtitle);
      setFieldValue(editForm, 'notice_notice_type', config.notice_type || 'info');
      setFieldValue(editForm, 'notice_tone', config.tone || 'soft');
      setFieldValue(editForm, 'notice_show_icon', !Object.prototype.hasOwnProperty.call(config, 'show_icon') || config.show_icon);

      setFieldValue(editForm, 'category_grid_title', config.title);
      setFieldValue(editForm, 'category_grid_item_limit', config.item_limit || 4);
      setFieldValue(editForm, 'category_grid_grid_type', config.grid_type || '4_col');
      setFieldValue(editForm, 'category_grid_label', config.label);
      setFieldValue(editForm, 'category_grid_image_path', config.image_path);

      setFieldValue(editForm, 'slider_title', config.title);
      setFieldValue(editForm, 'slider_subtitle', config.subtitle);
      setFieldValue(editForm, 'slider_button_text', config.button_text);
      setFieldValue(editForm, 'slider_button_text_preset', config.button_text_preset || 'custom');
      setFieldValue(editForm, 'slider_button_text_custom', config.button_text_preset && config.button_text_preset !== 'custom' ? '' : (config.button_text || ''));
      setFieldValue(editForm, 'slider_button_link', config.button_link);
      setFieldValue(editForm, 'slider_button_link_type', config.button_link_type || 'custom_url');
      var sliderTarget = editForm.querySelector('[name="slider_button_link_target"]');
      if (sliderTarget) {
        sliderTarget.setAttribute('data-selected-value', config.button_link_target || '');
      }
      setFieldValue(editForm, 'slider_button_link_custom_url', config.button_link_custom_url || config.button_link || '');
      setFieldValue(editForm, 'slider_variant', config.variant || 'light');
      setFieldValue(editForm, 'slider_image_path', config.image_path);

      toggleScopedSections(editForm, 'data-edit-block-form', blockCode);

      syncFormEnhancements(editForm);
      refreshAllMediaGroups();
    }

      function refreshMediaGroup(group, previewSrc) {
        if (!group) {
          return;
        }

        var pathInput = group.querySelector('[data-media-path]');
        var previewWrap = group.querySelector('[data-media-preview-wrap]');
        var previewImage = group.querySelector('[data-media-preview-image]');
        var placeholder = group.querySelector('[data-media-placeholder]');
        var placeholderLabel = group.getAttribute('data-placeholder-label') || 'Gorsel yukleyin';
        var value = previewSrc || (pathInput ? pathInput.value.trim() : '');

        if (previewImage) {
          previewImage.src = '';
        }

        if (value !== '') {
          if (previewImage) {
            previewImage.src = value;
          }
          if (previewWrap) {
            previewWrap.classList.remove('d-none');
          }
          if (placeholder) {
            placeholder.classList.add('d-none');
          }
        } else {
          if (previewWrap) {
            previewWrap.classList.add('d-none');
          }
      if (placeholder) {
        placeholder.classList.remove('d-none');
        var placeholderTitle = placeholder.querySelector('.fw-semibold');
            if (placeholderTitle) {
              placeholderTitle.textContent = placeholderLabel;
            }
          }
        }
      }

      function refreshAllMediaGroups() {
        mediaGroups.forEach(function (group) {
          refreshMediaGroup(group);
        });
      }

    function applyEditButtonState(button, overrideConfig) {
      if (!button) {
        return;
      }

      var blockId = button.getAttribute('data-block-id') || '';
      var blockNameText = button.getAttribute('data-block-name') || 'Block';
      var blockCode = button.getAttribute('data-block-code') || '';
      var blockSummaryText = button.getAttribute('data-block-summary') || 'Varsayilan ayarlar';
      var rawConfig = button.getAttribute('data-block-config') || '{}';
      var config = {};

      try {
        config = JSON.parse(rawConfig);
      } catch (e) {
        config = {};
      }

      if (overrideConfig) {
        config = overrideConfig;
      }

      if (editBlockIdInput) {
        editBlockIdInput.value = blockId;
      }
      if (editBlockStateIdInput) {
        editBlockStateIdInput.value = blockId;
      }
      if (editBlockTypeCodeInput) {
        editBlockTypeCodeInput.value = blockCode;
      }
      if (editBlockName) {
        editBlockName.textContent = blockNameText;
      }
      if (editBlockSummary) {
        editBlockSummary.textContent = blockSummaryText;
      }
      if (editBlockCodeBadge) {
        editBlockCodeBadge.textContent = blockCode || '-';
      }

      fillEditForm(config, blockCode);
    }

    if (addSelect) {
      addSelect.addEventListener('change', function () {
        var selected = addSelect.options[addSelect.selectedIndex];
        var blockCode = selected ? selected.getAttribute('data-block-code') : '';
        toggleScopedSections(addForm, 'data-block-form', blockCode);
        syncFormEnhancements(addForm);
        refreshAllMediaGroups();
      });

      var initialSelected = addSelect.options[addSelect.selectedIndex];
      toggleScopedSections(addForm, 'data-block-form', initialSelected ? initialSelected.getAttribute('data-block-code') : '');
    }

    editButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        applyEditButtonState(button);
      });
    });

    mediaGroups.forEach(function (group) {
      var browseBtn = group.querySelector('[data-media-browse]');
      var clearBtn = group.querySelector('[data-media-clear]');
      var fileInput = group.querySelector('[data-media-file]');
      var pathInput = group.querySelector('[data-media-path]');

      if (browseBtn && fileInput) {
        browseBtn.addEventListener('click', function () {
          fileInput.click();
        });
      }

      if (clearBtn && pathInput) {
        clearBtn.addEventListener('click', function () {
          pathInput.value = '';
          if (fileInput) {
            fileInput.value = '';
          }
          refreshMediaGroup(group);
        });
      }

      if (fileInput) {
        fileInput.addEventListener('change', function () {
          if (!fileInput.files || !fileInput.files[0]) {
            refreshMediaGroup(group);
            return;
          }

          var file = fileInput.files[0];
          if (pathInput && pathInput.value.trim() === '') {
            pathInput.value = file.name;
          }

          refreshMediaGroup(group, URL.createObjectURL(file));
        });
      }

      if (pathInput) {
        pathInput.addEventListener('input', function () {
          refreshMediaGroup(group);
        });
      }
    });

    document.querySelectorAll('[data-cta-group]').forEach(function (group) {
      var select = group.querySelector('[data-cta-select]');
      var customInput = group.querySelector('[data-cta-custom]');

      if (select) {
        select.addEventListener('change', function () {
          syncCtaGroup(group);
        });
      }

      if (customInput) {
        customInput.addEventListener('input', function () {
          syncCtaGroup(group);
        });
      }
    });

    document.querySelectorAll('[data-link-group]').forEach(function (group) {
      var typeSelect = group.querySelector('[data-link-type]');
      var targetSelect = group.querySelector('[data-link-target]');
      var customInput = group.querySelector('[data-link-custom-url]');

      if (typeSelect) {
        typeSelect.addEventListener('change', function () {
          if (targetSelect) {
            targetSelect.setAttribute('data-selected-value', '');
          }
          syncLinkGroup(group);
        });
      }

      if (targetSelect) {
        targetSelect.addEventListener('change', function () {
          syncLinkGroup(group);
        });
      }

      if (customInput) {
        customInput.addEventListener('input', function () {
          syncLinkGroup(group);
        });
      }
    });

    document.querySelectorAll('[data-mode-group]').forEach(function (group) {
      var select = group.querySelector('[data-mode-select]');
      if (select) {
        select.addEventListener('change', function () {
          syncModeGroup(group);
        });
      }
    });

    enhanceEditSections();

    if (oldEditState.blockId && editOffcanvasEl && window.bootstrap && window.bootstrap.Offcanvas) {
      var activeButton = document.querySelector('.page-builder-edit-btn[data-block-id="' + oldEditState.blockId + '"]');
      var offcanvas = window.bootstrap.Offcanvas.getOrCreateInstance(editOffcanvasEl);
      var oldValues = oldEditState.values || {};
      applyEditButtonState(activeButton, oldEditState.blockTypeCode ? oldValues : null);
      offcanvas.show();
    }

    if (oldDraftMetaVersionId && draftOffcanvasEl && window.bootstrap && window.bootstrap.Offcanvas) {
      var draftOffcanvas = window.bootstrap.Offcanvas.getOrCreateInstance(draftOffcanvasEl);
      draftOffcanvas.show();
    }

    syncFormEnhancements(addForm);
    syncFormEnhancements(editForm);
    refreshAllMediaGroups();
  })();
</script>
<?= $this->endSection() ?>
