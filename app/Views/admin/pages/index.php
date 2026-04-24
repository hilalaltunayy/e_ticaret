<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-block">
        <div class="row">
            <div class="col-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Sayfa Yonetimi</h2>
                </div>
            </div>
            <div class="col-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yonetim</a></li>
                    <li class="breadcrumb-item" aria-current="page">Sayfa Yonetimi</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Sayfa Yonetimi</h5>
        </div>
    </div>
    <div class="card-body">
        <?php if (! $tablesReady): ?>
            <div class="alert alert-warning mb-0">
                Page Management tablolari henuz hazir degil. Once migration ve seeder calistirilmali.
            </div>
        <?php elseif ($pages === []): ?>
            <div class="alert alert-info mb-0">
                Tanimli sayfa kaydi bulunamadi.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Sayfa</th>
                            <th>Kod</th>
                            <th>Durum</th>
                            <th>Taslak</th>
                            <th>Published Version</th>
                            <th>Islem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                            <tr>
                                <td><?= esc($page->name) ?></td>
                                <td><code><?= esc($page->code) ?></code></td>
                                <td><?= esc($page->status) ?></td>
                                <td><?= esc((string) $page->draftCount) ?></td>
                                <td>
                                    <?php if ($page->publishedVersionId !== null): ?>
                                        <a href="<?= site_url('admin/page-versions/' . $page->publishedVersionId) ?>">
                                            <?= esc($page->publishedVersionName ?? 'Published') ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Yok</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= site_url('admin/pages/' . $page->code . '/builder') ?>" class="btn btn-sm btn-primary">Builder</a>
                                    <a href="<?= site_url('admin/pages/' . $page->code) ?>" class="btn btn-sm btn-outline-secondary">Detay</a>
                                    <a href="<?= site_url('admin/pages/' . $page->code . '/drafts') ?>" class="btn btn-sm btn-outline-secondary">Draftlar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
