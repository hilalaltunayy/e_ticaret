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
$routes->get('/', 'StorefrontController::home');
$routes->get('yardim/favorilerim', 'Favorites::index', ['filter' => 'auth']);
$routes->get('yardim/sepetim', 'Cart::index', ['filter' => 'auth']);
$routes->get('yardim/odeme', 'Checkout::index', ['filter' => 'auth']);
$routes->post('yardim/odeme/tamamla', 'Checkout::complete', ['filter' => 'auth']);
$routes->get('yardim/hesabim', 'Account::index', ['filter' => 'auth']);
$routes->get('yardim/dijital-kitaplarim', 'DigitalBooks::index', ['filter' => 'auth']);
$routes->get('yardim/dijital-kitaplarim/(:segment)/oku', 'DigitalBooks::read/$1', ['filter' => 'auth']);
$routes->get('api/digital-books/(:segment)/pages/(:num)', 'DigitalBooks::page/$1/$2', ['filter' => 'auth']);
$routes->get('api/digital-books/(:segment)/highlights', 'DigitalBooks::highlights/$1', ['filter' => 'auth']);
$routes->post('api/digital-books/(:segment)/highlights', 'DigitalBooks::createHighlight/$1', ['filter' => 'auth']);
$routes->delete('api/digital-books/(:segment)/highlights/(:segment)', 'DigitalBooks::deleteHighlight/$1/$2', ['filter' => 'auth']);
$routes->post('yardim/hesabim/profil', 'Account::updateProfile', ['filter' => 'auth']);
$routes->post('yardim/hesabim/sifre', 'Account::updatePassword', ['filter' => 'auth']);
$routes->get('yardim/siparislerim', 'CustomerOrders::index', ['filter' => 'auth']);
$routes->get('yardim/siparislerim/(:segment)', 'CustomerOrders::show/$1', ['filter' => 'auth']);
$routes->post('yardim/siparislerim/(:segment)/iade-talebi', 'CustomerOrders::createReturnRequest/$1', ['filter' => 'auth']);
$routes->post('favorites/toggle', 'Favorites::toggle', ['filter' => 'auth']);
$routes->post('favorites/remove', 'Favorites::remove', ['filter' => 'auth']);
$routes->post('favorites/add-to-cart', 'Favorites::addToCart', ['filter' => 'auth']);
$routes->post('cart/add', 'Cart::add', ['filter' => 'auth']);
$routes->post('cart/increase', 'Cart::increase', ['filter' => 'auth']);
$routes->post('cart/decrease', 'Cart::decrease', ['filter' => 'auth']);
$routes->post('cart/update', 'Cart::update', ['filter' => 'auth']);
$routes->post('cart/remove', 'Cart::remove', ['filter' => 'auth']);
$routes->post('cart/clear', 'Cart::clear', ['filter' => 'auth']);
$routes->get('yardim/(:segment)', 'StorefrontController::placeholder/$1');
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
$routes->get('products/detail/(:segment)', 'ProductController::detail/$1');
$routes->post('products/detail/(:segment)/reviews', 'ProductController::submitReview/$1', ['filter' => 'auth']);
$routes->get('products/list/(:any)/(:any)', 'ProductController::listByCategory/$1/$2');

// Tip bazli liste
$routes->get('products/list/(:any)', 'ProductController::listByType/$1');

// Digerleri
$routes->get('products/selection', 'ProductController::selection');

