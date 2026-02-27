<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Pricing extends BaseController
{
    public function index()
    {
        return view('admin/pricing/index', [
            'title' => 'Kampanya / Fiyat Paneli',
        ]);
    }
}

