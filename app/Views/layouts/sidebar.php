<style>
  .pc-sidebar {
    background: #ffffff !important;
    border-right: 1px solid #e9ecef;
  }

  .pc-sidebar .b-brand,
  .pc-sidebar .pc-caption label,
  .pc-sidebar .pc-link,
  .pc-sidebar .pc-mtext,
  .pc-sidebar .pc-micon,
  .pc-sidebar .pc-link .pc-icon {
    color: #0D2B5B !important;
  }

  .pc-sidebar .m-header .b-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 2px 0;
    text-decoration: none;
  }

  .pc-sidebar .m-header .pc-brand-icon {
    width: 22px;
    height: 22px;
    flex: 0 0 22px;
  }

  .pc-sidebar .m-header .pc-brand-text {
    font-size: 21px;
    font-weight: 800;
    line-height: 1.1;
    white-space: nowrap;
  }

  .pc-sidebar .pc-link:hover {
    background: rgba(13, 43, 91, 0.12) !important;
    color: #0D2B5B !important;
  }

  .pc-user-card {
    background: #f8fafc !important;
    border: 1px solid #e9ecef;
  }

  .pc-user-card h6,
  .pc-user-card small {
    color: #0D2B5B !important;
  }

  .pc-sidebar .pc-link .pc-icon {
    width: 18px;
    height: 18px;
  }
</style>

<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header py-3">
      <a href="<?= base_url('dashboard_anasayfa') ?>" class="b-brand">
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
              <img src="<?= base_url('assets/images/user/avatar-1.jpg') ?>" class="user-avtar wid-45 rounded-circle" alt="avatar" />
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="mb-0"><?= session()->get('userData')['name'] ?? 'Misafir Kullanıcı' ?></h6>
              <small>Mühendis / Yönetici</small>
            </div>
          </div>
        </div>
      </div>

      <ul class="pc-navbar">
        <li class="pc-item pc-caption"><label>Admin Erişim</label></li>
        <li class="pc-item">
          <a href="<?= base_url('admin/dashboard') ?>" class="pc-link">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-status-up"></use></svg>
            </span>
            <span class="pc-mtext">Dashboard</span>
          </a>
        </li>

        <li class="pc-item pc-caption"><label>Ürün Yönetimi</label></li>
        <li class="pc-item">
          <a href="<?= base_url('admin/products') ?>" class="pc-link">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-shopping-bag"></use></svg>
            </span>
            <span class="pc-mtext">Ürünler</span>
          </a>
        </li>

        <li class="pc-item pc-caption"><label>Stok Yönetimi</label></li>
        <li class="pc-item">
          <a href="<?= base_url('products/stock-management') ?>" class="pc-link">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-box-1"></use></svg>
            </span>
            <span class="pc-mtext">Stok Takip Paneli</span>
          </a>
        </li>

        <li class="pc-item pc-caption"><label>Sipariş Yönetimi</label></li>
        <li class="pc-item">
          <a href="<?= base_url('orders') ?>" class="pc-link">
            <span class="pc-micon">
              <svg class="pc-icon"><use xlink:href="#custom-bill"></use></svg>
            </span>
            <span class="pc-mtext">Siparişler</span>
          </a>
        </li>

        <li class="pc-item pc-caption"><label>Sistem</label></li>
        <li class="pc-item">
          <a href="<?= base_url('logout') ?>" class="pc-link text-danger">
            <span class="pc-micon"><i class="ti ti-power"></i></span>
            <span class="pc-mtext">Çıkış Yap</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
