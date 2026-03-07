<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\MarketingService;

class Marketing extends BaseController
{
    public function __construct(private ?MarketingService $service = null)
    {
        $this->service = $this->service ?? new MarketingService();
    }

    public function index()
    {
        return view('admin/marketing/index', [
            'title' => 'Kampanya / Kupon / Fiyat',
            'summary' => $this->service->getLandingSummary(),
        ]);
    }
}

