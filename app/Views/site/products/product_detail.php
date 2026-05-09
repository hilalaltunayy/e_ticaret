<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('product_media');

$productName = is_object($product ?? null) ? (string) ($product->product_name ?? '') : (string) ($product['product_name'] ?? '');
$productId = is_object($product ?? null) ? (string) ($product->id ?? '') : (string) ($product['id'] ?? '');
$productPrice = is_object($product ?? null) ? (float) ($product->price ?? 0) : (float) ($product['price'] ?? 0);
$productStock = is_object($product ?? null) ? (int) ($product->stock ?? 0) : (int) ($product['stock'] ?? 0);
$productType = is_object($product ?? null) ? (string) ($product->type ?? '') : (string) ($product['type'] ?? '');
$productAuthor = is_object($product ?? null) ? (string) ($product->author ?? '') : (string) ($product['author'] ?? '');
$productCategory = is_object($product ?? null) ? (string) ($product->category_name ?? '') : (string) ($product['category_name'] ?? '');
$productDescription = is_object($product ?? null) ? (string) ($product->description ?? '') : (string) ($product['description'] ?? '');
$productImageUrl = is_object($product ?? null)
    ? (string) ($product->image_url ?? product_image_url((string) ($product->image ?? '')))
    : product_image_url((string) ($product['image'] ?? ''));

$productTypeLabel = match ($productType) {
    'basili' => 'Basili Kitap',
    'dijital' => 'Dijital Urun',
    'paket' => 'Paket Urun',
    default => 'Urun',
};

$productStockLabel = $productType === 'dijital'
    ? 'Dijital teslimat'
    : ($productStock > 0 ? 'Stokta mevcut' : 'Stok bilgisi sinirli');

$productStockToneClass = $productType === 'dijital' || $productStock > 0
    ? 'book-detail-stock--available'
    : 'book-detail-stock--limited';
$approvedReviews = is_array($approvedReviews ?? null) ? $approvedReviews : [];
$reviewSummary = is_array($reviewSummary ?? null) ? $reviewSummary : [];
$reviewUiState = is_array($reviewUiState ?? null) ? $reviewUiState : [];
$reviewCount = max(0, (int) ($reviewSummary['review_count'] ?? 0));
$averageRatingRaw = $reviewSummary['average_rating'] ?? null;
$averageRating = $averageRatingRaw !== null && $averageRatingRaw !== '' ? (float) $averageRatingRaw : null;
$averageRatingValue = $averageRating !== null ? max(0, min(5, $averageRating)) : 0.0;
$averageRatingPercent = max(0, min(100, ($averageRatingValue / 5) * 100));
$productRatingValue = $averageRating !== null ? number_format($averageRating, 1, ',', '.') : '0,0';
$productReviewLabel = $reviewCount > 0 ? $reviewCount . ' degerlendirme' : 'Henuz degerlendirme yok';
$reviewPillLabel = $reviewCount > 0 ? 'Onayli okur yorumlari' : 'Ilk yorumu siz yazin';
$isReviewUserLoggedIn = (bool) ($reviewUiState['isLoggedIn'] ?? false);
$canSubmitReview = (bool) ($reviewUiState['canSubmitReview'] ?? false);
$hasSubmittedReview = (bool) ($reviewUiState['hasSubmittedReview'] ?? false);
$similarProducts = is_array($similarProducts ?? null) ? $similarProducts : [];
$isFavorited = (bool) ($isFavorited ?? false);
$productDetailBinding = is_array($productDetailBinding ?? null) ? $productDetailBinding : [];
$productDetailPresenter = is_array($productDetailBinding['presenter'] ?? null) ? $productDetailBinding['presenter'] : [];
$hasProductDetailConfig = ! empty($productDetailBinding['hasPublishedConfig']);
$showDetailBreadcrumb = ! $hasProductDetailConfig || ! empty($productDetailPresenter['showBreadcrumb']);
$detailPageSubtitle = trim((string) ($productDetailPresenter['pageSubtitle'] ?? ''));
$detailShortDescription = trim((string) ($productDetailPresenter['shortDescription'] ?? ''));
$detailPromoBadgeText = trim((string) ($productDetailPresenter['promoBadgeText'] ?? ''));
$detailHeroSectionTitle = trim((string) ($productDetailPresenter['heroSectionTitle'] ?? ''));
$detailHeroShortDescription = trim((string) ($productDetailPresenter['heroShortDescription'] ?? ''));
$detailContentSectionTitle = trim((string) ($productDetailPresenter['contentSectionTitle'] ?? '')) ?: 'Urun Icerigi';
$detailMetaSectionTitle = trim((string) ($productDetailPresenter['metaSectionTitle'] ?? ''));
$detailLongDescriptionTitle = trim((string) ($productDetailPresenter['longDescriptionTitle'] ?? ''));
$detailBackCoverTitle = trim((string) ($productDetailPresenter['backCoverTitle'] ?? ''));
$detailHighlightsTitle = trim((string) ($productDetailPresenter['highlightsTitle'] ?? ''));
$detailContentNote = trim((string) ($productDetailPresenter['contentNote'] ?? ''));
$detailReviewSectionTitle = trim((string) ($productDetailPresenter['reviewSectionTitle'] ?? ''));
$detailReviewSummaryText = trim((string) ($productDetailPresenter['reviewSummaryText'] ?? ''));
$detailReviewCalloutText = trim((string) ($productDetailPresenter['reviewCalloutText'] ?? ''));
$detailRelatedSectionTitle = trim((string) ($productDetailPresenter['relatedSectionTitle'] ?? '')) ?: 'Benzer Kitaplar';
$detailRelatedSectionIntro = trim((string) ($productDetailPresenter['relatedSectionIntro'] ?? ''));
$detailPurchaseNotes = is_array($productDetailPresenter['purchaseNotes'] ?? null) ? $productDetailPresenter['purchaseNotes'] : [];

