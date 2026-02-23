<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $authors = $authors ?? []; ?>
<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Ürünler</h5>
            <a href="<?= site_url('admin/products/create') ?>" class="btn btn-primary btn-sm">Yeni Ürün</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Arama</label>
                <input type="text" id="filterQ" class="form-control" placeholder="Ürün adı, kategori veya yazar ara">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tür</label>
                <select id="filterType" class="form-select">
                    <option value="">Tümü</option>
                    <option value="basili">Basılı</option>
                    <option value="dijital">Dijital</option>
                    <option value="paket">Paket</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Aktiflik</label>
                <select id="filterActive" class="form-select">
                    <option value="">Tümü</option>
                    <option value="1">Aktif</option>
                    <option value="0">Pasif</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Stok Aralığı</label>
                <select id="filterStockRange" class="form-select">
                    <option value="">Tümü</option>
                    <option value="low">0-5</option>
                    <option value="mid">6-20</option>
                    <option value="high">21-100</option>
                    <option value="over100">100+</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Yazar</label>
                <select id="filterAuthor" class="form-select">
                    <option value="">Tümü</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= esc((string) ($author['id'] ?? '')) ?>"><?= esc((string) ($author['name'] ?? '-')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" id="btnClearFilters" class="btn btn-light">Temizle</button>
            <button type="button" id="btnApplyFilters" class="btn btn-primary">Filtrele</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="productsTable" class="table table-hover align-middle mb-0 w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ürün Adı</th>
                        <th>Yazar</th>
                        <th>Tür</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Stok Durumu</th>
                        <th>Aktif</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.bootstrap5.min.js') ?>"></script>
<script>
  (function () {
    const table = $('#productsTable').DataTable({
      processing: true,
      serverSide: true,
      searching: false,
      pageLength: 10,
      order: [[0, 'desc']],
      ajax: {
        url: "<?= site_url('admin/products/datatables') ?>",
        type: 'GET',
        data: function (d) {
          d.q = $('#filterQ').val();
          d.type = $('#filterType').val();
          d.is_active = $('#filterActive').val();
          d.stock_range = $('#filterStockRange').val();
          d.author_id = $('#filterAuthor').val();
        }
      },
      columns: [
        { data: 'id' },
        { data: 'product_name' },
        { data: 'author_name' },
        { data: 'type' },
        { data: 'category_name' },
        { data: 'price' },
        { data: 'stock_overview', orderable: false, searchable: false },
        { data: 'is_active', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
      ],
      language: {
        processing: 'Yükleniyor...',
        lengthMenu: '_MENU_ kayıt göster',
        zeroRecords: 'Kayıt bulunamadı',
        info: '_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor',
        infoEmpty: 'Kayıt yok',
        infoFiltered: '(_MAX_ kayıt içinden filtrelendi)',
        paginate: {
          first: 'İlk',
          last: 'Son',
          next: 'Sonraki',
          previous: 'Önceki'
        }
      }
    });

    $('#btnApplyFilters').on('click', function () {
      table.ajax.reload();
    });

    $('#btnClearFilters').on('click', function () {
      $('#filterQ').val('');
      $('#filterType').val('');
      $('#filterActive').val('');
      $('#filterStockRange').val('');
      $('#filterAuthor').val('');
      table.ajax.reload();
    });

    $('#filterQ').on('keypress', function (e) {
      if (e.which === 13) {
        table.ajax.reload();
      }
    });
  })();
</script>
<?= $this->endSection() ?>
