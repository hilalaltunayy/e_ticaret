<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Complaints extends BaseController
{
    public function index()
    {
        return view('admin/complaints/index', [
            'title' => 'Sikayet Yonetimi',
            'summary' => [
                'total' => 0,
                'open' => 0,
                'resolved' => 0,
            ],
            'items' => [],
        ]);
    }
}
