<?php

namespace Config;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes = Services::routes();

// ----------------------------------------------------
// ACIK ALAN - HERKES ERISEBILIR
// ----------------------------------------------------
$routes->get('/', 'Login::index');
$routes->get('login', 'Login::index');
$routes->post('login/auth', 'Login::auth');

$routes->get('register', 'Register::index');
$routes->post('register/save', 'Register::save');

$routes->get('logout', 'Logout::index');

// ----------------------------------------------------
// KORUMALI ALAN - SADECE GIRIS YAPANLAR (auth)
// ----------------------------------------------------
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard_anasayfa', 'Home::index');

    // ------------------------------------------------
    // URUN YONETIMI (login olan herkes - mevcut davranisi bozmadik)
    // ------------------------------------------------
    $routes->get('products', 'ProductController::index');
    //$routes->get('products/new', 'ProductController::new');
    $routes->get('products/detail/(:num)', 'ProductController::detail/$1');

    //$routes->post('products/save', 'ProductController::save');
    //$routes->get('products/delete/(:num)', 'ProductController::delete/$1');

    // Stok
    //$routes->get('products/stock-management', 'ProductController::stock_management');

    // Siparisler (liste / olusturma)
    $routes->get('orders', 'OrderController::index');
    $routes->post('orders/create', 'OrderController::create');

});

// ----------------------------------------------------
// URUN LISTELEME & FILTRELEME (ACIK ALAN)
// ----------------------------------------------------

// En spesifik rota EN USTTE
$routes->get('products/list/(:any)/(:any)', 'ProductController::listByCategory/$1/$2');

// Tip bazli liste
$routes->get('products/list/(:any)', 'ProductController::listByType/$1');

// Digerleri
$routes->get('products/selection', 'ProductController::selection');

// Edit & Update
$routes->get('products/edit/(:num)', 'ProductController::edit/$1');
$routes->post('products/update', 'ProductController::update');

// ----------------------------------------------------
// ADMIN ALANI - SADECE ADMIN
// ----------------------------------------------------
$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {

    // Admin Dashboard
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('stock', 'Admin\Stock::index');
    $routes->get('stock/moves', 'Admin\Stock::moves');
    $routes->get('stock/move/(:segment)', 'Admin\StockMove::create/$1');
    $routes->post('stock/move/(:segment)', 'Admin\StockMove::store/$1');
    $routes->post('stock/deactivate/(:segment)', 'Admin\Stock::deactivate/$1');

    // Products + API (admin only)
    $routes->get('products', 'Admin\Products::index');
    $routes->get('api/products', 'Admin\Products::datatables');
    $routes->get('products/create', 'Admin\Products::create');
    $routes->post('products/store', 'Admin\Products::store');
    $routes->get('products/edit/(:segment)', 'Admin\Products::edit/$1');
    $routes->post('products/update/(:segment)', 'Admin\Products::update/$1');


    // Authors (admin only)
    $routes->get('authors/create', 'Admin\Products::createAuthor');
    $routes->post('authors/store', 'Admin\Products::storeAuthor');
    // Categories (admin only)
    $routes->get('categories/create', 'Admin\Products::createCategory');
    $routes->post('categories/store', 'Admin\Products::storeCategory');

    // (ileride)
    // $routes->get('users', 'Admin\Users::index');
    // $routes->get('roles', 'Admin\Roles::index');
});

// ----------------------------------------------------
// ADMIN + SECRETARY - SIPARIS YONETIMI
// ----------------------------------------------------
$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_orders'], function ($routes) {

    $routes->get('orders', 'Admin\Orders::index');
    $routes->post('orders/create', 'Admin\Orders::create');
    $routes->post('orders/ship/(:segment)', 'Admin\Orders::ship/$1');
    $routes->post('orders/cancel/(:segment)', 'Admin\Orders::cancel/$1');
    $routes->post('orders/return/(:segment)', 'Admin\Orders::return/$1');
});
