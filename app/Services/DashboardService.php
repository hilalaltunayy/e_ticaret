<?php

namespace App\Services;

use App\Models\DashboardModel;

class DashboardService
{
    public function __construct(private ?DashboardModel $dashboardModel = null)
    {
        $this->dashboardModel = $this->dashboardModel ?? new DashboardModel();
    }

    public function getActiveDashboard(?string $userId = null): ?array
    {
        if (! $this->builderTablesReady()) {
            return null;
        }

        return $this->dashboardModel->findActiveForUser($userId);
    }

    private function builderTablesReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('dashboards');
    }
}