?>

<style>
    .book-detail-page {
        max-width: 1220px;
        margin: 0 auto;
        padding: 1.25rem 1.25rem 2.5rem;
    }
    .book-detail-breadcrumb {
        margin-bottom: 1rem;
    }
    .book-detail-breadcrumb .breadcrumb {
        margin: 0;
        gap: 0.15rem;
    }
    .book-detail-breadcrumb .breadcrumb-item,
    .book-detail-breadcrumb .breadcrumb-item a {
        color: #64748b;
        font-size: 0.92rem;
        text-decoration: none;
    }
    .book-detail-breadcrumb .breadcrumb-item.active {
        color: #0f172a;
        font-weight: 600;
    }
    .book-detail-hero {
        display: grid;
        grid-template-columns: minmax(280px, 420px) minmax(0, 1fr);
        gap: 1.75rem;
        align-items: start;
    }
    .book-detail-cover-card,
    .book-detail-info-card,
    .book-detail-content-card {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 251, 255, 0.98) 100%);
        border: 1px solid rgba(15, 23, 42, 0.07);
        border-radius: 26px;
        box-shadow: 0 20px 42px rgba(15, 23, 42, 0.06);
    }
    .book-detail-cover-card {
        position: relative;
        padding: 1.1rem;
    }
    .book-detail-favorite-form {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 3;
        margin: 0;
    }
    .book-detail-favorite {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.25);
        background: rgba(255, 255, 255, 0.96);
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .book-detail-favorite:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(15, 23, 42, 0.12);
    }
    .book-detail-favorite.is-active {
        color: #e11d48;
        border-color: rgba(220, 38, 38, 0.25);
        background: #fff1f2;
    }
    .book-detail-favorite-icon {
        font-size: 1.35rem;
        line-height: 1;
        font-weight: 700;
        display: inline-block;
        color: currentColor;
    }
    .book-detail-cover-frame {
        padding: 0.5rem;
        border-radius: 22px;
        background: linear-gradient(180deg, #eef5ff 0%, #ffffff 100%);
    }
    .book-detail-cover-image {
        width: 100%;
        aspect-ratio: 4 / 5.35;
        object-fit: cover;
        border-radius: 20px;
        display: block;
        background: #f8fafc;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
    }
    .book-detail-info-card {
        padding: 1.6rem;
        display: flex;
        flex-direction: column;
    }
    .book-detail-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-bottom: 0.95rem;
    }
    .book-detail-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .book-detail-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.9rem, 2.6vw, 2.7rem);
        line-height: 1.12;
        letter-spacing: -0.03em;
        font-weight: 800;
    }
    .book-detail-author {
        margin-top: 0.7rem;
        color: #475569;
        font-size: 1rem;
    }
    .book-detail-author strong {
        color: #0f172a;
        font-weight: 700;
    }
    .book-detail-helper-stack {
        display: grid;
        gap: 0.55rem;
        margin-top: 0.9rem;
    }
    .book-detail-helper-title {
        color: #1d4ed8;
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .book-detail-helper-copy {
        margin: 0;
        color: #64748b;
        line-height: 1.7;
        font-size: 0.95rem;
    }
    .book-detail-rating-row {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 0.9rem 1rem;
        border-radius: 18px;
        border: 1px solid rgba(245, 158, 11, 0.16);
        background: linear-gradient(135deg, rgba(255, 251, 235, 0.98) 0%, rgba(255, 255, 255, 0.96) 100%);
        color: #475569;
    }
    .book-detail-rating-main {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        flex-wrap: wrap;
    }
    .book-detail-rating-stars {
        --rating-percent: 0%;
        position: relative;
        display: inline-block;
        line-height: 1;
        font-size: 1rem;
        letter-spacing: 0.12rem;
        color: #cbd5e1;
        white-space: nowrap;
    }
    .book-detail-rating-stars--sm {
        font-size: 0.9rem;
        letter-spacing: 0.1rem;
    }
    .book-detail-rating-stars-base,
    .book-detail-rating-stars-fill {
        display: block;
        line-height: 1;
    }
    .book-detail-rating-stars-fill {
        position: absolute;
        inset: 0 auto 0 0;
        width: var(--rating-percent);
        overflow: hidden;
        color: #f59e0b;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.35);
    }
    .book-detail-rating-score {
        display: inline-flex;
        align-items: baseline;
        gap: 0.35rem;
        color: #0f172a;
        font-weight: 800;
    }
    .book-detail-rating-score strong {
        font-size: 1.1rem;
        line-height: 1;
    }
    .book-detail-rating-score span {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .book-detail-rating-text {
        font-size: 0.92rem;
        color: #64748b;
    }
    .book-detail-rating-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.92);
        color: #92400e;
        border: 1px solid rgba(245, 158, 11, 0.16);
        font-size: 0.84rem;
        font-weight: 700;
    }
    .book-detail-price-row {
        margin-top: 1.35rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1.1rem 1.2rem;
        border-radius: 22px;
        background: linear-gradient(135deg, rgba(239, 246, 255, 0.95) 0%, rgba(255, 255, 255, 0.95) 100%);
        border: 1px solid rgba(59, 130, 246, 0.10);
    }
    .book-detail-price-block {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .book-detail-price-label {
        color: #64748b;
        font-size: 0.84rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }
    .book-detail-price-value {
        color: #0f172a;
        font-size: clamp(1.7rem, 2vw, 2.1rem);
        font-weight: 800;
        line-height: 1;
    }
    .book-detail-stock {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.88rem;
    }
    .book-detail-stock--available {
        background: rgba(22, 163, 74, 0.10);
        color: #166534;
    }
    .book-detail-stock--limited {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
    }
    .book-detail-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin-top: 1.35rem;
    }
    .book-detail-meta-card {
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;
        padding: 0.95rem 1rem;
        background: rgba(255, 255, 255, 0.88);
    }
    .book-detail-meta-label {
        display: block;
        color: #64748b;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    .book-detail-meta-value {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.5;
    }
    .book-detail-actions {
        margin-top: 1.5rem;
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }
    .book-detail-primary-btn,
    .book-detail-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.95rem 1.2rem;
        border-radius: 16px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .book-detail-primary-btn {
        min-width: 220px;
        border: none;
        background: linear-gradient(135deg, #1d4ed8 0%, #38bdf8 100%);
        color: #fff;
        box-shadow: 0 14px 26px rgba(37, 99, 235, 0.18);
    }
    .book-detail-primary-btn:hover {
        color: #fff;
        transform: translateY(-1px);
    }
    .book-detail-secondary-btn {
        border: 1px solid rgba(148, 163, 184, 0.25);
        background: rgba(255, 255, 255, 0.92);
        color: #334155;
    }
    .book-detail-secondary-btn:hover {
        color: #0f172a;
        transform: translateY(-1px);
    }
    .book-detail-cart-form {
        margin: 0;
    }
    .book-detail-content-card {
        padding: 1.35rem 0 0;
        margin-top: 1.35rem;
        border-top: 1px solid rgba(226, 232, 240, 0.92);
        background: transparent;
        border-radius: 0;
        box-shadow: none;
        border-left: 0;
        border-right: 0;
        border-bottom: 0;
    }
    .book-detail-tabs-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.15rem;
        flex-wrap: wrap;
    }
    .book-detail-section-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.15rem;
        font-weight: 800;
    }
    .book-detail-tab-list {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
    }
    .book-detail-tab-button {
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: rgba(248, 250, 252, 0.95);
        color: #475569;
        border-radius: 999px;
        padding: 0.72rem 1rem;
        font-weight: 700;
        font-size: 0.9rem;
        line-height: 1;
        transition: all 0.2s ease;
    }
    .book-detail-tab-button:hover,
    .book-detail-tab-button:focus-visible {
        color: #0f172a;
        border-color: rgba(59, 130, 246, 0.25);
        background: rgba(239, 246, 255, 0.95);
        outline: none;
    }
    .book-detail-tab-button.is-active {
        color: #1d4ed8;
        background: rgba(37, 99, 235, 0.1);
        border-color: rgba(37, 99, 235, 0.18);
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.04);
    }
    .book-detail-tab-panel {
        display: none;
        padding-top: 0.35rem;
    }
    .book-detail-tab-panel.is-active {
        display: block;
    }
    .book-detail-description {
        color: #475569;
        line-height: 1.82;
        font-size: 0.98rem;
        white-space: pre-line;
    }
    .book-detail-notes {
        display: grid;
        gap: 1rem;
    }
    .book-detail-note {
        padding: 1rem 1.05rem;
        border-radius: 18px;
        background: rgba(248, 250, 252, 0.92);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }
    .book-detail-note-title {
        color: #0f172a;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    .book-detail-note-text {
        color: #64748b;
        line-height: 1.65;
        font-size: 0.92rem;
        margin: 0;
    }
    .book-detail-inline-heading {
        margin: 0 0 0.7rem;
        color: #0f172a;
        font-size: 0.96rem;
        font-weight: 700;
    }
    .book-detail-info-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }
    .book-detail-info-item {
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.82);
    }
    .book-detail-info-item .book-detail-meta-label {
        margin-bottom: 0.45rem;
    }
    .book-detail-overview-card {
        border: 1px solid rgba(226, 232, 240, 0.92);
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(255, 255, 255, 0.96) 100%);
        padding: 1.15rem 1.2rem;
    }
    .book-detail-overview-text {
        color: #475569;
        line-height: 1.8;
        font-size: 0.96rem;
        margin: 0;
    }
    .book-detail-comments-empty {
        border: 1px dashed rgba(148, 163, 184, 0.35);
        border-radius: 22px;
        padding: 1.35rem 1.2rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.92) 0%, rgba(255, 255, 255, 0.96) 100%);
        color: #64748b;
    }
    .book-detail-comments-empty-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .book-detail-comments-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 1.45rem;
    }
    .book-detail-comments-summary {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        flex-wrap: wrap;
    }
    .book-detail-comments-summary-score {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }
    .book-detail-comments-summary-meta {
        color: #64748b;
        font-size: 0.9rem;
    }
    .book-detail-comments-divider {
        height: 1px;
        background: rgba(226, 232, 240, 0.95);
        margin: 1rem 0 1.05rem;
    }
    .book-detail-comments-empty-title {
        margin: 0 0 0.45rem;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 700;
    }
    .book-detail-comments-empty-actions {
        display: flex;
        gap: 0.7rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    .book-detail-comments-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 0.75rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(226, 232, 240, 0.95);
        color: #475569;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .book-detail-review-list {
        display: grid;
        gap: 1rem;
    }
    .book-detail-review-card,
    .book-detail-review-form-card {
        border: 1px solid rgba(226, 232, 240, 0.92);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 251, 255, 0.98) 100%);
        padding: 1.15rem 1.2rem;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.05);
    }
    .book-detail-review-header {
        display: flex;
        align-items: start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 0.65rem;
    }
    .book-detail-review-author {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
    }
    .book-detail-review-date {
        color: #64748b;
        font-size: 0.88rem;
    }
    .book-detail-review-title {
        color: #0f172a;
        font-size: 0.98rem;
        font-weight: 700;
        margin: 0.2rem 0 0.55rem;
    }
    .book-detail-review-comment {
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.75;
        margin: 0;
        white-space: pre-line;
    }
    .book-detail-review-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }
    .book-detail-review-form-grid .book-detail-form-field--full {
        grid-column: 1 / -1;
    }
    .book-detail-form-label {
        display: block;
        color: #0f172a;
        font-size: 0.88rem;
        font-weight: 700;
        margin-bottom: 0.45rem;
    }
    .book-detail-form-note {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.7;
        margin: 0;
    }
    .book-detail-review-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    .book-detail-review-empty {
        margin-top: 1rem;
    }
    .book-detail-related {
        margin-top: 1.85rem;
    }
    .book-detail-related-header {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    .book-detail-related-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.3rem;
        font-weight: 800;
    }
    .book-detail-related-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }
    .book-detail-related-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 0.9rem;
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 251, 255, 0.98) 100%);
        border: 1px solid rgba(15, 23, 42, 0.07);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.06);
    }
    .book-detail-related-image-link {
        display: block;
        text-decoration: none;
    }
    .book-detail-related-image {
        width: 100%;
        aspect-ratio: 4 / 5.2;
        object-fit: cover;
        border-radius: 18px;
        background: #f8fafc;
        display: block;
    }
    .book-detail-related-body {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        gap: 0.75rem;
        padding-top: 0.9rem;
    }
    .book-detail-related-type {
        display: inline-flex;
        align-items: center;
        align-self: flex-start;
        padding: 0.38rem 0.7rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .book-detail-related-name {
        margin: 0;
        font-size: 1rem;
        line-height: 1.45;
        font-weight: 700;
    }
    .book-detail-related-name a {
        color: #0f172a;
        text-decoration: none;
    }
    .book-detail-related-name a:hover {
        color: #1d4ed8;
    }
    .book-detail-related-footer {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: auto;
        padding-top: 0.2rem;
    }
    .book-detail-related-price {
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 800;
    }
    .book-detail-related-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        width: 100%;
        padding: 0.8rem 1rem;
        border-radius: 14px;
        background: rgba(15, 23, 42, 0.04);
        border: 1px solid rgba(148, 163, 184, 0.2);
        color: #0f172a;
        font-size: 0.92rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .book-detail-related-button:hover {
        color: #1d4ed8;
        border-color: rgba(37, 99, 235, 0.22);
        background: rgba(239, 246, 255, 0.92);
    }
    @media (max-width: 991.98px) {
        .book-detail-page {
            padding: 1rem 1rem 2rem;
        }
        .book-detail-hero {
            grid-template-columns: 1fr;
        }
        .book-detail-cover-image {
            aspect-ratio: 4 / 4.8;
        }
        .book-detail-related-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767.98px) {
        .book-detail-page {
            padding: 0.9rem 0.85rem 1.75rem;
        }
        .book-detail-info-card,
        .book-detail-content-card {
            padding: 1.1rem;
        }
        .book-detail-content-card {
            padding: 1.1rem 0 0;
        }
        .book-detail-meta-grid {
            grid-template-columns: 1fr;
        }
        .book-detail-info-list {
            grid-template-columns: 1fr;
        }
        .book-detail-price-row,
        .book-detail-actions {
            align-items: stretch;
        }
        .book-detail-primary-btn,
        .book-detail-secondary-btn {
            width: 100%;
        }
        .book-detail-tab-list {
            width: 100%;
        }
        .book-detail-tab-button {
            flex: 1 1 calc(50% - 0.5rem);
            justify-content: center;
        }
        .book-detail-related {
            margin-top: 1.5rem;
        }
        .book-detail-related-grid {
            grid-template-columns: 1fr;
        }
        .book-detail-review-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="book-detail-page">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success mb-3"><?= esc((string) session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mb-3"><?= esc((string) session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if ($showDetailBreadcrumb): ?>
        <nav class="book-detail-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('products/selection') ?>">Kitaplar</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($productName) ?></li>
            </ol>
        </nav>
    <?php endif; ?>

    <section class="book-detail-hero">
        <div class="book-detail-cover-card">
            <form action="<?= base_url('favorites/toggle') ?>" method="post" class="book-detail-favorite-form">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= esc($productId) ?>">
                <button
                    type="submit"
                    class="book-detail-favorite<?= $isFavorited ? ' is-active' : '' ?>"
                    aria-label="<?= $isFavorited ? 'Favorilerden kaldır' : 'Favorilere ekle' ?>"
                    title="<?= $isFavorited ? 'Favorilerden kaldır' : 'Favorilere ekle' ?>"
                >
                    <span class="book-detail-favorite-icon" aria-hidden="true"><?= $isFavorited ? '♥' : '♡' ?></span>
                </button>
            </form>

            <div class="book-detail-cover-frame">
                <img src="<?= esc($productImageUrl) ?>" alt="<?= esc($productName) ?>" class="book-detail-cover-image">
            </div>

            <div class="book-detail-cover-meta"></div>
        </div>

        <div class="book-detail-info-card">
            <div class="book-detail-badge-row">
                <?php if ($detailPromoBadgeText !== ''): ?>
                    <span class="book-detail-badge">
                        <i class="ti ti-sparkles"></i>
                        <?= esc($detailPromoBadgeText) ?>
                    </span>
                <?php endif; ?>
                <span class="book-detail-badge">
                    <i class="ti ti-book-2"></i>
                    <?= esc($productTypeLabel) ?>
                </span>
                <?php if ($productCategory !== ''): ?>
                    <span class="book-detail-badge">
                        <i class="ti ti-category"></i>
                        <?= esc($productCategory) ?>
                    </span>
                <?php endif; ?>
            </div>

            <h1 class="book-detail-title"><?= esc($productName) ?></h1>
            <p class="book-detail-author">
                Yazar:
                <strong><?= esc($productAuthor !== '' ? $productAuthor : 'Belirtilmemis') ?></strong>
            </p>
            <?php if ($detailPageSubtitle !== '' || $detailShortDescription !== '' || $detailHeroSectionTitle !== '' || $detailHeroShortDescription !== ''): ?>
                <div class="book-detail-helper-stack">
                    <?php if ($detailHeroSectionTitle !== ''): ?>
                        <div class="book-detail-helper-title"><?= esc($detailHeroSectionTitle) ?></div>
                    <?php endif; ?>
                    <?php if ($detailPageSubtitle !== ''): ?>
                        <p class="book-detail-helper-copy"><?= esc($detailPageSubtitle) ?></p>
                    <?php endif; ?>
                    <?php if ($detailShortDescription !== ''): ?>
                        <p class="book-detail-helper-copy"><?= esc($detailShortDescription) ?></p>
                    <?php endif; ?>
                    <?php if ($detailHeroShortDescription !== ''): ?>
                        <p class="book-detail-helper-copy"><?= esc($detailHeroShortDescription) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="book-detail-rating-row" aria-label="Urun puan ozeti">
                <div class="book-detail-rating-main">
                    <div class="book-detail-rating-stars" style="--rating-percent: <?= esc((string) $averageRatingPercent) ?>%;" aria-label="<?= esc($productRatingValue) ?>/5">
                        <span class="book-detail-rating-stars-base">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        <span class="book-detail-rating-stars-fill" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                    </div>
                    <div class="book-detail-rating-score">
                        <strong><?= esc($productRatingValue) ?></strong>
                        <span>/ 5</span>
                    </div>
                    <div class="book-detail-rating-text"><?= esc($productReviewLabel) ?></div>
                </div>
                <span class="book-detail-rating-pill">
                    <i class="ti ti-message-circle-star"></i>
                    <?= esc($reviewPillLabel) ?>
                </span>
            </div>

            <div class="book-detail-price-row">
                <div class="book-detail-price-block">
                    <span class="book-detail-price-label">Satis Fiyati</span>
                    <span class="book-detail-price-value"><?= number_format($productPrice, 2, ',', '.') ?> TL</span>
                </div>
                <span class="book-detail-stock <?= esc($productStockToneClass) ?>">
                    <i class="ti ti-package"></i>
                    <?= esc($productStockLabel) ?>
                </span>
            </div>

            <div class="book-detail-meta-grid">
                <div class="book-detail-meta-card">
                    <span class="book-detail-meta-label">Tur</span>
                    <span class="book-detail-meta-value"><?= esc($productTypeLabel) ?></span>
                </div>
                <div class="book-detail-meta-card">
                    <span class="book-detail-meta-label">Kategori</span>
                    <span class="book-detail-meta-value"><?= esc($productCategory !== '' ? $productCategory : 'Kategori bilgisi yok') ?></span>
                </div>
            </div>

            <div class="book-detail-actions">
                <form action="<?= base_url('cart/add') ?>" method="post" class="book-detail-cart-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= esc($productId) ?>">
                    <button type="submit" class="book-detail-primary-btn" aria-label="Sepete ekle">
                        <i class="ti ti-shopping-cart-plus"></i>
                        <span>Sepete Ekle</span>
                    </button>
                </form>
                <a href="<?= base_url('products/selection') ?>" class="book-detail-secondary-btn">
                    <i class="ti ti-arrow-left"></i>
                    <span>Listeye Don</span>
                </a>
            </div>
            <?php if ($detailPurchaseNotes !== []): ?>
                <div class="book-detail-notes mt-4">
                    <?php foreach ($detailPurchaseNotes as $purchaseNote): ?>
                        <?php if (trim((string) $purchaseNote) === '') {
                            continue;
                        } ?>
                        <div class="book-detail-note">
                            <p class="book-detail-note-text"><?= esc((string) $purchaseNote) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="book-detail-content-card">
                <div class="book-detail-tabs-header">
                    <h2 class="book-detail-section-title"><?= esc($detailContentSectionTitle) ?></h2>
                    <div class="book-detail-tab-list" role="tablist" aria-label="Urun detay sekmeleri">
                        <button type="button" class="book-detail-tab-button is-active" role="tab" aria-selected="true" aria-controls="book-tab-description" id="book-tab-button-description" data-tab-target="book-tab-description">Aciklama</button>
                        <button type="button" class="book-detail-tab-button" role="tab" aria-selected="false" aria-controls="book-tab-details" id="book-tab-button-details" data-tab-target="book-tab-details">Urun Bilgileri</button>
                        <button type="button" class="book-detail-tab-button" role="tab" aria-selected="false" aria-controls="book-tab-overview" id="book-tab-button-overview" data-tab-target="book-tab-overview">Genel Bakis</button>
                        <button type="button" class="book-detail-tab-button" role="tab" aria-selected="false" aria-controls="book-tab-comments" id="book-tab-button-comments" data-tab-target="book-tab-comments">Yorumlar</button>
                    </div>
                </div>

                <div class="book-detail-tab-panel is-active" id="book-tab-description" role="tabpanel" aria-labelledby="book-tab-button-description">
                    <?php if ($detailLongDescriptionTitle !== ''): ?>
                        <h3 class="book-detail-inline-heading"><?= esc($detailLongDescriptionTitle) ?></h3>
                    <?php endif; ?>
                    <div class="book-detail-description">
                        <?= esc(trim($productDescription) !== '' ? $productDescription : 'Bu urun icin henuz detayli bir aciklama eklenmemis.') ?>
                    </div>
                </div>

                <div class="book-detail-tab-panel" id="book-tab-details" role="tabpanel" aria-labelledby="book-tab-button-details">
                    <?php if ($detailMetaSectionTitle !== ''): ?>
                        <h3 class="book-detail-inline-heading"><?= esc($detailMetaSectionTitle) ?></h3>
                    <?php endif; ?>
                    <div class="book-detail-info-list">
                        <div class="book-detail-info-item">
                            <span class="book-detail-meta-label">Yazar</span>
                            <div class="book-detail-meta-value"><?= esc($productAuthor !== '' ? $productAuthor : 'Belirtilmemis') ?></div>
                        </div>
                        <div class="book-detail-info-item">
                            <span class="book-detail-meta-label">Tur</span>
                            <div class="book-detail-meta-value"><?= esc($productTypeLabel) ?></div>
                        </div>
                        <div class="book-detail-info-item">
                            <span class="book-detail-meta-label">Kategori</span>
                            <div class="book-detail-meta-value"><?= esc($productCategory !== '' ? $productCategory : 'Kategori bilgisi yok') ?></div>
                        </div>
                        <div class="book-detail-info-item">
                            <span class="book-detail-meta-label">Fiyat</span>
                            <div class="book-detail-meta-value"><?= number_format($productPrice, 2, ',', '.') ?> TL</div>
                        </div>
                        <div class="book-detail-info-item">
                            <span class="book-detail-meta-label">Stok Durumu</span>
                            <div class="book-detail-meta-value"><?= esc($productStockLabel) ?></div>
                        </div>
                    </div>
                </div>

                <div class="book-detail-tab-panel" id="book-tab-overview" role="tabpanel" aria-labelledby="book-tab-button-overview">
                    <?php if ($detailContentNote !== '' && ($detailBackCoverTitle !== '' || $detailHighlightsTitle !== '')): ?>
                        <div class="book-detail-notes mb-3">
                            <?php if ($detailBackCoverTitle !== ''): ?>
                                <div class="book-detail-note">
                                    <div class="book-detail-note-title"><?= esc($detailBackCoverTitle) ?></div>
                                    <p class="book-detail-note-text"><?= esc($detailContentNote) ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($detailHighlightsTitle !== ''): ?>
                                <div class="book-detail-note">
                                    <div class="book-detail-note-title"><?= esc($detailHighlightsTitle) ?></div>
                                    <p class="book-detail-note-text"><?= esc($detailContentNote) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="book-detail-overview-card">
                        <p class="book-detail-overview-text">
                            <?= esc($productName) ?>, <?= esc($productAuthor !== '' ? $productAuthor : 'yazar bilgisi eklenmemis bir eser') ?> imzasi tasiyan
                            <?= esc(mb_strtolower($productTypeLabel, 'UTF-8')) ?> kategorisinde sunulan bir urundur.
                            <?= esc($productCategory !== '' ? $productCategory . ' kategorisi altinda listelenir. ' : '') ?>
                            Mevcut fiyat ve stok durumu bilgileri bu sayfada guncel olarak gosterilir.
                        </p>
                    </div>
                </div>

                <div class="book-detail-tab-panel" id="book-tab-comments" role="tabpanel" aria-labelledby="book-tab-button-comments">
                    <?php if ($detailReviewSectionTitle !== '' || $detailReviewSummaryText !== ''): ?>
                        <div class="book-detail-notes mb-3">
                            <?php if ($detailReviewSectionTitle !== ''): ?>
                                <div class="book-detail-note">
                                    <div class="book-detail-note-title"><?= esc($detailReviewSectionTitle) ?></div>
                                    <?php if ($detailReviewSummaryText !== ''): ?>
                                        <p class="book-detail-note-text"><?= esc($detailReviewSummaryText) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="book-detail-comments-empty">
                        <div class="book-detail-comments-empty-top">
                            <div class="d-flex align-items-center gap-3">
                                <div class="book-detail-comments-icon">
                                    <i class="ti ti-message-2-heart"></i>
                                </div>
                                <div>
                                    <h3 class="book-detail-comments-empty-title">Okur Yorumlari</h3>
                                    <p class="book-detail-note-text mb-0">
                                        <?= $reviewCount > 0
                                            ? 'Onaylanmis okur yorumlarini bu alanda inceleyebilirsiniz.'
                                            : 'Bu eser icin henuz yayinlanmis bir okur yorumu bulunmuyor.' ?>
                                    </p>
                                </div>
                            </div>
                            <div class="book-detail-comments-summary">
                                <div class="book-detail-rating-stars" style="--rating-percent: <?= esc((string) $averageRatingPercent) ?>%;" aria-label="<?= esc($productRatingValue) ?>/5">
                                    <span class="book-detail-rating-stars-base">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                    <span class="book-detail-rating-stars-fill" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                </div>
                                <div>
                                    <div class="book-detail-comments-summary-score"><?= esc($productRatingValue) ?>/5</div>
                                    <div class="book-detail-comments-summary-meta"><?= esc($productReviewLabel) ?></div>
                                </div>
                            </div>
                        </div>

                        <?php if ($approvedReviews !== []): ?>
                            <div class="book-detail-review-list mt-3">
                                <?php foreach ($approvedReviews as $review): ?>
                                    <?php
                                    $reviewRating = max(1, min(5, (int) ($review['rating'] ?? 0)));
                                    $reviewRatingPercent = max(0, min(100, ($reviewRating / 5) * 100));
                                    $reviewAuthor = trim((string) ($review['username'] ?? ''));
                                    $reviewAuthorEmail = trim((string) ($review['email'] ?? ''));
                                    if ($reviewAuthor === '') {
                                        $reviewAuthor = $reviewAuthorEmail !== '' ? $reviewAuthorEmail : 'Okur';
                                    }
                                    $reviewTitle = trim((string) ($review['title'] ?? ''));
                                    $reviewComment = trim((string) ($review['comment'] ?? ''));
                                    $reviewCreatedAt = trim((string) ($review['created_at'] ?? ''));
                                    $reviewDateLabel = $reviewCreatedAt !== '' ? date('d.m.Y', strtotime($reviewCreatedAt)) : '';
                                    ?>
                                    <article class="book-detail-review-card">
                                        <div class="book-detail-review-header">
                                            <div>
                                                <h4 class="book-detail-review-author mb-1"><?= esc($reviewAuthor !== '' ? $reviewAuthor : 'Okur') ?></h4>
                                                <div class="book-detail-rating-stars book-detail-rating-stars--sm" style="--rating-percent: <?= esc((string) $reviewRatingPercent) ?>%;" aria-label="<?= esc((string) $reviewRating) ?>/5">
                                                    <span class="book-detail-rating-stars-base">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                                    <span class="book-detail-rating-stars-fill" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                                </div>
                                            </div>
                                            <?php if ($reviewDateLabel !== ''): ?>
                                                <div class="book-detail-review-date"><?= esc($reviewDateLabel) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($reviewTitle !== ''): ?>
                                            <h5 class="book-detail-review-title"><?= esc($reviewTitle) ?></h5>
                                        <?php endif; ?>
                                        <?php if ($reviewComment !== ''): ?>
                                            <p class="book-detail-review-comment"><?= esc($reviewComment) ?></p>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="book-detail-review-empty">
                                <p class="book-detail-note-text mb-0">Bu eser icin henuz paylasilmis bir okur yorumu bulunmuyor.</p>
                            </div>
                        <?php endif; ?>

                        <div class="book-detail-comments-divider"></div>
                        <?php if ($detailReviewCalloutText !== ''): ?>
                            <p class="book-detail-note-text mb-3"><?= esc($detailReviewCalloutText) ?></p>
                        <?php endif; ?>

                        <?php if (! $isReviewUserLoggedIn): ?>
                            <div class="book-detail-review-form-card">
                                <div class="book-detail-note-title">Yorum Yap</div>
                                <p class="book-detail-form-note mb-0">Yorum gonderebilmek icin once giris yapmalisiniz.</p>
                            </div>
                        <?php elseif ($hasSubmittedReview): ?>
                            <div class="book-detail-review-form-card">
                                <div class="book-detail-note-title">Yorum Durumu</div>
                                <p class="book-detail-form-note mb-0">Bu urun icin daha once yorum gonderdiniz.</p>
                            </div>
                        <?php elseif (! $canSubmitReview): ?>
                            <div class="book-detail-review-form-card">
                                <div class="book-detail-note-title">Yorum Yap</div>
                                <p class="book-detail-form-note mb-0">Yorum yapabilmek icin urunu satin almis olmaniz gerekir.</p>
                            </div>
                        <?php else: ?>
                            <div class="book-detail-review-form-card">
                                <div class="book-detail-note-title">Yorum Yap</div>
                                <p class="book-detail-form-note">Yorumunuz moderasyon onayindan sonra yayinlanir.</p>
                                <form action="<?= base_url('products/detail/' . rawurlencode($productId) . '/reviews') ?>" method="post" class="mt-3">
                                    <?= csrf_field() ?>
                                    <div class="book-detail-review-form-grid">
                                        <div class="book-detail-form-field">
                                            <label for="review-rating" class="book-detail-form-label">Puan</label>
                                            <select name="rating" id="review-rating" class="form-select" required>
                                                <option value="">Seciniz</option>
                                                <?php for ($score = 5; $score >= 1; $score--): ?>
                                                    <option value="<?= $score ?>" <?= old('rating') == (string) $score ? 'selected' : '' ?>>
                                                        <?= $score ?> Yildiz
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="book-detail-form-field">
                                            <label for="review-title" class="book-detail-form-label">Baslik</label>
                                            <input
                                                type="text"
                                                id="review-title"
                                                name="title"
                                                class="form-control"
                                                maxlength="255"
                                                value="<?= esc((string) old('title')) ?>"
                                                placeholder="Yorumunuz icin kisa bir baslik"
                                            >
                                        </div>
                                        <div class="book-detail-form-field book-detail-form-field--full">
                                            <label for="review-comment" class="book-detail-form-label">Yorum</label>
                                            <textarea
                                                id="review-comment"
                                                name="comment"
                                                class="form-control"
                                                rows="5"
                                                placeholder="Deneyiminizi diger okurlarla paylasin"
                                            ><?= esc((string) old('comment')) ?></textarea>
                                        </div>
                                    </div>
                                    <div class="book-detail-review-form-actions">
                                        <p class="book-detail-form-note mb-0">Baslik veya yorum metni alanlarindan en az birini doldurabilirsiniz.</p>
                                        <button type="submit" class="book-detail-primary-btn" style="min-width: 0;">
                                            <i class="ti ti-send"></i>
                                            <span>Yorumu Gonder</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($similarProducts !== []): ?>
        <section class="book-detail-related" aria-labelledby="similar-books-title">
            <div class="book-detail-related-header">
                <div>
                    <h2 class="book-detail-related-title" id="similar-books-title"><?= esc($detailRelatedSectionTitle) ?></h2>
                    <?php if ($detailRelatedSectionIntro !== ''): ?>
                        <p class="book-detail-note-text mt-2 mb-0"><?= esc($detailRelatedSectionIntro) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="book-detail-related-grid">
                <?php foreach ($similarProducts as $similarProduct): ?>
                    <?php
                    $similarName = is_object($similarProduct) ? (string) ($similarProduct->product_name ?? '') : (string) ($similarProduct['product_name'] ?? '');
                    $similarType = is_object($similarProduct) ? (string) ($similarProduct->type ?? '') : (string) ($similarProduct['type'] ?? '');
                    $similarPrice = is_object($similarProduct) ? (float) ($similarProduct->price ?? 0) : (float) ($similarProduct['price'] ?? 0);
                    $similarDetailUrl = is_object($similarProduct) ? (string) ($similarProduct->detail_url ?? '#') : (string) ($similarProduct['detail_url'] ?? '#');
                    $similarImageUrl = is_object($similarProduct)
                        ? (string) ($similarProduct->image_url ?? product_image_url((string) ($similarProduct->image ?? '')))
                        : product_image_url((string) ($similarProduct['image'] ?? ''));
                    $similarTypeLabel = match ($similarType) {
                        'basili' => 'Basili',
                        'dijital' => 'Dijital',
                        'paket' => 'Paket',
                        default => 'Urun',
                    };
                    ?>
                    <article class="book-detail-related-card">
                        <a href="<?= esc($similarDetailUrl) ?>" class="book-detail-related-image-link">
                            <img src="<?= esc($similarImageUrl) ?>" alt="<?= esc($similarName) ?>" class="book-detail-related-image">
                        </a>

                        <div class="book-detail-related-body">
                            <span class="book-detail-related-type"><?= esc($similarTypeLabel) ?></span>
                            <h3 class="book-detail-related-name">
                                <a href="<?= esc($similarDetailUrl) ?>"><?= esc($similarName) ?></a>
                            </h3>

                            <div class="book-detail-related-footer">
                                <div class="book-detail-related-price"><?= number_format($similarPrice, 2, ',', '.') ?> TL</div>
                                <a href="<?= esc($similarDetailUrl) ?>" class="book-detail-related-button">
                                    <i class="ti ti-arrow-right"></i>
                                    <span>Detayi Gor</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
<script>
    (function () {
        var tabButtons = document.querySelectorAll('.book-detail-tab-button');
        var tabPanels = document.querySelectorAll('.book-detail-tab-panel');

        if (!tabButtons.length || !tabPanels.length) {
            return;
        }

        function activateTab(targetId) {
            tabButtons.forEach(function (button) {
                var isActive = button.getAttribute('data-tab-target') === targetId;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                button.setAttribute('tabindex', isActive ? '0' : '-1');
            });

            tabPanels.forEach(function (panel) {
                var isActive = panel.id === targetId;
                panel.classList.toggle('is-active', isActive);
            });
        }

        tabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                activateTab(button.getAttribute('data-tab-target'));
            });

            button.addEventListener('keydown', function (event) {
                if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
                    return;
                }

                event.preventDefault();
                var buttons = Array.prototype.slice.call(tabButtons);
                var currentIndex = buttons.indexOf(button);
                var nextIndex = event.key === 'ArrowRight'
                    ? (currentIndex + 1) % buttons.length
                    : (currentIndex - 1 + buttons.length) % buttons.length;

                buttons[nextIndex].focus();
                activateTab(buttons[nextIndex].getAttribute('data-tab-target'));
            });
        });
    })();
</script>
<?= $this->endSection() ?>
