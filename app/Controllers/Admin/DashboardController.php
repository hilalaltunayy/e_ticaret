<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\DashboardBlockService;
use App\Services\DashboardDataSourceService;
use App\Services\DashboardService;
use App\Services\Admin\DashboardService as LegacyDashboardService;

class DashboardController extends BaseController
{
    public function __construct(
        private ?DashboardService $dashboardService = null,
        private ?DashboardBlockService $dashboardBlockService = null,
        private ?DashboardDataSourceService $dashboardDataSourceService = null,
        private ?LegacyDashboardService $legacyDashboardService = null
    ) {
        $this->dashboardService = $this->dashboardService ?? new DashboardService();
        $this->dashboardBlockService = $this->dashboardBlockService ?? new DashboardBlockService();
        $this->dashboardDataSourceService = $this->dashboardDataSourceService ?? new DashboardDataSourceService();
        $this->legacyDashboardService = $this->legacyDashboardService ?? new LegacyDashboardService();
    }

    public function index()
    {
        $activeDashboard = $this->dashboardService->getVisibleDashboard($this->actorId(), $this->actorRole());
        $builderBlocks = $this->dashboardBlockService->getBlocksForDashboard((string) ($activeDashboard['id'] ?? ''));

        return view('admin/dashboard/index', [
            'dto' => $this->legacyDashboardService->getDashboard(),
            'builderDashboard' => $activeDashboard,
            'builderBlocks' => $this->dashboardDataSourceService->hydrateBlocks($builderBlocks),
            'builderBlockTypes' => $this->dashboardBlockService->getAvailableBlockTypes(),
            'canManageDashboardBuilder' => $this->actorRole() === 'admin',
        ]);
    }

    private function actorId(): ?string
    {
        $user = session()->get('user') ?? [];
        $id = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));

        return $id === '' ? null : $id;
    }

    private function actorRole(): string
    {
        $user = session()->get('user') ?? [];

        return strtolower(trim((string) ($user['role'] ?? session('role') ?? '')));
    }
}
