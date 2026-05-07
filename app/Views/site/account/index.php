<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$accountOverview = is_array($accountOverview ?? null) ? $accountOverview : [];
$user = is_array($accountOverview['user'] ?? null) ? $accountOverview['user'] : [];
$stats = is_array($accountOverview['stats'] ?? null) ? $accountOverview['stats'] : [];
$latestOrder = is_array($accountOverview['latestOrder'] ?? null) ? $accountOverview['latestOrder'] : null;
$errors = session()->getFlashdata('errors');
$errors = is_array($errors) ? $errors : [];
?>
<style>
    .account-page {
        max-width: 1240px;
        margin: 0 auto;
        padding: 1.25rem 1rem 2.5rem;
    }
    .account-header {
        margin-bottom: 1.5rem;
    }
    .account-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.45rem, 2vw, 1.95rem);
        font-weight: 800;
    }
    .account-subtitle {
        margin: 0.45rem 0 0;
        color: #64748b;
        line-height: 1.65;
        max-width: 760px;
    }
    .account-alert-stack {
        display: grid;
        gap: 0.75rem;
        margin-bottom: 1.15rem;
    }
    .account-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1.35rem;
    }
    .account-card,
    .account-stat-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }
    .account-stat-card {
        padding: 1rem 1.05rem;
        display: grid;
        gap: 0.45rem;
        text-decoration: none;
    }
    .account-stat-label {
        color: #475569;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .account-stat-value {
        color: #0f172a;
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .account-stat-help {
        color: #64748b;
        font-size: 0.88rem;
        line-height: 1.55;
    }
    .account-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.95fr);
        gap: 1.25rem;
        align-items: start;
    }
    .account-column {
        display: grid;
        gap: 1.1rem;
    }
    .account-card {
        padding: 1.25rem;
    }
    .account-card-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 800;
    }
    .account-card-text {
        margin: 0.45rem 0 0;
        color: #64748b;
        line-height: 1.6;
    }
    .account-form-grid {
        display: grid;
        gap: 1rem;
        margin-top: 1rem;
    }
    .account-field-group {
        display: grid;
        gap: 0.45rem;
    }
    .account-field-label {
        color: #334155;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .account-input {
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: #fff;
        color: #0f172a;
        padding: 0.75rem 0.9rem;
        width: 100%;
    }
    .account-input.is-invalid {
        border-color: rgba(220, 38, 38, 0.4);
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
    }
    .account-input[readonly] {
        background: #f8fafc;
        color: #64748b;
    }
    .account-field-help,
    .account-error-text {
        font-size: 0.84rem;
        line-height: 1.55;
    }
    .account-field-help {
        color: #64748b;
    }
    .account-error-text {
        color: #dc2626;
        font-weight: 600;
    }
    .account-form-actions {
        margin-top: 0.25rem;
    }
    .account-form-actions .btn {
        min-height: 46px;
        border-radius: 14px;
        font-weight: 700;
    }
    .account-quick-links {
        display: grid;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    .account-quick-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: #f8fafc;
        color: #0f172a;
        text-decoration: none;
        font-weight: 700;
    }
    .account-quick-link small {
        display: block;
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 600;
        margin-top: 0.2rem;
    }
    .account-order-summary {
        display: grid;
        gap: 0.85rem;
        margin-top: 1rem;
    }
    .account-order-meta {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
    }
    .account-badge {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-size: 0.82rem;
        font-weight: 800;
    }
    .account-order-grid {
        display: grid;
        gap: 0.65rem;
        color: #475569;
        font-size: 0.92rem;
    }
    .account-order-grid strong {
        color: #0f172a;
    }
    .account-help-box {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(14, 165, 233, 0.05));
        border: 1px solid rgba(37, 99, 235, 0.12);
    }
    .account-help-box h3 {
        margin: 0 0 0.35rem;
        color: #0f172a;
        font-size: 0.98rem;
        font-weight: 800;
    }
    .account-help-box p {
        margin: 0;
        color: #64748b;
        line-height: 1.6;
    }
    @media (max-width: 991.98px) {
        .account-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 767.98px) {
        .account-page {
            padding-inline: 0.85rem;
        }
    }
