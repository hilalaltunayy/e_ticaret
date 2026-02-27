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
              <h6 class="mb-0">Admin</h6>
              <small>Administrator</small>
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
          <a href="<?= site_url('admin/dashboard') ?>" class="pc-link">
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
          <a href="<?= site_url('admin/products') ?>" class="pc-link">
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
          <a href="<?= site_url('admin/stock') ?>" class="pc-link">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-box-1"></use></svg>
            </span>
            <span class="pc-mtext">Stok Takip Paneli</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>Sipariş Yönetimi</label>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/orders') ?>" class="pc-link">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-bill"></use></svg>
            </span>
            <span class="pc-mtext">Siparişler</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="<?= site_url('admin/orders/statuses') ?>" class="pc-link">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-clipboard-text"></use></svg>
            </span>
            <span class="pc-mtext">Sipariş Durumları</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
