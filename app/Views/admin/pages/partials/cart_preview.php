<?php
$preview = is_array($cartPreview ?? null) ? $cartPreview : [];
$config = is_array($preview['config'] ?? null) ? $preview['config'] : [];
$sections = is_array($preview['sections'] ?? null) ? $preview['sections'] : [];
$items = is_array($preview['sampleItems'] ?? null) ? $preview['sampleItems'] : [];
$hasVisibleSections = ! empty($preview['hasVisibleSections']);
$visibleSectionCount = (int) ($preview['visibleSectionCount'] ?? 0);
$pricingDeltaCount = (int) ($preview['pricingDeltaCount'] ?? 0);
$stockWarningCount = (int) ($preview['stockWarningCount'] ?? 0);
?>

<div class="card border shadow-none bg-light mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="fw-semibold mb-1">Cart Mini Preview</div>
                <div class="small text-muted">Sepet urunleri, fiyat farki, stok uyari ve CTA alanlari icin sade yonetici gorunumu.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-light-primary"><?= esc((string) $visibleSectionCount) ?> aktif bolum</span>
                <span class="badge bg-light-warning"><?= esc((string) $pricingDeltaCount) ?> fiyat farki</span>
                <span class="badge bg-light-danger"><?= esc((string) $stockWarningCount) ?> stok uyari</span>
            </div>
        </div>
    </div>
</div>

<?php if (! $hasVisibleSections): ?>
    <div class="alert alert-light border mb-3">
        <div class="fw-semibold mb-1">Preview hazir</div>
        <div class="small text-muted">Tum bolumler pasif olsa bile cart akisinin bos kalmamasi icin fallback durum gosterilir.</div>
    </div>
<?php endif; ?>

<?php if (! empty($config['breadcrumb_goster']) && ! empty($config['sections']['sayfa_ust_alani']['active'])): ?>
    <div class="small text-muted mb-3">Anasayfa / Sepet / <?= esc((string) ($config['sayfa_basligi'] ?? 'Sepetim')) ?></div>
<?php endif; ?>