</style>

<section class="account-page">
    <header class="account-header">
        <h1 class="account-title">Hesabım</h1>
        <p class="account-subtitle">Profil bilgilerinizi, güvenlik ayarlarınızı ve alışveriş özetinizi buradan yönetebilirsiniz.</p>
    </header>

    <div class="account-alert-stack">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success mb-0"><?= esc((string) session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger mb-0"><?= esc((string) session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
    </div>

    <div class="account-stats-grid">
        <a href="<?= base_url('yardim/siparislerim') ?>" class="account-stat-card">
            <span class="account-stat-label">Siparişlerim</span>
            <strong class="account-stat-value"><?= esc((string) ($stats['orders_count'] ?? 0)) ?></strong>
            <span class="account-stat-help">Sipariş geçmişinizi görüntüleyin ve son durumlarını takip edin.</span>
        </a>
        <a href="<?= base_url('yardim/favorilerim') ?>" class="account-stat-card">
            <span class="account-stat-label">Favorilerim</span>
            <strong class="account-stat-value"><?= esc((string) ($stats['favorites_count'] ?? 0)) ?></strong>
            <span class="account-stat-help">Kaydettiğiniz kitaplara hızlıca geri dönün.</span>
        </a>
        <a href="<?= base_url('yardim/sepetim') ?>" class="account-stat-card">
            <span class="account-stat-label">Sepetim</span>
            <strong class="account-stat-value"><?= esc((string) ($stats['cart_item_count'] ?? 0)) ?></strong>
            <span class="account-stat-help">Aktif sepetinizde bekleyen ürünleri gözden geçirin.</span>
        </a>
        <a href="#security" class="account-stat-card">
            <span class="account-stat-label">Güvenlik</span>
            <strong class="account-stat-value">•••</strong>
            <span class="account-stat-help"><?= esc((string) ($stats['security_label'] ?? 'Şifrenizi güncelleyin')) ?></span>
        </a>
    </div>

    <div class="account-layout">
        <div class="account-column">
            <section class="account-card">
                <h2 class="account-card-title">Profil Bilgileri</h2>
                <p class="account-card-text">Hesabınızda görünen temel bilgileri güncel tutarak sipariş ve destek süreçlerini daha rahat yönetebilirsiniz.</p>

                <form action="<?= base_url('yardim/hesabim/profil') ?>" method="post" class="account-form-grid">
                    <?= csrf_field() ?>
                    <div class="account-field-group">
                        <label for="username" class="account-field-label">Ad Soyad</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="account-input<?= isset($errors['username']) ? ' is-invalid' : '' ?>"
                            value="<?= esc(old('username', (string) ($user['username'] ?? ''))) ?>"
                            autocomplete="name"
                        >
                        <?php if (isset($errors['username'])): ?>
                            <div class="account-error-text"><?= esc((string) $errors['username']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="account-field-group">
                        <label for="email" class="account-field-label">E-posta</label>
                        <input
                            type="email"
                            id="email"
                            class="account-input"
                            value="<?= esc((string) ($user['email'] ?? '')) ?>"
                            readonly
                        >
                        <div class="account-field-help">Bu sprintte giriş güvenliğini korumak için e-posta adresi yalnızca görüntülenir.</div>
                    </div>

                    <div class="account-form-actions">
                        <button type="submit" class="btn btn-primary px-4">Bilgileri Güncelle</button>
                    </div>
                </form>
            </section>

            <section class="account-card" id="security">
                <h2 class="account-card-title">Güvenlik</h2>
                <p class="account-card-text">Şifrenizi düzenli aralıklarla güncelleyerek hesabınızı daha güvenli tutabilirsiniz.</p>

                <form action="<?= base_url('yardim/hesabim/sifre') ?>" method="post" class="account-form-grid">
                    <?= csrf_field() ?>
                    <div class="account-field-group">
                        <label for="current_password" class="account-field-label">Mevcut Şifre</label>
                        <input type="password" id="current_password" name="current_password" class="account-input" autocomplete="current-password">
                    </div>

                    <div class="account-field-group">
                        <label for="new_password" class="account-field-label">Yeni Şifre</label>
                        <input type="password" id="new_password" name="new_password" class="account-input" autocomplete="new-password">
                        <div class="account-field-help">Yeni şifreniz en az 8 karakter uzunluğunda olmalıdır.</div>
                    </div>

                    <div class="account-field-group">
                        <label for="new_password_confirmation" class="account-field-label">Yeni Şifre Tekrar</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="account-input" autocomplete="new-password">
                    </div>

                    <div class="account-form-actions">
                        <button type="submit" class="btn btn-outline-primary px-4">Şifreyi Güncelle</button>
                    </div>
                </form>
            </section>
        </div>

        <div class="account-column">
            <section class="account-card">
                <h2 class="account-card-title">Hızlı Erişim</h2>
                <p class="account-card-text">En sık kullandığınız hesap alanlarına tek adımda ulaşın.</p>

                <div class="account-quick-links">
                    <a href="<?= base_url('yardim/siparislerim') ?>" class="account-quick-link">
                        <div>
                            Siparişlerim
                            <small>Devam eden ve geçmiş siparişlerinizi görüntüleyin.</small>
                        </div>
                        <span aria-hidden="true">→</span>
                    </a>
                    <a href="<?= base_url('yardim/favorilerim') ?>" class="account-quick-link">
                        <div>
                            Favorilerim
                            <small>Kaydettiğiniz kitapları ve fiyat değişimlerini takip edin.</small>
                        </div>
                        <span aria-hidden="true">→</span>
                    </a>
                    <a href="<?= base_url('yardim/sepetim') ?>" class="account-quick-link">
                        <div>
                            Sepetim
                            <small>Sepetinizde bekleyen ürünleri gözden geçirin.</small>
                        </div>
                        <span aria-hidden="true">→</span>
                    </a>
                    <a href="<?= base_url('logout') ?>" class="account-quick-link">
                        <div>
                            Çıkış Yap
                            <small>Bu cihazdaki oturumunuzu güvenli şekilde sonlandırın.</small>
                        </div>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </section>

            <section class="account-card">
                <h2 class="account-card-title">Son Hareketler</h2>
                <p class="account-card-text">Son siparişinizin durumunu ve ödeme özetini hızlıca kontrol edin.</p>

                <?php if (is_array($latestOrder)): ?>
                    <div class="account-order-summary">
                        <div class="account-order-meta">
                            <span class="account-badge"><?= esc((string) ($latestOrder['status_label'] ?? 'Sipariş Alındı')) ?></span>
                        </div>
                        <div class="account-order-grid">
                            <div><strong>Sipariş No:</strong> <?= esc((string) ($latestOrder['order_number'] ?? '-')) ?></div>
                            <div><strong>Tarih:</strong> <?= esc((string) ($latestOrder['order_date'] ?? '-')) ?></div>
                            <div><strong>Tutar:</strong> <?= number_format((float) ($latestOrder['total_amount'] ?? 0), 2, ',', '.') ?> TL</div>
                        </div>
                        <div class="account-form-actions">
                            <a href="<?= esc((string) ($latestOrder['detail_url'] ?? '#')) ?>" class="btn btn-outline-primary px-4">Detayları Gör</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="account-help-box">
                        <h3>Henüz siparişiniz yok.</h3>
                        <p>İlk siparişinizi verdiğinizde teslimat ve ödeme durumunu burada özet olarak göreceksiniz.</p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="account-card">
                <h2 class="account-card-title">Hesap Yardımı</h2>
                <p class="account-card-text">Siparişler, favoriler veya sepetinizle ilgili konular için müşteri destek alanlarını kullanabilirsiniz.</p>
                <div class="account-help-box">
                    <h3>Yardıma mı ihtiyacınız var?</h3>
                    <p>Önce sipariş detaylarınızı ve hesap özetinizi kontrol edin; gerekirse destek kanalınıza bu bilgilerle daha hızlı ulaşabilirsiniz.</p>
                </div>
            </section>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
