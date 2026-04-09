<?php
$preview = is_array($productListPreview ?? null) ? $productListPreview : [];
$config = is_array($preview['config'] ?? null) ? $preview['config'] : [];
$sections = is_array($preview['sections'] ?? null) ? $preview['sections'] : [];
$gridColClass = (string) ($preview['gridColClass'] ?? 'col-lg-4 col-md-6');
$visibleSectionCount = (int) ($preview['visibleSectionCount'] ?? 0);
$hiddenSectionCount = (int) ($preview['hiddenSectionCount'] ?? 0);
$hasVisibleSections = ! empty($preview['hasVisibleSections']);
?>

<div class="card border shadow-none bg-light mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="fw-semibold mb-1">Product List Mini Preview</div>
                <div class="small text-muted">Kayitli veya formdaki config ile olusturulan sade yonetici onizlemesi.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-light-primary"><?= esc((string) $visibleSectionCount) ?> aktif bolum</span>
                <span class="badge bg-light-secondary"><?= esc((string) $hiddenSectionCount) ?> gizli bolum</span>
            </div>
        </div>
    </div>
</div>

<?php if (! $hasVisibleSections): ?>
    <div class="alert alert-light border mb-3">
        <div class="fw-semibold mb-1">Onizleme bos kalmadi</div>
        <div class="small text-muted">Bu taslakta tum section alanlari pasif durumda. En az bir bolumu aktiflestirdiginizde sayfa akisi burada gorunur.</div>
    </div>
<?php endif; ?>

<?php if (! empty($config['breadcrumb_goster']) && ! empty($config['sections']['sayfa_ust_alani']['active'])): ?>
    <div class="small text-muted mb-3">Ana Sayfa / Kategoriler / <?= esc((string) ($config['sayfa_basligi'] ?? 'Kategori Sayfasi')) ?></div>
<?php endif; ?>

