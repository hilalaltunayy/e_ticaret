<?php
$preview = is_array($productDetailPreview ?? null) ? $productDetailPreview : [];
$config = is_array($preview['config'] ?? null) ? $preview['config'] : [];
$sections = is_array($preview['sections'] ?? null) ? $preview['sections'] : [];
$product = is_array($preview['product'] ?? null) ? $preview['product'] : [];
$hasVisibleSections = ! empty($preview['hasVisibleSections']);
$visibleSectionCount = (int) ($preview['visibleSectionCount'] ?? 0);

$sectionMap = [];
foreach ($sections as $section) {
    if (! is_array($section) || empty($section['key'])) {
        continue;
    }

    $sectionMap[(string) $section['key']] = $section;
}

$topSection = $sectionMap['sayfa_ust_alani'] ?? ['active' => true];
$heroSection = $sectionMap['urun_ana_tanitim_alani'] ?? ['active' => true];
$priceSection = $sectionMap['fiyat_satin_alma_bilgi_alani'] ?? ['active' => true];
$metaSection = $sectionMap['urun_meta_bilgi_alani'] ?? ['active' => true];
$contentSection = $sectionMap['aciklama_icerik_alani'] ?? ['active' => true];
$reviewSection = $sectionMap['yorum_puan_alani'] ?? ['active' => true];
$relatedSection = $sectionMap['ilgili_urunler_cta_alani'] ?? ['active' => true];

$pageTitle = trim((string) ($config['sayfa_basligi'] ?? 'Urun Detayi'));
$pageSubtitle = trim((string) ($config['sayfa_alt_basligi'] ?? ''));
$shortDescription = trim((string) ($config['kisa_aciklama'] ?? ''));
$promoBadge = trim((string) ($config['bilgi_kampanya_rozeti_metni'] ?? ''));
$heroTitle = trim((string) ($config['urun_tanitim_baslik'] ?? ''));
$heroDescription = trim((string) ($config['urun_tanitim_kisa_aciklama'] ?? ''));
$contentTitle = trim((string) ($config['aciklama_icerik_baslik'] ?? 'Aciklama ve Icerik'));
$metaTitle = trim((string) ($config['urun_meta_bilgi_baslik'] ?? ''));
$reviewTitle = trim((string) ($config['yorum_puan_baslik'] ?? ''));
$reviewSummary = trim((string) ($config['yorum_ozeti_metni'] ?? ''));
$reviewCallout = trim((string) ($config['yorum_yap_cagrisi_metni'] ?? ''));
$relatedTitle = trim((string) ($config['benzer_urunler_basligi'] ?? 'Benzer Urunler'));
$relatedDescription = trim((string) ($config['ilgili_urunler_cta_aciklama'] ?? ''));
?>

