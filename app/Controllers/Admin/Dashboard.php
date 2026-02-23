<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\DashboardService;

class Dashboard extends BaseController
{
    public function index()
    {
        $service = new DashboardService();
        $dto = $service->getDashboard();

        return view('admin/dashboard/index', [
            'dto' => $dto,
        ]);
    }
}