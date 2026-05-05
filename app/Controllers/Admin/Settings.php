<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminSettingModel;

class Settings extends BaseController
{
    private const SETTING_MAP = [
        'general_site_name' => 'general.site_name',
        'general_admin_email' => 'general.admin_email',
        'general_timezone' => 'general.timezone',
        'stock_critical_threshold' => 'stock.critical_threshold',
        'stock_low_notification_enabled' => 'stock.low_notification_enabled',
        'stock_allow_zero_sale' => 'stock.allow_zero_sale',
        'order_return_days' => 'order.return_days',
        'order_cancel_hours' => 'order.cancel_hours',
        'order_cash_on_delivery_enabled' => 'order.cash_on_delivery_enabled',
        'shipping_default_sla_days' => 'shipping.default_sla_days',
        'shipping_default_desi_limit' => 'shipping.default_desi_limit',
        'shipping_default_mode' => 'shipping.default_mode',
        'panel_table_row_limit' => 'panel.table_row_limit',
        'panel_default_language' => 'panel.default_language',
    ];

    public function index()
    {
        $model = new AdminSettingModel();
        $saved = $model->getMapByKeys(array_values(self::SETTING_MAP));
        $appConfig = config('App');

        return view('admin/settings/index', [
            'title' => 'Ayarlar',
            'general_site_name' => (string) ($saved['general.site_name'] ?? 'Admin Panel'),
            'general_admin_email' => (string) ($saved['general.admin_email'] ?? 'admin@example.com'),
            'general_timezone' => (string) ($saved['general.timezone'] ?? (string) ($appConfig->appTimezone ?? 'UTC')),
            'stock_critical_threshold' => (string) ($saved['stock.critical_threshold'] ?? '5'),
            'stock_low_notification_enabled' => (string) ($saved['stock.low_notification_enabled'] ?? '1'),
            'stock_allow_zero_sale' => (string) ($saved['stock.allow_zero_sale'] ?? '0'),
            'order_return_days' => (string) ($saved['order.return_days'] ?? '14'),
            'order_cancel_hours' => (string) ($saved['order.cancel_hours'] ?? '24'),
            'order_cash_on_delivery_enabled' => (string) ($saved['order.cash_on_delivery_enabled'] ?? '1'),
            'shipping_default_sla_days' => (string) ($saved['shipping.default_sla_days'] ?? '2'),
            'shipping_default_desi_limit' => (string) ($saved['shipping.default_desi_limit'] ?? '30'),
            'shipping_default_mode' => (string) ($saved['shipping.default_mode'] ?? 'dengeli'),
            'panel_table_row_limit' => (string) ($saved['panel.table_row_limit'] ?? '25'),
            'panel_default_language' => (string) ($saved['panel.default_language'] ?? 'tr'),
            'validation' => session('validation'),
        ]);
    }

    public function update()
    {
        $rules = [
            'general_site_name' => 'required|max_length[120]',
            'general_admin_email' => 'required|valid_email|max_length[120]',
            'general_timezone' => 'required|max_length[80]',
            'stock_critical_threshold' => 'required|integer|greater_than_equal_to[0]',
            'stock_low_notification_enabled' => 'required|in_list[0,1]',
            'stock_allow_zero_sale' => 'required|in_list[0,1]',
            'order_return_days' => 'required|integer|greater_than_equal_to[0]',
            'order_cancel_hours' => 'required|integer|greater_than_equal_to[0]',
            'order_cash_on_delivery_enabled' => 'required|in_list[0,1]',
            'shipping_default_sla_days' => 'required|integer|greater_than_equal_to[1]',
            'shipping_default_desi_limit' => 'required|integer|greater_than_equal_to[0]',
            'shipping_default_mode' => 'required|in_list[hizli,ekonomik,dengeli]',
            'panel_table_row_limit' => 'required|in_list[10,25,50,100]',
            'panel_default_language' => 'required|in_list[tr,en]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Lutfen ayar alanlarini kontrol edin.');
        }

        $db = db_connect();
        if (! $db->tableExists('admin_settings')) {
            return redirect()->back()->withInput()->with('error', 'Ayarlar tablosu bulunamadi.');
        }

        $model = new AdminSettingModel();
        foreach (self::SETTING_MAP as $formKey => $settingKey) {
            $value = trim((string) ($this->request->getPost($formKey) ?? ''));
            if (in_array($formKey, [
                'stock_low_notification_enabled',
                'stock_allow_zero_sale',
                'order_cash_on_delivery_enabled',
            ], true)) {
                $value = $value === '1' ? '1' : '0';
            }
            $model->setValue($settingKey, $value);
        }

        return redirect()->to(site_url('admin/settings'))->with('success', 'Ayarlar kaydedildi.');
    }
}
