<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$draftName = trim((string) ($draft['name'] ?? ('Taslak ' . (string) ($draft['version_no'] ?? 1))));
$draftStatus = trim((string) ($draft['status'] ?? 'DRAFT'));
$config = is_array($productDetailConfig ?? null) ? $productDetailConfig : [];
$sections = is_array($config['sections'] ?? null) ? $config['sections'] : [];
$scheduledPublishValue = trim((string) ($draft['scheduled_publish_at'] ?? ''));
$scheduledPublishInputValue = $scheduledPublishValue !== '' ? date('Y-m-d\TH:i', strtotime($scheduledPublishValue)) : '';
?>

<div class="page-header">
    <div class="page-block">
        <div class="row">
            <div class="col-12"><div class="page-header-title"><h2 class="mb-0"><?= esc($page['name']) ?> Yonetimi</h2></div></div>
            <div class="col-12"><ul class="breadcrumb"><li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yonetim</a></li><li class="breadcrumb-item"><a href="<?= site_url('admin/pages') ?>">Sayfa Yonetimi</a></li><li class="breadcrumb-item" aria-current="page">Product Detail Builder</li></ul></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1">Urun Detay Sayfasi</h4>
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

                <form action="<?= site_url('admin/pages/product-detail-builder/update') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                    <input type="hidden" name="version_id" value="<?= esc($draft['id']) ?>">

                    <div class="accordion" id="productDetailSectionsAccordion">
                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#detailTopArea">Sayfa Ust Alani</button></h2><div id="detailTopArea" class="accordion-collapse collapse show" data-bs-parent="#productDetailSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_sayfa_ust_alani_active" value="1" <?= old('section_sayfa_ust_alani_active', ! empty($sections['sayfa_ust_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_sayfa_ust_alani_order" class="form-control" value="<?= esc((string) old('section_sayfa_ust_alani_order', (string) ($sections['sayfa_ust_alani']['order'] ?? 1))) ?>"></div></div><div class="mb-3"><label class="form-label">Baslik</label><input type="text" name="sayfa_basligi" class="form-control" value="<?= esc(old('sayfa_basligi', (string) ($config['sayfa_basligi'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Alt Baslik</label><textarea name="sayfa_alt_basligi" rows="3" class="form-control"><?= esc(old('sayfa_alt_basligi', (string) ($config['sayfa_alt_basligi'] ?? ''))) ?></textarea></div><div class="row g-3"><div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="breadcrumb_goster" value="1" <?= old('breadcrumb_goster', ! empty($config['breadcrumb_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Breadcrumb goster</label></div></div><div class="col-md-6"><label class="form-label">Rozet Metni</label><input type="text" name="bilgi_kampanya_rozeti_metni" class="form-control" value="<?= esc(old('bilgi_kampanya_rozeti_metni', (string) ($config['bilgi_kampanya_rozeti_metni'] ?? ''))) ?>"></div></div><div class="mt-3"><label class="form-label">Kisa Aciklama</label><textarea name="kisa_aciklama" rows="3" class="form-control"><?= esc(old('kisa_aciklama', (string) ($config['kisa_aciklama'] ?? ''))) ?></textarea></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#detailHeroArea">Urun Ana Tanitim Alani</button></h2><div id="detailHeroArea" class="accordion-collapse collapse" data-bs-parent="#productDetailSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_urun_ana_tanitim_alani_active" value="1" <?= old('section_urun_ana_tanitim_alani_active', ! empty($sections['urun_ana_tanitim_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_urun_ana_tanitim_alani_order" class="form-control" value="<?= esc((string) old('section_urun_ana_tanitim_alani_order', (string) ($sections['urun_ana_tanitim_alani']['order'] ?? 2))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="urun_tanitim_baslik" class="form-control" value="<?= esc(old('urun_tanitim_baslik', (string) ($config['urun_tanitim_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Kisa Aciklama</label><textarea name="urun_tanitim_kisa_aciklama" rows="3" class="form-control"><?= esc(old('urun_tanitim_kisa_aciklama', (string) ($config['urun_tanitim_kisa_aciklama'] ?? ''))) ?></textarea></div><div class="row g-2"><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="kapak_galeri_goster" value="1" <?= old('kapak_galeri_goster', ! empty($config['kapak_galeri_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Kapak / galeri goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="format_etiketi_goster" value="1" <?= old('format_etiketi_goster', ! empty($config['format_etiketi_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Format etiketini goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="yazar_bilgisi_goster" value="1" <?= old('yazar_bilgisi_goster', ! empty($config['yazar_bilgisi_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Yazar bilgisini goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="favori_butonu_goster" value="1" <?= old('favori_butonu_goster', ! empty($config['favori_butonu_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Favori butonunu goster</label></div></div></div><div class="mt-3"><label class="form-label">Sepete Ekle Buton Metni</label><input type="text" name="sepete_ekle_buton_metni" class="form-control" value="<?= esc(old('sepete_ekle_buton_metni', (string) ($config['sepete_ekle_buton_metni'] ?? ''))) ?>"></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#detailPriceArea">Fiyat / Satin Alma Bilgi Alani</button></h2><div id="detailPriceArea" class="accordion-collapse collapse" data-bs-parent="#productDetailSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_fiyat_satin_alma_bilgi_alani_active" value="1" <?= old('section_fiyat_satin_alma_bilgi_alani_active', ! empty($sections['fiyat_satin_alma_bilgi_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_fiyat_satin_alma_bilgi_alani_order" class="form-control" value="<?= esc((string) old('section_fiyat_satin_alma_bilgi_alani_order', (string) ($sections['fiyat_satin_alma_bilgi_alani']['order'] ?? 3))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="fiyat_satin_alma_baslik" class="form-control" value="<?= esc(old('fiyat_satin_alma_baslik', (string) ($config['fiyat_satin_alma_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Aciklama Metni</label><textarea name="fiyat_satin_alma_aciklama" rows="3" class="form-control"><?= esc(old('fiyat_satin_alma_aciklama', (string) ($config['fiyat_satin_alma_aciklama'] ?? ''))) ?></textarea></div><div class="row g-2 mb-3"><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="eski_fiyat_goster" value="1" <?= old('eski_fiyat_goster', ! empty($config['eski_fiyat_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Eski fiyati goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="indirim_rozeti_goster" value="1" <?= old('indirim_rozeti_goster', ! empty($config['indirim_rozeti_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Indirim rozetini goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="stok_uygunluk_bilgisi_goster" value="1" <?= old('stok_uygunluk_bilgisi_goster', ! empty($config['stok_uygunluk_bilgisi_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Stok / uygunluk bilgisini goster</label></div></div></div><div class="mb-3"><label class="form-label">Dijital Erisim Kisa Notu</label><textarea name="dijital_erisim_kisa_notu" rows="2" class="form-control"><?= esc(old('dijital_erisim_kisa_notu', (string) ($config['dijital_erisim_kisa_notu'] ?? ''))) ?></textarea></div><div class="mb-3"><label class="form-label">Teslimat / Kargo Kisa Notu</label><textarea name="teslimat_kargo_kisa_notu" rows="2" class="form-control"><?= esc(old('teslimat_kargo_kisa_notu', (string) ($config['teslimat_kargo_kisa_notu'] ?? ''))) ?></textarea></div><div class="mb-0"><label class="form-label">Guvenli Alisveris Kisa Notu</label><textarea name="guvenli_alisveris_kisa_notu" rows="2" class="form-control"><?= esc(old('guvenli_alisveris_kisa_notu', (string) ($config['guvenli_alisveris_kisa_notu'] ?? ''))) ?></textarea></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#detailMetaArea">Urun Meta Bilgi Alani</button></h2><div id="detailMetaArea" class="accordion-collapse collapse" data-bs-parent="#productDetailSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_urun_meta_bilgi_alani_active" value="1" <?= old('section_urun_meta_bilgi_alani_active', ! empty($sections['urun_meta_bilgi_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_urun_meta_bilgi_alani_order" class="form-control" value="<?= esc((string) old('section_urun_meta_bilgi_alani_order', (string) ($sections['urun_meta_bilgi_alani']['order'] ?? 4))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="urun_meta_bilgi_baslik" class="form-control" value="<?= esc(old('urun_meta_bilgi_baslik', (string) ($config['urun_meta_bilgi_baslik'] ?? ''))) ?>"></div><div class="row g-2"><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="isbn_goster" value="1" <?= old('isbn_goster', ! empty($config['isbn_goster'])) ? 'checked' : '' ?>><label class="form-check-label">ISBN goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="dil_goster" value="1" <?= old('dil_goster', ! empty($config['dil_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Dil goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="sayfa_sayisi_goster" value="1" <?= old('sayfa_sayisi_goster', ! empty($config['sayfa_sayisi_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Sayfa sayisi goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="yayin_yili_goster" value="1" <?= old('yayin_yili_goster', ! empty($config['yayin_yili_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Yayin yili goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="format_goster" value="1" <?= old('format_goster', ! empty($config['format_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Format goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="kategori_etiket_goster" value="1" <?= old('kategori_etiket_goster', ! empty($config['kategori_etiket_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Kategori / etiket goster</label></div></div></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#detailContentArea">Aciklama / Icerik Alani</button></h2><div id="detailContentArea" class="accordion-collapse collapse" data-bs-parent="#productDetailSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_aciklama_icerik_alani_active" value="1" <?= old('section_aciklama_icerik_alani_active', ! empty($sections['aciklama_icerik_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_aciklama_icerik_alani_order" class="form-control" value="<?= esc((string) old('section_aciklama_icerik_alani_order', (string) ($sections['aciklama_icerik_alani']['order'] ?? 5))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="aciklama_icerik_baslik" class="form-control" value="<?= esc(old('aciklama_icerik_baslik', (string) ($config['aciklama_icerik_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Uzun Aciklama Basligi</label><input type="text" name="uzun_aciklama_basligi" class="form-control" value="<?= esc(old('uzun_aciklama_basligi', (string) ($config['uzun_aciklama_basligi'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Arka Kapak / Tanitim Basligi</label><input type="text" name="arka_kapak_tanitim_basligi" class="form-control" value="<?= esc(old('arka_kapak_tanitim_basligi', (string) ($config['arka_kapak_tanitim_basligi'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">One Cikanlar Basligi</label><input type="text" name="one_cikanlar_basligi" class="form-control" value="<?= esc(old('one_cikanlar_basligi', (string) ($config['one_cikanlar_basligi'] ?? ''))) ?>"></div><div class="mb-0"><label class="form-label">Icerik Aciklama Notu</label><textarea name="icerik_aciklama_notu" rows="3" class="form-control"><?= esc(old('icerik_aciklama_notu', (string) ($config['icerik_aciklama_notu'] ?? ''))) ?></textarea></div></div></div></div>

                        <div class="accordion-item border rounded mb-3"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#detailReviewArea">Yorum / Puan Alani</button></h2><div id="detailReviewArea" class="accordion-collapse collapse" data-bs-parent="#productDetailSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_yorum_puan_alani_active" value="1" <?= old('section_yorum_puan_alani_active', ! empty($sections['yorum_puan_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_yorum_puan_alani_order" class="form-control" value="<?= esc((string) old('section_yorum_puan_alani_order', (string) ($sections['yorum_puan_alani']['order'] ?? 6))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="yorum_puan_baslik" class="form-control" value="<?= esc(old('yorum_puan_baslik', (string) ($config['yorum_puan_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Yorum Ozeti Metni</label><textarea name="yorum_ozeti_metni" rows="3" class="form-control"><?= esc(old('yorum_ozeti_metni', (string) ($config['yorum_ozeti_metni'] ?? ''))) ?></textarea></div><div class="row g-2 mb-3"><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="puan_ortalamasi_goster" value="1" <?= old('puan_ortalamasi_goster', ! empty($config['puan_ortalamasi_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Puan ortalamasini goster</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="yorum_sayisi_goster" value="1" <?= old('yorum_sayisi_goster', ! empty($config['yorum_sayisi_goster'])) ? 'checked' : '' ?>><label class="form-check-label">Yorum sayisini goster</label></div></div></div><div class="mb-0"><label class="form-label">Yorum Yap Cagrisi Metni</label><input type="text" name="yorum_yap_cagrisi_metni" class="form-control" value="<?= esc(old('yorum_yap_cagrisi_metni', (string) ($config['yorum_yap_cagrisi_metni'] ?? ''))) ?>"></div></div></div></div>

                        <div class="accordion-item border rounded mb-4"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#detailRelatedArea">Ilgili Urunler / CTA Alani</button></h2><div id="detailRelatedArea" class="accordion-collapse collapse" data-bs-parent="#productDetailSectionsAccordion"><div class="accordion-body"><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Bolum Durumu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="section_ilgili_urunler_cta_alani_active" value="1" <?= old('section_ilgili_urunler_cta_alani_active', ! empty($sections['ilgili_urunler_cta_alani']['active']) ? '1' : '') ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div></div><div class="col-md-6"><label class="form-label">Sira</label><input type="number" min="1" name="section_ilgili_urunler_cta_alani_order" class="form-control" value="<?= esc((string) old('section_ilgili_urunler_cta_alani_order', (string) ($sections['ilgili_urunler_cta_alani']['order'] ?? 7))) ?>"></div></div><div class="mb-3"><label class="form-label">Bolum Basligi</label><input type="text" name="ilgili_urunler_cta_baslik" class="form-control" value="<?= esc(old('ilgili_urunler_cta_baslik', (string) ($config['ilgili_urunler_cta_baslik'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">Aciklama Metni</label><textarea name="ilgili_urunler_cta_aciklama" rows="3" class="form-control"><?= esc(old('ilgili_urunler_cta_aciklama', (string) ($config['ilgili_urunler_cta_aciklama'] ?? ''))) ?></textarea></div><div class="mb-3"><label class="form-label">Benzer Urunler Basligi</label><input type="text" name="benzer_urunler_basligi" class="form-control" value="<?= esc(old('benzer_urunler_basligi', (string) ($config['benzer_urunler_basligi'] ?? ''))) ?>"></div><div class="mb-3"><label class="form-label">CTA Buton Metni</label><input type="text" name="cta_buton_metni" class="form-control" value="<?= esc(old('cta_buton_metni', (string) ($config['cta_buton_metni'] ?? ''))) ?>"></div><div class="mb-0"><label class="form-label">Guven Notu / Kisa Bilgi</label><textarea name="guven_notu_kisa_bilgi" rows="3" class="form-control"><?= esc(old('guven_notu_kisa_bilgi', (string) ($config['guven_notu_kisa_bilgi'] ?? ''))) ?></textarea></div></div></div></div>
                    </div>

                    <div class="d-grid"><button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Product Detail Ayarlarini Kaydet</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xxl-7">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-1">Mini Product Detail Onizlemesi</h5><p class="text-muted mb-0">Urun gorseli, fiyat, meta bilgiler ve CTA alanlarinin sade etkisini gorun.</p></div>
            <div class="card-body">
                <?= view('admin/pages/partials/product_detail_preview', ['productDetailPreview' => $productDetailPreview ?? []]) ?>
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
                        <input type="text" name="draft_name" class="form-control" value="<?= esc(old('draft_name', $draftName)) ?>" placeholder="Orn. Urun detay taslagi">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Kisa Not</label>
                        <textarea name="draft_notes" rows="4" class="form-control" placeholder="Orn. Aciklama ve CTA metinleri guncellenecek"><?= esc(old('draft_notes', (string) ($draft['notes'] ?? ''))) ?></textarea>
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
