<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\DashboardBlockService;
use App\Services\DashboardBuilderService;

class DashboardBuilder extends BaseController
{
    public function __construct(
        private ?DashboardBuilderService $dashboardBuilderService = null,
        private ?DashboardBlockService $dashboardBlockService = null
    ) {
        $this->dashboardBuilderService = $this->dashboardBuilderService ?? new DashboardBuilderService();
        $this->dashboardBlockService = $this->dashboardBlockService ?? new DashboardBlockService();
    }

    public function index()
    {
        $user = session()->get('user') ?? [];
        $userId = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));
        $dashboard = $this->dashboardBuilderService->getOrCreateAdminDashboard($userId);

        return view('admin/dashboard_builder/index', [
            'builderDashboard' => $dashboard,
            'builderBlocks' => $this->dashboardBuilderService->getBuilderBlocks($userId),
            'builderBlockTypes' => $this->dashboardBlockService->getAvailableBlockTypes(),
        ]);
    }
}
