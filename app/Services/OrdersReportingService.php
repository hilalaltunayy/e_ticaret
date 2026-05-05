<?php

namespace App\Services;

use App\Models\InvoiceModel;
use App\Models\OrderModel;

class OrdersReportingService
{
    public function __construct(
        private ?OrderModel $orderModel = null,
        private ?InvoiceModel $invoiceModel = null
    ) {
        $this->orderModel = $this->orderModel ?? new OrderModel();
        $this->invoiceModel = $this->invoiceModel ?? new InvoiceModel();
    }

    public function getSummaryCounts(): array
    {
        return [
            'total' => $this->orderModel->countAllOrders(),
            'pending' => $this->countByOrderStatuses(['pending', 'preparing']),
            'shipped' => $this->countByOrderStatuses(['shipped', 'delivered']),
            'returned' => $this->countByOrderStatuses(['return_in_progress', 'return_done']),
            'cancelled' => $this->countByOrderStatuses(['cancelled']),
        ];
    }

    public function buildAnalyticsPayload(?string $rangeRaw): array
    {
        $range = strtolower(trim((string) ($rangeRaw ?? 'daily')));
        if (! in_array($range, ['daily', 'weekly', 'monthly'], true)) {
            $range = 'daily';
        }

        $db = db_connect();
        $today = new \DateTimeImmutable('today');
        $categories = [];
        $rowsByLabel = [];
        $labelExpr = "DATE_FORMAT(DATE(COALESCE(order_date, created_at)), '%Y-%m-%d')";
        $fromDate = $today->modify('-29 days')->format('Y-m-d');

        if ($range === 'weekly') {
            $labelExpr = "DATE_FORMAT(DATE_SUB(DATE(COALESCE(order_date, created_at)), INTERVAL WEEKDAY(DATE(COALESCE(order_date, created_at))) DAY), '%Y-%m-%d')";
            $weekStart = $today->modify('-11 weeks')->modify('monday this week');
            $fromDate = $weekStart->format('Y-m-d');

            for ($i = 0; $i < 12; $i++) {
                $categories[] = $weekStart->modify('+' . $i . ' week')->format('Y-m-d');
            }
        } elseif ($range === 'monthly') {
            $labelExpr = "DATE_FORMAT(DATE(COALESCE(order_date, created_at)), '%Y-%m')";
            $monthStart = $today->modify('first day of this month')->modify('-11 months');
            $fromDate = $monthStart->format('Y-m-d');

            for ($i = 0; $i < 12; $i++) {
                $categories[] = $monthStart->modify('+' . $i . ' month')->format('Y-m');
            }
        } else {
            for ($i = 0; $i < 30; $i++) {
                $categories[] = $today->modify('-' . (29 - $i) . ' days')->format('Y-m-d');
            }
        }

        $rows = $db->table('orders')
            ->select($labelExpr . ' AS period_label', false)
            ->select('COALESCE(SUM(total_amount), 0) AS total_amount', false)
            ->where("DATE(COALESCE(order_date, created_at)) >= " . $db->escape($fromDate), null, false)
            ->groupBy('period_label')
            ->orderBy('period_label', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $key = trim((string) ($row['period_label'] ?? ''));
            if ($key === '') {
                continue;
            }

            $rowsByLabel[$key] = round((float) ($row['total_amount'] ?? 0), 2);
        }

        $seriesData = [];
        foreach ($categories as $label) {
            $seriesData[] = $rowsByLabel[$label] ?? 0;
        }

        return [
            'success' => true,
            'range' => $range,
            'categories' => $categories,
            'series' => [[
                'name' => 'Toplam Tutar',
                'data' => $seriesData,
            ]],
            'currency' => '&#8378;',
            'csrf' => [
                'token' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ];
    }

    public function buildStatusDistributionPayload(?string $rangeRaw): array
    {
        $range = strtolower(trim((string) ($rangeRaw ?? 'weekly')));
        if ($range !== 'weekly') {
            $range = 'weekly';
        }

        $statuses = [
            'pending' => 'Beklemede',
            'preparing' => "Haz\u{0131}rlan\u{0131}yor",
            'packed' => 'Paketlendi',
            'shipped' => 'Kargoya Verildi',
            'delivered' => 'Teslim Edildi',
            'cancelled' => "\u{0130}ptal Edildi",
            'return_in_progress' => "\u{0130}ade S\u{00FC}recinde",
            'return_done' => "\u{0130}ade Tamamland\u{0131}",
        ];

        $dateFrom = (new \DateTimeImmutable('today'))->modify('-6 days')->format('Y-m-d');
        $db = db_connect();
        $rows = $db->table('orders')
            ->select("COALESCE(NULLIF(order_status, ''), status) AS normalized_status", false)
            ->select('COUNT(*) AS total_count', false)
            ->where("DATE(COALESCE(order_date, created_at)) >= " . $db->escape($dateFrom), null, false)
            ->groupBy('normalized_status')
            ->get()
            ->getResultArray();

        $countsByStatus = [];
        foreach ($rows as $row) {
            $key = trim((string) ($row['normalized_status'] ?? ''));
            if ($key === '') {
                continue;
            }

            $countsByStatus[$key] = (int) ($row['total_count'] ?? 0);
        }

        $series = [];
        foreach ($statuses as $statusKey => $label) {
            $series[] = [
                'name' => $label,
                'data' => [$countsByStatus[$statusKey] ?? 0],
            ];
        }

        return [
            'success' => true,
            'range' => $range,
            'categories' => ["Son 7 G\u{00FC}n"],
            'series' => $series,
            'csrf' => [
                'token' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ];
    }

    public function getShowData(string $identifier, InvoiceService $invoiceService): ?array
    {
        $order = $this->orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return null;
        }

        $items = $this->orderModel->getOrderItems((string) $order['id']);
        if ($items === []) {
            $qty = max(1, (int) ($order['quantity'] ?? 1));
            $total = (float) ($order['total_amount'] ?? 0);
            $unitPrice = $qty > 0 ? ($total / $qty) : $total;
            $items = [[
                'product_name_snapshot' => (string) ($order['product_name'] ?? '-'),
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'line_total' => $total,
            ]];
        }

        $logs = $this->orderModel->getOrderLogs((string) $order['id']);
        $invoice = $this->invoiceModel->findByOrderId((string) $order['id']);
        $invoiceEligibility = $invoiceService->evaluateInvoiceEligibility($order);

        return [
            'order' => $order,
            'items' => $items,
            'logs' => $logs,
            'invoice' => $invoice,
            'canCreateInvoice' => (bool) ($invoiceEligibility['allowed'] ?? false),
            'invoiceBlockMessage' => (string) ($invoiceEligibility['message'] ?? ''),
        ];
    }

    public function buildPaymentReportsPageData(): array
    {
        $summary = $this->getSummaryCounts();

        return [
            'summary' => $summary,
            'amountColumn' => $this->amountColumn(),
            'totalAmount' => $this->sumOrderAmount(),
            'paidAmount' => $this->sumOrderAmount(null, ['paid', 'completed']),
            'returnedAmount' => $this->sumOrderAmount(['returned', 'return_in_progress', 'return_done']),
            'cancelledAmount' => $this->sumOrderAmount(['cancelled']),
            'paymentProviderActive' => false,
            'recentOrders' => $this->latestOrders(null, 8),
        ];
    }

    public function buildReturnsPageData(): array
    {
        $returnStatuses = ['returned', 'return_in_progress', 'return_done'];

        return [
            'summary' => [
                'total' => $this->countOrdersByStatuses($returnStatuses),
                'inProgress' => $this->countOrdersByStatuses(['return_in_progress']),
                'completed' => $this->countOrdersByStatuses(['returned', 'return_done']),
                'amount' => $this->sumOrderAmount($returnStatuses),
            ],
            'orders' => $this->latestOrders($returnStatuses, 25),
        ];
    }

    private function countByOrderStatuses(array $statuses): int
    {
        return (int) $this->orderModel
            ->builder()
            ->groupStart()
            ->whereIn('order_status', $statuses)
            ->orWhereIn('status', $statuses)
            ->groupEnd()
            ->countAllResults();
    }

    private function countOrdersByStatuses(array $statuses): int
    {
        $builder = $this->baseOrdersBuilder();
        $this->applyStatusFilter($builder, $statuses);

        return (int) $builder->countAllResults();
    }

    private function sumOrderAmount(?array $statuses = null, ?array $paymentStatuses = null): float
    {
        $amountColumn = $this->amountColumn();
        if ($amountColumn === null) {
            return 0.0;
        }

        $builder = $this->baseOrdersBuilder()
            ->select('COALESCE(SUM(' . $amountColumn . '), 0) AS total_amount', false);

        if ($statuses !== null) {
            $this->applyStatusFilter($builder, $statuses);
        }

        if ($paymentStatuses !== null && $this->orderHasField('payment_status')) {
            $builder->whereIn('payment_status', $paymentStatuses);
        }

        $row = $builder->get()->getRowArray() ?? [];

        return round((float) ($row['total_amount'] ?? 0), 2);
    }

    private function latestOrders(?array $statuses, int $limit): array
    {
        $fields = $this->orderFields();
        $select = [];

        foreach ([
            'id',
            'order_no',
            'customer_name',
            'total_amount',
            'total_price',
            'payment_method',
            'payment_status',
            'status',
            'order_status',
            'shipping_status',
            'created_at',
            'order_date',
            'returned_at',
            'return_started_at',
            'return_completed_at',
        ] as $field) {
            if (in_array($field, $fields, true)) {
                $select[] = $field;
            }
        }

        $builder = $this->baseOrdersBuilder()
            ->select($select !== [] ? implode(', ', $select) : '*');

        if ($statuses !== null) {
            $this->applyStatusFilter($builder, $statuses);
        }

        $dateField = in_array('created_at', $fields, true) ? 'created_at' : 'id';

        return $builder
            ->orderBy($dateField, 'DESC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();
    }

    private function applyStatusFilter($builder, array $statuses): void
    {
        $statusFields = array_values(array_filter(
            ['order_status', 'status', 'shipping_status'],
            fn (string $field): bool => $this->orderHasField($field)
        ));

        if ($statusFields === []) {
            return;
        }

        $builder->groupStart();
        foreach ($statusFields as $index => $field) {
            if ($index === 0) {
                $builder->whereIn($field, $statuses);
                continue;
            }

            $builder->orWhereIn($field, $statuses);
        }
        $builder->groupEnd();
    }

    private function baseOrdersBuilder()
    {
        $builder = db_connect()->table('orders');
        if ($this->orderHasField('deleted_at')) {
            $builder->where('deleted_at', null);
        }

        return $builder;
    }

    private function amountColumn(): ?string
    {
        if ($this->orderHasField('total_amount')) {
            return 'total_amount';
        }

        if ($this->orderHasField('total_price')) {
            return 'total_price';
        }

        return null;
    }

    private function orderHasField(string $field): bool
    {
        return in_array($field, $this->orderFields(), true);
    }

    private function orderFields(): array
    {
        static $fields = null;
        if ($fields !== null) {
            return $fields;
        }

        $db = db_connect();
        if (! $db->tableExists('orders')) {
            $fields = [];
            return $fields;
        }

        $fields = $db->getFieldNames('orders');

        return $fields;
    }
}
