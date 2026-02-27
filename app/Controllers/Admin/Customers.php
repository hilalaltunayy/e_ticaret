<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Customers extends BaseController
{
    public function index()
    {
        return view('admin/customers/index', [
            'title' => 'Müşteri Operasyonu',
        ]);
    }
}

