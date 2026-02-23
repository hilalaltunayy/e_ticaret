<!doctype html>
<html lang="en">
<head>
  <?= $this->include('admin/partials/head_page_meta') ?>
  <?= $this->include('admin/partials/head_css') ?>
</head>

<body>
  <?= $this->include('admin/partials/loader') ?>

  <?= $this->include('admin/partials/sidebar') ?>

  <?= $this->include('admin/partials/topbar') ?>

  <div class="pc-container">
    <div class="pc-content">

      <?php if (isset($pageTitle)): ?>
        <div class="page-header">
          <div class="page-block">
            <div class="row align-items-center">
              <div class="col">
                <div class="page-header-title">
                  <h5 class="m-b-10"><?= esc($pageTitle) ?></h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?= $this->renderSection('content') ?>

    </div>
  </div>

  <?= $this->include('admin/partials/footer') ?>
  <?= $this->include('admin/partials/footer_js') ?>
</body>
</html>