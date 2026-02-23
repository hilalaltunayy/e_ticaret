<?php

namespace App\DTO\Admin;

class DashboardDTO
{
    /** @var MetricCardDTO[] */
    public array $orderCards;
    /** @var ChartPointDTO[] */
    public array $ordersLineSeries;
    /** @var ChartPointDTO[] */
    public array $ordersBarSeries;
    /** @var OrderListItemDTO[] */
    public array $latestOrders;
    public RevenueTableDTO $revenueTable;
    public MetricCardDTO $visitCard;
    /** @var ChartPointDTO[] */
    public array $visitsCompareSeries;
    /** @var PieSliceDTO[] */
    public array $topCategoryPie;
    /** @var array<int, array{label:string, value:int}> */
    public array $topAuthors;
    /** @var array<int, array{label:string, value:int}> */
    public array $topDigitalBooks;
    /** @var MetricCardDTO[] */
    public array $newUserCards;
    /** @var AuditLogItemDTO[] */
    public array $latestLogs;
    /** @var AdminNoteDTO[] */
    public array $notes;

    /**
     * @param MetricCardDTO[] $orderCards
     * @param ChartPointDTO[] $ordersLineSeries
     * @param ChartPointDTO[] $ordersBarSeries
     * @param OrderListItemDTO[] $latestOrders
     * @param RevenueTableDTO $revenueTable
     * @param MetricCardDTO $visitCard
     * @param ChartPointDTO[] $visitsCompareSeries   // ör: bu hafta vs geçen hafta gibi
     * @param PieSliceDTO[] $topCategoryPie
     * @param array $topAuthors  // [{label, value}] gibi sade bırakıyorum
     * @param array $topDigitalBooks // [{label, value}]
     * @param MetricCardDTO[] $newUserCards
     * @param AuditLogItemDTO[] $latestLogs
     * @param AdminNoteDTO[] $notes
     */
    public function __construct(array $data = [])
    {
        $this->orderCards = $data['orderCards'] ?? [];
        $this->ordersLineSeries = $data['ordersLineSeries'] ?? [];
        $this->ordersBarSeries = $data['ordersBarSeries'] ?? [];
        $this->latestOrders = $data['latestOrders'] ?? [];
        $this->revenueTable = $data['revenueTable'] ?? new RevenueTableDTO([]);
        $this->visitCard = $data['visitCard'] ?? new MetricCardDTO('', 0);
        $this->visitsCompareSeries = $data['visitsCompareSeries'] ?? [];
        $this->topCategoryPie = $data['topCategoryPie'] ?? [];
        $this->topAuthors = $data['topAuthors'] ?? [];
        $this->topDigitalBooks = $data['topDigitalBooks'] ?? [];
        $this->newUserCards = $data['newUserCards'] ?? [];
        $this->latestLogs = $data['latestLogs'] ?? [];
        $this->notes = $data['notes'] ?? [];
    }
}
