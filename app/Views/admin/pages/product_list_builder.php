<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$draftName = trim((string) ($draft['name'] ?? ('Draft ' . (string) ($draft['version_no'] ?? 1))));
$draftStatus = trim((string) ($draft['status'] ?? 'DRAFT'));
$config = is_array($productListConfig ?? null) ? $productListConfig : [];
$scheduledPublishValue = trim((string) ($draft['scheduled_publish_at'] ?? ''));
$scheduledPublishInputValue = $scheduledPublishValue !== '' ? date('Y-m-d\TH:i', strtotime($scheduledPublishValue)) : '';
$gridDensity = (string) ($config['grid_density'] ?? '3');
$gridColClass = $gridDensity === '2' ? 'col-md-6' : ($gridDensity === '4' ? 'col-xl-3 col-md-6' : 'col-lg-4 col-md-6');
?>

<div class="page-header">
    <div class="page-block">
        <div class="row">
            <div class="col-12"><div class="page-header-title"><h2 class="mb-0"><?= esc($page['name']) ?> Builder</h2></div></div>
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
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-light-primary"><?= esc($draftStatus) ?></span>
                        <span class="badge bg-light-secondary"><?= esc($page['code']) ?></span>
                        <span class="badge bg-light-success">Product List Template</span>
                    </div>
                    <h4 class="mb-1">Product List Specific Builder</h4>
                    <p class="text-muted mb-0"><?= esc((string) ($builderPolicy['message'] ?? 'Urun listeleme sayfasi kontrollu section ayarlariyla yonetilir.')) ?></p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#draftMetaOffcanvas">Taslak Islemleri</button>
                    <a href="<?= site_url('admin/pages/' . $page['code'] . '/drafts') ?>" class="btn btn-outline-primary">Draftlar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-5 col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-1">Template Ayarlari</h5>
                <p class="text-muted mb-0">Hazir urun listeleme sablonunun ana section'larini kontrollu sekilde yonetin.</p>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
                <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>

                <div class="alert alert-light border">
                    <div class="fw-semibold mb-1"><?= esc((string) ($builderPolicy['title'] ?? 'Product list policy')) ?></div>
                    <div class="small mb-0"><?= esc((string) ($builderPolicy['message'] ?? 'Bu sayfa generic block builder yerine page-specific config ile yonetilir.')) ?></div>
                </div>

                <form action="<?= site_url('admin/pages/product-list-builder/update') ?>" method="post" id="productListBuilderForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                    <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">

                    <div class="card border shadow-none mb-3">
                        <div class="card-header"><h6 class="mb-0">Sayfa Ust Alani</h6></div>
                        <div class="card-body">
                            <div class="mb-3"><label class="form-label">Page Title</label><input type="text" name="page_title" class="form-control" value="<?= esc(old('page_title', (string) ($config['page_title'] ?? ''))) ?>"></div>
                            <div class="mb-3"><label class="form-label">Page Subtitle</label><textarea name="page_subtitle" rows="3" class="form-control"><?= esc(old('page_subtitle', (string) ($config['page_subtitle'] ?? ''))) ?></textarea></div>
                            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="show_breadcrumb" name="show_breadcrumb" value="1" <?= old('show_breadcrumb', ! empty($config['show_breadcrumb'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_breadcrumb">Breadcrumb goster</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="show_top_banner" name="show_top_banner" value="1" <?= old('show_top_banner', ! empty($config['show_top_banner'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_top_banner">Ust banner goster</label></div>
                        </div>
                    </div>

                    <div class="card border shadow-none mb-3">
                        <div class="card-header"><h6 class="mb-0">Gorsel / Banner Ayarlari</h6></div>
                        <div class="card-body">
                            <div class="mb-3"><label class="form-label">Banner Title</label><input type="text" name="banner_title" class="form-control" value="<?= esc(old('banner_title', (string) ($config['banner_title'] ?? ''))) ?>"></div>
                            <div class="mb-3"><label class="form-label">Banner Subtitle</label><textarea name="banner_subtitle" rows="3" class="form-control"><?= esc(old('banner_subtitle', (string) ($config['banner_subtitle'] ?? ''))) ?></textarea></div>
                            <div class="card border shadow-none mb-3" data-media-group>
                                <div class="card-body">
                                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap><img src="" alt="Banner preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image></div>
                                    <div class="border rounded text-center p-4" data-media-placeholder><i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i><div class="fw-semibold mb-1">Banner gorseli secin</div><div class="small text-muted">Listeleme ust alani gorseli.</div></div>
                                    <div class="d-flex flex-wrap gap-2 mt-3"><button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button><button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button></div>
                                    <input type="file" class="d-none" accept="image/*" data-media-file>
                                    <div class="mt-3"><label class="form-label small text-muted">Media Path</label><input type="text" name="banner_image" class="form-control" value="<?= esc(old('banner_image', (string) ($config['banner_image'] ?? ''))) ?>" placeholder="/uploads/product-list-banner.jpg" data-media-path></div>
                                </div>
                            </div>
                            <div class="mb-0"><label class="form-label">Notice Image</label><input type="text" name="notice_image" class="form-control" value="<?= esc(old('notice_image', (string) ($config['notice_image'] ?? ''))) ?>" placeholder="/uploads/product-list-notice.jpg"></div>
                        </div>
                    </div>

                    <div class="card border shadow-none mb-3">
                        <div class="card-header"><h6 class="mb-0">Filtre ve Toolbar</h6></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Filter Position</label><select name="filter_position" class="form-select"><option value="left" <?= old('filter_position', (string) ($config['filter_position'] ?? 'left')) === 'left' ? 'selected' : '' ?>>Left</option><option value="top" <?= old('filter_position', (string) ($config['filter_position'] ?? 'left')) === 'top' ? 'selected' : '' ?>>Top</option></select></div>
                                <div class="col-md-6"><label class="form-label">Default Grid Density</label><select name="default_grid_density" class="form-select"><option value="2" <?= old('default_grid_density', (string) ($config['default_grid_density'] ?? '3')) === '2' ? 'selected' : '' ?>>2 Kolon</option><option value="3" <?= old('default_grid_density', (string) ($config['default_grid_density'] ?? '3')) === '3' ? 'selected' : '' ?>>3 Kolon</option><option value="4" <?= old('default_grid_density', (string) ($config['default_grid_density'] ?? '3')) === '4' ? 'selected' : '' ?>>4 Kolon</option></select></div>
                            </div>
                            <div class="row g-2 mt-1">
                                <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="show_filters" name="show_filters" value="1" <?= old('show_filters', ! empty($config['show_filters'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_filters">Filtre panelini goster</label></div></div>
                                <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="show_filter_summary" name="show_filter_summary" value="1" <?= old('show_filter_summary', ! empty($config['show_filter_summary'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_filter_summary">Filtre ozetini goster</label></div></div>
                                <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="show_sort_bar" name="show_sort_bar" value="1" <?= old('show_sort_bar', ! empty($config['show_sort_bar'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_sort_bar">Siralama cubugunu goster</label></div></div>
                                <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="show_result_count" name="show_result_count" value="1" <?= old('show_result_count', ! empty($config['show_result_count'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_result_count">Sonuc sayisini goster</label></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="card border shadow-none mb-3">
                        <div class="card-header"><h6 class="mb-0">Urun Grid Ayarlari</h6></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Card Variant</label><select name="card_variant" class="form-select"><option value="classic" <?= old('card_variant', (string) ($config['card_variant'] ?? 'classic')) === 'classic' ? 'selected' : '' ?>>Classic</option><option value="minimal" <?= old('card_variant', (string) ($config['card_variant'] ?? 'classic')) === 'minimal' ? 'selected' : '' ?>>Minimal</option><option value="elevated" <?= old('card_variant', (string) ($config['card_variant'] ?? 'classic')) === 'elevated' ? 'selected' : '' ?>>Elevated</option></select></div>
                                <div class="col-md-6"><label class="form-label">Grid Density</label><select name="grid_density" class="form-select"><option value="2" <?= old('grid_density', (string) ($config['grid_density'] ?? '3')) === '2' ? 'selected' : '' ?>>2 Kolon</option><option value="3" <?= old('grid_density', (string) ($config['grid_density'] ?? '3')) === '3' ? 'selected' : '' ?>>3 Kolon</option><option value="4" <?= old('grid_density', (string) ($config['grid_density'] ?? '3')) === '4' ? 'selected' : '' ?>>4 Kolon</option></select></div>
                            </div>
                            <div class="row g-2 mt-1">
                                <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="show_badges" name="show_badges" value="1" <?= old('show_badges', ! empty($config['show_badges'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_badges">Badge goster</label></div></div>
                                <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="show_quick_actions" name="show_quick_actions" value="1" <?= old('show_quick_actions', ! empty($config['show_quick_actions'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_quick_actions">Hizli aksiyonlar</label></div></div>
                                <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="show_favorite_button" name="show_favorite_button" value="1" <?= old('show_favorite_button', ! empty($config['show_favorite_button'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_favorite_button">Favori butonu</label></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="card border shadow-none mb-3">
                        <div class="card-header"><h6 class="mb-0">Bos Durum ve Notice</h6></div>
                        <div class="card-body">
                            <div class="mb-3"><label class="form-label">Empty Title</label><input type="text" name="empty_title" class="form-control" value="<?= esc(old('empty_title', (string) ($config['empty_title'] ?? ''))) ?>"></div>
                            <div class="mb-3"><label class="form-label">Empty Description</label><textarea name="empty_description" rows="3" class="form-control"><?= esc(old('empty_description', (string) ($config['empty_description'] ?? ''))) ?></textarea></div>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Empty Tone</label><select name="empty_notice_tone" class="form-select"><?php foreach (['info'=>'Info','success'=>'Success','warning'=>'Warning','danger'=>'Danger'] as $value => $label): ?><option value="<?= esc($value) ?>" <?= old('empty_notice_tone', (string) ($config['empty_notice_tone'] ?? 'warning')) === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
                                <div class="col-md-6 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" id="show_notice" name="show_notice" value="1" <?= old('show_notice', ! empty($config['show_notice'])) ? 'checked' : '' ?>><label class="form-check-label" for="show_notice">Notice alani acik</label></div></div>
                            </div>
                            <div class="mb-3 mt-3"><label class="form-label">Notice Title</label><input type="text" name="notice_title" class="form-control" value="<?= esc(old('notice_title', (string) ($config['notice_title'] ?? ''))) ?>"></div>
                            <div class="mb-3"><label class="form-label">Notice Text</label><textarea name="notice_text" rows="3" class="form-control"><?= esc(old('notice_text', (string) ($config['notice_text'] ?? ''))) ?></textarea></div>
                            <div class="mb-0"><label class="form-label">Notice Tone</label><select name="notice_tone" class="form-select"><?php foreach (['info'=>'Info','success'=>'Success','warning'=>'Warning','danger'=>'Danger'] as $value => $label): ?><option value="<?= esc($value) ?>" <?= old('notice_tone', (string) ($config['notice_tone'] ?? 'info')) === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
                        </div>
                    </div>

                    <div class="d-grid"><button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Product List Ayarlarini Kaydet</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xxl-7 col-xl-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-1">Mini Preview</h5><p class="text-muted mb-0">Sablon uzerindeki degisikliklerin mini etkisini gorun.</p></div>
            <div class="card-body">
                <?php if (! empty($config['show_breadcrumb'])): ?><div class="small text-muted mb-3">Ana Sayfa / Kategoriler / <?= esc((string) ($config['page_title'] ?? 'Kategori Sayfasi')) ?></div><?php endif; ?>
                <div class="card border shadow-none mb-3"><div class="card-body"><div class="d-flex flex-wrap align-items-center justify-content-between gap-3"><div><h4 class="mb-1"><?= esc((string) ($config['page_title'] ?? 'Kategori Sayfasi')) ?></h4><p class="text-muted mb-0"><?= esc((string) ($config['page_subtitle'] ?? 'One cikan urunleri ve filtreleri duzenleyin')) ?></p></div><?php if (! empty($config['show_result_count'])): ?><span class="badge bg-light-secondary">128 sonuc</span><?php endif; ?></div></div></div>
                <?php if (! empty($config['show_top_banner'])): ?><div class="card border-0 bg-light-primary mb-3"><div class="card-body"><div class="row g-3 align-items-center"><div class="col-md-7"><span class="badge bg-light-secondary mb-2">Top Banner</span><h5 class="mb-1"><?= esc((string) ($config['banner_title'] ?? 'Secili Kategori')) ?></h5><p class="text-muted mb-0"><?= esc((string) ($config['banner_subtitle'] ?? 'Listeleme banner alani')) ?></p></div><div class="col-md-5"><div class="card border shadow-none mb-0"><div class="card-body text-center"><?php if (trim((string) ($config['banner_image'] ?? '')) !== ''): ?><div class="ratio ratio-16x9 mb-2"><img src="<?= esc((string) $config['banner_image'], 'attr') ?>" alt="Banner preview" class="img-fluid rounded object-fit-cover"></div><?php else: ?><i class="ti ti-photo fs-2 text-muted d-block mb-2"></i><div class="small text-muted">Banner media preview</div><?php endif; ?></div></div></div></div></div></div><?php endif; ?>
                <div class="card border shadow-none mb-3"><div class="card-body"><?php if (($config['filter_position'] ?? 'left') === 'top' && ! empty($config['show_filters'])): ?><div class="card border shadow-none mb-3"><div class="card-body py-2"><div class="d-flex flex-wrap gap-2"><span class="badge bg-light-secondary">Yazar</span><span class="badge bg-light-secondary">Yayinevi</span><span class="badge bg-light-secondary">Fiyat</span><?php if (! empty($config['show_filter_summary'])): ?><span class="badge bg-light-primary">3 aktif filtre</span><?php endif; ?></div></div></div><?php endif; ?><div class="row g-3"><?php if (($config['filter_position'] ?? 'left') === 'left' && ! empty($config['show_filters'])): ?><div class="col-lg-3"><div class="card border shadow-none h-100 mb-0"><div class="card-body"><div class="fw-semibold mb-2">Filtre Paneli</div><div class="small text-muted mb-2">Kategori secenekleri</div><div class="small text-muted mb-2">Fiyat araligi</div><div class="small text-muted">Stok durumu</div></div></div></div><div class="col-lg-9"><?php else: ?><div class="col-12"><?php endif; ?><?php if (! empty($config['show_sort_bar'])): ?><div class="card border shadow-none mb-3"><div class="card-body py-2"><div class="d-flex flex-wrap align-items-center justify-content-between gap-2"><div class="d-flex flex-wrap gap-2"><span class="badge bg-light-primary">En Cok Satanlar</span><span class="badge bg-light-secondary"><?= esc((string) ($config['default_grid_density'] ?? '3')) ?> kolon varsayilan</span></div><?php if (! empty($config['show_result_count'])): ?><div class="small text-muted">128 urun</div><?php endif; ?></div></div></div><?php endif; ?><div class="row g-3"><?php for ($i = 0; $i < (int) ($config['grid_density'] ?? 3) * 2; $i++): ?><div class="<?= esc($gridColClass, 'attr') ?>"><div class="card <?= ($config['card_variant'] ?? 'classic') === 'elevated' ? 'shadow-sm border-0' : 'border shadow-none' ?> h-100 mb-0"><div class="card-body"><div class="bg-light rounded-3 p-4 text-center mb-3"><i class="ti ti-photo text-primary"></i></div><div class="d-flex align-items-start justify-content-between gap-2 mb-2"><div class="fw-semibold text-truncate">Ornek Urun <?= esc((string) ($i + 1)) ?></div><?php if (! empty($config['show_favorite_button'])): ?><i class="ti ti-heart text-muted"></i><?php endif; ?></div><div class="small text-muted mb-2"><?= esc((string) ($config['card_variant'] ?? 'classic')) ?> card preview</div><?php if (! empty($config['show_badges'])): ?><span class="badge bg-light-warning">Yeni</span><?php endif; ?><?php if (! empty($config['show_quick_actions'])): ?><div class="d-flex gap-2 mt-3"><span class="btn btn-sm btn-outline-secondary disabled">Hizli Bakis</span></div><?php endif; ?></div></div></div><?php endfor; ?></div></div></div></div></div>
                <div class="card border shadow-none mb-3"><div class="card-body"><div class="alert alert-<?= esc((string) ($config['empty_notice_tone'] ?? 'warning')) ?> mb-0"><div class="fw-semibold"><?= esc((string) ($config['empty_title'] ?? 'Sonuc bulunamadi')) ?></div><div class="small"><?= esc((string) ($config['empty_description'] ?? 'Filtreleri degistirerek tekrar deneyin.')) ?></div></div></div></div>
                <?php if (! empty($config['show_notice'])): ?><div class="card border shadow-none mb-0"><div class="card-body"><div class="alert alert-<?= esc((string) ($config['notice_tone'] ?? 'info')) ?> mb-0"><div class="row g-3 align-items-center"><div class="col-md-8"><div class="fw-semibold"><?= esc((string) ($config['notice_title'] ?? 'Kargo Bilgisi')) ?></div><div class="small"><?= esc((string) ($config['notice_text'] ?? '250 TL ve uzeri siparislerde ucretsiz kargo.')) ?></div></div><div class="col-md-4"><?php if (trim((string) ($config['notice_image'] ?? '')) !== ''): ?><div class="ratio ratio-16x9"><img src="<?= esc((string) $config['notice_image'], 'attr') ?>" alt="Notice preview" class="img-fluid rounded object-fit-cover"></div><?php else: ?><div class="border rounded p-3 text-center bg-white"><i class="ti ti-info-circle text-muted"></i></div><?php endif; ?></div></div></div></div></div><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="draftMetaOffcanvas" aria-labelledby="draftMetaOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <div><h5 class="offcanvas-title mb-1" id="draftMetaOffcanvasLabel">Taslak Islemleri</h5><div class="d-flex flex-wrap gap-2"><span class="badge bg-light-primary"><?= esc($draftStatus) ?></span><span class="badge bg-light-secondary"><?= esc($page['code']) ?></span></div></div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php if (session()->getFlashdata('draft_error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('draft_error')) ?></div><?php endif; ?>
        <form action="<?= site_url('admin/pages/builder/draft/update') ?>" method="post" id="draftMetaForm">
            <?= csrf_field() ?>
            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
            <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">
            <div class="card border shadow-none mb-3"><div class="card-header"><h6 class="mb-0">Temel Ayarlar</h6></div><div class="card-body"><div class="mb-3"><label class="form-label">Draft Adi</label><input type="text" name="draft_name" class="form-control" value="<?= esc(old('draft_name', $draftName)) ?>"></div><div class="mb-0"><label class="form-label">Kisa Not</label><textarea name="draft_notes" rows="4" class="form-control"><?= esc(old('draft_notes', (string) ($draft['notes'] ?? ''))) ?></textarea></div></div></div>
            <div class="card border shadow-none mb-3"><div class="card-header"><h6 class="mb-0">Canliya Al ve Schedule</h6></div><div class="card-body"><div class="d-grid gap-2 mb-3"><button type="submit" class="btn btn-success" form="draftMetaForm" formaction="<?= site_url('admin/pages/builder/draft/publish') ?>" <?= $draftStatus === 'PUBLISHED' ? 'disabled' : '' ?>><?= $draftStatus === 'PUBLISHED' ? 'Canlida' : 'Canliya Al' ?></button></div><label class="form-label">Planlanan Tarih</label><input type="datetime-local" name="scheduled_publish_at" class="form-control mb-3" value="<?= esc(old('scheduled_publish_at', $scheduledPublishInputValue)) ?>"><div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary" form="draftMetaForm" formaction="<?= site_url('admin/pages/builder/draft/schedule') ?>" <?= $draftStatus === 'PUBLISHED' ? 'disabled' : '' ?>>Schedule Et</button><?php if ($draftStatus === 'SCHEDULED'): ?><button type="submit" class="btn btn-outline-warning" form="draftMetaForm" formaction="<?= site_url('admin/pages/builder/draft/unschedule') ?>">Planlamayi Kaldir</button><?php endif; ?></div></div></div>
            <div class="card border shadow-none mb-0"><div class="card-header"><h6 class="mb-0">Draft Yasam Dongusu</h6></div><div class="card-body"><div class="d-grid gap-2"><form action="<?= site_url('admin/pages/drafts/create') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="page_code" value="<?= esc($page['code']) ?>"><button type="submit" class="btn btn-outline-success w-100">Yeni Draft Olustur</button></form><form action="<?= site_url('admin/pages/drafts/duplicate') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="page_code" value="<?= esc($page['code']) ?>"><input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>"><button type="submit" class="btn btn-outline-info w-100" <?= $draftStatus === 'ARCHIVED' ? 'disabled' : '' ?>>Taslagi Kopyala</button></form></div></div></div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3"><div class="d-flex justify-content-end gap-2"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Kapat</button><button type="submit" class="btn btn-primary" form="draftMetaForm">Kaydet</button></div></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    document.querySelectorAll('[data-media-group]').forEach(function (group) {
      var fileInput = group.querySelector('[data-media-file]');
      var pathInput = group.querySelector('[data-media-path]');
      var browseButton = group.querySelector('[data-media-browse]');
      var clearButton = group.querySelector('[data-media-clear]');
      var previewWrap = group.querySelector('[data-media-preview-wrap]');
      var previewImage = group.querySelector('[data-media-preview-image]');
      var placeholder = group.querySelector('[data-media-placeholder]');
      function refreshMedia(previewUrl) {
        var pathValue = pathInput ? pathInput.value.trim() : '';
        var hasPreview = previewUrl || pathValue !== '';
        if (previewWrap) previewWrap.classList.toggle('d-none', !hasPreview);
        if (placeholder) placeholder.classList.toggle('d-none', hasPreview);
        if (previewImage) previewImage.src = previewUrl || pathValue || '';
      }
      if (browseButton && fileInput) browseButton.addEventListener('click', function () { fileInput.click(); });
      if (fileInput) fileInput.addEventListener('change', function () {
        if (!fileInput.files || !fileInput.files[0]) { refreshMedia(); return; }
        if (pathInput && pathInput.value.trim() === '') pathInput.value = fileInput.files[0].name;
        refreshMedia(URL.createObjectURL(fileInput.files[0]));
      });
      if (pathInput) pathInput.addEventListener('input', function () { refreshMedia(); });
      if (clearButton && pathInput && fileInput) clearButton.addEventListener('click', function () { pathInput.value = ''; fileInput.value = ''; refreshMedia(); });
      refreshMedia();
    });
  })();
</script>
<?= $this->endSection() ?>
