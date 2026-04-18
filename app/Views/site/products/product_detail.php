<?= $this->extend("layouts/main") ?>

<?= $this->section("content") ?>
<?php
helper('product_media');
$productName = is_object($product ?? null) ? (string) ($product->product_name ?? '') : (string) ($product['product_name'] ?? '');
$productIdValue = is_object($product ?? null) ? (string) ($product->id ?? '') : (string) ($product['id'] ?? '');
$productPrice = is_object($product ?? null) ? (float) ($product->price ?? 0) : (float) ($product['price'] ?? 0);
$productStock = is_object($product ?? null) ? (int) ($product->stock ?? 0) : (int) ($product['stock'] ?? 0);
$productCreatedAt = is_object($product ?? null) ? ($product->created_at ?? null) : ($product['created_at'] ?? null);
$productImageUrl = is_object($product ?? null)
    ? (string) ($product->image_url ?? product_image_url((string) ($product->image ?? '')))
    : product_image_url((string) ($product['image'] ?? ''));
?>
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Anasayfa</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('products/selection') ?>">Urun Listesi</a></li>
                            <li class="breadcrumb-item" aria-current="page"><?= esc($productName) ?> Detaylari</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card overflow-hidden">
                    <div class="card-body bg-light-primary">
                        <div class="text-center">
                            <img src="<?= esc($productImageUrl) ?>" alt="<?= esc($productName) ?>" class="img-fluid rounded border bg-white p-2" style="max-height: 280px; object-fit: cover;">
                            <h4 class="mt-3 mb-0"><?= esc($productName) ?></h4>
                            <p class="text-muted">Urun Kimligi: #<?= esc($productIdValue) ?></p>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="ti ti-currency-dollar me-2"></i>Fiyat:</span>
                                <span class="fw-bold text-dark"><?= number_format($productPrice, 2) ?> TL</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="ti ti-package me-2"></i>Mevcut Stok:</span>
                                <span class="badge bg-success rounded-pill"><?= esc((string) $productStock) ?> Adet</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="ti ti-calendar me-2"></i>Kayit Tarihi:</span>
                                <span class="text-muted"><?= ! empty($productCreatedAt) ? date('d.m.Y H:i', strtotime((string) $productCreatedAt)) : '-' ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Aylik Stok Hareket Analizi</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary">2026 Yili</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="stock-movement-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("scripts") ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var options = {
        series: [{
            name: 'Stok Girisi (Adet)',
            data: [31, 40, 28, 51, 42, 109, 100, 80, 95, 120, 110, 150]
        }, {
            name: 'Stok Cikisi (Satis)',
            data: [11, 32, 45, 32, 34, 52, 41, 60, 70, 85, 90, 105]
        }],
        chart: {
            height: 350,
            type: 'area',
            toolbar: { show: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        colors: ['#4680ff', '#f44236'],
        xaxis: {
            categories: ["Ocak", "Subat", "Mart", "Nisan", "Mayis", "Haziran", "Temmuz", "Agustos", "Eylul", "Ekim", "Kasim", "Aralik"]
        },
        tooltip: {
            x: { format: 'dd/MM/yy HH:mm' },
        },
    };

    var chart = new ApexCharts(document.querySelector("#stock-movement-chart"), options);
    chart.render();
});
</script>
<?= $this->endSection() ?>
