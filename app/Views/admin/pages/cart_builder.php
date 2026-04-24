<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$draftName = trim((string) ($draft['name'] ?? ('Taslak ' . (string) ($draft['version_no'] ?? 1))));
$draftStatus = trim((string) ($draft['status'] ?? 'DRAFT'));
$config = is_array($cartConfig ?? null) ? $cartConfig : [];
$sections = is_array($config['sections'] ?? null) ? $config['sections'] : [];
$cartPreviewData = is_array($cartPreview ?? null) ? $cartPreview : [];
$cartVisibleSectionCount = (int) ($cartPreviewData['visibleSectionCount'] ?? 0);
$scheduledPublishValue = trim((string) ($draft['scheduled_publish_at'] ?? ''));
$scheduledPublishInputValue = $scheduledPublishValue !== '' ? date('Y-m-d\TH:i', strtotime($scheduledPublishValue)) : '';
?>

<div class="page-header">
    <div class="page-block">
        <div class="row">
            <div class="col-12"><div class="page-header-title"><h2 class="mb-0"><?= esc($page['name']) ?> Yonetimi</h2></div></div>
            <div class="col-12"><ul class="breadcrumb"><li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yonetim</a></li><li class="breadcrumb-item"><a href="<?= site_url('admin/pages') ?>">Sayfa Yonetimi</a></li><li class="breadcrumb-item" aria-current="page">Cart Builder</li></ul></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1">Odeme Sayfasi Sistemi</h4>
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
            <div class="card-header"><h5 class="mb-1">Oturum Yonetimi</h5></div>
            <div class="card-body">
                <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
                <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>

                <form action="<?= site_url('admin/pages/cart-builder/update') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                    <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">

                    <div class="accordion" id="cartSectionsAccordion">
                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cartTopArea">Sayfa Ust Alani</button></h2><div id="cartTopArea" class="accordion-collapse collapse show" data-bs-parent="#cartSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_sayfa_ust_alani_active" value="1" <?= old('section_sayfa_ust_alani_active', ! empty($sections['sayfa_ust_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_sayfa_ust_alani_order" class="form-control" value="<?= esc((string) old('section_sayfa_ust_alani_order', (string) ($sections['sayfa_ust_alani']['order'] ?? 1))) ?>"></div></div><div class="mb-3"><label class="form-label">Baslik</label><input type="text" name="sayfa_basligi" class="form-control" value="<?= esc(old('sayfa_basligi', (string) ($config['sayfa_basligi'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Alt Baslik</label><textarea name="sayfa_alt_basligi" rows="3" class="form-control"><?= esc(old('sayfa_alt_basligi', (string) ($config['sayfa_alt_basligi'] ?? ''))) ?></textarea></div><div class="row g-3"><div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="breadcrumb_goster" value="1" <?= old('breadcrumb_goster', ! empty($config['breadcrumb_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Breadcrumb goster</label></div></div><div class="col-md-6"><label class="form-label">Kisa Aciklama</label><input type="text" name="kisa_aciklama" class="form-control" value="<?= esc(old('kisa_aciklama', (string) ($config['kisa_aciklama'] ?? ''))) ?>"></div></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cartItemsArea">Sepet Urunleri Alani</button></h2><div id="cartItemsArea" class="accordion-collapse collapse" data-bs-parent="#cartSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_sepet_urunleri_alani_active" value="1" <?= old('section_sepet_urunleri_alani_active', ! empty($sections['sepet_urunleri_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_sepet_urunleri_alani_order" class="form-control" value="<?= esc((string) old('section_sepet_urunleri_alani_order', (string) ($sections['sepet_urunleri_alani']['order'] ?? 2))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="sepet_urunleri_baslik" class="form-control" value="<?= esc(old('sepet_urunleri_baslik', (string) ($config['sepet_urunleri_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Aciklama Metni</label><textarea name="sepet_urunleri_aciklama" rows="3" class="form-control"><?= esc(old('sepet_urunleri_aciklama', (string) ($config['sepet_urunleri_aciklama'] ?? ''))) ?></textarea></div><div class="row g-2 mb-3"><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="urun_gorseli_goster" value="1" <?= old('urun_gorseli_goster', ! empty($config['urun_gorseli_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Urun gorselini goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="format_etiketi_goster" value="1" <?= old('format_etiketi_goster', ! empty($config['format_etiketi_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Format etiketini goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="adet_kontrolu_goster" value="1" <?= old('adet_kontrolu_goster', ! empty($config['adet_kontrolu_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Adet kontrolunu goster</label></div></div></div><div class="mb-0"><label class="form-label">Kaldir Butonu Metni</label><input type="text" name="kaldir_buton_metni" class="form-control" value="<?= esc(old('kaldir_buton_metni', (string) ($config['kaldir_buton_metni'] ?? ''))) ?>"></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cartPriceArea">Fiyat Guncelleme Uyari Alani</button></h2><div id="cartPriceArea" class="accordion-collapse collapse" data-bs-parent="#cartSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_fiyat_guncelleme_uyari_alani_active" value="1" <?= old('section_fiyat_guncelleme_uyari_alani_active', ! empty($sections['fiyat_guncelleme_uyari_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_fiyat_guncelleme_uyari_alani_order" class="form-control" value="<?= esc((string) old('section_fiyat_guncelleme_uyari_alani_order', (string) ($sections['fiyat_guncelleme_uyari_alani']['order'] ?? 3))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="fiyat_uyari_baslik" class="form-control" value="<?= esc(old('fiyat_uyari_baslik', (string) ($config['fiyat_uyari_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Aciklama Metni</label><textarea name="fiyat_uyari_aciklama" rows="3" class="form-control"><?= esc(old('fiyat_uyari_aciklama', (string) ($config['fiyat_uyari_aciklama'] ?? ''))) ?></textarea></div><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="fiyat_farki_bilgi_kutusu_goster" value="1" <?= old('fiyat_farki_bilgi_kutusu_goster', ! empty($config['fiyat_farki_bilgi_kutusu_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Fiyat farki bilgi kutusunu goster</label></div><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Eski Fiyat Etiketi</label><input type="text" name="eski_fiyat_etiketi" class="form-control" value="<?= esc(old('eski_fiyat_etiketi', (string) ($config['eski_fiyat_etiketi'] ?? ''))) ?>"></div><div class="col-md-6"><label class="form-label">Guncel Fiyat Etiketi</label><input type="text" name="guncel_fiyat_etiketi" class="form-control" value="<?= esc(old('guncel_fiyat_etiketi', (string) ($config['guncel_fiyat_etiketi'] ?? ''))) ?>"></div></div><div class="mb-0"><label class="form-label">Toplam Guncelleme Notu</label><textarea name="toplam_guncelleme_notu" rows="3" class="form-control"><?= esc(old('toplam_guncelleme_notu', (string) ($config['toplam_guncelleme_notu'] ?? ''))) ?></textarea></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cartStockArea">Stok / Uygunluk Uyari Alani</button></h2><div id="cartStockArea" class="accordion-collapse collapse" data-bs-parent="#cartSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_stok_uygunluk_uyari_alani_active" value="1" <?= old('section_stok_uygunluk_uyari_alani_active', ! empty($sections['stok_uygunluk_uyari_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_stok_uygunluk_uyari_alani_order" class="form-control" value="<?= esc((string) old('section_stok_uygunluk_uyari_alani_order', (string) ($sections['stok_uygunluk_uyari_alani']['order'] ?? 4))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="stok_uyari_baslik" class="form-control" value="<?= esc(old('stok_uyari_baslik', (string) ($config['stok_uyari_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Aciklama Metni</label><textarea name="stok_uyari_aciklama" rows="3" class="form-control"><?= esc(old('stok_uyari_aciklama', (string) ($config['stok_uyari_aciklama'] ?? ''))) ?></textarea></div><div class="row g-2 mb-3"><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="dusuk_stok_uyarisi_goster" value="1" <?= old('dusuk_stok_uyarisi_goster', ! empty($config['dusuk_stok_uyarisi_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Dusuk stok uyarisini goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="tukenme_mesaji_goster" value="1" <?= old('tukenme_mesaji_goster', ! empty($config['tukenme_mesaji_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Tukenme mesajini goster</label></div></div></div><div class="mb-3"><label class="form-label">Dusuk Stok Mesaj Sablonu</label><input type="text" name="dusuk_stok_mesaj_sablonu" class="form-control" value="<?= esc(old('dusuk_stok_mesaj_sablonu', (string) ($config['dusuk_stok_mesaj_sablonu'] ?? ''))) ?>"></div><div class="mb-0"><label class="form-label">Son Urun Mesaj Sablonu</label><input type="text" name="son_urun_mesaj_sablonu" class="form-control" value="<?= esc(old('son_urun_mesaj_sablonu', (string) ($config['son_urun_mesaj_sablonu'] ?? ''))) ?>"></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cartCouponArea">Kupon / Kampanya Alani</button></h2><div id="cartCouponArea" class="accordion-collapse collapse" data-bs-parent="#cartSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_kupon_kampanya_alani_active" value="1" <?= old('section_kupon_kampanya_alani_active', ! empty($sections['kupon_kampanya_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_kupon_kampanya_alani_order" class="form-control" value="<?= esc((string) old('section_kupon_kampanya_alani_order', (string) ($sections['kupon_kampanya_alani']['order'] ?? 5))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="kupon_kampanya_baslik" class="form-control" value="<?= esc(old('kupon_kampanya_baslik', (string) ($config['kupon_kampanya_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Aciklama Metni</label><textarea name="kupon_kampanya_aciklama" rows="3" class="form-control"><?= esc(old('kupon_kampanya_aciklama', (string) ($config['kupon_kampanya_aciklama'] ?? ''))) ?></textarea></div><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="kupon_alani_goster" value="1" <?= old('kupon_alani_goster', ! empty($config['kupon_alani_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Kupon alanini goster</label></div><div class="mb-3"><label class="form-label">Kampanya Bilgi Notu</label><textarea name="kampanya_bilgi_notu" rows="3" class="form-control"><?= esc(old('kampanya_bilgi_notu', (string) ($config['kampanya_bilgi_notu'] ?? ''))) ?></textarea></div><div class="mb-0"><label class="form-label">Ucretsiz Kargo Bilgi Notu</label><textarea name="ucretsiz_kargo_bilgi_notu" rows="3" class="form-control"><?= esc(old('ucretsiz_kargo_bilgi_notu', (string) ($config['ucretsiz_kargo_bilgi_notu'] ?? ''))) ?></textarea></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cartSummaryArea">Sepet Ozeti ve CTA Alani</button></h2><div id="cartSummaryArea" class="accordion-collapse collapse" data-bs-parent="#cartSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_sepet_ozeti_cta_alani_active" value="1" <?= old('section_sepet_ozeti_cta_alani_active', ! empty($sections['sepet_ozeti_cta_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_sepet_ozeti_cta_alani_order" class="form-control" value="<?= esc((string) old('section_sepet_ozeti_cta_alani_order', (string) ($sections['sepet_ozeti_cta_alani']['order'] ?? 6))) ?>"></div></div><div class="mb-3"><label class="form-label">Baslik</label><input type="text" name="sepet_ozeti_baslik" class="form-control" value="<?= esc(old('sepet_ozeti_baslik', (string) ($config['sepet_ozeti_baslik'] ?? ''))) ?>"></div><div class="row g-2 mb-3"><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="ara_toplam_goster" value="1" <?= old('ara_toplam_goster', ! empty($config['ara_toplam_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Ara toplami goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="indirim_goster" value="1" <?= old('indirim_goster', ! empty($config['indirim_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Indirimi goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="kargo_goster" value="1" <?= old('kargo_goster', ! empty($config['kargo_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Kargoyu goster</label></div></div></div><div class="mb-3"><label class="form-label">Genel Toplam Basligi</label><input type="text" name="genel_toplam_basligi" class="form-control" value="<?= esc(old('genel_toplam_basligi', (string) ($config['genel_toplam_basligi'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Odeme Sayfasina Git Buton Metni</label><input type="text" name="odeme_sayfasina_git_buton_metni" class="form-control" value="<?= esc(old('odeme_sayfasina_git_buton_metni', (string) ($config['odeme_sayfasina_git_buton_metni'] ?? ''))) ?>"></div><div class="mb-0"><label class="form-label">Guvenli Odeme Kisa Notu</label><textarea name="guvenli_odeme_kisa_notu" rows="3" class="form-control"><?= esc(old('guvenli_odeme_kisa_notu', (string) ($config['guvenli_odeme_kisa_notu'] ?? ''))) ?></textarea></div></div></div></div>

                        <div class="accordion-item border rounded mb-4"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cartEmptyArea">Bos Sepet Alani</button></h2><div id="cartEmptyArea" class="accordion-collapse collapse" data-bs-parent="#cartSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_bos_sepet_alani_active" value="1" <?= old('section_bos_sepet_alani_active', ! empty($sections['bos_sepet_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_bos_sepet_alani_order" class="form-control" value="<?= esc((string) old('section_bos_sepet_alani_order', (string) ($sections['bos_sepet_alani']['order'] ?? 7))) ?>"></div></div><div class="mb-3"><label class="form-label">Baslik</label><input type="text" name="bos_sepet_baslik" class="form-control" value="<?= esc(old('bos_sepet_baslik', (string) ($config['bos_sepet_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Aciklama</label><textarea name="bos_sepet_aciklama" rows="3" class="form-control"><?= esc(old('bos_sepet_aciklama', (string) ($config['bos_sepet_aciklama'] ?? ''))) ?></textarea></div><div class="mb-0"><label class="form-label">Alisverise Basla Buton Metni</label><input type="text" name="alisverise_basla_buton_metni" class="form-control" value="<?= esc(old('alisverise_basla_buton_metni', (string) ($config['alisverise_basla_buton_metni'] ?? ''))) ?>"></div></div></div></div>
                    </div>

                    <div class="d-grid"><button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Cart Ayarlarini Kaydet</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xxl-7">
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0">Mini Cart Onizlemesi</h5>
                <span class="badge bg-light-primary"><?= esc((string) $cartVisibleSectionCount) ?> aktif bolum</span>
            </div>
            <div class="card-body">
                <?= view('admin/pages/partials/cart_preview', ['cartPreview' => $cartPreview ?? []]) ?>
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

        <form action="<?= site_url('admin/pages/builder/draft/update') ?>" method="post" id="draftMetaForm">
            <?= csrf_field() ?>
            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
            <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">

            <div class="card border shadow-none mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Temel Ayarlar</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Taslak Adi</label>
                        <input type="text" name="draft_name" class="form-control" value="<?= esc(old('draft_name', $draftName)) ?>" placeholder="Orn. Cart taslagi">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Kisa Not</label>
                        <textarea name="draft_notes" rows="4" class="form-control" placeholder="Orn. Sepet ve odeme CTA metinleri guncellenecek"><?= esc(old('draft_notes', (string) ($draft['notes'] ?? ''))) ?></textarea>
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
                    <h6 class="mb-0">Taslak Yasam Dongusu</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="<?= site_url('admin/pages/drafts/create') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="ti ti-copy-plus me-1"></i> Yeni Taslak Olustur
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
                        <?php if (is_array($publishedVersion ?? null) && ! empty($publishedVersion['id'])): ?>
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
<?= $this->endSection() ?>
