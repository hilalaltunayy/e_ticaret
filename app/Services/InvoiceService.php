<?php

namespace App\Services;

use App\Models\InvoiceModel;
use App\Models\OrderModel;

class InvoiceService
{
    private const VAT_RATE = 0.20;
    private const COD_METHODS = ['cash_on_delivery', 'cod', 'kapida_odeme', 'kapida'];
    private const ONLINE_ALLOWED_STATUSES = ['shipped', 'delivered'];
    private const COD_ALLOWED_STATUSES = ['delivered'];
    private const BLOCKED_STATUSES = ['cancelled', 'return_in_progress', 'return_done', 'returned'];

    public function __construct(
        private ?InvoiceModel $invoiceModel = null,
        private ?OrderModel $orderModel = null
    ) {
        $this->invoiceModel = $this->invoiceModel ?? new InvoiceModel();
        $this->orderModel = $this->orderModel ?? new OrderModel();
    }

    public function findByOrderId(string $orderId): ?array
    {
        return $this->invoiceModel->findByOrderId($orderId);
    }

    public function evaluateInvoiceEligibility(array $order): array
    {
        $orderStatus = strtolower(trim((string) ($order['order_status'] ?? $order['status'] ?? '')));
        $paymentMethod = strtolower(trim((string) ($order['payment_method'] ?? '')));

        if (in_array($orderStatus, self::BLOCKED_STATUSES, true)) {
            return [
                'allowed' => false,
                'reason_code' => 'blocked_status',
                'message' => 'Bu sipariş için fatura oluşturulamaz (iptal/iade sürecinde).',
            ];
        }

        $isCod = in_array($paymentMethod, self::COD_METHODS, true);
        if ($isCod) {
            if (! in_array($orderStatus, self::COD_ALLOWED_STATUSES, true)) {
                return [
                    'allowed' => false,
                    'reason_code' => 'cod_requires_delivered',
                    'message' => 'Kapıda ödemede fatura, sipariş teslim edildikten sonra oluşturulabilir.',
                ];
            }

            return ['allowed' => true, 'reason_code' => 'allowed_cod', 'message' => ''];
        }

        if (! in_array($orderStatus, self::ONLINE_ALLOWED_STATUSES, true)) {
            return [
                'allowed' => false,
                'reason_code' => 'online_requires_shipped_or_delivered',
                'message' => 'Online ödemelerde fatura, sipariş kargoya verildikten sonra oluşturulabilir.',
            ];
        }

        return ['allowed' => true, 'reason_code' => 'allowed_online', 'message' => ''];
    }

    public function createForOrder(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            return [
                'success' => false,
                'code' => 'invalid_order',
                'message' => 'Geçersiz sipariş.',
            ];
        }

        $order = $this->orderModel->findByIdOrOrderNo($orderId);
        if (! $order) {
            return [
                'success' => false,
                'code' => 'order_not_found',
                'message' => 'Sipariş bulunamadı.',
            ];
        }

        $existing = $this->invoiceModel->findByOrderId((string) $order['id']);
        if ($existing) {
            return [
                'success' => false,
                'code' => 'already_exists',
                'message' => 'Bu sipariş için fatura zaten oluşturulmuş.',
                'invoice' => $existing,
            ];
        }

        $eligibility = $this->evaluateInvoiceEligibility($order);
        if (! ($eligibility['allowed'] ?? false)) {
            return [
                'success' => false,
                'code' => 'not_allowed',
                'message' => (string) ($eligibility['message'] ?? 'Bu sipariş için fatura oluşturulamaz.'),
            ];
        }

        $items = $this->orderModel->getOrderItems((string) $order['id']);
        $subtotal = $this->calculateSubtotal($order, $items);
        $taxTotal = round($subtotal * self::VAT_RATE, 2);
        $grandTotal = round($subtotal + $taxTotal, 2);

        $createdInvoice = null;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $invoiceNo = $this->generateNextInvoiceNo((int) date('Y'));
            $invoiceId = InvoiceModel::uuidV4();

            $insertData = [
                'id' => $invoiceId,
                'order_id' => (string) $order['id'],
                'invoice_no' => $invoiceNo,
                'series' => 'TEMP',
                'status' => 'generated',
                'currency' => 'TRY',
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'vat_rate' => self::VAT_RATE,
                'ubl_xml_path' => null,
                'pdf_path' => null,
            ];

