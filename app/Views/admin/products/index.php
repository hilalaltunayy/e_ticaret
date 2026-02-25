<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins/dataTables.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Ürünler</h5>
        <a href="<?= site_url('admin/products/create') ?>" class="btn btn-primary btn-sm">Yeni Ürün</a>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="dt-responsive table-responsive">
            <table id="productsTable" class="table table-hover table-striped align-middle mb-0 w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ürün Adı</th>
                        <th>Yazar</th>
                        <th>Tür</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Stok</th>
                        <th>Rezerve</th>
                        <th>Satılabilir</th>
                        <th>Durum</th>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/plugins/dataTables.bootstrap5.min.js') ?>"></script>
<script>
  (function () {
    $('#productsTable').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[1, 'asc']],
      dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      ajax: {
        url: "<?= site_url('admin/api/products') ?>",
        type: 'GET'
      },
      columns: [
        { data: 'id', name: 'id' },
        { data: 'title', name: 'title' },
        { data: 'author_name', name: 'author_name' },
        { data: 'type', name: 'type' },
        { data: 'category_name', name: 'category_name' },
        { data: 'price', name: 'price' },
        { data: 'stock_total', name: 'stock_total' },
        { data: 'stock_reserved', name: 'stock_reserved' },
        { data: 'stock_available', name: 'stock_available' },
        { data: 'is_active', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
      ],
      language: {
        lengthMenu: '_MENU_ kayıt göster',
        search: 'Ara:',
        zeroRecords: 'Kayıt bulunamadı',
        info: '_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor',
        infoEmpty: '0 kayıttan 0 - 0 arası gösteriliyor',
        infoFiltered: '(_MAX_ kayıt içinden filtrelendi)',
        paginate: {
          first: 'İlk',
          last: 'Son',
          next: 'Sonraki',
          previous: 'Önceki'
        },
        processing: 'Yükleniyor...'
      }
    });
  })();
</script>
<?= $this->endSection() ?>