// ----------------------------------------------------
// ADMIN ALANI - SADECE ADMIN
// ----------------------------------------------------
$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {

    $routes->get('dashboard-builder', 'Admin\DashboardBuilder::index');
    $routes->post('dashboard-builder/reorder', 'Admin\DashboardBuilder::reorder');
    $routes->post('dashboard-builder/resize', 'Admin\DashboardBuilder::resize');
    $routes->get('dashboard/blocks/fetch/(:segment)', 'Admin\DashboardBlockController::fetch/$1');
    $routes->get('dashboard/blocks/detail', 'Admin\DashboardBlockController::detail');
    $routes->post('dashboard/blocks/store', 'Admin\DashboardBlockController::store');
    $routes->post('dashboard/blocks/update/(:segment)', 'Admin\DashboardBlockController::update/$1');
    $routes->post('dashboard/blocks/delete/(:segment)', 'Admin\DashboardBlockController::delete/$1');
    $routes->get('settings', 'Admin\Settings::index');
    $routes->post('settings', 'Admin\Settings::update');
    $routes->get('settings/permissions', 'Admin\SettingsPermissionsController::index');
    $routes->post('settings/permissions/update', 'Admin\SettingsPermissionsController::update');
    $routes->post('settings/permissions/secretaries/create', 'Admin\SettingsPermissionsController::createSecretary');
    // (ileride)
    // $routes->get('users', 'Admin\Users::index');
    // $routes->get('roles', 'Admin\Roles::index');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_pages'], function ($routes) {
    $routes->get('pages', 'Admin\PageController::index');
    $routes->get('pages/(:segment)/builder', 'Admin\PageController::builder/$1');
    $routes->post('pages/product-list-builder/update', 'Admin\PageController::updateProductListBuilder');
    $routes->post('pages/product-detail-builder/update', 'Admin\PageController::updateProductDetailBuilder');
    $routes->post('pages/checkout-builder/update', 'Admin\PageController::updateCheckoutBuilder');
    $routes->post('pages/cart-builder/update', 'Admin\PageController::updateCartBuilder');
    $routes->post('pages/drafts/create', 'Admin\PageController::createDraft');
    $routes->post('pages/drafts/duplicate', 'Admin\PageController::duplicateDraft');
    $routes->post('pages/drafts/start-editing', 'Admin\PageController::startEditingVersion');
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
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_banners'], function ($routes) {
    $routes->get('banners', 'Admin\Banners::index');
    $routes->post('banners/save', 'Admin\Banners::save');
    $routes->post('banners/toggle/(:segment)', 'Admin\Banners::toggle/$1');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_marketing'], function ($routes) {
    $routes->get('marketing', 'Admin\Marketing::index');
    $routes->get('pricing', 'Admin\Pricing::index');
    $routes->get('pricing/rules', 'Admin\Pricing::rules');
    $routes->get('pricing/rules/create', 'Admin\Pricing::createRule');
    $routes->post('pricing/rules/store', 'Admin\Pricing::storeRule');
    $routes->get('pricing/rules/edit/(:segment)', 'Admin\Pricing::editRule/$1');
    $routes->post('pricing/rules/update/(:segment)', 'Admin\Pricing::updateRule/$1');
    $routes->post('pricing/rules/toggle/(:segment)', 'Admin\Pricing::toggleRule/$1');
    $routes->post('pricing/rules/delete/(:segment)', 'Admin\Pricing::deleteRule/$1');
    $routes->get('automation', 'Admin\Automation::index');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_dashboard'], function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_stock'], function ($routes) {
    $routes->get('stock', 'Admin\Stock::index');
    $routes->get('stock/moves', 'Admin\Stock::moves');
    $routes->get('stock/move/(:segment)', 'Admin\StockMove::create/$1');
    $routes->post('stock/move/(:segment)', 'Admin\StockMove::store/$1');
    $routes->post('stock/deactivate/(:segment)', 'Admin\Stock::deactivate/$1');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_notifications'], function ($routes) {
    $routes->get('notifications', 'Admin\Notifications::index');
    $routes->post('notifications/test-email', 'Admin\Notifications::sendTestEmail');
    $routes->post('notifications/test-sms', 'Admin\Notifications::sendTestSms');
    $routes->post('notifications/templates/save', 'Admin\Notifications::saveTemplate');
    $routes->post('notifications/templates/send-test', 'Admin\Notifications::sendSavedTemplateTest');
    $routes->get('notifications-management', 'Admin\NotificationsManagement::index');
    $routes->post('notifications-management', 'Admin\NotificationsManagement::update');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_customers'], function ($routes) {
    $routes->get('customers', 'Admin\Customers::index');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_products'], function ($routes) {
    $routes->get('products', 'Admin\Products::index');
    $routes->get('api/products', 'Admin\Products::datatables');
    $routes->get('products/create', 'Admin\Products::create');
    $routes->post('products/store', 'Admin\Products::store');
    $routes->get('products/edit/(:segment)', 'Admin\Products::edit/$1');
    $routes->post('products/update/(:segment)', 'Admin\Products::update/$1');
    $routes->get('authors/create', 'Admin\Products::createAuthor');
    $routes->post('authors/store', 'Admin\Products::storeAuthor');
    $routes->get('categories/create', 'Admin\Products::createCategory');
    $routes->post('categories/store', 'Admin\Products::storeCategory');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_reviews'], function ($routes) {
    $routes->get('reviews', 'Admin\Reviews::index');
    $routes->post('reviews/(:segment)/approve', 'Admin\Reviews::approve/$1');
    $routes->post('reviews/(:segment)/hide', 'Admin\Reviews::hide/$1');
    $routes->post('reviews/(:segment)/reject', 'Admin\Reviews::reject/$1');
    $routes->post('reviews/(:segment)/delete', 'Admin\Reviews::delete/$1');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_complaints'], function ($routes) {
    $routes->get('complaints', 'Admin\Complaints::index');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_traffic'], function ($routes) {
    $routes->get('traffic-analysis', 'Admin\TrafficAnalysis::index');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_logs'], function ($routes) {
    $routes->get('log-records', 'Admin\LogRecords::index');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_customer_messages'], function ($routes) {
    $routes->get('customer-messages', 'Admin\CustomerMessages::index');
});

$routes->group('admin', ['filter' => 'role:admin,secretary|perm:manage_shipping'], function ($routes) {
    $routes->get('shipping', 'Admin\Shipping::index');
    $routes->get('shipping/tracking-template', 'Admin\Shipping::trackingTemplate');
    $routes->get('shipping/templates/tracking-upload', 'Admin\Shipping::trackingUploadTemplate');
    $routes->get('shipping/manifesto/download', 'Admin\Shipping::manifestoDownload');
    $routes->post('shipping/bulk/labels', 'Admin\Shipping::bulkLabels');
    $routes->post('shipping/bulk/barcodes', 'Admin\Shipping::bulkBarcodes');
    $routes->post('shipping/bulk/manifest', 'Admin\Shipping::bulkManifest');
    $routes->get('shipping/companies/create', 'Admin\ShippingCompanies::create');
    $routes->post('shipping/companies/store', 'Admin\ShippingCompanies::store');
    $routes->get('api/shipping', 'Admin\Shipping::datatables');
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
    $routes->get('orders/returns', 'Admin\Orders::returns');
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
