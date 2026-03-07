<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\ShippingModel;
use App\Services\PackingService;

class Shipping extends BaseController
{
    public function index()
    {
        $shippingModel = new ShippingModel();

        return view('admin/shipping/index', [
            'title' => 'Kargo Takip',
            'kpi' => $shippingModel->kpiStats(),
            'shippingCompanies' => $this->shippingCompanies(),
        ]);
    }

    public function datatables()
    {
        $params = $this->request->getGet();

        try {
            $result = (new ShippingModel())->datatablesList($params);
            $rows = $result['data'] ?? [];

            $data = array_map(function (array $row) {
                $id = trim((string) ($row['id'] ?? ''));
                $orderNo = trim((string) ($row['order_no'] ?? ''));
                if ($orderNo === '' && $id !== '') {
                    $orderNo = '#' . strtoupper(substr(str_replace('-', '', $id), 0, 8));
                }
                if ($orderNo === '') {
                    $orderNo = '-';
                }

                $customer = trim((string) ($row['customer_name'] ?? ''));
                $shippingCompany = trim((string) ($row['shipping_company'] ?? ''));
                $trackingNo = trim((string) ($row['tracking_no'] ?? ''));
                $updatedAt = trim((string) ($row['updated_at'] ?? ''));
                $shippingStatus = trim((string) ($row['shipping_status'] ?? 'not_shipped'));
                $shippedDate = trim((string) ($row['shipped_date'] ?? ''));
                $deliveredDate = trim((string) ($row['delivered_date'] ?? ''));
                $statusGroup = $this->statusGroup($shippingStatus);

                $detailHref = $id !== '' ? site_url('admin/orders/' . $id) : '#';

                return [
                    'order_id' => esc($id),
                    'order_no' => esc($orderNo),
                    'customer_name' => esc($customer !== '' ? $customer : '-'),
                    'shipping_company' => esc($shippingCompany !== '' ? $shippingCompany : '-'),
                    'tracking_no' => esc($trackingNo !== '' ? $trackingNo : '-'),
                    'shipping_status' => $this->shippingStatusBadge($shippingStatus),
                    'updated_at' => esc($updatedAt !== '' ? $updatedAt : '-'),
                    'shipping_status_raw' => esc($statusGroup),
                    'shipped_date' => esc($shippedDate),
                    'delivered_filter' => ($deliveredDate !== '' || $statusGroup === 'delivered') ? '1' : '0',
                    'problem_filter' => $statusGroup === 'delayed' ? '1' : '0',
                    'actions' => '<div class="d-flex gap-1">'
                        . '<a href="#" class="btn btn-sm btn-light-secondary">Takip Gör</a>'
                        . '<a href="' . esc($detailHref) . '" class="btn btn-sm btn-outline-primary">Sipariş Detayı</a>'
                        . '</div>',
                ];
            }, $rows);

            $payload = [
                'draw' => (int) ($params['draw'] ?? 0),
                'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
                'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
                'data' => $data,
            ];
        } catch (\Throwable $e) {
            log_message('error', '[shipping-api] ' . $e->getMessage());
            log_message('error', $e->getTraceAsString());

            try {
                $lastQuery = db_connect()->getLastQuery();
                if ($lastQuery !== null) {
                    log_message('error', '[shipping-api] last_query: ' . (string) $lastQuery);
                }
            } catch (\Throwable $queryError) {
                log_message('error', '[shipping-api] last_query_unavailable: ' . $queryError->getMessage());
            }

            $payload = [
                'draw' => (int) ($params['draw'] ?? 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Kargo verileri alınırken bir hata oluştu.',
            ];
        }

        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function trackingTemplate()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'trk_tpl_');
        if ($tmpFile === false) {
            return redirect()->to(site_url('admin/shipping'))->with('error', 'Şablon dosyası oluşturulamadı.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($tmpFile, \ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpFile);
            return redirect()->to(site_url('admin/shipping'))->with('error', 'Şablon dosyası oluşturulamadı.');
        }

        $sampleOrderNo = 'DMO-' . date('Ym') . '-0001';

        $zip->addFromString(
            '[Content_Types].xml',
            '<xml version="1.0" encoding="UTF-8">'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>'
        );

        $zip->addFromString(
            '_rels/.rels',
            '<xml version="1.0" encoding="UTF-8">'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>'
        );

        $zip->addFromString(
            'xl/workbook.xml',
            '<xml version="1.0" encoding="UTF-8">'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>'
            . '<sheet name="TakipNoYukleme" sheetId="1" r:id="rId1"/>'
            . '<sheet name="Kılavuz" sheetId="2" r:id="rId2"/>'
            . '</sheets>'
            . '</workbook>'
        );

        $zip->addFromString(
            'xl/_rels/workbook.xml.rels',
            '<xml version="1.0" encoding="UTF-8">'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>'
            . '</Relationships>'
        );

        $zip->addFromString(
            'xl/worksheets/sheet1.xml',
            $this->buildTemplateSheetXml([
                ['Sipariş No', 'Kargo Firması', 'Takip No', 'Not'],
                [$sampleOrderNo, 'Yurtiçi Kargo', 'TRK1234567890', 'Örnek'],
            ])
        );

        $zip->addFromString(
            'xl/worksheets/sheet2.xml',
            $this->buildTemplateSheetXml([
                ['Bu dosya toplu takip numarası yükleme içindir.'],
                ['1) Sipariş No, Kargo Firması ve Takip No alanları zorunludur.'],
                ['2) Kargo firması adını sistemdeki ad ile eşleşecek şekilde giriniz.'],
                ['3) Dosyayı düzenleyip toplu yükleme ekranından içe aktarınız.'],
            ])
        );

        $zip->close();

        $binary = file_get_contents($tmpFile);
        @unlink($tmpFile);

        if ($binary === false) {
            return redirect()->to(site_url('admin/shipping'))->with('error', 'Şablon dosyası okunamadı.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="takip_no_sablonu.xlsx"')
            ->setHeader('Content-Length', (string) strlen($binary))
            ->setBody($binary);
    }

    public function trackingUploadTemplate()
    {
        return $this->trackingTemplate();
    }

    public function manifestoDownload()
    {
        $date = trim((string) $this->request->getGet('date'));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $db = db_connect();
        if (! $db->tableExists('orders')) {
            return $this->response
                ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
                ->setHeader('Content-Disposition', 'attachment; filename="manifesto_' . $date . '.csv"')
                ->setBody("\xEF\xBB\xBFSipariş No,Müşteri,Kargo Firması,Takip No,Kargo Durumu,Kargoya Verildi Tarihi,Son Güncelleme\r\n");
        }

        $fields = $this->ordersFieldMap($db);
        if (! isset($fields['shipped_at'])) {
            return $this->response
                ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
                ->setHeader('Content-Disposition', 'attachment; filename="manifesto_' . $date . '.csv"')
                ->setBody("\xEF\xBB\xBFSipariş No,Müşteri,Kargo Firması,Takip No,Kargo Durumu,Kargoya Verildi Tarihi,Son Güncelleme\r\n");
        }

        $select = [];
        $select[] = isset($fields['order_no']) ? 'o.order_no AS order_no' : 'o.id AS order_no';
        $select[] = isset($fields['customer_name']) ? 'o.customer_name AS customer_name' : "'-' AS customer_name";
        $select[] = isset($fields['shipping_company']) ? 'o.shipping_company AS shipping_company' : "'-' AS shipping_company";
        if (isset($fields['tracking_number'])) {
            $select[] = 'o.tracking_number AS tracking_no';
        } elseif (isset($fields['tracking_no'])) {
            $select[] = 'o.tracking_no AS tracking_no';
        } else {
            $select[] = "'-' AS tracking_no";
        }
        if (isset($fields['shipping_status'])) {
            $select[] = "COALESCE(NULLIF(o.shipping_status, ''), 'not_shipped') AS shipping_status";
        } else {
            $select[] = "'not_shipped' AS shipping_status";
        }
        $select[] = 'o.shipped_at AS shipped_at';

        if (isset($fields['updated_at'])) {
            $select[] = 'o.updated_at AS updated_at';
        } elseif (isset($fields['created_at'])) {
            $select[] = 'o.created_at AS updated_at';
        } else {
            $select[] = 'NULL AS updated_at';
        }

        $builder = $db->table('orders o')
            ->select(implode(', ', $select), false)
            ->where('o.shipped_at IS NOT NULL', null, false)
            ->where('DATE(o.shipped_at)', $date);

        if (isset($fields['deleted_at'])) {
            $builder->where('o.deleted_at', null);
        }

        $rows = $builder
            ->orderBy('o.shipped_at', 'ASC')
            ->get()
            ->getResultArray();

        $lines = [];
        $lines[] = [
            'Sipariş No',
            'Müşteri',
            'Kargo Firması',
            'Takip No',
            'Kargo Durumu',
            'Kargoya Verildi Tarihi',
            'Son Güncelleme',
        ];

        foreach ($rows as $row) {
            $status = trim((string) ($row['shipping_status'] ?? 'not_shipped'));
            $lines[] = [
                trim((string) ($row['order_no'] ?? '-')),
                trim((string) ($row['customer_name'] ?? '-')),
                trim((string) ($row['shipping_company'] ?? '-')),
                trim((string) ($row['tracking_no'] ?? '-')),
                $this->shippingStatusText($status),
                trim((string) ($row['shipped_at'] ?? '')),
                trim((string) ($row['updated_at'] ?? '')),
            ];
        }

        $csv = "\xEF\xBB\xBF";
        foreach ($lines as $line) {
            $csv .= implode(',', array_map([$this, 'csvCell'], $line)) . "\r\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="manifesto_' . $date . '.csv"')
            ->setBody($csv);
    }

    public function bulkLabels()
    {
        $orderIds = $this->selectedOrderIds();
        if ($orderIds === []) {
            return $this->invalidBulkSelectionResponse();
        }

        $orders = $this->selectedOrders($orderIds);
        if ($orders === []) {
            return $this->invalidBulkSelectionResponse();
        }

        $user = session()->get('user') ?? [];
        $actorId = trim((string) ($user['id'] ?? ''));
        $packingService = new PackingService();

        $labels = [];
        foreach ($orders as $order) {
            $orderId = trim((string) ($order['id'] ?? ''));
            if ($orderId === '') {
                continue;
            }

            $session = $packingService->createOrGetSession($orderId, $actorId !== '' ? $actorId : null);
            $labels[] = [
                'order_id' => $orderId,
                'order_no' => $this->orderNo($order),
                'customer_name' => trim((string) ($order['customer_name'] ?? '-')),
                'shipping_company' => trim((string) ($order['shipping_company'] ?? '-')),
                'tracking_no' => trim((string) ($order['tracking_no'] ?? '-')),
                'package_code' => trim((string) ($session['package_code'] ?? '-')),
                'verify_url' => site_url('admin/orders/' . $orderId . '/packing/verify'),
            ];
        }

        $html = view('admin/shipping/bulk_labels', [
            'labels' => $labels,
            'generatedAt' => date('Y-m-d H:i:s'),
        ]);

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="toplu_kargo_etiketleri.html"')
            ->setBody($html);
    }

    public function bulkBarcodes()
    {
        $orderIds = $this->selectedOrderIds();
        if ($orderIds === []) {
            return $this->invalidBulkSelectionResponse();
        }

        $orders = $this->selectedOrders($orderIds);
        if ($orders === []) {
            return $this->invalidBulkSelectionResponse();
        }

        $user = session()->get('user') ?? [];
        $actorId = trim((string) ($user['id'] ?? ''));
        $packingService = new PackingService();
        $orderModel = new OrderModel();

        $rows = [];
        foreach ($orders as $order) {
            $orderId = trim((string) ($order['id'] ?? ''));
            if ($orderId === '') {
                continue;
            }

            $session = $packingService->createOrGetSession($orderId, $actorId !== '' ? $actorId : null);

            $productCodes = [];
            foreach ($orderModel->getOrderItems($orderId) as $item) {
                $pid = trim((string) ($item['product_id'] ?? ''));
                if ($pid !== '') {
                    $productCodes[] = $pid;
                }
            }
            if ($productCodes === []) {
                $fallbackProductId = trim((string) ($order['product_id'] ?? ''));
                if ($fallbackProductId !== '') {
                    $productCodes[] = $fallbackProductId;
                }
            }
            $productCodes = array_values(array_unique($productCodes));

            $rows[] = [
                'order_no' => $this->orderNo($order),
                'tracking_no' => trim((string) ($order['tracking_no'] ?? '-')),
                'package_code' => trim((string) ($session['package_code'] ?? '-')),
                'barcode_text' => $productCodes !== [] ? implode(', ', $productCodes) : '-',
            ];
        }

        $html = view('admin/shipping/bulk_barcodes', [
            'rows' => $rows,
            'generatedAt' => date('Y-m-d H:i:s'),
        ]);

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="toplu_barkod_ciktisi.html"')
            ->setBody($html);
    }

    public function bulkManifest()
    {
        $orderIds = $this->selectedOrderIds();
        if ($orderIds === []) {
            return $this->invalidBulkSelectionResponse();
        }

        $orders = $this->selectedOrders($orderIds);
        if ($orders === []) {
            return $this->invalidBulkSelectionResponse();
        }

        $lines = [];
        $lines[] = [
            'Sipariş No',
            'Müşteri',
            'Kargo Firması',
            'Takip No',
            'Kargo Durumu',
            'Kargoya Veriliş Tarihi',
        ];

        foreach ($orders as $order) {
            $status = trim((string) ($order['shipping_status'] ?? 'not_shipped'));
            $lines[] = [
                $this->orderNo($order),
                trim((string) ($order['customer_name'] ?? '-')),
                trim((string) ($order['shipping_company'] ?? '-')),
                trim((string) ($order['tracking_no'] ?? '-')),
                $this->shippingStatusText($status),
                trim((string) ($order['shipped_at'] ?? '')),
            ];
        }

        $csv = "\xEF\xBB\xBF";
        foreach ($lines as $line) {
            $csv .= implode(',', array_map([$this, 'csvCell'], $line)) . "\r\n";
        }

        $fileDate = date('Y-m-d');

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="manifesto_' . $fileDate . '.csv"')
            ->setBody($csv);
    }

    /**
     * @return array<int, string>
     */
    private function selectedOrderIds(): array
    {
        $raw = $this->request->getPost('order_ids');
        $items = [];

        if (is_array($raw)) {
            $items = $raw;
        } elseif (is_string($raw)) {
            $items = explode(',', $raw);
        }

        $ids = [];
        foreach ($items as $item) {
            $id = trim((string) $item);
            if ($id !== '') {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param array<int, string> $orderIds
     * @return array<int, array<string, mixed>>
     */
    private function selectedOrders(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        $db = db_connect();
        if (! $db->tableExists('orders')) {
            return [];
        }

        $fields = $this->ordersFieldMap($db);
        if (! isset($fields['id'])) {
            return [];
        }

        $select = ['o.id AS id'];
        $select[] = isset($fields['order_no']) ? 'o.order_no AS order_no' : "'' AS order_no";
        $select[] = isset($fields['customer_name']) ? 'o.customer_name AS customer_name' : "'-' AS customer_name";
        $select[] = isset($fields['shipping_company']) ? 'o.shipping_company AS shipping_company' : "'-' AS shipping_company";
        if (isset($fields['tracking_number'])) {
            $select[] = 'o.tracking_number AS tracking_no';
        } elseif (isset($fields['tracking_no'])) {
            $select[] = 'o.tracking_no AS tracking_no';
        } else {
            $select[] = "'-' AS tracking_no";
        }
        if (isset($fields['shipping_status'])) {
            $select[] = "COALESCE(NULLIF(o.shipping_status, ''), 'not_shipped') AS shipping_status";
        } else {
            $select[] = "'not_shipped' AS shipping_status";
        }
        $select[] = isset($fields['shipped_at']) ? 'o.shipped_at AS shipped_at' : "'' AS shipped_at";
        $select[] = isset($fields['product_id']) ? 'o.product_id AS product_id' : "'' AS product_id";

        $builder = $db->table('orders o')
            ->select(implode(', ', $select), false)
            ->whereIn('o.id', $orderIds);

        if (isset($fields['deleted_at'])) {
            $builder->where('o.deleted_at', null);
        }

        $rows = $builder->get()->getResultArray();
        if ($rows === []) {
            return [];
        }

        $mapped = [];
        foreach ($rows as $row) {
            $id = trim((string) ($row['id'] ?? ''));
            if ($id !== '') {
                $mapped[$id] = $row;
            }
        }

        $orderedRows = [];
        foreach ($orderIds as $orderId) {
            if (isset($mapped[$orderId])) {
                $orderedRows[] = $mapped[$orderId];
            }
        }

        return $orderedRows;
    }

    private function invalidBulkSelectionResponse()
    {
        $message = 'Lütfen en az bir gönderi seçin.';

        if ($this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => $message,
                ]);
        }

        return redirect()->to(site_url('admin/shipping'))->with('error', $message);
    }

    /**
     * @param array<string, mixed> $order
     */
    private function orderNo(array $order): string
    {
        $orderNo = trim((string) ($order['order_no'] ?? ''));
        if ($orderNo !== '') {
            return $orderNo;
        }

        $id = trim((string) ($order['id'] ?? ''));
        if ($id === '') {
            return '-';
        }

        return '#' . strtoupper(substr(str_replace('-', '', $id), 0, 8));
    }

    private function shippingStatusBadge(string $status): string
    {
        $labelMap = [
            'not_shipped' => 'Hazırlanıyor',
            'preparing' => 'Hazırlanıyor',
            'ready' => 'Hazırlanıyor',
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim',
            'returned' => 'İade',
            'return_in_progress' => 'İade',
            'cancelled' => 'Geciken',
            'delayed' => 'Geciken',
        ];

        $label = $labelMap[$status] ?? 'Hazırlanıyor';

        return match ($label) {
            'Kargoda' => '<span class="badge bg-light-primary text-primary">Kargoda</span>',
            'Teslim' => '<span class="badge bg-light-success text-success">Teslim</span>',
            'İade' => '<span class="badge bg-light-warning text-warning">İade</span>',
            'Geciken' => '<span class="badge bg-light-danger text-danger">Geciken</span>',
            default => '<span class="badge bg-light-secondary text-secondary">Hazırlanıyor</span>',
        };
    }

    private function statusGroup(string $status): string
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'not_shipped', 'preparing', 'ready' => 'preparing',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'returned', 'return_in_progress' => 'returned',
            'delayed', 'cancelled' => 'delayed',
            default => 'preparing',
        };
    }

    /**
     * @return array<string, bool>
     */
    private function ordersFieldMap(\CodeIgniter\Database\BaseConnection $db): array
    {
        $map = [];
        foreach ($db->getFieldNames('orders') as $fieldName) {
            $map[strtolower((string) $fieldName)] = true;
        }

        return $map;
    }

    private function shippingStatusText(string $status): string
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim',
            'returned', 'return_in_progress' => 'İade',
            'delayed', 'cancelled' => 'Geciken',
            default => 'Hazırlanıyor',
        };
    }

