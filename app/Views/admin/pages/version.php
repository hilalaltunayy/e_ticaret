<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-block">
        <div class="row">
            <div class="col-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Version Detayi</h2>
                </div>
            </div>
            <div class="col-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Yonetim</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/pages') ?>">Sayfa Yonetimi</a></li>
                    <?php if (! empty($version['page_code'])): ?>
                        <li class="breadcrumb-item"><a href="<?= site_url('admin/pages/' . $version['page_code']) ?>"><?= esc($version['page_name'] ?? $version['page_code']) ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item" aria-current="page">Version Detayi</li>
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
                    <h5 class="mb-1"><?= esc($version['name']) ?></h5>
                    <p class="text-muted mb-0">
                        <code><?= esc($version['page_code'] ?? '-') ?></code> / version <?= esc((string) ($version['version_no'] ?? 0)) ?>
                    </p>
                </div>
                <?php if (! empty($version['page_code'])): ?>
                    <div class="d-flex gap-2">
                        <?php if (($version['status'] ?? '') === 'PUBLISHED'): ?>
                            <form action="<?= site_url('admin/pages/drafts/start-editing') ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="page_code" value="<?= esc($version['page_code']) ?>">
                                <input type="hidden" name="version_id" value="<?= esc($version['id'] ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-primary">Duzenlemeye Basla</button>
                            </form>
                        <?php else: ?>
                        <a href="<?= site_url('admin/pages/' . $version['page_code'] . '/builder') ?>" class="btn btn-sm btn-primary">Builder</a>
                        <?php endif; ?>
                        <a href="<?= site_url('admin/pages/' . $version['page_code'] . '/drafts') ?>" class="btn btn-sm btn-outline-secondary">Taslak Listesine Don</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('draft_error')): ?>
                    <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('draft_error')) ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Durum</div>
                            <div class="fw-semibold"><?= esc($version['status']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Olusturma</div>
                            <div class="fw-semibold"><?= esc($version['created_at'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Guncelleme</div>
                            <div class="fw-semibold"><?= esc($version['updated_at'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Published At</div>
                            <div class="fw-semibold"><?= esc($version['published_at'] ?? '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Bagli Block Instance Kayitlari</h6>
            </div>
            <div class="card-body">
                <?php if ($blocks === []): ?>
                    <div class="alert alert-info mb-0">Bu version icin block instance kaydi bulunmuyor.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Zone</th>
                                    <th>Owner Type</th>
                                    <th>Order</th>
                                    <th>Visible</th>
                                    <th>Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blocks as $block): ?>
                                    <tr>
                                        <td><?= esc($block['zone']) ?></td>
                                        <td><?= esc($block['owner_type']) ?></td>
                                        <td><?= esc((string) $block['order_index']) ?></td>
                                        <td><?= (int) $block['is_visible'] === 1 ? 'Yes' : 'No' ?></td>
                                        <td><?= esc((string) $block['width']) ?> x <?= esc((string) $block['height']) ?></td>
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
