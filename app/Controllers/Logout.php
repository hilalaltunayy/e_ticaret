<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Logout extends BaseController
{
    public function index()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))
                         ->with('success', 'Başarıyla çıkış yapıldı');
    }
}