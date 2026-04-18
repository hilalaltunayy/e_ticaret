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

    public function getVisibleDashboard(?string $userId = null, ?string $role = null): ?array
    {
        if (! $this->builderTablesReady()) {
            return null;
        }

        $role = strtolower(trim((string) $role));

        if ($role === 'admin') {
            return $this->getActiveDashboard($userId);
        }

        return $this->findSharedActiveDashboard();
    }

    private function builderTablesReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('dashboards');
    }

    private function findSharedActiveDashboard(): ?array
    {
        $globalDashboard = $this->dashboardModel->findActiveForUser(null);
        if (is_array($globalDashboard)) {
            return $globalDashboard;
        }

        $db = db_connect();
        $builder = $db->table('dashboards d')
            ->select('d.*')
            ->join('users u', 'u.id = d.user_id', 'inner')
            ->where('d.is_active', 1)
            ->where('u.role', 'admin');

        if ($db->fieldExists('deleted_at', 'dashboards')) {
            $builder->where('d.deleted_at', null);
        }

        if ($db->fieldExists('deleted_at', 'users')) {
            $builder->where('u.deleted_at', null);
        }

        $row = $builder
            ->orderBy('d.updated_at', 'DESC')
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }
}
