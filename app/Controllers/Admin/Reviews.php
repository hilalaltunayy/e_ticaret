<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Reviews extends BaseController
{
    public function index()
    {
        return view('admin/reviews/index', [
            'title' => 'Urun Yorumlari',
            'summary' => [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
            ],
            'items' => [],
        ]);
    }
}