<?php foreach ($sections as $section): ?>
    <?php if (! ($section['active'] ?? false)) {
        continue;
    } ?>
    <?php if (($section['key'] ?? '') === 'sayfa_ust_alani'): ?>
        <div class="card border shadow-none mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <span class="badge bg-light-primary mb-2"><?= esc((string) ($section['title'] ?? 'Sayfa Ust Alani')) ?></span>
                        <h4 class="mb-1"><?= esc((string) ($config['sayfa_basligi'] ?? 'Kategori Sayfasi')) ?></h4>
                        <p class="text-muted mb-0"><?= esc((string) ($config['sayfa_alt_basligi'] ?? 'One cikan urunleri ve filtreleri duzenleyin')) ?></p>
                    </div>
                    <span class="badge bg-light-secondary">Sira <?= esc((string) ($section['order'] ?? 1)) ?></span>
                </div>
            </div>
        </div>
        <?php if (! empty($config['ust_banner_goster'])): ?>
            <div class="card border-0 <?= ($config['banner_tonu'] ?? 'light') === 'dark' ? 'bg-dark text-white' : (($config['banner_tonu'] ?? 'light') === 'accent' ? 'bg-light-primary' : 'bg-light') ?> mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-7">
                            <span class="badge bg-light-secondary mb-2">Banner</span>
                            <h5 class="mb-1"><?= esc((string) ($config['banner_basligi'] ?? 'Secili Kategori')) ?></h5>
                            <p class="mb-0 <?= ($config['banner_tonu'] ?? 'light') === 'dark' ? 'text-white-50' : 'text-muted' ?>"><?= esc((string) ($config['banner_alt_metni'] ?? 'Listeleme sayfasinin ust alanini yonetin')) ?></p>
                        </div>
                        <div class="col-md-5">
                            <div class="card border shadow-none mb-0">
                                <div class="card-body text-center">
                                    <?php if (trim((string) ($config['banner_gorseli'] ?? '')) !== ''): ?>
                                        <div class="ratio ratio-16x9">
                                            <img src="<?= esc((string) $config['banner_gorseli'], 'attr') ?>" alt="Banner preview" class="img-fluid rounded object-fit-cover">
                                        </div>
                                    <?php else: ?>
                                        <i class="ti ti-photo text-muted fs-2 d-block mb-2"></i>
                                        <div class="small text-muted">Banner gorseli preview</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php elseif (($section['key'] ?? '') === 'filtre_alani'): ?>
        <div class="card border shadow-none mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <div class="fw-semibold"><?= esc((string) ($config['filtre_basligi'] ?? 'Filtreler')) ?></div>
                    <span class="badge bg-light-secondary"><?= ($config['filtre_konumu'] ?? 'left') === 'top' ? 'Ust Konum' : 'Sol Konum' ?></span>
                </div>
                <?php if (! empty($config['filtreler_goster'])): ?>
                    <?php if (($config['filtre_konumu'] ?? 'left') === 'top'): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light-secondary">Kategori</span>
                            <span class="badge bg-light-secondary">Fiyat</span>
                            <span class="badge bg-light-secondary">Yazar</span>
                            <?php if (! empty($config['filtre_ozeti_goster'])): ?>
                                <span class="badge bg-light-primary">3 aktif filtre</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted mb-2">Sol filtre paneli</div>
                                    <div class="fw-semibold mb-1">Kategori</div>
                                    <div class="small text-muted">Fiyat araligi</div>
                                    <div class="small text-muted">Stok durumu</div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <div class="small text-muted">Filtreler sol panel olarak konumlanir.</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-light border mb-0">Filtre alani bu surumde gizlenmis durumda.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif (($section['key'] ?? '') === 'siralama_sonuc_cubugu'): ?>
        <div class="card border shadow-none mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex flex-wrap gap-2">
                        <?php if (! empty($config['siralama_cubugu_goster'])): ?>
                            <span class="badge bg-light-primary">Siralama Acik</span>
                        <?php endif; ?>
                        <span class="badge bg-light-secondary"><?= esc((string) ($config['varsayilan_grid_yogunlugu'] ?? '3')) ?> kolon varsayilan</span>
                        <?php if (! empty($config['aktif_filtre_etiketleri_goster'])): ?>
                            <span class="badge bg-light-warning">Aktif filtre etiketleri</span>
                        <?php endif; ?>
                    </div>
                    <?php if (! empty($config['sonuc_sayisi_goster'])): ?>
                        <div class="small text-muted">128 sonuc</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif (($section['key'] ?? '') === 'urun_listesi_gorunumu'): ?>
        <div class="card border shadow-none mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-light-primary"><?= esc((string) ($config['kart_varyanti'] ?? 'classic')) ?> kart</span>
                    <span class="badge bg-light-secondary"><?= esc((string) ($config['grid_yogunlugu'] ?? '3')) ?> kolon</span>
                    <?php if (! empty($config['rozetleri_goster'])): ?>
                        <span class="badge bg-light-warning">Rozetler</span>
                    <?php endif; ?>
                </div>
                <div class="row g-3">
                    <?php for ($i = 0; $i < ((int) ($config['grid_yogunlugu'] ?? 3) * 2); $i++): ?>
                        <div class="<?= esc($gridColClass, 'attr') ?>">
                            <div class="card <?= ($config['kart_varyanti'] ?? 'classic') === 'elevated' ? 'shadow-sm border-0' : 'border shadow-none' ?> h-100 mb-0">
                                <div class="card-body">
                                    <div class="bg-light rounded-3 p-4 text-center mb-3"><i class="ti ti-photo text-primary"></i></div>
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                        <div class="fw-semibold text-truncate">Ornek Urun <?= esc((string) ($i + 1)) ?></div>
                                        <?php if (! empty($config['favori_butonu_goster'])): ?>
                                            <i class="ti ti-heart text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted mb-2">Listeleme kart preview</div>
                                    <?php if (! empty($config['rozetleri_goster'])): ?>
                                        <span class="badge bg-light-warning">Yeni</span>
                                    <?php endif; ?>
                                    <?php if (! empty($config['hizli_aksiyonlari_goster'])): ?>
                                        <div class="d-flex gap-2 mt-3"><span class="btn btn-sm btn-outline-secondary disabled">Hizli Bakis</span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    <?php elseif (($section['key'] ?? '') === 'bilgilendirme_kampanya_alani' && ! empty($config['bilgilendirme_alani_goster'])): ?>
        <div class="card border shadow-none mb-3">
            <div class="card-body">
                <div class="alert alert-<?= esc((string) ($config['bilgilendirme_tonu'] ?? 'info')) ?> mb-0">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-8">
                            <div class="fw-semibold"><?= esc((string) ($config['bilgilendirme_basligi'] ?? 'Kargo Bilgisi')) ?></div>
                            <div class="small"><?= esc((string) ($config['bilgilendirme_metni'] ?? '250 TL ve uzeri siparislerde ucretsiz kargo.')) ?></div>
                        </div>
                        <div class="col-md-4">
                            <?php if (trim((string) ($config['bilgilendirme_gorseli'] ?? '')) !== ''): ?>
                                <div class="ratio ratio-16x9">
                                    <img src="<?= esc((string) $config['bilgilendirme_gorseli'], 'attr') ?>" alt="Bilgilendirme gorseli" class="img-fluid rounded object-fit-cover">
                                </div>
                            <?php else: ?>
                                <div class="border rounded p-3 text-center bg-white"><i class="ti ti-speakerphone text-muted"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (($section['key'] ?? '') === 'bos_sonuc_alani'): ?>
        <div class="card border shadow-none mb-3">
            <div class="card-body">
                <div class="alert alert-<?= esc((string) ($config['bos_sonuc_tonu'] ?? 'warning')) ?> mb-0">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-8">
                            <div class="fw-semibold"><?= esc((string) ($config['bos_sonuc_basligi'] ?? 'Sonuc bulunamadi')) ?></div>
                            <div class="small"><?= esc((string) ($config['bos_sonuc_aciklamasi'] ?? 'Filtreleri degistirerek tekrar deneyin.')) ?></div>
                        </div>
                        <div class="col-md-4">
                            <?php if (trim((string) ($config['bos_sonuc_gorseli'] ?? '')) !== ''): ?>
                                <div class="ratio ratio-16x9">
                                    <img src="<?= esc((string) $config['bos_sonuc_gorseli'], 'attr') ?>" alt="Bos sonuc gorseli" class="img-fluid rounded object-fit-cover">
                                </div>
                            <?php else: ?>
                                <div class="border rounded p-3 text-center bg-white"><i class="ti ti-mood-empty text-muted"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (($section['key'] ?? '') === 'alt_aciklama_alani' && ! empty($config['alt_aciklama_goster'])): ?>
        <div class="card border shadow-none mb-0">
            <div class="card-body">
                <span class="badge bg-light-secondary mb-2"><?= esc((string) ($section['title'] ?? 'Alt Aciklama Alani')) ?></span>
                <h5 class="mb-1"><?= esc((string) ($config['alt_aciklama_basligi'] ?? 'Kategori Hakkinda')) ?></h5>
                <p class="text-muted mb-0"><?= esc((string) ($config['alt_aciklama_metni'] ?? 'Listeleme sayfasinin altinda kisa bir aciklama alani gosterilir.')) ?></p>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
