<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CustomerMessages extends BaseController
{
    public function index()
    {
        return view('admin/customer_messages/index', [
            'title' => 'Musteri Not ve Mesajlari',
            'summary' => [
                'total' => 0,
                'open' => 0,
                'waiting' => 0,
                'closed' => 0,
            ],
            'items' => [],
        ]);
    }
}
