<?php

namespace App\Services\Admin;

helper(['dashboard_date', 'dashboard_series']);

use App\DTO\Admin\{
    AdminNoteDTO,
    AuditLogItemDTO,
    DashboardDTO,
    MetricCardDTO,
    OrderListItemDTO,
    PieSliceDTO,
    RevenueRowDTO,
    RevenueTableDTO
};
use App\Models\AdminNoteModel;
use App\Models\AdminSettingModel;
use App\Models\AuditLogModel;
use App\Models\OrderModel;
use App\Models\UserModel;
use App\Models\VisitModel;

class DashboardService
{
    public function __construct(
        private ?OrderModel $orderModel = null,
        private ?AdminSettingModel $adminSettingModel = null,
        private ?VisitModel $visitModel = null,
        private ?UserModel $userModel = null,
        private ?AuditLogModel $auditLogModel = null,
        private ?AdminNoteModel $adminNoteModel = null
    ) {
        $this->orderModel = $this->orderModel ?? new OrderModel();
        $this->adminSettingModel = $this->adminSettingModel ?? new AdminSettingModel();
        $this->visitModel = $this->visitModel ?? new VisitModel();
        $this->userModel = $this->userModel ?? new UserModel();
        $this->auditLogModel = $this->auditLogModel ?? new AuditLogModel();
        $this->adminNoteModel = $this->adminNoteModel ?? new AdminNoteModel();
    }

    public function getDashboard(): DashboardDTO
    {
        $totalOrders   = $this->countOrders();
        $todayOrders   = $this->countOrdersBetween(dash_today_start(), dash_today_end());
        $weekOrders    = $this->countOrdersBetween(dash_week_start(), dash_today_end());
        $pendingOrders = $this->countOrdersByStatus('pending');
        $orderCards = [
            new MetricCardDTO('Toplam Siparis', $totalOrders),
            new MetricCardDTO('Bugun Siparis', $todayOrders),
            new MetricCardDTO('Bu Hafta Siparis', $weekOrders),
            new MetricCardDTO('Bekleyen Siparis', $pendingOrders),
        ];

        $latestOrders = $this->getLatestOrders(5);
        $ordersLineSeries = $this->getOrdersDailySeries(14);
        $ordersBarSeries  = $ordersLineSeries;
        $revenueTable = $this->getRevenueTable();

        $visitCard = $this->getVisitCard();
        $visitsCompareSeries = $this->getVisitsCompareSeries(14);

        $topCategoryPie = $this->getTopCategoryPie(6);
        $topAuthors = $this->getTopAuthors(10);
        $topDigitalBooks = $this->getTopDigitalBooks(10);

        $newUserCards = $this->getNewUserCards();
        $latestLogs = $this->getLatestAuditLogs(15);
        $notes = $this->getAdminNotes(20);

        return new DashboardDTO([
            'orderCards' => $orderCards,
            'ordersLineSeries' => $ordersLineSeries,
            'ordersBarSeries' => $ordersBarSeries,
            'latestOrders' => $latestOrders,
            'revenueTable' => $revenueTable,
            'visitCard' => $visitCard,
            'visitsCompareSeries' => $visitsCompareSeries,
            'topCategoryPie' => $topCategoryPie,
            'topAuthors' => $topAuthors,
            'topDigitalBooks' => $topDigitalBooks,
            'newUserCards' => $newUserCards,
            'latestLogs' => $latestLogs,
            'notes' => $notes,
        ]);
    }

    private function countOrders(): int
    {
        return $this->orderModel->countAllOrders();
    }

    private function countOrdersBetween(string $start, string $end): int
    {
        return $this->orderModel->countOrdersBetween($start, $end);
    }

    private function countOrdersByStatus(string $status): int
    {
        return $this->orderModel->countOrdersByStatus($status);
    }

    private function getLatestOrders(int $limit = 5): array
    {
        $rows = $this->orderModel->getLatestWithProductName($limit);

        return array_map(fn($r) => new OrderListItemDTO(
            id: (int) $r['id'],
            customerName: (string) ($r['customer_name'] ?? ('Order #' . $r['id'])),
            totalAmount: (float) ($r['total_amount'] ?? 0),
            status: (string) (($r['status'] ?? '-') . (isset($r['product_name']) ? ' • ' . $r['product_name'] : '')),
            createdAt: (string) ($r['order_date'] ?? '-'),
        ), $rows);
    }

    private function getOrdersDailySeries(int $days = 14): array
    {
        $start = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
        $end   = dash_today_end();

        $rows = $this->orderModel->getDailyCounts($start, $end);

        $map = [];
        foreach ($rows as $r) {
            $map[$r['d']] = (int) $r['c'];
        }

        return dash_fill_daily_series($map, $days);
    }

    private function getRevenueTable(): RevenueTableDTO
    {
        $daily  = $this->sumRevenueBetween(dash_today_start(), dash_today_end());
        $weekly = $this->sumRevenueBetween(dash_week_start(), dash_today_end());
        $monthly = $this->sumRevenueBetween(dash_month_start(), dash_today_end());

        $rows = [
            new RevenueRowDTO('Bugun', $daily),
            new RevenueRowDTO('Bu Hafta', $weekly),
            new RevenueRowDTO('Bu Ay', $monthly),
        ];

        $style = $this->getRevenueTableStyleFromSettings();

        return new RevenueTableDTO($rows, $style);
    }

    private function sumRevenueBetween(string $start, string $end): float
    {
        $row = $this->orderModel->getRevenueSumRow($start, $end);

        return (float) ($row['total'] ?? 0);
    }

