<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ShippingModel;

class Shipping extends BaseController
{
    public function index()
    {
        return view('admin/shipping/index', [
            'title' => 'Kargo Takip',
        ]);
    }

    public function datatables()
    {
        $params = $this->request->getGet();
        $result = (new ShippingModel())->datatablesList($params);
        $rows = $result['data'] ?? [];

        $data = array_map(function (array $row) {
            $id = trim((string) ($row['id'] ?? ''));
            $orderNo = trim((string) ($row['order_no'] ?? ''));
            if ($orderNo === '' && $id !== '') {
                $orderNo = '#' . strtoupper(substr(str_replace('-', '', $id), 0, 8));
            }
            if ($orderNo === '') {
                $orderNo = '-';
            }

            $customer = trim((string) ($row['customer_name'] ?? ''));
            $shippingCompany = trim((string) ($row['shipping_company'] ?? ''));
            $trackingNo = trim((string) ($row['tracking_no'] ?? ''));
            $updatedAt = trim((string) ($row['updated_at'] ?? ''));
            $shippingStatus = trim((string) ($row['shipping_status'] ?? 'not_shipped'));

            $detailHref = $id !== '' ? site_url('admin/orders/' . $id) : '#';

            return [
                'order_no' => esc($orderNo),
                'customer_name' => esc($customer !== '' ? $customer : '-'),
                'shipping_company' => esc($shippingCompany !== '' ? $shippingCompany : '-'),
                'tracking_no' => esc($trackingNo !== '' ? $trackingNo : '-'),
                'shipping_status' => $this->shippingStatusBadge($shippingStatus),
                'updated_at' => esc($updatedAt !== '' ? $updatedAt : '-'),
                'actions' => '<div class="d-flex gap-1">'
                    . '<a href="#" class="btn btn-sm btn-light-secondary">Takip Gör</a>'
                    . '<a href="' . esc($detailHref) . '" class="btn btn-sm btn-outline-primary">Sipariş Detayı</a>'
                    . '</div>',
            ];
        }, $rows);

        $payload = [
            'draw' => (int) ($params['draw'] ?? 0),
            'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'data' => $data,
        ];

        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function shippingStatusBadge(string $status): string
    {
        $labelMap = [
            'not_shipped' => 'Hazırlanıyor',
            'preparing' => 'Hazırlanıyor',
            'ready' => 'Hazırlanıyor',
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim',
            'returned' => 'İade',
            'return_in_progress' => 'İade',
            'cancelled' => 'Geciken',
            'delayed' => 'Geciken',
        ];

        $label = $labelMap[$status] ?? 'Hazırlanıyor';

        return match ($label) {
            'Kargoda' => '<span class="badge bg-light-primary text-primary">Kargoda</span>',
            'Teslim' => '<span class="badge bg-light-success text-success">Teslim</span>',
            'İade' => '<span class="badge bg-light-warning text-warning">İade</span>',
            'Geciken' => '<span class="badge bg-light-danger text-danger">Geciken</span>',
            default => '<span class="badge bg-light-secondary text-secondary">Hazırlanıyor</span>',
        };
    }
}
