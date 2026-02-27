<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class OrderStatuses extends BaseController
{
    public function index()
    {
        $user = session()->get('user') ?? [];

        return view('admin/orders/statuses', [
            'title' => 'Sipariş Durumları',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
        ]);
    }
}