<style>
    .product-detail-builder-preview {
        display: grid;
        gap: 1rem;
    }
    .product-detail-builder-preview-shell,
    .product-detail-builder-preview-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }
    .product-detail-builder-preview-shell {
        padding: 1rem;
    }
    .product-detail-builder-preview-breadcrumb {
        color: #64748b;
        font-size: 0.85rem;
        margin-bottom: 0.85rem;
    }
    .product-detail-builder-preview-hero {
        display: grid;
        grid-template-columns: minmax(220px, 300px) minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }
    .product-detail-builder-preview-cover,
    .product-detail-builder-preview-info {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 20px;
        background: #fff;
        padding: 0.95rem;
    }
    .product-detail-builder-preview-cover-frame {
        border-radius: 18px;
        background: linear-gradient(180deg, #eef5ff 0%, #ffffff 100%);
        padding: 0.45rem;
    }
    .product-detail-builder-preview-cover-image {
        width: 100%;
        aspect-ratio: 4 / 5.2;
        border-radius: 16px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .product-detail-builder-preview-label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.75rem;
        padding: 0.32rem 0.7rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .product-detail-builder-preview-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-bottom: 0.85rem;
    }
    .product-detail-builder-preview-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.68rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.76rem;
        font-weight: 700;
    }
    .product-detail-builder-preview-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .product-detail-builder-preview-author,
    .product-detail-builder-preview-copy,
    .product-detail-builder-preview-meta-note {
        color: #64748b;
        line-height: 1.65;
    }
    .product-detail-builder-preview-author {
        margin: 0.55rem 0 0;
        font-size: 0.95rem;
    }
    .product-detail-builder-preview-copy {
        margin: 0.65rem 0 0;
        font-size: 0.9rem;
    }
    .product-detail-builder-preview-rating {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 0.95rem;
        padding: 0.8rem 0.9rem;
        border-radius: 16px;
        background: rgba(255, 251, 235, 0.9);
        border: 1px solid rgba(245, 158, 11, 0.14);
    }
    .product-detail-builder-preview-rating-stars {
        color: #f59e0b;
        letter-spacing: 0.04em;
        font-size: 0.92rem;
    }
    .product-detail-builder-preview-price {
        margin-top: 1rem;
        padding: 0.95rem 1rem;
        border-radius: 18px;
        background: rgba(239, 246, 255, 0.92);
        border: 1px solid rgba(59, 130, 246, 0.1);
    }
    .product-detail-builder-preview-price-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .product-detail-builder-preview-price-old {
        color: #dc2626;
        font-size: 0.84rem;
        text-decoration: line-through;
    }
    .product-detail-builder-preview-price-current {
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 800;
    }
    .product-detail-builder-preview-stock {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.75rem;
        border-radius: 999px;
        background: rgba(22, 163, 74, 0.1);
        color: #166534;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .product-detail-builder-preview-actions {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
        margin-top: 0.95rem;
    }
    .product-detail-builder-preview-btn {
        min-height: 40px;
        padding: 0.75rem 1rem;
        border-radius: 14px;
        font-size: 0.84rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .product-detail-builder-preview-btn.primary {
        background: linear-gradient(135deg, #1d4ed8 0%, #38bdf8 100%);
        color: #fff;
    }
    .product-detail-builder-preview-btn.secondary {
        background: rgba(248, 250, 252, 0.95);
        border: 1px solid rgba(148, 163, 184, 0.22);
        color: #334155;
    }
    .product-detail-builder-preview-grid {
        display: grid;
        gap: 1rem;
    }
    .product-detail-builder-preview-card {
        padding: 1rem;
    }
    .product-detail-builder-preview-card-title {
        margin: 0 0 0.8rem;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
    }
    .product-detail-builder-preview-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
    }
    .product-detail-builder-preview-meta-item {
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.85);
        padding: 0.85rem;
    }
    .product-detail-builder-preview-meta-label {
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
    }
    .product-detail-builder-preview-meta-value {
        color: #0f172a;
        font-size: 0.92rem;
        font-weight: 600;
        line-height: 1.45;
    }
    .product-detail-builder-preview-related-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.7rem;
    }
    .product-detail-builder-preview-related-card {
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.88);
        padding: 0.85rem;
    }
    .product-detail-builder-preview-related-thumb {
        width: 100%;
        height: 100px;
        border-radius: 14px;
        background: #f8fafc;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.76rem;
        font-weight: 700;
        margin-bottom: 0.7rem;
    }
    .product-detail-builder-preview-related-name {
        color: #0f172a;
        font-size: 0.88rem;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 0.45rem;
    }
    .product-detail-builder-preview-related-price {
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 800;
    }
    @media (max-width: 991.98px) {
        .product-detail-builder-preview-hero {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 767.98px) {
        .product-detail-builder-preview-meta-grid,
        .product-detail-builder-preview-related-grid {
            grid-template-columns: 1fr;
        }
        .product-detail-builder-preview-actions .product-detail-builder-preview-btn {
            width: 100%;
        }
    }
</style>

<div class="product-detail-builder-preview">
    <div class="card border shadow-none bg-light mb-0">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <div class="fw-semibold mb-1">Canli urun detay akisina yakin on izleme</div>
                    <div class="small text-muted">Bu alan gercek storefront duzenini ornekler; urun verisi canli degildir.</div>
                </div>
                <span class="badge bg-light-primary"><?= esc((string) $visibleSectionCount) ?> aktif bolum</span>
            </div>
        </div>
    </div>

    <?php if (! $hasVisibleSections): ?>
        <div class="alert alert-light border mb-0">
            <div class="fw-semibold mb-1">On izleme hazir</div>
            <div class="small text-muted">Tum bolumler pasif olsa bile product detail akisinin bos kalmamasi icin guvenli fallback durum gosterilir.</div>
        </div>
    <?php endif; ?>

    <div class="product-detail-builder-preview-shell">
        <?php if (! empty($config['breadcrumb_goster']) && ! empty($topSection['active'])): ?>
            <div class="product-detail-builder-preview-breadcrumb">Ana Sayfa / Kitaplar / <?= esc((string) ($product['name'] ?? 'Urun')) ?></div>
        <?php endif; ?>

        <div class="product-detail-builder-preview-hero">
            <div class="product-detail-builder-preview-cover">
                <div class="product-detail-builder-preview-label">Kapak / galeri alani</div>
                <div class="product-detail-builder-preview-cover-frame">
                    <div class="product-detail-builder-preview-cover-image">URUN GORSELI</div>
                </div>
            </div>

            <div class="product-detail-builder-preview-info">
                <?php if (! empty($heroSection['active'])): ?>
                    <div class="product-detail-builder-preview-label">Urun ana tanitim alani</div>
                <?php endif; ?>

                <div class="product-detail-builder-preview-badges">
                    <?php if ($promoBadge !== ''): ?>
                        <span class="product-detail-builder-preview-badge"><?= esc($promoBadge) ?></span>
                    <?php endif; ?>
                    <?php if (! empty($config['format_etiketi_goster'])): ?>
                        <span class="product-detail-builder-preview-badge"><?= esc((string) ($product['format'] ?? 'Basili')) ?></span>
                    <?php endif; ?>
                    <span class="product-detail-builder-preview-badge">Egitim</span>
                </div>

                <h3 class="product-detail-builder-preview-title"><?= esc((string) ($product['name'] ?? 'Urun')) ?></h3>
                <?php if (! empty($config['yazar_bilgisi_goster'])): ?>
                    <p class="product-detail-builder-preview-author">Yazar: <?= esc((string) ($product['author'] ?? 'Hilal Y.')) ?></p>
                <?php endif; ?>
                <?php if ($pageSubtitle !== ''): ?>
                    <p class="product-detail-builder-preview-copy"><?= esc($pageSubtitle) ?></p>
                <?php endif; ?>
                <?php if ($shortDescription !== ''): ?>
                    <p class="product-detail-builder-preview-copy"><?= esc($shortDescription) ?></p>
                <?php endif; ?>
                <?php if ($heroTitle !== ''): ?>
                    <p class="product-detail-builder-preview-copy"><?= esc($heroTitle) ?></p>
                <?php endif; ?>
                <?php if ($heroDescription !== ''): ?>
                    <p class="product-detail-builder-preview-copy"><?= esc($heroDescription) ?></p>
                <?php endif; ?>

                <div class="product-detail-builder-preview-rating">
                    <div>
                        <div class="product-detail-builder-preview-rating-stars">★★★★★</div>
                        <div class="product-detail-builder-preview-copy mt-1"><?= esc(number_format((float) ($product['rating'] ?? 4.8), 1, ',', '.')) ?>/5 · <?= esc((string) ($product['review_count'] ?? 126)) ?> degerlendirme</div>
                    </div>
                    <?php if (! empty($config['favori_butonu_goster'])): ?>
                        <span class="product-detail-builder-preview-badge">Favori butonu</span>
                    <?php endif; ?>
                </div>

                <?php if (! empty($priceSection['active'])): ?>
                    <div class="product-detail-builder-preview-price">
                        <div class="product-detail-builder-preview-price-row">
                            <div>
                                <?php if (! empty($config['eski_fiyat_goster'])): ?>
                                    <div class="product-detail-builder-preview-price-old"><?= esc(number_format((float) ($product['old_price'] ?? 399.90), 2, ',', '.')) ?> TL</div>
                                <?php endif; ?>
                                <div class="product-detail-builder-preview-price-current"><?= esc(number_format((float) ($product['price'] ?? 349.90), 2, ',', '.')) ?> TL</div>
                            </div>
                            <?php if (! empty($config['stok_uygunluk_bilgisi_goster'])): ?>
                                <span class="product-detail-builder-preview-stock"><?= esc((string) ($product['stock_message'] ?? 'Stokta var')) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (trim((string) ($config['fiyat_satin_alma_aciklama'] ?? '')) !== ''): ?>
                            <p class="product-detail-builder-preview-copy"><?= esc((string) $config['fiyat_satin_alma_aciklama']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="product-detail-builder-preview-actions">
                    <span class="product-detail-builder-preview-btn primary"><?= esc(trim((string) ($config['sepete_ekle_buton_metni'] ?? 'Sepete Ekle')) ?: 'Sepete Ekle') ?></span>
                    <span class="product-detail-builder-preview-btn secondary">Listeye Don</span>
                </div>
            </div>
        </div>
    </div>

    <div class="product-detail-builder-preview-grid">
        <?php if (! empty($contentSection['active'])): ?>
            <div class="product-detail-builder-preview-card">
                <h4 class="product-detail-builder-preview-card-title"><?= esc($contentTitle) ?></h4>
                <?php if (trim((string) ($config['uzun_aciklama_basligi'] ?? '')) !== ''): ?>
                    <div class="product-detail-builder-preview-meta-label"><?= esc((string) $config['uzun_aciklama_basligi']) ?></div>
                <?php endif; ?>
                <p class="product-detail-builder-preview-copy">Bu alan canli sayfadaki aciklama, urun bilgileri, genel bakis ve yorum sekmelerini temsil eder.</p>
                <?php if (trim((string) ($config['icerik_aciklama_notu'] ?? '')) !== ''): ?>
                    <p class="product-detail-builder-preview-copy"><?= esc((string) $config['icerik_aciklama_notu']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($metaSection['active'])): ?>
            <div class="product-detail-builder-preview-card">
                <h4 class="product-detail-builder-preview-card-title"><?= esc($metaTitle !== '' ? $metaTitle : 'Urun Meta Bilgileri') ?></h4>
                <div class="product-detail-builder-preview-meta-grid">
                    <?php if (! empty($config['isbn_goster'])): ?>
                        <div class="product-detail-builder-preview-meta-item">
                            <div class="product-detail-builder-preview-meta-label">ISBN</div>
                            <div class="product-detail-builder-preview-meta-value"><?= esc((string) ($product['isbn'] ?? '978-625-0000-12-3')) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (! empty($config['dil_goster'])): ?>
                        <div class="product-detail-builder-preview-meta-item">
                            <div class="product-detail-builder-preview-meta-label">Dil</div>
                            <div class="product-detail-builder-preview-meta-value"><?= esc((string) ($product['language'] ?? 'Turkce')) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (! empty($config['sayfa_sayisi_goster'])): ?>
                        <div class="product-detail-builder-preview-meta-item">
                            <div class="product-detail-builder-preview-meta-label">Sayfa</div>
                            <div class="product-detail-builder-preview-meta-value"><?= esc((string) ($product['page_count'] ?? '240')) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (! empty($config['yayin_yili_goster'])): ?>
                        <div class="product-detail-builder-preview-meta-item">
                            <div class="product-detail-builder-preview-meta-label">Yayin yili</div>
                            <div class="product-detail-builder-preview-meta-value"><?= esc((string) ($product['publish_year'] ?? '2026')) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (! empty($reviewSection['active'])): ?>
            <div class="product-detail-builder-preview-card">
                <h4 class="product-detail-builder-preview-card-title"><?= esc($reviewTitle !== '' ? $reviewTitle : 'Yorumlar ve Puanlar') ?></h4>
                <?php if ($reviewSummary !== ''): ?>
                    <p class="product-detail-builder-preview-copy"><?= esc($reviewSummary) ?></p>
                <?php endif; ?>
                <div class="product-detail-builder-preview-rating mt-3">
                    <div>
                        <div class="product-detail-builder-preview-rating-stars">★★★★☆</div>
                        <div class="product-detail-builder-preview-copy mt-1">Yorum ozeti preview alani</div>
                    </div>
                    <span class="product-detail-builder-preview-badge"><?= esc((string) ($product['review_count'] ?? 126)) ?> yorum</span>
                </div>
                <?php if ($reviewCallout !== ''): ?>
                    <p class="product-detail-builder-preview-copy"><?= esc($reviewCallout) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($relatedSection['active'])): ?>
            <div class="product-detail-builder-preview-card">
                <h4 class="product-detail-builder-preview-card-title"><?= esc($relatedTitle) ?></h4>
                <?php if ($relatedDescription !== ''): ?>
                    <p class="product-detail-builder-preview-copy"><?= esc($relatedDescription) ?></p>
                <?php endif; ?>
                <div class="product-detail-builder-preview-related-grid">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <div class="product-detail-builder-preview-related-card">
                            <div class="product-detail-builder-preview-related-thumb">ORNEK</div>
                            <div class="product-detail-builder-preview-related-name">Benzer urun karti <?= esc((string) ($i + 1)) ?></div>
                            <div class="product-detail-builder-preview-related-price"><?= esc(number_format(249.90 + ($i * 20), 2, ',', '.')) ?> TL</div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
