<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$draftName = trim((string) ($draft['name'] ?? ('Taslak ' . (string) ($draft['version_no'] ?? 1))));
$draftStatus = trim((string) ($draft['status'] ?? 'DRAFT'));
$config = is_array($productListConfig ?? null) ? $productListConfig : [];
$sections = is_array($config['sections'] ?? null) ? $config['sections'] : [];
$scheduledPublishValue = trim((string) ($draft['scheduled_publish_at'] ?? ''));
$scheduledPublishInputValue = $scheduledPublishValue !== '' ? date('Y-m-d\TH:i', strtotime($scheduledPublishValue)) : '';
?>

<div class="page-header">
    <div class="page-block">
        <div class="row">
            <div class="col-12"><div class="page-header-title"><h2 class="mb-0"><?= esc($page['name']) ?> Yonetimi</h2></div></div>
            <div class="col-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yonetim</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/pages') ?>">Sayfa Yonetimi</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/pages/' . $page['code']) ?>"><?= esc($page['name']) ?></a></li>
                    <li class="breadcrumb-item" aria-current="page">Page System Management</li>
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
                    <h4 class="mb-1">Urun Listeleme Sayfa Sistemi</h4>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#draftMetaOffcanvas">Taslak Islemleri</button>
                    <a href="<?= site_url('admin/pages/' . $page['code'] . '/drafts') ?>" class="btn btn-outline-primary">Taslaklar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xxl-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-1">Oturum Yonetimi</h5>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
                <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>

                <form action="<?= site_url('admin/pages/product-list-builder/update') ?>" method="post" id="productListBuilderForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                    <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">

                    <div class="accordion" id="productListSectionsAccordion">
                        <div class="accordion-item border rounded mb-3">
                            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#topArea">Sayfa Ust Alani</button></h2>
                            <div id="topArea" class="accordion-collapse collapse show" data-bs-parent="#productListSectionsAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="section_top_active" name="section_sayfa_ust_alani_active" value="1" <?= old('section_sayfa_ust_alani_active', ! empty($sections['sayfa_ust_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label" for="section_top_active">Aktif</label></div></div>
                                        <div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_sayfa_ust_alani_order" class="form-control" value="<?= esc((string) old('section_sayfa_ust_alani_order', (string) ($sections['sayfa_ust_alani']['order'] ?? 1))) ?>"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Sayfa Basligi</label><input type="text" name="sayfa_basligi" class="form-control" value="<?= esc(old('sayfa_basligi', (string) ($config['sayfa_basligi'] ?? ''))) ?>"></div>
                                    <div class="mb-3"><label class="form-label">Sayfa Alt Basligi</label><textarea name="sayfa_alt_basligi" rows="3" class="form-control"><?= esc(old('sayfa_alt_basligi', (string) ($config['sayfa_alt_basligi'] ?? ''))) ?></textarea></div>
                                    <div class="row g-3">
                                        <div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" id="breadcrumb_goster" name="breadcrumb_goster" value="1" <?= old('breadcrumb_goster', ! empty($config['breadcrumb_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="breadcrumb_goster">Breadcrumb goster</label></div></div>
                                        <div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" id="ust_banner_goster" name="ust_banner_goster" value="1" <?= old('ust_banner_goster', ! empty($config['ust_banner_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="ust_banner_goster">Ust banner goster</label></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bannerArea">Gorsel ve Banner Ayarlari</button></h2>
                            <div id="bannerArea" class="accordion-collapse collapse" data-bs-parent="#productListSectionsAccordion">
                                <div class="accordion-body">
                                    <div class="mb-3"><label class="form-label">Banner Basligi</label><input type="text" name="banner_basligi" class="form-control" value="<?= esc(old('banner_basligi', (string) ($config['banner_basligi'] ?? ''))) ?>"></div>
                                    <div class="mb-3"><label class="form-label">Banner Alt Metni</label><textarea name="banner_alt_metni" rows="3" class="form-control"><?= esc(old('banner_alt_metni', (string) ($config['banner_alt_metni'] ?? ''))) ?></textarea></div>
                                    <div class="mb-3"><label class="form-label">Banner Tonu</label><select name="banner_tonu" class="form-select"><?php foreach (['light' => 'Acik', 'dark' => 'Koyu', 'soft' => 'Yumusak', 'accent' => 'Vurgulu'] as $value => $label): ?><option value="<?= esc($value) ?>" <?= old('banner_tonu', (string) ($config['banner_tonu'] ?? 'light')) === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
                                    <div class="card border shadow-none mb-0" data-media-group>
                                        <div class="card-body">
                                            <div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap><img src="" alt="Banner preview" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image></div>
                                            <div class="border rounded text-center p-4" data-media-placeholder><i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i><div class="fw-semibold mb-1">Banner gorseli secin</div><div class="small text-muted">Listeleme ust alani gorseli.</div></div>
                                            <div class="d-flex flex-wrap gap-2 mt-3"><button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button><button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button></div>
                                            <input type="file" class="d-none" accept="image/*" data-media-file>
                                            <div class="mt-3"><label class="form-label small text-muted">Gelişmis Yol Alani</label><input type="text" name="banner_gorseli" class="form-control" value="<?= esc(old('banner_gorseli', (string) ($config['banner_gorseli'] ?? ''))) ?>" placeholder="/uploads/product-list-banner.jpg" data-media-path></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filterArea">Filtre Alani</button></h2>
                            <div id="filterArea" class="accordion-collapse collapse" data-bs-parent="#productListSectionsAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="section_filter_active" name="section_filtre_alani_active" value="1" <?= old('section_filtre_alani_active', ! empty($sections['filtre_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label" for="section_filter_active">Aktif</label></div></div>
                                        <div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_filtre_alani_order" class="form-control" value="<?= esc((string) old('section_filtre_alani_order', (string) ($sections['filtre_alani']['order'] ?? 2))) ?>"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Filtre Basligi</label><input type="text" name="filtre_basligi" class="form-control" value="<?= esc(old('filtre_basligi', (string) ($config['filtre_basligi'] ?? ''))) ?>"></div>
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Filtre Konumu</label><select name="filtre_konumu" class="form-select"><option value="left" <?= old('filtre_konumu', (string) ($config['filtre_konumu'] ?? 'left')) === 'left' ? 'selected' : '' ?>>Sol</option><option value="top" <?= old('filtre_konumu', (string) ($config['filtre_konumu'] ?? 'left')) === 'top' ? 'selected' : '' ?>>Ust</option></select></div>
                                        <div class="col-md-6 d-flex flex-column justify-content-end gap-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="filtreler_goster" name="filtreler_goster" value="1" <?= old('filtreler_goster', ! empty($config['filtreler_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="filtreler_goster">Filtreleri goster</label></div><div class="form-check"><input class="form-check-input" type="checkbox" id="filtre_ozeti_goster" name="filtre_ozeti_goster" value="1" <?= old('filtre_ozeti_goster', ! empty($config['filtre_ozeti_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="filtre_ozeti_goster">Filtre ozetini goster</label></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#toolbarArea">Siralama ve Sonuc Cubugu</button></h2>
                            <div id="toolbarArea" class="accordion-collapse collapse" data-bs-parent="#productListSectionsAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="section_toolbar_active" name="section_siralama_sonuc_cubugu_active" value="1" <?= old('section_siralama_sonuc_cubugu_active', ! empty($sections['siralama_sonuc_cubugu']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label" for="section_toolbar_active">Aktif</label></div></div>
                                        <div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_siralama_sonuc_cubugu_order" class="form-control" value="<?= esc((string) old('section_siralama_sonuc_cubugu_order', (string) ($sections['siralama_sonuc_cubugu']['order'] ?? 3))) ?>"></div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Varsayilan Grid Yogunlugu</label><select name="varsayilan_grid_yogunlugu" class="form-select"><?php foreach (['2', '3', '4'] as $value): ?><option value="<?= esc($value) ?>" <?= old('varsayilan_grid_yogunlugu', (string) ($config['varsayilan_grid_yogunlugu'] ?? '3')) === $value ? 'selected' : '' ?>><?= esc($value) ?> Kolon</option><?php endforeach; ?></select></div>
                                        <div class="col-md-6 d-flex flex-column justify-content-end gap-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="siralama_cubugu_goster" name="siralama_cubugu_goster" value="1" <?= old('siralama_cubugu_goster', ! empty($config['siralama_cubugu_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="siralama_cubugu_goster">Siralama cubugunu goster</label></div><div class="form-check"><input class="form-check-input" type="checkbox" id="sonuc_sayisi_goster" name="sonuc_sayisi_goster" value="1" <?= old('sonuc_sayisi_goster', ! empty($config['sonuc_sayisi_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="sonuc_sayisi_goster">Sonuc sayisini goster</label></div><div class="form-check"><input class="form-check-input" type="checkbox" id="aktif_filtre_etiketleri_goster" name="aktif_filtre_etiketleri_goster" value="1" <?= old('aktif_filtre_etiketleri_goster', ! empty($config['aktif_filtre_etiketleri_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="aktif_filtre_etiketleri_goster">Aktif filtre etiketlerini goster</label></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gridArea">Urun Listesi Gorunumu</button></h2>
                            <div id="gridArea" class="accordion-collapse collapse" data-bs-parent="#productListSectionsAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="section_grid_active" name="section_urun_listesi_gorunumu_active" value="1" <?= old('section_urun_listesi_gorunumu_active', ! empty($sections['urun_listesi_gorunumu']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label" for="section_grid_active">Aktif</label></div></div>
                                        <div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_urun_listesi_gorunumu_order" class="form-control" value="<?= esc((string) old('section_urun_listesi_gorunumu_order', (string) ($sections['urun_listesi_gorunumu']['order'] ?? 4))) ?>"></div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6"><label class="form-label">Kart Varyanti</label><select name="kart_varyanti" class="form-select"><?php foreach (['classic' => 'Klasik', 'minimal' => 'Minimal', 'elevated' => 'Yukseltilmis'] as $value => $label): ?><option value="<?= esc($value) ?>" <?= old('kart_varyanti', (string) ($config['kart_varyanti'] ?? 'classic')) === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
                                        <div class="col-md-6"><label class="form-label">Grid Yogunlugu</label><select name="grid_yogunlugu" class="form-select"><?php foreach (['2', '3', '4'] as $value): ?><option value="<?= esc($value) ?>" <?= old('grid_yogunlugu', (string) ($config['grid_yogunlugu'] ?? '3')) === $value ? 'selected' : '' ?>><?= esc($value) ?> Kolon</option><?php endforeach; ?></select></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="rozetleri_goster" name="rozetleri_goster" value="1" <?= old('rozetleri_goster', ! empty($config['rozetleri_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="rozetleri_goster">Rozetleri goster</label></div></div>
                                        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="favori_butonu_goster" name="favori_butonu_goster" value="1" <?= old('favori_butonu_goster', ! empty($config['favori_butonu_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="favori_butonu_goster">Favori butonunu goster</label></div></div>
                                        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="hizli_aksiyonlari_goster" name="hizli_aksiyonlari_goster" value="1" <?= old('hizli_aksiyonlari_goster', ! empty($config['hizli_aksiyonlari_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="hizli_aksiyonlari_goster">Hizli aksiyonlari goster</label></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#noticeArea">Bilgilendirme / Kampanya Alani</button></h2>
                            <div id="noticeArea" class="accordion-collapse collapse" data-bs-parent="#productListSectionsAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="section_notice_active" name="section_bilgilendirme_kampanya_alani_active" value="1" <?= old('section_bilgilendirme_kampanya_alani_active', ! empty($sections['bilgilendirme_kampanya_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label" for="section_notice_active">Aktif</label></div></div>
                                        <div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_bilgilendirme_kampanya_alani_order" class="form-control" value="<?= esc((string) old('section_bilgilendirme_kampanya_alani_order', (string) ($sections['bilgilendirme_kampanya_alani']['order'] ?? 5))) ?>"></div>
                                    </div>
                                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="bilgilendirme_alani_goster" name="bilgilendirme_alani_goster" value="1" <?= old('bilgilendirme_alani_goster', ! empty($config['bilgilendirme_alani_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="bilgilendirme_alani_goster">Bilgilendirme alanini goster</label></div>
                                    <div class="mb-3"><label class="form-label">Bilgilendirme Basligi</label><input type="text" name="bilgilendirme_basligi" class="form-control" value="<?= esc(old('bilgilendirme_basligi', (string) ($config['bilgilendirme_basligi'] ?? ''))) ?>"></div>
                                    <div class="mb-3"><label class="form-label">Bilgilendirme Metni</label><textarea name="bilgilendirme_metni" rows="3" class="form-control"><?= esc(old('bilgilendirme_metni', (string) ($config['bilgilendirme_metni'] ?? ''))) ?></textarea></div>
                                    <div class="mb-3"><label class="form-label">Bilgilendirme Tonu</label><select name="bilgilendirme_tonu" class="form-select"><?php foreach (['info' => 'Bilgi', 'success' => 'Basari', 'warning' => 'Uyari', 'danger' => 'Dikkat'] as $value => $label): ?><option value="<?= esc($value) ?>" <?= old('bilgilendirme_tonu', (string) ($config['bilgilendirme_tonu'] ?? 'info')) === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
                                    <div class="card border shadow-none mb-0" data-media-group><div class="card-body"><div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap><img src="" alt="Bilgilendirme gorseli" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image></div><div class="border rounded text-center p-4" data-media-placeholder><i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i><div class="fw-semibold mb-1">Kampanya gorseli secin</div><div class="small text-muted">Bilgilendirme bolumunde kullanilir.</div></div><div class="d-flex flex-wrap gap-2 mt-3"><button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button><button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button></div><input type="file" class="d-none" accept="image/*" data-media-file><div class="mt-3"><label class="form-label small text-muted">Gelişmis Yol Alani</label><input type="text" name="bilgilendirme_gorseli" class="form-control" value="<?= esc(old('bilgilendirme_gorseli', (string) ($config['bilgilendirme_gorseli'] ?? ''))) ?>" placeholder="/uploads/product-list-notice.jpg" data-media-path></div></div></div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#emptyArea">Bos Sonuc Alani</button></h2>
                            <div id="emptyArea" class="accordion-collapse collapse" data-bs-parent="#productListSectionsAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="section_empty_active" name="section_bos_sonuc_alani_active" value="1" <?= old('section_bos_sonuc_alani_active', ! empty($sections['bos_sonuc_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label" for="section_empty_active">Aktif</label></div></div>
                                        <div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_bos_sonuc_alani_order" class="form-control" value="<?= esc((string) old('section_bos_sonuc_alani_order', (string) ($sections['bos_sonuc_alani']['order'] ?? 6))) ?>"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Bos Sonuc Basligi</label><input type="text" name="bos_sonuc_basligi" class="form-control" value="<?= esc(old('bos_sonuc_basligi', (string) ($config['bos_sonuc_basligi'] ?? ''))) ?>"></div>
                                    <div class="mb-3"><label class="form-label">Bos Sonuc Aciklamasi</label><textarea name="bos_sonuc_aciklamasi" rows="3" class="form-control"><?= esc(old('bos_sonuc_aciklamasi', (string) ($config['bos_sonuc_aciklamasi'] ?? ''))) ?></textarea></div>
                                    <div class="mb-3"><label class="form-label">Bos Sonuc Tonu</label><select name="bos_sonuc_tonu" class="form-select"><?php foreach (['info' => 'Bilgi', 'success' => 'Basari', 'warning' => 'Uyari', 'danger' => 'Dikkat'] as $value => $label): ?><option value="<?= esc($value) ?>" <?= old('bos_sonuc_tonu', (string) ($config['bos_sonuc_tonu'] ?? 'warning')) === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
                                    <div class="card border shadow-none mb-0" data-media-group><div class="card-body"><div class="ratio ratio-16x9 rounded overflow-hidden bg-light mb-3 d-none" data-media-preview-wrap><img src="" alt="Bos sonuc gorseli" class="img-fluid object-fit-cover w-100 h-100" data-media-preview-image></div><div class="border rounded text-center p-4" data-media-placeholder><i class="ti ti-photo-up fs-2 text-muted d-block mb-2"></i><div class="fw-semibold mb-1">Bos sonuc gorseli secin</div><div class="small text-muted">Sonuc bulunmadiginda gosterilir.</div></div><div class="d-flex flex-wrap gap-2 mt-3"><button type="button" class="btn btn-sm btn-primary" data-media-browse>Gorsel Sec</button><button type="button" class="btn btn-sm btn-outline-danger" data-media-clear>Kaldir</button></div><input type="file" class="d-none" accept="image/*" data-media-file><div class="mt-3"><label class="form-label small text-muted">Gelişmis Yol Alani</label><input type="text" name="bos_sonuc_gorseli" class="form-control" value="<?= esc(old('bos_sonuc_gorseli', (string) ($config['bos_sonuc_gorseli'] ?? ''))) ?>" placeholder="/uploads/product-list-empty.jpg" data-media-path></div></div></div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded mb-4">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#footerArea">Alt Aciklama Alani</button></h2>
                            <div id="footerArea" class="accordion-collapse collapse" data-bs-parent="#productListSectionsAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="section_footer_active" name="section_alt_aciklama_alani_active" value="1" <?= old('section_alt_aciklama_alani_active', ! empty($sections['alt_aciklama_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label" for="section_footer_active">Aktif</label></div></div>
                                        <div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_alt_aciklama_alani_order" class="form-control" value="<?= esc((string) old('section_alt_aciklama_alani_order', (string) ($sections['alt_aciklama_alani']['order'] ?? 7))) ?>"></div>
                                    </div>
                                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="alt_aciklama_goster" name="alt_aciklama_goster" value="1" <?= old('alt_aciklama_goster', ! empty($config['alt_aciklama_goster'])) ? 'checked' : '' ?>><label class="form-check-label" for="alt_aciklama_goster">Alt aciklama alanini goster</label></div>
                                    <div class="mb-3"><label class="form-label">Alt Aciklama Basligi</label><input type="text" name="alt_aciklama_basligi" class="form-control" value="<?= esc(old('alt_aciklama_basligi', (string) ($config['alt_aciklama_basligi'] ?? ''))) ?>"></div>
                                    <div class="mb-0"><label class="form-label">Alt Aciklama Metni</label><textarea name="alt_aciklama_metni" rows="4" class="form-control"><?= esc(old('alt_aciklama_metni', (string) ($config['alt_aciklama_metni'] ?? ''))) ?></textarea></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid"><button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Sayfa Sistemi Ayarlarini Kaydet</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xxl-7">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-1">Mini Sayfa Onizlemesi</h5></div>
            <div class="card-body">
                <?= view('admin/pages/partials/product_list_preview', ['productListPreview' => $productListPreview ?? []]) ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="draftMetaOffcanvas" aria-labelledby="draftMetaOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-1" id="draftMetaOffcanvasLabel">Taslak Islemleri</h5>
            <div class="d-flex flex-wrap gap-2"><span class="badge bg-light-primary"><?= esc($draftStatus) ?></span><span class="badge bg-light-secondary"><?= esc($page['code']) ?></span></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php if (session()->getFlashdata('draft_error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('draft_error')) ?></div><?php endif; ?>
        <form action="<?= site_url('admin/pages/builder/draft/update') ?>" method="post" id="draftMetaForm">
            <?= csrf_field() ?>
            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
            <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">
            <div class="card border shadow-none mb-3"><div class="card-header"><h6 class="mb-0">Temel Ayarlar</h6></div><div class="card-body"><div class="mb-3"><label class="form-label">Taslak Adi</label><input type="text" name="draft_name" class="form-control" value="<?= esc(old('draft_name', $draftName)) ?>"></div><div class="mb-0"><label class="form-label">Kisa Not</label><textarea name="draft_notes" rows="4" class="form-control"><?= esc(old('draft_notes', (string) ($draft['notes'] ?? ''))) ?></textarea></div></div></div>
            <div class="card border shadow-none mb-3"><div class="card-header"><h6 class="mb-0">Canliya Al ve Schedule</h6></div><div class="card-body"><div class="d-grid gap-2 mb-3"><button type="submit" class="btn btn-success" form="draftMetaForm" formaction="<?= site_url('admin/pages/builder/draft/publish') ?>" <?= $draftStatus === 'PUBLISHED' ? 'disabled' : '' ?>><?= $draftStatus === 'PUBLISHED' ? 'Canlida' : 'Canliya Al' ?></button></div><label class="form-label">Planlanan Tarih</label><input type="datetime-local" name="scheduled_publish_at" class="form-control mb-3" value="<?= esc(old('scheduled_publish_at', $scheduledPublishInputValue)) ?>"><div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary" form="draftMetaForm" formaction="<?= site_url('admin/pages/builder/draft/schedule') ?>" <?= $draftStatus === 'PUBLISHED' ? 'disabled' : '' ?>>Schedule Et</button><?php if ($draftStatus === 'SCHEDULED'): ?><button type="submit" class="btn btn-outline-warning" form="draftMetaForm" formaction="<?= site_url('admin/pages/builder/draft/unschedule') ?>">Planlamayi Kaldir</button><?php endif; ?></div></div></div>
            <div class="card border shadow-none mb-0"><div class="card-header"><h6 class="mb-0">Taslak Yasam Dongusu</h6></div><div class="card-body"><div class="d-grid gap-2"><form action="<?= site_url('admin/pages/drafts/create') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="page_code" value="<?= esc($page['code']) ?>"><button type="submit" class="btn btn-outline-success w-100">Yeni Taslak Olustur</button></form><form action="<?= site_url('admin/pages/drafts/duplicate') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="page_code" value="<?= esc($page['code']) ?>"><input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>"><button type="submit" class="btn btn-outline-info w-100" <?= $draftStatus === 'ARCHIVED' ? 'disabled' : '' ?>>Taslagi Kopyala</button></form></div></div></div>
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
