<?php
$preview = is_array($productListPreview ?? null) ? $productListPreview : [];
$config = is_array($preview['config'] ?? null) ? $preview['config'] : [];
$sections = is_array($preview['sections'] ?? null) ? $preview['sections'] : [];
$sectionMap = is_array($preview['sectionMap'] ?? null) ? $preview['sectionMap'] : [];
$gridColClass = (string) ($preview['gridColClass'] ?? 'col-lg-4 col-md-6');
$visibleSectionCount = (int) ($preview['visibleSectionCount'] ?? 0);
$hiddenSectionCount = (int) ($preview['hiddenSectionCount'] ?? 0);
$hasVisibleSections = ! empty($preview['hasVisibleSections']);
$orderedKeys = [
    'sayfa_ust_alani',
    'urun_listesi_gorunumu',
    'bos_sonuc_alani',
    'alt_aciklama_alani',
];
$sectionLabels = [
    'sayfa_ust_alani' => 'Sayfa üst alanı',
    'urun_listesi_gorunumu' => 'Ürün liste alanı',
    'bos_sonuc_alani' => 'Boş sonuç alanı',
    'alt_aciklama_alani' => 'Alt açıklama alanı',
];
$bannerEnabled = ! empty($config['ust_banner_goster']) && ! empty($sectionMap['sayfa_ust_alani']['active']);
$footerEnabled = ! empty($config['alt_aciklama_goster']) && ! empty($sectionMap['alt_aciklama_alani']['active']);
$hiddenSectionLabels = [];
foreach ($orderedKeys as $key) {
    if (! empty($sectionMap[$key]['active'])) {
        continue;
    }

    $hiddenSectionLabels[] = $sectionLabels[$key] ?? (string) ($sectionMap[$key]['title'] ?? $key);
}
?>

