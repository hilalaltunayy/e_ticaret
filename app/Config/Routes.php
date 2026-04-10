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
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('dashboard-builder', 'Admin\DashboardBuilder::index');
    $routes->post('dashboard-builder/reorder', 'Admin\DashboardBuilder::reorder');
    $routes->post('dashboard-builder/resize', 'Admin\DashboardBuilder::resize');
    $routes->get('pages', 'Admin\PageController::index');
    $routes->get('pages/(:segment)/builder', 'Admin\PageController::builder/$1');
    $routes->post('pages/product-list-builder/update', 'Admin\PageController::updateProductListBuilder');
    $routes->post('pages/product-detail-builder/update', 'Admin\PageController::updateProductDetailBuilder');
    $routes->post('pages/checkout-builder/update', 'Admin\PageController::updateCheckoutBuilder');
    $routes->post('pages/cart-builder/update', 'Admin\PageController::updateCartBuilder');
    $routes->post('pages/drafts/create', 'Admin\PageController::createDraft');
    $routes->post('pages/drafts/duplicate', 'Admin\PageController::duplicateDraft');
    $routes->post('pages/drafts/archive', 'Admin\PageController::archiveDraft');
    $routes->post('pages/drafts/unpublish', 'Admin\PageController::unpublishVersion');
    $routes->post('pages/builder/draft/update', 'Admin\PageController::updateDraft');
    $routes->post('pages/builder/draft/publish', 'Admin\PageController::publishDraft');
    $routes->post('pages/builder/draft/schedule', 'Admin\PageController::scheduleDraft');
    $routes->post('pages/builder/draft/unschedule', 'Admin\PageController::unscheduleDraft');
    $routes->post('pages/builder/blocks', 'Admin\PageController::addBlock');
    $routes->post('pages/builder/blocks/update', 'Admin\PageController::updateBlock');
    $routes->post('pages/builder/blocks/delete', 'Admin\PageController::deleteBlock');
    $routes->post('pages/builder/blocks/reorder', 'Admin\PageController::reorderBlock');
    $routes->get('pages/(:segment)/drafts', 'Admin\PageController::drafts/$1');
    $routes->get('pages/(:segment)', 'Admin\PageController::show/$1');
    $routes->get('page-versions/(:segment)', 'Admin\PageController::show/$1');
    $routes->get('dashboard/blocks/fetch/(:segment)', 'Admin\DashboardBlockController::fetch/$1');
    $routes->get('dashboard/blocks/detail', 'Admin\DashboardBlockController::detail');
    $routes->post('dashboard/blocks/store', 'Admin\DashboardBlockController::store');
    $routes->post('dashboard/blocks/update/(:segment)', 'Admin\DashboardBlockController::update/$1');
    $routes->post('dashboard/blocks/delete/(:segment)', 'Admin\DashboardBlockController::delete/$1');
    $routes->get('stock', 'Admin\Stock::index');
    $routes->get('shipping', 'Admin\Shipping::index');
    $routes->get('shipping/tracking-template', 'Admin\Shipping::trackingTemplate');
    $routes->get('shipping/templates/tracking-upload', 'Admin\Shipping::trackingUploadTemplate');
    $routes->get('shipping/manifesto/download', 'Admin\Shipping::manifestoDownload');
    $routes->post('shipping/bulk/labels', 'Admin\Shipping::bulkLabels');
    $routes->post('shipping/bulk/barcodes', 'Admin\Shipping::bulkBarcodes');
    $routes->post('shipping/bulk/manifest', 'Admin\Shipping::bulkManifest');
    $routes->get('shipping/companies/create', 'Admin\ShippingCompanies::create');
    $routes->post('shipping/companies/store', 'Admin\ShippingCompanies::store');
    $routes->get('marketing', 'Admin\Marketing::index');
    $routes->get('pricing', 'Admin\Pricing::index');
    $routes->get('pricing/rules', 'Admin\Pricing::rules');
    $routes->get('pricing/rules/create', 'Admin\Pricing::createRule');
    $routes->post('pricing/rules/store', 'Admin\Pricing::storeRule');
    $routes->get('pricing/rules/edit/(:segment)', 'Admin\Pricing::editRule/$1');
    $routes->post('pricing/rules/update/(:segment)', 'Admin\Pricing::updateRule/$1');
    $routes->post('pricing/rules/toggle/(:segment)', 'Admin\Pricing::toggleRule/$1');
    $routes->post('pricing/rules/delete/(:segment)', 'Admin\Pricing::deleteRule/$1');
    $routes->get('customers', 'Admin\Customers::index');
    $routes->get('automation', 'Admin\Automation::index');
    $routes->get('notifications', 'Admin\Notifications::index');
    $routes->post('notifications/test-email', 'Admin\Notifications::sendTestEmail');
    $routes->post('notifications/test-sms', 'Admin\Notifications::sendTestSms');
    $routes->post('notifications/templates/save', 'Admin\Notifications::saveTemplate');
    $routes->post('notifications/templates/send-test', 'Admin\Notifications::sendSavedTemplateTest');
    $routes->get('settings', 'Admin\Settings::index');
    $routes->get('settings/permissions', 'Admin\SettingsPermissionsController::index');
    $routes->post('settings/permissions/update', 'Admin\SettingsPermissionsController::update');
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
    $routes->get('api/shipping', 'Admin\Shipping::datatables');


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

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_shipping'], function ($routes) {
    $routes->get('shipping/automation', 'Admin\ShippingAutomationController::index');
    $routes->get('shipping/automation/rules', 'Admin\ShippingAutomationController::rules');
    $routes->get('shipping/automation/rules/show/(:segment)', 'Admin\ShippingAutomationController::show/$1');
    $routes->post('shipping/automation/rules/create', 'Admin\ShippingAutomationController::create');
    $routes->post('shipping/automation/rules/update/(:segment)', 'Admin\ShippingAutomationController::update/$1');
    $routes->post('shipping/automation/simulate', 'Admin\ShippingAutomationController::simulate');
});