    private function getRevenueTableStyleFromSettings(): array
    {
        $defaults = [
            'headerBg' => '#111827',
            'headerText' => '#ffffff',
            'rowOddBg' => '#f3f4f6',
            'rowEvenBg' => '#ffffff',
        ];

        $rows = $this->adminSettingModel->getByKeyPrefix('revenue_table_');

        if (!$rows) {
            return $defaults;
        }

        $style = $defaults;
        foreach ($rows as $r) {
            $k = (string) $r['setting_key'];
            $v = (string) $r['setting_value'];

            if ($k === 'revenue_table_header_bg') {
                $style['headerBg'] = $v;
            }
            if ($k === 'revenue_table_header_text') {
                $style['headerText'] = $v;
            }
            if ($k === 'revenue_table_row_odd_bg') {
                $style['rowOddBg'] = $v;
            }
            if ($k === 'revenue_table_row_even_bg') {
                $style['rowEvenBg'] = $v;
            }
        }

        return $style;
    }

    private function getVisitCard(): MetricCardDTO
    {
        $today = $this->countVisitsBetween(dash_today_start(), dash_today_end());
        [$yStart, $yEnd] = dash_yesterday_range();
        $yesterday = $this->countVisitsBetween($yStart, $yEnd);

        $deltaPct = null;
        $trend = 'flat';
        if ($yesterday > 0) {
            $deltaPct = (($today - $yesterday) / $yesterday) * 100;
            $trend = $deltaPct > 0 ? 'up' : ($deltaPct < 0 ? 'down' : 'flat');
        }

        return new MetricCardDTO(
            title: 'Site Ziyaret (Bugun)',
            value: $today,
            deltaPct: $deltaPct,
            trend: $trend,
            subtitle: 'Dune gore'
        );
    }

    private function countVisitsBetween(string $start, string $end): int
    {
        return $this->visitModel->countBetween($start, $end);
    }

    private function getVisitsCompareSeries(int $days = 14): array
    {
        $start = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
        $end   = dash_today_end();

        $rows = $this->visitModel->getDailyCounts($start, $end);

        $map = [];
        foreach ($rows as $r) {
            $map[$r['d']] = (int) $r['c'];
        }

        return dash_fill_daily_series($map, $days);
    }

    private function getTopCategoryPie(int $limit = 6): array
    {
        $rows = $this->orderModel->getTopCategoriesByQuantity($limit);

        $total = 0;
        foreach ($rows as $r) {
            $total += (int) ($r['qty'] ?? 0);
        }
        if ($total <= 0) {
            return [];
        }

        $out = [];
        foreach ($rows as $r) {
            $qty = (int) ($r['qty'] ?? 0);
            $out[] = new PieSliceDTO(
                label: (string) ($r['category_name'] ?? 'Unknown'),
                percent: ($qty / $total) * 100,
                value: $qty
            );
        }

        return $out;
    }

    private function getTopAuthors(int $limit = 10): array
    {
        $rows = $this->orderModel->getTopAuthorsByQuantity($limit);

        return array_map(fn($r) => [
            'label' => (string) ($r['author_name'] ?? 'Unknown'),
            'value' => (int) ($r['qty'] ?? 0),
        ], $rows);
    }

    private function getTopDigitalBooks(int $limit = 10): array
    {
        $rows = $this->orderModel->getTopDigitalBooksByQuantity($limit);

        return array_map(fn($r) => [
            'label' => (string) ($r['title'] ?? 'Unknown'),
            'value' => (int) ($r['qty'] ?? 0),
        ], $rows);
    }

    private function getNewUserCards(): array
    {
        $daily  = $this->countUsersBetween(dash_today_start(), dash_today_end());
        $weekly = $this->countUsersBetween(dash_week_start(), dash_today_end());
        $monthly = $this->countUsersBetween(dash_month_start(), dash_today_end());

        return [
            new MetricCardDTO('Yeni Kullanici (Bugun)', $daily),
            new MetricCardDTO('Yeni Kullanici (Bu Hafta)', $weekly),
            new MetricCardDTO('Yeni Kullanici (Bu Ay)', $monthly),
        ];
    }

    private function countUsersBetween(string $start, string $end): int
    {
        return $this->userModel->countCreatedBetween($start, $end);
    }

    private function getLatestAuditLogs(int $limit = 15): array
    {
        $rows = $this->auditLogModel->getLatestWithActor($limit);

        return array_map(fn($r) => new AuditLogItemDTO(
            id: (int) $r['id'],
            actorName: (string) ($r['actor_name'] ?? 'System'),
            actorRole: (string) ($r['actor_role'] ?? '-'),
            action: (string) ($r['action'] ?? '-'),
            entityType: (string) ($r['entity_type'] ?? '-'),
            entityId: isset($r['entity_id']) ? (string) $r['entity_id'] : null,
            createdAt: (string) ($r['created_at'] ?? '-'),
            meta: isset($r['meta_json']) ? (string) $r['meta_json'] : null,
        ), $rows);
    }

    private function getAdminNotes(int $limit = 20): array
    {
        $rows = $this->adminNoteModel->getLatest($limit);

        return array_map(fn($r) => new AdminNoteDTO(
            id: (int) $r['id'],
            note: (string) ($r['note'] ?? ''),
            createdAt: (string) ($r['created_at'] ?? '-'),
            updatedAt: $r['updated_at'] ?? null
        ), $rows);
    }
}
