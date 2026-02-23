<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Admin Panel') ?></title>
  <?= $this->include('admin/partials/head_css') ?>
</head>
<body
  data-pc-preset="preset-6"
  data-pc-sidebar-caption="true"
  data-pc-layout="vertical"
  data-pc-direction="ltr"
  data-pc-theme="light"
>
  <?= $this->include('admin/partials/sidebar') ?>
  <?= $this->include('admin/partials/topbar') ?>

  <div class="pc-container">
    <div class="pc-content">
      <?= $this->renderSection('content') ?>
    </div>
  </div>

  <?= $this->include('admin/partials/footer_js') ?>
  <?= $this->renderSection('pageScripts') ?>
</body>
</html>