    private function csvCell(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function buildTemplateSheetXml(array $rows): string
    {
        $xml = '<xml version="1.0" encoding="UTF-8">'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>';

        $rowIndex = 1;
        foreach ($rows as $row) {
            $xml .= '<row r="' . $rowIndex . '">';
            $colIndex = 1;
            foreach ($row as $cellValue) {
                $cellRef = $this->excelColumnName($colIndex) . $rowIndex;
                $xml .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>'
                    . $this->escapeXml($cellValue)
                    . '</t></is></c>';
                $colIndex++;
            }
            $xml .= '</row>';
            $rowIndex++;
        }

        $xml .= '</sheetData></worksheet>';

        return $xml;
    }

    private function excelColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * @return array<int, array{name:string, integration_type:string}>
     */
    private function shippingCompanies(): array
    {
        $fallback = [
            ['name' => 'Yurtiçi Kargo', 'integration_type' => 'Yok'],
            ['name' => 'Aras Kargo', 'integration_type' => 'Yok'],
            ['name' => 'MNG Kargo', 'integration_type' => 'Yok'],
            ['name' => 'Sürat Kargo', 'integration_type' => 'Yok'],
            ['name' => 'PTT Kargo', 'integration_type' => 'Yok'],
            ['name' => 'UPS', 'integration_type' => 'Yok'],
        ];

        $db = db_connect();
        if (! $db->tableExists('shipping_companies')) {
            return $fallback;
        }

        $fields = array_map(
            static fn ($field): string => strtolower((string) $field),
            $db->getFieldNames('shipping_companies')
        );

        $nameField = null;
        foreach (['name', 'company_name', 'title'] as $candidate) {
            if (in_array($candidate, $fields, true)) {
                $nameField = $candidate;
                break;
            }
        }
        if ($nameField === null) {
            return $fallback;
        }

        $integrationField = null;
        foreach (['integration_type', 'api_type', 'integration'] as $candidate) {
            if (in_array($candidate, $fields, true)) {
                $integrationField = $candidate;
                break;
            }
        }

        $builder = $db->table('shipping_companies')
            ->select($nameField . ' AS name' . ($integrationField !== null ? ', ' . $integrationField . ' AS integration_type' : ''));

        if (in_array('deleted_at', $fields, true)) {
            $builder->where('deleted_at', null);
        }

        if (in_array('is_active', $fields, true)) {
            $builder->where('is_active', 1);
        } elseif (in_array('active', $fields, true)) {
            $builder->where('active', 1);
        } elseif (in_array('status', $fields, true)) {
            $builder->where('status', 'active');
        }

        $rows = $builder->get()->getResultArray();
        if ($rows === []) {
            return $fallback;
        }

        $items = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $items[] = [
                'name' => $name,
                'integration_type' => trim((string) ($row['integration_type'] ?? 'Yok')),
            ];
        }

        return $items !== [] ? $items : $fallback;
    }
}
