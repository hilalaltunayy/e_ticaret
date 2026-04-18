<!doctype html>
<html lang="tr">
<head>
    <?= $this->include('site/partials/head') ?>
</head>
<body class="storefront-body">
    <?= $this->include('site/partials/header') ?>

    <main class="storefront-main">
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('site/partials/footer') ?>
    <?= $this->include('site/partials/scripts') ?>
</body>
</html>
