<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $session = session();
        $sessionUser = $session->get('user');
        $sessionUserData = $session->get('userData');

        $db = \Config\Database::connect();
        
        // 1. Sadece BasÄ±lÄ± ÃœrÃ¼nlerin Ä°statistikleri
        $data['total_basili'] = $db->table('products')
                                ->where('type', 'basili')
                                ->where('deleted_at', null)
                                ->countAllResults();

        // 2. Kritik Stok (Stoku 5'ten az olan basÄ±lÄ± kitaplar)
        $data['critical_stock'] = $db->table('products')
                                    ->where('type', 'basili')
                                    ->where('stock_count <', 5)
                                    ->where('deleted_at', null)
                                    ->get()->getResult();

        // 3. Kategori DaÄŸÄ±lÄ±mÄ± (Pasta Grafik Ä°Ã§in Veri)
        $data['chart_data'] = $db->table('products')
                                ->select('categories.category_name, COUNT(products.id) as count')
                                ->join('categories', 'categories.id = products.category_id')
                                ->where('products.type', 'basili')
                                ->groupBy('products.category_id')
                                ->get()->getResult();

        $data['userName'] = (string) ($sessionUser['name'] ?? $sessionUserData['name'] ?? 'Kullanıcı');
        $data['roleName'] = (string) ($sessionUser['role'] ?? $sessionUserData['role'] ?? 'kullanıcı');
        $data['permissions'] = $session->get('permissions') ?? [];

        return view('site/home/index', $data);
    }
}
