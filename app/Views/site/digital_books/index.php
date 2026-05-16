<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php $books = is_array($books ?? null) ? $books : []; ?>
<style>
    .digital-books-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 1.25rem 1rem 2.5rem;
    }
    .digital-books-card {
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
        padding: 1.2rem;
    }
    .digital-books-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.3rem, 2vw, 1.8rem);
        font-weight: 800;
    }
    .digital-books-subtitle {
        margin: 0.45rem 0 1rem;
        color: #64748b;
        line-height: 1.6;
    }
    .digital-books-empty {
        margin: 0;
        color: #334155;
        line-height: 1.7;
    }
    .digital-books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .digital-book-item {
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 14px;
        background: #fff;
        padding: 0.8rem;
        display: grid;
        gap: 0.6rem;
    }
    .digital-book-cover {
        width: 100%;
        height: 240px;
        object-fit: cover;
        border-radius: 10px;
        background: #eef2ff;
    }
    .digital-book-title {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 800;
        color: #0f172a;
    }
    .digital-book-meta {
        margin: 0;
        color: #64748b;
        font-size: 0.86rem;
        line-height: 1.5;
    }
    .digital-book-actions {
        margin-top: 0.2rem;
    }
    .digital-book-read-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0.45rem 0.9rem;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.84rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #1677ff, #3b8cff);
    }
    .digital-book-read-link:hover {
        color: #fff;
        background: linear-gradient(135deg, #0f69eb, #2f83ff);
    }
</style>

<section class="digital-books-page">
    <div class="digital-books-card">
        <h1 class="digital-books-title">Dijital Kitaplarim</h1>
        <p class="digital-books-subtitle">Satin aldiginiz dijital kitaplar bu alanda listelenecek.</p>
        <?php if ($books === []): ?>
            <p class="digital-books-empty">Henuz listelenecek bir dijital kitap bulunmuyor.</p>
        <?php else: ?>
            <div class="digital-books-grid">
                <?php foreach ($books as $book): ?>
                    <article class="digital-book-item">
                        <img
                            src="<?= esc((string) ($book['image_url'] ?? '')) ?>"
                            alt="<?= esc((string) ($book['title'] ?? 'Dijital Kitap')) ?>"
                            class="digital-book-cover"
                        >
                        <h2 class="digital-book-title"><?= esc((string) ($book['title'] ?? 'Dijital Kitap')) ?></h2>
                        <p class="digital-book-meta"><?= esc((string) ($book['author'] ?? 'Yazar belirtilmedi')) ?></p>
                        <div class="digital-book-actions">
                            <a href="<?= esc(base_url('yardim/dijital-kitaplarim/' . (string) ($book['product_id'] ?? '') . '/oku')) ?>" class="digital-book-read-link">
                                Kitabi Oku
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