<style>
    .product-list-preview-shell {
        border: 1px solid rgba(17, 24, 39, 0.08);
        border-radius: 18px;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        overflow: hidden;
    }
    .product-list-preview-topbar {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid rgba(17, 24, 39, 0.08);
        background: rgba(248, 250, 252, 0.95);
    }
    .product-list-preview-topbar h6 {
        margin: 0 0 0.25rem;
        font-size: 1rem;
        font-weight: 800;
        color: #1f2937;
    }
    .product-list-preview-topbar p {
        margin: 0;
        color: #6b7280;
        font-size: 0.88rem;
    }
    .product-list-preview-flow {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .product-list-preview-block {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 16px;
        background: #fff;
        padding: 0.95rem 1rem;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
    }
    .product-list-preview-block.is-muted {
        border-style: dashed;
        background: rgba(248, 250, 252, 0.88);
        box-shadow: none;
    }
    .product-list-preview-block__label {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #1d4ed8;
        margin-bottom: 0.55rem;
    }
    .product-list-preview-block__title {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 800;
        color: #1f2937;
    }
    .product-list-preview-block__text {
        margin: 0.45rem 0 0;
        color: #6b7280;
        line-height: 1.6;
        font-size: 0.9rem;
    }
    .product-list-preview-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }
    .product-list-preview-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.65rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        background: rgba(241, 245, 249, 0.95);
        color: #475569;
        border: 1px solid rgba(148, 163, 184, 0.18);
    }
    .product-list-preview-banner {
        background: linear-gradient(135deg, rgba(29, 78, 216, 0.08) 0%, rgba(255, 255, 255, 0.98) 100%);
    }
    .product-list-preview-banner.is-dark {
        background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
    }
    .product-list-preview-banner.is-dark .product-list-preview-block__label,
    .product-list-preview-banner.is-dark .product-list-preview-block__title,
    .product-list-preview-banner.is-dark .product-list-preview-block__text {
        color: #f8fafc;
    }
    .product-list-preview-toolbar {
        display: grid;
        gap: 0.75rem;
    }
    .product-list-preview-toolbar__row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: center;
    }
    .product-list-preview-sample-note {
        margin-top: 0.75rem;
        padding: 0.75rem 0.9rem;
        border-radius: 14px;
        background: rgba(239, 246, 255, 0.9);
        color: #475569;
        font-size: 0.84rem;
        border: 1px solid rgba(59, 130, 246, 0.14);
    }
    .product-list-preview-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: 0.8rem;
    }
    .product-list-preview-card {
        border: 1px dashed rgba(148, 163, 184, 0.28);
        border-radius: 14px;
        background: rgba(248, 250, 252, 0.95);
        padding: 0.85rem;
    }
    .product-list-preview-card__media {
        height: 84px;
        border-radius: 12px;
        background: linear-gradient(135deg, #e2e8f0 0%, #f8fafc 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        margin-bottom: 0.7rem;
    }
    .product-list-preview-card__title {
        margin: 0 0 0.3rem;
        font-size: 0.92rem;
        font-weight: 700;
        color: #334155;
    }
    .product-list-preview-card__hint {
        margin: 0;
        color: #64748b;
        font-size: 0.78rem;
    }
    .product-list-preview-hidden {
        margin-top: 0.35rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .product-list-preview-hidden .product-list-preview-pill {
        background: rgba(248, 250, 252, 0.96);
        color: #94a3b8;
        border-style: dashed;
    }
    @media (max-width: 991.98px) {
        .product-list-preview-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 575.98px) {
        .product-list-preview-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="product-list-preview-shell">
    <div class="product-list-preview-topbar">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h6>Canlı sayfa yapısı ön izlemesi</h6>
                <p>Bu alan, ürün listeleme sayfasında hangi bölümlerin hangi sırayla görüneceğini sadeleştirilmiş olarak gösterir.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-light-primary"><?= esc((string) $visibleSectionCount) ?> aktif bölüm</span>
                <span class="badge bg-light-secondary"><?= esc((string) $hiddenSectionCount) ?> gizli bölüm</span>
            </div>
        </div>
    </div>

    <div class="product-list-preview-flow">
        <?php if (! $hasVisibleSections): ?>
            <div class="alert alert-light border mb-0">
                <div class="fw-semibold mb-1">Ön izlemede görünür bölüm yok</div>
                <div class="small text-muted">Tüm bölümler pasif durumda. En az bir bölümü aktif yaptığınızda sayfa akışı burada görünür.</div>
            </div>
        <?php endif; ?>

        <?php if (! empty($sectionMap['sayfa_ust_alani']['active'])): ?>
            <section class="product-list-preview-block">
                <div class="product-list-preview-block__label">
                    <i class="ti ti-layout-navbar"></i>
                    <span>Sayfa üst alanı</span>
                </div>
                <?php if (! empty($config['breadcrumb_goster'])): ?>
                    <div class="small text-muted mb-2">Ana Sayfa / Kategoriler / <?= esc((string) ($config['sayfa_basligi'] ?? 'Kategori Sayfası')) ?></div>
                <?php endif; ?>
                <h4 class="product-list-preview-block__title"><?= esc((string) ($config['sayfa_basligi'] ?? 'Kategori Sayfası')) ?></h4>
                <p class="product-list-preview-block__text"><?= esc((string) ($config['sayfa_alt_basligi'] ?? 'Öne çıkan ürünleri ve filtreleri düzenleyin.')) ?></p>
                <div class="product-list-preview-meta">
                    <span class="product-list-preview-pill">Sıra <?= esc((string) ($sectionMap['sayfa_ust_alani']['order'] ?? 1)) ?></span>
                    <span class="product-list-preview-pill"><?= ! empty($config['breadcrumb_goster']) ? 'Breadcrumb açık' : 'Breadcrumb gizli' ?></span>
                </div>
            </section>
        <?php else: ?>
            <section class="product-list-preview-block is-muted">
                <div class="product-list-preview-block__label">
                    <i class="ti ti-layout-navbar"></i>
                    <span>Sayfa üst alanı</span>
                </div>
                <p class="product-list-preview-block__text">Bu bölüm kapalı olduğu için canlı sayfada üst başlık alanı görünmez.</p>
            </section>
        <?php endif; ?>

        <?php if ($bannerEnabled): ?>
            <section class="product-list-preview-block product-list-preview-banner<?= ($config['banner_tonu'] ?? 'soft') === 'dark' ? ' is-dark' : '' ?>">
                <div class="product-list-preview-block__label">
                    <i class="ti ti-photo"></i>
                    <span>Banner alanı</span>
                </div>
                <h4 class="product-list-preview-block__title"><?= esc((string) ($config['banner_basligi'] ?? 'Seçili Kategori')) ?></h4>
                <p class="product-list-preview-block__text"><?= esc((string) ($config['banner_alt_metni'] ?? 'Listeleme sayfasının üst alanını yönetin.')) ?></p>
                <div class="product-list-preview-meta">
                    <span class="product-list-preview-pill">Banner açık</span>
                    <span class="product-list-preview-pill">Ton: <?= esc((string) ($config['banner_tonu'] ?? 'soft')) ?></span>
                    <span class="product-list-preview-pill"><?= trim((string) ($config['banner_gorseli'] ?? '')) !== '' ? 'Görsel tanımlı' : 'Görsel örnek alanı' ?></span>
                </div>
            </section>
        <?php endif; ?>

        <section class="product-list-preview-block<?= empty($sectionMap['urun_listesi_gorunumu']['active']) ? ' is-muted' : '' ?>">
            <div class="product-list-preview-block__label">
                <i class="ti ti-layout-grid"></i>
                <span>Ürün liste alanı</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="product-list-preview-pill"><?= ! empty($sectionMap['urun_listesi_gorunumu']['active']) ? 'Ürün listesi aktif' : 'Ürün listesi kapalı' ?></span>
                <span class="product-list-preview-pill">Kart: <?= esc((string) ($config['kart_varyanti'] ?? 'classic')) ?></span>
                <span class="product-list-preview-pill">Grid: <?= esc((string) ($config['grid_yogunlugu'] ?? '3')) ?> kolon</span>
                <span class="product-list-preview-pill"><?= ! empty($config['rozetleri_goster']) ? 'Rozetler görünür' : 'Rozetler gizli' ?></span>
                <span class="product-list-preview-pill"><?= ! empty($config['favori_butonu_goster']) ? 'Favori butonu görünür' : 'Favori butonu gizli' ?></span>
                <span class="product-list-preview-pill"><?= ! empty($config['hizli_aksiyonlari_goster']) ? 'Hızlı aksiyon açık' : 'Hızlı aksiyon kapalı' ?></span>
            </div>
            <div class="product-list-preview-sample-note">
                Aşağıdaki kartlar yalnızca düzen ön izlemesidir. Canlı ürün verisi gösterilmez.
            </div>
            <div class="row g-3 mt-1">
                <?php for ($i = 0; $i < max(2, (int) ($config['grid_yogunlugu'] ?? 3)); $i++): ?>
                    <div class="<?= esc($gridColClass, 'attr') ?>">
                        <div class="product-list-preview-card h-100">
                            <div class="product-list-preview-card__media">
                                <i class="ti ti-photo"></i>
                            </div>
                            <h5 class="product-list-preview-card__title">Örnek ürün kartı <?= esc((string) ($i + 1)) ?></h5>
                            <p class="product-list-preview-card__hint">Yerleşim örneği, canlı veri değildir.</p>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </section>

        <?php if (! empty($sectionMap['bos_sonuc_alani']['active'])): ?>
            <section class="product-list-preview-block">
                <div class="product-list-preview-block__label">
                    <i class="ti ti-mood-empty"></i>
                    <span>Boş sonuç alanı</span>
                </div>
                <h4 class="product-list-preview-block__title"><?= esc((string) ($config['bos_sonuc_basligi'] ?? 'Sonuç bulunamadı')) ?></h4>
                <p class="product-list-preview-block__text"><?= esc((string) ($config['bos_sonuc_aciklamasi'] ?? 'Filtreleri değiştirerek tekrar deneyin.')) ?></p>
                <div class="product-list-preview-meta">
                    <span class="product-list-preview-pill">Ton: <?= esc((string) ($config['bos_sonuc_tonu'] ?? 'warning')) ?></span>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($footerEnabled): ?>
            <section class="product-list-preview-block">
                <div class="product-list-preview-block__label">
                    <i class="ti ti-align-box-bottom-left"></i>
                    <span>Alt açıklama alanı</span>
                </div>
                <h4 class="product-list-preview-block__title"><?= esc((string) ($config['alt_aciklama_basligi'] ?? 'Listeleme Açıklaması')) ?></h4>
                <p class="product-list-preview-block__text"><?= esc((string) ($config['alt_aciklama_metni'] ?? 'Bu alan kategoriye ait açıklayıcı metinler için kullanılır.')) ?></p>
            </section>
        <?php endif; ?>

        <?php if (! empty($hiddenSectionLabels)): ?>
            <section class="product-list-preview-block is-muted">
                <div class="product-list-preview-block__label">
                    <i class="ti ti-eye-off"></i>
                    <span>Gizli bölümler</span>
                </div>
                <p class="product-list-preview-block__text">Aşağıdaki alanlar bu taslakta kapalıdır ve canlı sayfada gösterilmez.</p>
                <div class="product-list-preview-hidden">
                    <?php foreach ($hiddenSectionLabels as $label): ?>
                        <span class="product-list-preview-pill"><?= esc((string) $label) ?></span>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>
