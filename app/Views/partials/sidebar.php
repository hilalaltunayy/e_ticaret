<?php
$currentPath = trim(service('uri')->getPath(), '/');
$isActive = static function (string $path) use ($currentPath): string {
    return str_starts_with($currentPath, trim($path, '/')) ? ' active' : '';
};

$session = session();
$sessionUser = $session->get('user');
$sessionUserData = $session->get('userData');
$userName = trim((string) ($userName ?? ($sessionUser['name'] ?? $sessionUserData['name'] ?? 'Kullanıcı')));
$roleName = trim((string) ($roleName ?? ($sessionUser['role'] ?? $sessionUserData['role'] ?? 'Kullanıcı')));
$permissions = $permissions ?? $session->get('permissions') ?? [];
if (! is_array($permissions)) {
    $permissions = [];
}

$canManageOrders = ($roleName === 'admin') || in_array('manage_orders', $permissions, true);
$canManageShipping = ($roleName === 'admin') || in_array('manage_shipping', $permissions, true);
$canManageAuthz = ($roleName === 'admin');
?>
<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="<?= site_url('admin/dashboard') ?>" class="b-brand d-flex align-items-center">
        <svg class="pc-icon pc-brand-icon" aria-hidden="true">
          <use xlink:href="#custom-archive-book"></use>
        </svg>
        <span class="pc-brand-text">Kitap Dünyası</span>
      </a>
    </div>

    <div class="navbar-content">
      <div class="card pc-user-card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="wid-45 rounded-circle bg-light d-flex align-items-center justify-content-center">
                <i class="ti ti-user fs-4"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3 me-2">
              <h6 class="mb-0"><?= esc($userName) ?></h6>
              <small><?= esc(ucfirst($roleName)) ?></small>
            </div>
            <a class="btn btn-icon btn-link-secondary avtar" data-bs-toggle="collapse" href="#pc_sidebar_userlink">
              <svg class="pc-icon">
                <use xlink:href="#custom-sort-outline"></use>
              </svg>
            </a>
          </div>

          <div class="collapse pc-user-links" id="pc_sidebar_userlink">
            <div class="pt-3">
              <a href="#!"><i class="ti ti-user"></i><span>Hesabım</span></a>
              <a href="#!"><i class="ti ti-settings"></i><span>Ayarlar</span></a>
              <a href="#!"><i class="ti ti-lock"></i><span>Kilit Ekranı</span></a>
              <a href="<?= site_url('logout') ?>"><i class="ti ti-power"></i><span>Çıkış</span></a>
            </div>
          </div>
        </div>
      </div>

      <ul class="pc-navbar">
        <li class="pc-item pc-caption">
          <label>Admin Erişim</label>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/dashboard') ?>" class="pc-link<?= $isActive('admin/dashboard') ?>">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-status-up"></use></svg>
            </span>
            <span class="pc-mtext">Dashboard</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>Ürün Yönetimi</label>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/products') ?>" class="pc-link<?= $isActive('admin/products') ?>">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-shopping-bag"></use></svg>
            </span>
            <span class="pc-mtext">Ürünler</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>Stok Yönetimi</label>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/stock') ?>" class="pc-link<?= $isActive('admin/stock') ?>">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-box-1"></use></svg>
            </span>
            <span class="pc-mtext">Stok Takip Paneli</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>Sipariş Yönetimi</label>
        </li>
        <?php if ($canManageOrders): ?>
          <li class="pc-item">
            <a href="<?= site_url('admin/orders') ?>" class="pc-link<?= $isActive('admin/orders') ?>">
              <span class="pc-micon">
                <svg class="pc-icon"><use xlink:href="#custom-bill"></use></svg>
              </span>
              <span class="pc-mtext">Siparişler</span>
            </a>
          </li>
        <?php endif; ?>
        <li class="pc-item">
          <a href="<?= site_url('admin/shipping') ?>" class="pc-link<?= $isActive('admin/shipping') ?>">
            <span class="pc-micon">
              <i class="ti ti-truck"></i>
            </span>
            <span class="pc-mtext">Kargo Takip</span>
          </a>
        </li>
        <?php if ($canManageShipping): ?>
          <li class="pc-item">
            <a href="<?= site_url('admin/shipping/automation') ?>" class="pc-link<?= $isActive('admin/shipping/automation') ?>">
              <span class="pc-micon">
                <i class="ti ti-truck"></i>
              </span>
              <span class="pc-mtext">Kargo Optimizasyonu</span>
            </a>
          </li>
        <?php endif; ?>

        <li class="pc-item pc-caption">
          <label>Operasyon</label>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/pricing') ?>" class="pc-link<?= $isActive('admin/pricing') ?>">
            <span class="pc-micon">
              <i class="ti ti-percentage"></i>
            </span>
            <span class="pc-mtext">Kampanya / Fiyat Paneli</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/customers') ?>" class="pc-link<?= $isActive('admin/customers') ?>">
            <span class="pc-micon">
              <i class="ti ti-users"></i>
            </span>
            <span class="pc-mtext">Müşteri Operasyonu</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/automation') ?>" class="pc-link<?= $isActive('admin/automation') ?>">
            <span class="pc-micon">
              <i class="ti ti-adjustments-automation"></i>
            </span>
            <span class="pc-mtext">Otomasyon ve Akıllı Kurallar</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>Sistem</label>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/notifications') ?>" class="pc-link<?= $isActive('admin/notifications') ?>">
            <span class="pc-micon">
              <i class="ti ti-bell"></i>
            </span>
            <span class="pc-mtext">Bildirim Yönetimi</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/settings') ?>" class="pc-link<?= $isActive('admin/settings') ?>">
            <span class="pc-micon">
              <i class="ti ti-settings"></i>
            </span>
            <span class="pc-mtext">Ayarlar</span>
          </a>
        </li>
        <?php if ($canManageAuthz): ?>
          <li class="pc-item">
            <a href="<?= site_url('admin/settings/permissions') ?>" class="pc-link<?= $isActive('admin/settings/permissions') ?>">
              <span class="pc-micon">
                <i class="ti ti-shield-check"></i>
              </span>
              <span class="pc-mtext">Yetkilendirme</span>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
