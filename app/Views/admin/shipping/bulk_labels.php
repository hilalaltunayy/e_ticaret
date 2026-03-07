<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Toplu Kargo Etiketleri</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 16px; color: #1f2937; }
    .label { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin-bottom: 12px; page-break-inside: avoid; }
    .row { margin: 4px 0; }
    .muted { color: #6b7280; font-size: 12px; }
    @media print { body { margin: 0; }
    }
  </style>
</head>
<body>
  <h2>Toplu Kargo Etiketleri</h2>
  <p class="muted">Oluşturulma: <?= esc((string) ($generatedAt ?? '')) ?></p>

  <?php foreach ((array) ($labels ?? []) as $label): ?>
    <section class="label">
      <div class="row"><strong>Sipariş No:</strong> <?= esc((string) ($label['order_no'] ?? '-')) ?></div>
      <div class="row"><strong>Müşteri:</strong> <?= esc((string) ($label['customer_name'] ?? '-')) ?></div>
      <div class="row"><strong>Kargo Firması:</strong> <?= esc((string) ($label['shipping_company'] ?? '-')) ?></div>
      <div class="row"><strong>Takip No:</strong> <?= esc((string) ($label['tracking_no'] ?? '-')) ?></div>
      <div class="row"><strong>Paket Kodu:</strong> <?= esc((string) ($label['package_code'] ?? '-')) ?></div>
      <div class="row muted">Doğrulama: <?= esc((string) ($label['verify_url'] ?? '-')) ?></div>
    </section>
  <?php endforeach; ?>
</body>
</html>
