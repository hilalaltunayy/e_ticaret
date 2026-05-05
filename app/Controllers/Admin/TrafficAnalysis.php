<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TrafficAnalysis extends BaseController
{
    public function index()
    {
        return view('admin/traffic_analysis/index', [
            'title' => 'Trafik Analizi',
            'summary' => [
                'totalVisits' => 0,
                'todayVisits' => 0,
                'topPages' => 0,
                'sources' => 0,
            ],
            'topPages' => [],
            'sources' => [],
        ]);
    }
}
