<?php
$preview = is_array($checkoutPreview ?? null) ? $checkoutPreview : [];
$config = is_array($preview['config'] ?? null) ? $preview['config'] : [];
$sections = is_array($preview['sections'] ?? null) ? $preview['sections'] : [];
$hasVisibleSections = ! empty($preview['hasVisibleSections']);
$visibleSectionCount = (int) ($preview['visibleSectionCount'] ?? 0);
?>

<?php if (! $hasVisibleSections): ?>
    <div class="alert alert-light border mb-3">
        <div class="fw-semibold mb-1">Preview hazir</div>
        <div class="small text-muted">Tum bolumler pasif olsa bile checkout akisinin burada bos kalmamasi icin fallback durum gosterilir.</div>
    </div>
<?php endif; ?>

<?php if (! empty($config['breadcrumb_goster']) && ! empty($config['sections']['sayfa_ust_alani']['active'])): ?>
    <div class="small text-muted mb-3">Sepet / Odeme / <?= esc((string) ($config['sayfa_basligi'] ?? 'Guvenli Odeme')) ?></div>
<?php endif; ?>

<?php foreach ($sections as $section): ?>
    <?php if (! ($section['active'] ?? false)) {
        continue;
    } ?>
    <?php if (($section['key'] ?? '') === 'sayfa_ust_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="d-flex flex-wrap justify-content-between gap-3"><div><span class="badge bg-light-primary mb-2"><?= esc((string) ($section['title'] ?? 'Sayfa Ust Alani')) ?></span><h4 class="mb-1"><?= esc((string) ($config['sayfa_basligi'] ?? 'Guvenli Odeme')) ?></h4><p class="text-muted mb-2"><?= esc((string) ($config['sayfa_alt_basligi'] ?? 'Teslimat ve odeme adimlarini tamamlayarak siparisinizi olusturun.')) ?></p><div class="small text-success"><?= esc((string) ($config['guven_kisa_notu'] ?? 'SSL korumasi aktif, bilgileriniz guvende islenir.')) ?></div></div><span class="badge bg-light-secondary">Sira <?= esc((string) ($section['order'] ?? 1)) ?></span></div></div></div>
    <?php elseif (($section['key'] ?? '') === 'adim_cubugu' && ! empty($config['adim_cubugu_gorunur'])): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="d-flex flex-wrap align-items-center gap-2 mb-3"><span class="badge bg-light-primary">1 Teslimat</span><span class="badge bg-light-secondary">2 Odeme</span><span class="badge bg-light-secondary">3 Onay</span></div><div class="small text-muted"><?= esc((string) ($config['adim_cubugu_aciklama'] ?? 'Teslimat, odeme ve onay adimlarini sirasiyla tamamlayin.')) ?></div></div></div>
    <?php elseif (($section['key'] ?? '') === 'teslimat_fatura_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="row g-3"><div class="col-lg-7"><div class="fw-semibold mb-1"><?= esc((string) ($config['teslimat_baslik'] ?? 'Teslimat ve Fatura Bilgileri')) ?></div><div class="small text-muted mb-3"><?= esc((string) ($config['teslimat_aciklama'] ?? 'Adres ve iletisim bilgilerinizi eksiksiz girin.')) ?></div><div class="row g-2"><div class="col-md-6"><div class="form-control bg-light">Ad Soyad</div></div><div class="col-md-6"><div class="form-control bg-light">Telefon</div></div><div class="col-12"><div class="form-control bg-light">Adres</div></div><div class="col-md-6"><div class="form-control bg-light">Il</div></div><div class="col-md-6"><div class="form-control bg-light">Ilce</div></div></div></div><div class="col-lg-5"><div class="alert alert-light border mb-2"><?= esc((string) ($config['ayni_adres_notu'] ?? 'Fatura adresi teslimat adresi ile ayni olabilir.')) ?></div><div class="small text-muted"><?= esc((string) ($config['zorunlu_alan_bilgi_metni'] ?? '* ile isaretli alanlar zorunludur.')) ?></div></div></div></div></div>
    <?php elseif (($section['key'] ?? '') === 'odeme_yontemi_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="fw-semibold mb-1"><?= esc((string) ($config['odeme_baslik'] ?? 'Odeme Yontemi')) ?></div><div class="small text-muted mb-3"><?= esc((string) ($config['odeme_aciklama'] ?? 'Tercih ettiginiz odeme yontemini secin.')) ?></div><div class="d-flex flex-wrap gap-2 mb-3"><span class="badge bg-light-secondary">Kredi Karti</span><span class="badge bg-light-secondary">Banka Karti</span><span class="badge bg-light-secondary">Havale</span></div><div class="row g-2 align-items-center"><?php if (! empty($config['kart_logo_goster'])): ?><div class="col-md-6"><div class="border rounded p-3 text-center bg-light">VISA / MasterCard / Troy</div></div><?php endif; ?><?php if (! empty($config['guven_rozeti_goster'])): ?><div class="col-md-6"><div class="border rounded p-3 text-center bg-light-success">3D Secure / SSL</div></div><?php endif; ?></div><div class="small text-success mt-3"><?= esc((string) ($config['guvenli_odeme_notu'] ?? 'Kart bilgileriniz PCI uyumlu guvenli altyapida islenir.')) ?></div></div></div>
    <?php elseif (($section['key'] ?? '') === 'siparis_ozeti_alani'): ?>
        <div class="card border shadow-none mb-3"><div class="card-body"><div class="fw-semibold mb-3"><?= esc((string) ($config['ozet_baslik'] ?? 'Siparis Ozeti')) ?></div><div class="d-flex justify-content-between small mb-2"><span>Ara Toplam</span><span>1.250 TL</span></div><?php if (! empty($config['indirim_satiri_goster'])): ?><div class="d-flex justify-content-between small mb-2 text-success"><span>Indirim</span><span>-120 TL</span></div><?php endif; ?><?php if (! empty($config['kargo_satiri_goster'])): ?><div class="d-flex justify-content-between small mb-2"><span>Kargo</span><span>49 TL</span></div><?php endif; ?><div class="d-flex justify-content-between fw-semibold border-top pt-2 mt-2"><span>Toplam</span><span>1.179 TL</span></div><?php if (! empty($config['kupon_alani_goster'])): ?><div class="input-group input-group-sm mt-3"><span class="input-group-text">Kupon</span><input type="text" class="form-control" placeholder="INDIRIM10"><button class="btn btn-outline-secondary" type="button">Uygula</button></div><?php endif; ?><div class="small text-muted mt-3"><?= esc((string) ($config['siparis_tipi_notu'] ?? 'Dijital urunlerde teslimat e-posta ile saglanir.')) ?></div></div></div>
    <?php elseif (($section['key'] ?? '') === 'bilgilendirme_guven_cta_alani'): ?>
        <div class="card border shadow-none mb-0"><div class="card-body"><div class="alert alert-light border mb-3"><div class="fw-semibold mb-1"><?= esc((string) ($config['bilgi_kutusu_baslik'] ?? 'Siparisinizi Tamamlamadan Once')) ?></div><div class="small"><?= esc((string) ($config['bilgi_kutusu_aciklama'] ?? 'Adres, odeme ve siparis ozeti bilgilerinizi son kez kontrol edin.')) ?></div></div><div class="small text-success mb-3"><?= esc((string) ($config['guven_mesaji'] ?? '7/24 destek ve guvenli odeme korumasi ile yaninizdayiz.')) ?></div><div class="d-grid gap-2"><button type="button" class="btn btn-primary disabled"><?= esc((string) ($config['tamamla_buton_metni'] ?? 'Siparisi Tamamla')) ?></button><div class="small text-muted text-center"><?= esc((string) ($config['alt_yardim_metni'] ?? 'Bir sorun olursa destek ekibimiz yardim icin hazir.')) ?></div></div></div></div>
    <?php endif; ?>
<?php endforeach; ?>
