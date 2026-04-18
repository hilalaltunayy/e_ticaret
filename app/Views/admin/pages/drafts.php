<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-block">
        <div class="row">
            <div class="col-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Draftlar</h2>
                </div>
            </div>
            <div class="col-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yonetim</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/pages') ?>">Sayfa Yonetimi</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/pages/' . $page['code']) ?>"><?= esc($page['name']) ?></a></li>
                    <li class="breadcrumb-item" aria-current="page">Draftlar</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1"><?= esc($page['name']) ?> Versionlari</h5>
            <p class="text-muted mb-0"><code><?= esc($page['code']) ?></code> icin draft, scheduled ve published kayitlari</p>
        </div>
        <div class="d-flex gap-2">
            <form action="<?= site_url('admin/pages/drafts/create') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                <button type="submit" class="btn btn-sm btn-success">Yeni Draft</button>
            </form>
            <a href="<?= site_url('admin/pages/' . $page['code'] . '/builder') ?>" class="btn btn-sm btn-primary">Builder</a>
            <a href="<?= site_url('admin/pages/' . $page['code']) ?>" class="btn btn-sm btn-outline-secondary">Sayfa Detayi</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('draft_error')): ?>
            <div class="alert alert-danger">
                <?= esc(session()->getFlashdata('draft_error')) ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>
        <?php if ($drafts === []): ?>
            <div class="alert alert-info mb-0">Bu sayfa icin goruntulenecek version kaydi bulunmuyor.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Ad</th>
                            <th>Durum</th>
                            <th>Olusturulma</th>
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
                                <td><?= esc($draft->createdAt ?? '-') ?></td>
                                <td><?= esc($draft->updatedAt ?? '-') ?></td>
                                <td>
                                    <?php if ($draft->status !== 'PUBLISHED' && $draft->status !== 'ARCHIVED'): ?>
                                        <a href="<?= site_url('admin/pages/' . $page['code'] . '/builder') ?>" class="btn btn-sm btn-outline-primary">Builder</a>
                                    <?php endif; ?>
                                    <a href="<?= site_url('admin/page-versions/' . $draft->id) ?>" class="btn btn-sm btn-outline-secondary">Version Detayi</a>
                                    <?php if ($draft->status === 'PUBLISHED'): ?>
                                        <form action="<?= site_url('admin/pages/drafts/start-editing') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                                            <input type="hidden" name="version_id" value="<?= esc($draft->id) ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Duzenlemeye Basla</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($draft->status !== 'ARCHIVED'): ?>
                                        <form action="<?= site_url('admin/pages/drafts/duplicate') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                                            <input type="hidden" name="version_id" value="<?= esc($draft->id) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-info">Kopyala</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($draft->status === 'DRAFT' || $draft->status === 'SCHEDULED'): ?>
                                        <form action="<?= site_url('admin/pages/drafts/archive') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                                            <input type="hidden" name="version_id" value="<?= esc($draft->id) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Arsivle</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($draft->status === 'PUBLISHED'): ?>
                                        <form action="<?= site_url('admin/pages/drafts/unpublish') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="page_code" value="<?= esc($page['code']) ?>">
                                            <input type="hidden" name="version_id" value="<?= esc($draft->id) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Canlidan Cek</button>
                                        </form>
                                    <?php endif; ?>
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
