<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Automation extends BaseController
{
    public function index()
    {
        return view('admin/automation/index', [
            'title' => 'Otomasyon & Akıllı Kurallar',
        ]);
    }
}
