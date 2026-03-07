<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Toplu Barkod Çıktısı</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 16px; color: #1f2937; }
    .card { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin-bottom: 12px; page-break-inside: avoid; }
    .row { margin: 4px 0; }
    .barcode { font-family: "Courier New", monospace; padding: 6px; border: 1px dashed #9ca3af; display: inline-block; margin-top: 6px; }
    .muted { color: #6b7280; font-size: 12px; }
    @media print { body { margin: 0; }
    }
  </style>
</head>
<body>
  <h2>Toplu Barkod Çıktısı</h2>
  <p class="muted">Barkod sistemi demo çıktısı</p>
  <p class="muted">Oluşturulma: <?= esc((string) ($generatedAt ?? '')) ?></p>

  <?php foreach ((array) ($rows ?? []) as $row): ?>
    <section class="card">
      <div class="row"><strong>Sipariş No:</strong> <?= esc((string) ($row['order_no'] ?? '-')) ?></div>
      <div class="row"><strong>Paket Kodu:</strong> <?= esc((string) ($row['package_code'] ?? '-')) ?></div>
      <div class="row"><strong>Takip No:</strong> <?= esc((string) ($row['tracking_no'] ?? '-')) ?></div>
      <div class="row"><strong>Ürün ID Barkod Metni:</strong></div>
      <div class="barcode">*<?= esc((string) ($row['barcode_text'] ?? '-')) ?>*</div>
    </section>
  <?php endforeach; ?>
</body>
</html>
