<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$reader = is_array($reader ?? null) ? $reader : [];
$book = is_array($reader['book'] ?? null) ? $reader['book'] : [];
$currentPage = (int) ($reader['current_page'] ?? 1);
$totalPages = (int) ($reader['total_pages'] ?? 0);
$hasPrev = (bool) ($reader['has_prev'] ?? false);
$hasNext = (bool) ($reader['has_next'] ?? false);
$isEmpty = (bool) ($reader['is_empty'] ?? true);
$content = (string) ($reader['content'] ?? '');
$productId = (string) ($book['product_id'] ?? '');
$baseReadUrl = base_url('yardim/dijital-kitaplarim/' . $productId . '/oku');
?>
<style>
    .reader-page { max-width: 1100px; margin: 0 auto; padding: 1.2rem 1rem 2.2rem; }
    .reader-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
    .reader-back { color: #334155; text-decoration: none; font-weight: 700; }
    .reader-card { border: 1px solid rgba(148,163,184,.22); border-radius: 18px; background: #fff; box-shadow: 0 14px 28px rgba(15,23,42,.06); overflow: hidden; }
    .reader-head { padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148,163,184,.16); background: #f8fbff; }
    .reader-title { margin: 0; color: #0f172a; font-size: 1.15rem; font-weight: 800; }
    .reader-author { margin: .35rem 0 0; color: #64748b; font-size: .9rem; }
    .reader-body { padding: 1.15rem; min-height: 380px; background: #fff; }
    .reader-content { margin: 0; color: #111827; line-height: 1.9; white-space: pre-wrap; }
    .reader-empty { margin: 0; color: #475569; line-height: 1.7; }
    .reader-foot { padding: .9rem 1.1rem 1.1rem; border-top: 1px solid rgba(148,163,184,.16); display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; }
    .reader-nav { display: inline-flex; gap: .5rem; }
    .reader-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: .45rem .85rem; border-radius: 10px; border: 1px solid rgba(148,163,184,.28); color: #0f172a; text-decoration: none; font-size: .84rem; font-weight: 700; background: #fff; }
    .reader-btn.is-disabled { opacity: .45; pointer-events: none; }
    .reader-page-meta { color: #334155; font-size: .86rem; font-weight: 700; }
</style>

<section class="reader-page">
    <div class="reader-top">
        <a href="<?= esc(base_url('yardim/dijital-kitaplarim')) ?>" class="reader-back">Dijital Kitaplarim'a Don</a>
    </div>

    <article class="reader-card">
        <header class="reader-head">
            <h1 class="reader-title"><?= esc((string) ($book['title'] ?? 'Dijital Kitap')) ?></h1>
            <p class="reader-author"><?= esc((string) ($book['author'] ?? 'Yazar belirtilmedi')) ?></p>
        </header>

        <div class="reader-body">
            <?php if ($isEmpty): ?>
                <p class="reader-empty">Bu kitap icin okunabilir icerik henuz hazir degil.</p>
            <?php else: ?>
                <p class="reader-content"><?= esc($content) ?></p>
            <?php endif; ?>
        </div>

        <footer class="reader-foot">
            <div class="reader-nav">
                <a href="<?= esc($baseReadUrl . '?page=' . max(1, $currentPage - 1)) ?>" class="reader-btn<?= $hasPrev ? '' : ' is-disabled' ?>">Onceki</a>
                <a href="<?= esc($baseReadUrl . '?page=' . ($currentPage + 1)) ?>" class="reader-btn<?= $hasNext ? '' : ' is-disabled' ?>">Sonraki</a>
            </div>
            <div class="reader-page-meta">
                <?php if ($totalPages > 0): ?>
                    Sayfa <?= esc((string) $currentPage) ?> / <?= esc((string) $totalPages) ?>
                <?php else: ?>
                    Sayfa bilgisi bulunmuyor
                <?php endif; ?>
            </div>
        </footer>
    </article>
</section>
<?= $this->endSection() ?>
