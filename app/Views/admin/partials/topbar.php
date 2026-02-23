<header class="pc-header">
  <div class="header-wrapper">

    <div class="me-auto pc-mob-drp">
      <ul class="list-unstyled">
        <li class="pc-h-item pc-sidebar-collapse">
          <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
            <i class="ti ti-menu-2"></i>
          </a>
        </li>
        <li class="pc-h-item pc-sidebar-popup">
          <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
            <i class="ti ti-menu-2"></i>
          </a>
        </li>
        <li class="pc-h-item d-none d-md-inline-flex">
          <form class="form-search">
            <i class="search-icon">
              <svg class="pc-icon">
                <use xlink:href="#custom-search-normal-1"></use>
              </svg>
            </i>
            <input type="search" class="form-control" placeholder="Ctrl + K" />
          </form>
        </li>
      </ul>
    </div>

    <div class="ms-auto">
      <ul class="list-unstyled">
        <li class="dropdown pc-h-item">
          <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#">
            <svg class="pc-icon">
              <use xlink:href="#custom-sun-1"></use>
            </svg>
          </a>
          <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
            <a href="#!" class="dropdown-item" onclick="layout_change('dark')">
              <svg class="pc-icon"><use xlink:href="#custom-moon"></use></svg>
              <span>Dark</span>
            </a>
            <a href="#!" class="dropdown-item" onclick="layout_change('light')">
              <svg class="pc-icon"><use xlink:href="#custom-sun-1"></use></svg>
              <span>Light</span>
            </a>
            <a href="#!" class="dropdown-item" onclick="layout_change_default()">
              <svg class="pc-icon"><use xlink:href="#custom-setting-2"></use></svg>
              <span>Default</span>
            </a>
          </div>
        </li>

        <li class="dropdown pc-h-item header-user-profile">
          <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#">
            <img src="<?= base_url('assets/admin/images/user/avatar-2.jpg') ?>" alt="user-image" class="user-avtar" />
          </a>
          <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
            <div class="dropdown-header d-flex align-items-center justify-content-between">
              <h5 class="m-0">Profile</h5>
            </div>
            <div class="dropdown-body">
              <div class="d-grid mb-3">
                <a class="btn btn-primary" href="<?= site_url('logout') ?>">Logout</a>
              </div>
            </div>
          </div>
        </li>

      </ul>
    </div>

  </div>
</header>