            $inserted = $this->invoiceModel->insert($insertData, false);
            if (! $inserted) {
                $dbError = $this->invoiceModel->db->error();
                if ($this->isDuplicateError($dbError)) {
                    $existingAfterRace = $this->invoiceModel->findByOrderId((string) $order['id']);
                    if ($existingAfterRace) {
                        return [
                            'success' => false,
                            'code' => 'already_exists',
                            'message' => 'Bu sipariş için fatura zaten oluşturulmuş.',
                            'invoice' => $existingAfterRace,
                        ];
                    }

                    continue;
                }

                return [
                    'success' => false,
                    'code' => 'create_failed',
                    'message' => 'Fatura kaydı oluşturulamadı.',
                ];
            }

            $createdInvoice = $insertData;
            break;
        }

        if (! is_array($createdInvoice)) {
            return [
                'success' => false,
                'code' => 'create_failed',
                'message' => 'Fatura kaydı oluşturulamadı.',
            ];
        }

        $xmlRelative = 'uploads/invoices/xml/' . $createdInvoice['invoice_no'] . '.xml';
        $pdfRelative = 'uploads/invoices/' . $createdInvoice['invoice_no'] . '.pdf';
        $xmlAbsolute = $this->toWritableAbsolutePath($xmlRelative);
        $pdfAbsolute = $this->toWritableAbsolutePath($pdfRelative);

        if (! $this->ensureParentDirectory($xmlAbsolute) || ! $this->ensureParentDirectory($pdfAbsolute)) {
            return [
                'success' => false,
                'code' => 'write_failed',
                'message' => 'Fatura dosya klasörü hazırlanamadı.',
            ];
        }

        $xmlContent = $this->buildPlaceholderUblXml($order, $createdInvoice, $items);
        $pdfContent = $this->buildPdfContent($order, $createdInvoice, $items);

        if (@file_put_contents($xmlAbsolute, $xmlContent) === false) {
            return [
                'success' => false,
                'code' => 'write_failed',
                'message' => 'UBL XML dosyası yazılamadı.',
            ];
        }

        if (@file_put_contents($pdfAbsolute, $pdfContent) === false) {
            return [
                'success' => false,
                'code' => 'write_failed',
                'message' => 'PDF dosyası yazılamadı.',
            ];
        }

        $this->invoiceModel->update((string) $createdInvoice['id'], [
            'status' => 'pdf_generated',
            'ubl_xml_path' => $xmlRelative,
            'pdf_path' => $pdfRelative,
        ]);

        $savedInvoice = $this->invoiceModel->find((string) $createdInvoice['id']);

        return [
            'success' => true,
            'code' => 'created',
            'message' => 'Fatura oluşturuldu.',
            'invoice' => $savedInvoice ?: $createdInvoice,
        ];
    }

    private function calculateSubtotal(array $order, array $items): float
    {
        if ($items !== []) {
            $sum = 0.0;
            foreach ($items as $item) {
                $sum += (float) ($item['line_total'] ?? 0);
            }

            if ($sum > 0) {
                return round($sum, 2);
            }
        }

        return round((float) ($order['total_amount'] ?? 0), 2);
    }

    private function generateNextInvoiceNo(int $year): string
    {
        $prefix = 'TEMP-' . $year . '-';
        $latest = $this->invoiceModel
            ->select('invoice_no')
            ->like('invoice_no', $prefix, 'after')
            ->orderBy('invoice_no', 'DESC')
            ->first();

        $nextNumber = 1;
        if (is_array($latest)) {
            $latestNo = trim((string) ($latest['invoice_no'] ?? ''));
            if ($latestNo !== '' && preg_match('/^TEMP-\d{4}-(\d{6})$/', $latestNo, $matches) === 1) {
                $nextNumber = ((int) $matches[1]) + 1;
            }
        }

        return sprintf('TEMP-%d-%06d', $year, $nextNumber);
    }

    private function buildPlaceholderUblXml(array $order, array $invoice, array $items): string
    {
        $lineItems = '';
        $index = 1;
        foreach ($items as $item) {
            $lineItems .= sprintf(
                "  <Line><ID>%d</ID><Name>%s</Name><Quantity>%s</Quantity><LineTotal>%s</LineTotal></Line>\n",
                $index,
                $this->xml((string) ($item['product_name_snapshot'] ?? 'Urun')),
                $this->xml((string) ($item['quantity'] ?? 0)),
                $this->xml(number_format((float) ($item['line_total'] ?? 0), 2, '.', ''))
            );
            $index++;
        }

        if ($lineItems === '') {
            $lineItems = "  <Line><ID>1</ID><Name>Siparis Toplami</Name><Quantity>1</Quantity><LineTotal>"
                . $this->xml(number_format((float) ($invoice['subtotal'] ?? 0), 2, '.', ''))
                . "</LineTotal></Line>\n";
        }

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<Invoice>\n"
            . '  <InvoiceNo>' . $this->xml((string) ($invoice['invoice_no'] ?? '')) . "</InvoiceNo>\n"
            . '  <IssueDate>' . date('Y-m-d') . "</IssueDate>\n"
            . "  <Seller><Name>E-Ticaret Demo</Name></Seller>\n"
            . '  <Buyer><Name>' . $this->xml((string) ($order['customer_name'] ?? $order['user_name'] ?? 'Musteri')) . "</Name></Buyer>\n"
            . "  <Currency>TRY</Currency>\n"
            . "  <Totals>\n"
            . '    <Subtotal>' . $this->xml(number_format((float) ($invoice['subtotal'] ?? 0), 2, '.', '')) . "</Subtotal>\n"
            . '    <TaxTotal>' . $this->xml(number_format((float) ($invoice['tax_total'] ?? 0), 2, '.', '')) . "</TaxTotal>\n"
            . '    <GrandTotal>' . $this->xml(number_format((float) ($invoice['grand_total'] ?? 0), 2, '.', '')) . "</GrandTotal>\n"
            . "  </Totals>\n"
            . "  <Lines>\n"
            . $lineItems
            . "  </Lines>\n"
            . "</Invoice>\n";
    }

    private function buildPdfContent(array $order, array $invoice, array $items): string
    {
        $lines = [
            'FATURA',
            'Fatura No: ' . (string) ($invoice['invoice_no'] ?? '-'),
            'Düzenleme Tarihi: ' . date('Y-m-d'),
            'Müşteri: ' . (string) ($order['customer_name'] ?? $order['user_name'] ?? '-'),
            'Sipariş ID: ' . (string) ($order['id'] ?? '-'),
            '',
            'Ara Toplam: ' . number_format((float) ($invoice['subtotal'] ?? 0), 2, '.', '') . ' TRY',
            'KDV Toplamı: ' . number_format((float) ($invoice['tax_total'] ?? 0), 2, '.', '') . ' TRY',
            'Genel Toplam: ' . number_format((float) ($invoice['grand_total'] ?? 0), 2, '.', '') . ' TRY',
        ];

        if ($items !== []) {
            $lines[] = '';
            $lines[] = 'Kalemler:';
            foreach ($items as $item) {
                $name = trim((string) ($item['product_name_snapshot'] ?? 'Urun'));
                $qty = (int) ($item['quantity'] ?? 0);
                $lineTotal = number_format((float) ($item['line_total'] ?? 0), 2, '.', '');
                $lines[] = '- ' . $name . ' (Adet: ' . $qty . ') Tutar: ' . $lineTotal . ' TRY';
            }
        }

        return $this->renderSimplePdf($lines);
    }

    private function renderSimplePdf(array $lines): string
    {
        $content = "BT\n/F1 12 Tf\n50 800 Td\n14 TL\n";
        foreach ($lines as $line) {
            $normalized = $this->normalizePdfText($line);
            $content .= '(' . $this->escapePdfText($normalized) . ") Tj\nT*\n";
        }
        $content .= "ET";

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding 6 0 R >>";
        $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        $objects[] = "<< /Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences [208 /Gbreve 221 /Idotaccent 222 /Scedilla 240 /gbreve 253 /dotlessi 254 /scedilla] >>";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $objectBody) {
            $offsets[] = strlen($pdf);
            $objNo = $index + 1;
            $pdf .= $objNo . " 0 obj\n" . $objectBody . "\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

        return $pdf;
    }

    private function normalizePdfText(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $converted = @iconv('UTF-8', 'Windows-1254//TRANSLIT', $text);
        if ($converted === false) {
            $converted = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;
        }

        return $converted;
    }

    private function escapePdfText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\(', $text);
        $text = str_replace(')', '\)', $text);
        return str_replace(["\r", "\n"], ' ', $text);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function ensureParentDirectory(string $absolutePath): bool
    {
        $parent = dirname($absolutePath);
        if (is_dir($parent)) {
            return true;
        }

        return @mkdir($parent, 0775, true);
    }

    private function toWritableAbsolutePath(string $relativePath): string
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
        return WRITEPATH . $normalized;
    }

    private function isDuplicateError(array $error): bool
    {
        $code = (int) ($error['code'] ?? 0);
        if ($code === 1062) {
            return true;
        }

        $message = strtolower((string) ($error['message'] ?? ''));
        return str_contains($message, 'duplicate');
    }
}
