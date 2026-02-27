<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Notifications extends BaseController
{
    public function index()
    {
        return view('admin/notifications/index', [
            'title' => 'Bildirim Yönetimi',
        ]);
    }
}
