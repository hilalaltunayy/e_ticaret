<script src="<?= base_url('assets/admin/js/plugins/popper.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/plugins/bootstrap.min.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var firstSearch = document.querySelector('.storefront-search input[name="q"]');
        if (firstSearch && window.innerWidth > 991) {
            firstSearch.setAttribute('autocomplete', 'off');
        }
    });
</script>
