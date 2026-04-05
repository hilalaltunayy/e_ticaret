<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-block">
        <div class="row">
            <div class="col-12">
                <div class="page-header-title">
                    <h2 class="mb-0"><?= esc($page['name']) ?></h2>
                </div>
            </div>
            <div class="col-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yonetim</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/pages') ?>">Sayfa Yonetimi</a></li>
                    <li class="breadcrumb-item" aria-current="page"><?= esc($page['name']) ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><?= esc($page['name']) ?></h5>
                    <p class="text-muted mb-0"><code><?= esc($page['code']) ?></code> sayfasi</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= site_url('admin/pages/' . $page['code'] . '/builder') ?>" class="btn btn-sm btn-primary">Builder</a>
                    <a href="<?= site_url('admin/pages/' . $page['code'] . '/drafts') ?>" class="btn btn-sm btn-outline-secondary">Draftlari Gor</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Durum</div>
                            <div class="fw-semibold"><?= esc($page['status']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Olusturulma</div>
                            <div class="fw-semibold"><?= esc($page['created_at'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Published Version</div>
                            <?php if (is_array($publishedVersion)): ?>
                                <div class="fw-semibold">
                                    <a href="<?= site_url('admin/page-versions/' . $publishedVersion['id']) ?>">
                                        <?= esc($publishedVersion['name']) ?>
                                    </a>
                                </div>
                                <div class="text-muted small mt-1">Published at: <?= esc($publishedVersion['published_at'] ?? '-') ?></div>
                            <?php else: ?>
                                <div class="fw-semibold text-muted">Henuz published version yok</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Versiyon Durumlari</h6>
            </div>
            <div class="card-body">
                <?php if ($drafts === []): ?>
                    <div class="alert alert-info mb-0">Bu sayfa icin goruntulenecek version kaydi bulunmuyor.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Version</th>
                                    <th>Ad</th>
                                    <th>Durum</th>
                                    <th>Guncelleme</th>
                                    <th>Islem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($drafts as $draft): ?>
                                    <tr>
                                        <td><?= esc((string) $draft->versionNo) ?></td>
                                        <td><?= esc($draft->name) ?></td>
                                        <td><?= esc($draft->status) ?></td>
                                        <td><?= esc($draft->updatedAt ?? '-') ?></td>
                                        <td>
                                            <a href="<?= site_url('admin/pages/' . $page['code'] . '/builder') ?>" class="btn btn-sm btn-outline-primary">Builder</a>
                                            <a href="<?= site_url('admin/page-versions/' . $draft->id) ?>" class="btn btn-sm btn-outline-secondary">Version Detayi</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