<?php foreach ($sections as $section): ?>
    <?php if (! ($section['active'] ?? false)) {
        continue;
    } ?>
    <?php if (($section['key'] ?? '') === 'sayfa_ust_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="d-flex flex-wrap justify-content-between gap-3"><div><span class="badge bg-light-primary mb-2"><?= esc((string) ($section['title'] ?? 'Sayfa Ust Alani')) ?></span><h4 class="mb-1"><?= esc((string) ($config['sayfa_basligi'] ?? 'Sepetim')) ?></h4><p class="text-muted mb-2"><?= esc((string) ($config['sayfa_alt_basligi'] ?? 'Sepetinizdeki urunleri kontrol edip odeme adimina gecin.')) ?></p><div class="small text-muted"><?= esc((string) ($config['kisa_aciklama'] ?? 'Fiyat, stok ve kampanya bilgileri siparis oncesinde tekrar kontrol edilir.')) ?></div></div><span class="badge bg-light-secondary">Sira <?= esc((string) ($section['order'] ?? 1)) ?></span></div></div></div>
    <?php elseif (($section['key'] ?? '') === 'sepet_urunleri_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="fw-semibold mb-1"><?= esc((string) ($config['sepet_urunleri_baslik'] ?? 'Sepetinizdeki Urunler')) ?></div><div class="small text-muted mb-3"><?= esc((string) ($config['sepet_urunleri_aciklama'] ?? 'Urun adetlerini guncelleyebilir veya urunleri sepetinizden kaldirabilirsiniz.')) ?></div><?php foreach ($items as $item): ?><div class="border rounded p-3 mb-2"><div class="d-flex flex-wrap justify-content-between gap-3"><div class="d-flex gap-3 align-items-start"><?php if (! empty($config['urun_gorseli_goster'])): ?><div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:64px;height:64px;"><?= esc((string) ($item['image_label'] ?? 'URUN')) ?></div><?php endif; ?><div><div class="fw-semibold"><?= esc((string) ($item['name'] ?? 'Urun')) ?></div><?php if (! empty($config['format_etiketi_goster'])): ?><div class="small text-muted"><?= esc(ucfirst((string) ($item['type'] ?? ''))) ?> urun</div><?php endif; ?><?php if (! empty($item['stock_message'])): ?><div class="small text-warning mt-1"><?= esc((string) $item['stock_message']) ?></div><?php endif; ?></div></div><div class="text-end"><?php if (! empty($item['price_decreased'])): ?><div class="small text-decoration-line-through text-muted"><?= esc(number_format((float) ($item['snapshot_price'] ?? 0), 2, ',', '.')) ?> TL</div><div class="fw-bold text-success fs-5"><?= esc(number_format((float) ($item['current_price'] ?? 0), 2, ',', '.')) ?> TL</div><div class="small text-success">Fiyati dustu</div><?php else: ?><div class="fw-semibold"><?= esc(number_format((float) ($item['current_price'] ?? 0), 2, ',', '.')) ?> TL</div><?php endif; ?><?php if (! empty($config['adet_kontrolu_goster'])): ?><div class="small text-muted">Adet: <?= esc((string) ($item['quantity'] ?? 1)) ?></div><?php endif; ?><button type="button" class="btn btn-link btn-sm text-danger p-0 disabled"><?= esc((string) ($config['kaldir_buton_metni'] ?? 'Kaldir')) ?></button></div></div></div><?php endforeach; ?></div></div>
    <?php elseif (($section['key'] ?? '') === 'fiyat_guncelleme_uyari_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="fw-semibold mb-1"><?= esc((string) ($config['fiyat_uyari_baslik'] ?? 'Fiyat Guncellemesi')) ?></div><div class="small text-muted mb-3"><?= esc((string) ($config['fiyat_uyari_aciklama'] ?? 'Sepete eklediginiz andan sonra fiyati degisen urunler burada bilgilendirme kutusuyla vurgulanir.')) ?></div><?php if (! empty($config['fiyat_farki_bilgi_kutusu_goster'])): ?><div class="alert alert-warning mb-3"><div class="fw-semibold mb-1">Guncellenen urun fiyati algilandi</div><div class="small">Preview, hesap motorunu degistirmeden fiyat snapshot farkini gostermeye hazirdir.</div></div><?php endif; ?><?php foreach ($items as $item): ?><?php if (empty($item['has_price_change'])) {
            continue;
        } ?><div class="border rounded p-3 mb-2"><div class="fw-semibold mb-2"><?= esc((string) ($item['name'] ?? 'Urun')) ?></div><div class="d-flex flex-wrap gap-3 small"><span><?= esc((string) ($config['eski_fiyat_etiketi'] ?? 'Sepete eklendigindeki fiyat')) ?>: <strong><?= esc(number_format((float) ($item['snapshot_price'] ?? 0), 2, ',', '.')) ?> TL</strong></span><span><?= esc((string) ($config['guncel_fiyat_etiketi'] ?? 'Guncel fiyat')) ?>: <strong><?= esc(number_format((float) ($item['current_price'] ?? 0), 2, ',', '.')) ?> TL</strong></span></div><?php if (! empty($item['price_decreased'])): ?><div class="small text-success mt-2">Bu urunde kullanici lehine fiyat guncellemesi var.</div><?php elseif (! empty($item['price_increased'])): ?><div class="small text-warning mt-2">Bu urunde fiyat artisi uyarisi korunur.</div><?php endif; ?></div><?php endforeach; ?><div class="small text-muted mt-2"><?= esc((string) ($config['toplam_guncelleme_notu'] ?? 'Toplam tutar odeme adiminda en guncel fiyatlara gore yenilenir.')) ?></div></div></div>
    <?php elseif (($section['key'] ?? '') === 'stok_uygunluk_uyari_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="fw-semibold mb-1"><?= esc((string) ($config['stok_uyari_baslik'] ?? 'Stok ve Uygunluk Kontrolu')) ?></div><div class="small text-muted mb-3"><?= esc((string) ($config['stok_uyari_aciklama'] ?? 'Fiziksel urunlerde stok durumu mesaj bazli gosterilir, dijital urunlerde stok mesaji yer almaz.')) ?></div><div class="row g-2"><?php foreach ($items as $item): ?><?php if (trim((string) ($item['stock_message'] ?? '')) === '') {
            continue;
        } ?><div class="col-md-6"><div class="alert alert-light border mb-0"><div class="fw-semibold"><?= esc((string) ($item['name'] ?? 'Urun')) ?></div><div class="small text-warning"><?= esc((string) ($item['stock_message'] ?? '')) ?></div></div></div><?php endforeach; ?></div><div class="small text-muted mt-3">Dijital urunlerde stok uyari mesaji gosterilmez; preview bu davranisa gore hazirlanmistir.</div></div></div>
    <?php elseif (($section['key'] ?? '') === 'kupon_kampanya_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="fw-semibold mb-1"><?= esc((string) ($config['kupon_kampanya_baslik'] ?? 'Kupon ve Kampanyalar')) ?></div><div class="small text-muted mb-3"><?= esc((string) ($config['kupon_kampanya_aciklama'] ?? 'Aktif kampanya ve kupon bilgilerini odeme oncesinde gozden gecirin.')) ?></div><?php if (! empty($config['kupon_alani_goster'])): ?><div class="input-group input-group-sm mb-3"><span class="input-group-text">Kupon</span><input type="text" class="form-control" placeholder="BEABLE10"><button class="btn btn-outline-secondary" type="button">Uygula</button></div><?php endif; ?><div class="alert alert-light border mb-2"><?= esc((string) ($config['kampanya_bilgi_notu'] ?? 'Bu sipariste uygulanabilen kampanyalar odeme adiminda otomatik degerlendirilir.')) ?></div><div class="small text-success"><?= esc((string) ($config['ucretsiz_kargo_bilgi_notu'] ?? '500 TL ve uzeri alisverislerde ucretsiz kargo firsati sunulur.')) ?></div></div></div>
    <?php elseif (($section['key'] ?? '') === 'sepet_ozeti_cta_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="fw-semibold mb-3"><?= esc((string) ($config['sepet_ozeti_baslik'] ?? 'Sepet Ozeti')) ?></div><?php if (! empty($config['ara_toplam_goster'])): ?><div class="d-flex justify-content-between small mb-2"><span>Ara Toplam</span><span>589,70 TL</span></div><?php endif; ?><?php if (! empty($config['indirim_goster'])): ?><div class="d-flex justify-content-between small mb-2 text-success"><span>Indirim</span><span>-45,00 TL</span></div><?php endif; ?><?php if (! empty($config['kargo_goster'])): ?><div class="d-flex justify-content-between small mb-2"><span>Kargo</span><span>39,90 TL</span></div><?php endif; ?><div class="d-flex justify-content-between fw-semibold border-top pt-2 mt-2"><span><?= esc((string) ($config['genel_toplam_basligi'] ?? 'Genel Toplam')) ?></span><span>584,60 TL</span></div><div class="d-grid gap-2 mt-3"><button type="button" class="btn btn-primary disabled"><?= esc((string) ($config['odeme_sayfasina_git_buton_metni'] ?? 'Odeme Sayfasina Git')) ?></button><div class="small text-muted text-center"><?= esc((string) ($config['guvenli_odeme_kisa_notu'] ?? 'Guvenli odeme adiminda kart ve adres bilgileriniz korunur.')) ?></div></div></div></div>
    <?php elseif (($section['key'] ?? '') === 'bos_sepet_alani'): ?>
        <div class="card border shadow-none mb-0"><div class="card-body text-center"><div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;"><i class="ti ti-shopping-cart-off fs-2 text-muted"></i></div><div class="fw-semibold mb-1"><?= esc((string) ($config['bos_sepet_baslik'] ?? 'Sepetiniz Su Anda Bos')) ?></div><div class="small text-muted mb-3"><?= esc((string) ($config['bos_sepet_aciklama'] ?? 'Katalogtan urun ekleyerek alisverise devam edebilirsiniz.')) ?></div><button type="button" class="btn btn-outline-primary btn-sm disabled"><?= esc((string) ($config['alisverise_basla_buton_metni'] ?? 'Alisverise Basla')) ?></button></div></div>
    <?php endif; ?>
<?php endforeach; ?>
