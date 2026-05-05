<?php
$session = session();
$user = $session->get('user') ?? [];
$role = strtolower(trim((string) ($user['role'] ?? '')));
$permissions = $session->get('permissions');
if (! is_array($permissions)) {
    $permissions = [];
}
$hasPerm = static function (string $perm) use ($role, $permissions): bool {
    return $role === 'admin' || in_array($perm, $permissions, true);
};

$searchIndex = [
    ['title' => 'Dashboard', 'url' => site_url('admin/dashboard'), 'keywords' => ['dashboard', 'panel', 'anasayfa'], 'perm' => 'manage_dashboard'],
    ['title' => 'Urunler', 'url' => site_url('admin/products'), 'keywords' => ['urun', 'stok', 'product'], 'perm' => 'manage_products'],
    ['title' => 'Siparisler', 'url' => site_url('admin/orders'), 'keywords' => ['siparis', 'order'], 'perm' => 'manage_orders'],
    ['title' => 'Musteriler', 'url' => site_url('admin/customers'), 'keywords' => ['musteri', 'customer'], 'perm' => 'manage_customers'],
    ['title' => 'Musteri Mesajlari', 'url' => site_url('admin/customer-messages'), 'keywords' => ['mesaj', 'not', 'customer message'], 'perm' => 'manage_customer_messages'],
    ['title' => 'Urun Yorumlari', 'url' => site_url('admin/reviews'), 'keywords' => ['yorum', 'degerlendirme', 'review'], 'perm' => 'manage_reviews'],
    ['title' => 'Sikayet Yonetimi', 'url' => site_url('admin/complaints'), 'keywords' => ['sikayet', 'complaint'], 'perm' => 'manage_complaints'],
    ['title' => 'Stok Takip', 'url' => site_url('admin/stock'), 'keywords' => ['stok', 'envanter', 'stock'], 'perm' => 'manage_stock'],
    ['title' => 'Kargo Takip', 'url' => site_url('admin/shipping'), 'keywords' => ['kargo', 'shipping'], 'perm' => 'manage_shipping'],
    ['title' => 'Kargo Optimizasyonu', 'url' => site_url('admin/shipping/automation'), 'keywords' => ['kargo', 'otomasyon', 'shipping automation'], 'perm' => 'manage_shipping'],
    ['title' => 'Pazarlama', 'url' => site_url('admin/marketing'), 'keywords' => ['pazarlama', 'kampanya', 'kupon', 'fiyat'], 'perm' => 'manage_marketing'],
    ['title' => 'Banner Yonetimi', 'url' => site_url('admin/banners'), 'keywords' => ['banner', 'gorsel'], 'perm' => 'manage_banners'],
    ['title' => 'Trafik Analizi', 'url' => site_url('admin/traffic-analysis'), 'keywords' => ['trafik', 'analiz', 'ziyaret'], 'perm' => 'manage_traffic'],
    ['title' => 'Log Kayitlari', 'url' => site_url('admin/log-records'), 'keywords' => ['log', 'kayit', 'audit'], 'perm' => 'manage_logs'],
    ['title' => 'Sayfa Yonetimi', 'url' => site_url('admin/pages'), 'keywords' => ['sayfa', 'builder'], 'perm' => 'manage_pages'],
    ['title' => 'Ayarlar', 'url' => site_url('admin/settings'), 'keywords' => ['ayar', 'sistem'], 'perm' => '__admin_only__'],
    ['title' => 'Yetkilendirme', 'url' => site_url('admin/settings/permissions'), 'keywords' => ['yetki', 'rol', 'permission'], 'perm' => '__admin_only__'],
];

$visibleSearchIndex = [];
foreach ($searchIndex as $item) {
    $perm = (string) ($item['perm'] ?? '');
    if ($perm === '__admin_only__' && $role !== 'admin') {
        continue;
    }
    if ($perm !== '' && $perm !== '__admin_only__' && ! $hasPerm($perm)) {
        continue;
    }
    $visibleSearchIndex[] = $item;
}
?>
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
          <form class="form-search position-relative" id="adminGlobalSearchForm" autocomplete="off">
            <i class="search-icon">
              <svg class="pc-icon">
                <use xlink:href="#custom-search-normal-1"></use>
              </svg>
            </i>
            <input id="adminGlobalSearchInput" type="search" class="form-control" placeholder="Sayfa ara..." />
            <div id="adminGlobalSearchResults" class="dropdown-menu show w-100" style="display:none; max-height: 320px; overflow-y: auto;"></div>
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

<script>
  (function () {
    var index = <?= json_encode($visibleSearchIndex, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var input = document.getElementById('adminGlobalSearchInput');
    var results = document.getElementById('adminGlobalSearchResults');
    var form = document.getElementById('adminGlobalSearchForm');
    if (!input || !results || !form) return;

    function normalize(value) {
      return (value || '').toString().toLocaleLowerCase('tr-TR').trim();
    }

    function hideResults() {
      results.style.display = 'none';
      results.innerHTML = '';
    }

    function renderItems(items) {
      if (!items.length) {
        results.innerHTML = '<span class="dropdown-item-text text-muted">Sonuc bulunamadi</span>';
        results.style.display = 'block';
        return;
      }

      results.innerHTML = '';
      items.forEach(function (item) {
        var link = document.createElement('a');
        link.className = 'dropdown-item';
        link.href = item.url;
        link.textContent = item.title;
        results.appendChild(link);
      });
      results.style.display = 'block';
    }

    function search(query) {
      var q = normalize(query);
      if (q.length < 1) {
        hideResults();
        return;
      }

      var matched = index.filter(function (item) {
        var hay = [item.title].concat(item.keywords || []).map(normalize).join(' ');
        return hay.indexOf(q) !== -1;
      }).slice(0, 8);

      renderItems(matched);
    }

    input.addEventListener('input', function () {
      search(input.value);
    });

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var q = normalize(input.value);
      if (!q) {
        hideResults();
        return;
      }

      var first = index.find(function (item) {
        var hay = [item.title].concat(item.keywords || []).map(normalize).join(' ');
        return hay.indexOf(q) !== -1;
      });
      if (first) {
        window.location.href = first.url;
      }
    });

    document.addEventListener('click', function (event) {
      if (!form.contains(event.target)) {
        hideResults();
      }
    });
  })();
</script>