$routes->group('admin', ['filter' => 'campaign_access'], function ($routes) {
    $routes->get('campaigns', 'Admin\Campaigns::index');
    $routes->get('campaigns/create', 'Admin\Campaigns::create');
    $routes->post('campaigns', 'Admin\Campaigns::store');
    $routes->get('campaigns/edit/(:segment)', 'Admin\Campaigns::edit/$1');
    $routes->post('campaigns/update/(:segment)', 'Admin\Campaigns::update/$1');
    $routes->post('campaigns/toggle/(:segment)', 'Admin\Campaigns::toggle/$1');
    $routes->post('campaigns/delete/(:segment)', 'Admin\Campaigns::delete/$1');
    $routes->get('coupons', 'Admin\Coupons::index');
    $routes->get('coupons/create', 'Admin\Coupons::create');
    $routes->post('coupons', 'Admin\Coupons::store');
    $routes->get('coupons/edit/(:segment)', 'Admin\Coupons::edit/$1');
    $routes->post('coupons/update/(:segment)', 'Admin\Coupons::update/$1');
    $routes->post('coupons/toggle/(:segment)', 'Admin\Coupons::toggle/$1');
    $routes->post('coupons/delete/(:segment)', 'Admin\Coupons::delete/$1');
});

// ----------------------------------------------------
// ADMIN + SECRETARY - SIPARIS YONETIMI
// ----------------------------------------------------
$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_orders'], function ($routes) {

    $routes->get('orders', 'Admin\Orders::index');
    $routes->get('api/orders', 'Admin\Orders::datatables');
    $routes->get('api/orders/analytics', 'Admin\Orders::analytics');
    $routes->get('api/orders/status-distribution', 'Admin\Orders::statusDistribution');
    $routes->get('orders/summary', 'Admin\Orders::summary');
    $routes->get('orders/statuses', 'Admin\OrderStatuses::index');
    $routes->get('orders/(:segment)/packing/label', 'Admin\Orders::packingLabel/$1');
    $routes->get('orders/(:segment)/packing/verify', 'Admin\Orders::packingVerify/$1');
    $routes->post('orders/(:segment)/packing/scan', 'Admin\Orders::packingScan/$1');
    $routes->post('orders/(:segment)/packing/finish', 'Admin\Orders::packingFinish/$1');
    $routes->get('orders/(:segment)', 'Admin\Orders::show/$1');
    $routes->post('orders/create', 'Admin\Orders::create');
    $routes->post('orders/update-status', 'Admin\Orders::inlineStatusUpdate');
    $routes->post('orders/update-status/(:segment)', 'Admin\Orders::updateStatus/$1');
    $routes->post('orders/update-shipping/(:segment)', 'Admin\Orders::updateShipping/$1');
    $routes->post('orders/add-note/(:segment)', 'Admin\Orders::addNote/$1');
    $routes->post('orders/ship/(:segment)', 'Admin\Orders::ship/$1');
    $routes->post('orders/cancel/(:segment)', 'Admin\Orders::cancel/$1');
    $routes->post('orders/return/(:segment)', 'Admin\Orders::return/$1');
    $routes->post('orders/return/start/(:segment)', 'Admin\Orders::startReturn/$1');
    $routes->post('orders/return/complete/(:segment)', 'Admin\Orders::completeReturn/$1');
    $routes->post('orders/invoice/create/(:segment)', 'Admin\Orders::createInvoice/$1');
    $routes->get('orders/invoice/view/(:segment)', 'Admin\Orders::viewInvoice/$1');
    $routes->get('orders/invoice/download/(:segment)', 'Admin\Orders::downloadInvoice/$1');
});
