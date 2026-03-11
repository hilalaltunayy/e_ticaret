<?php

namespace App\Presenters;

class OrderDatatablePresenter
{
    public function formatRows(array $rows): array
    {
        return array_map(fn (array $row): array => $this->formatRow($row), $rows);
    }

    private function formatRow(array $row): array
    {
        $id = (string) ($row['id'] ?? '');
        $orderNo = trim((string) ($row['order_no'] ?? ''));
        if ($orderNo === '') {
            $orderNo = '#' . strtoupper(substr(str_replace('-', '', $id), 0, 8));
        }

        $date = (string) ($row['created_at'] ?? $row['order_date'] ?? '-');
        $amount = number_format((float) ($row['total_amount'] ?? 0), 2, ',', '.');
        $detailHref = $id !== '' ? site_url('admin/orders/' . $id) : '#';

        return [
            'order_no' => esc($orderNo),
            'customer' => esc((string) ($row['customer_display'] ?? '-')),
            'date' => esc($date),
            'total_amount' => $amount . ' &#8378;',
            'payment_status' => $this->renderInlineStatusDropdown(
                $id,
                'payment_status',
                (string) ($row['payment_status'] ?? 'unpaid')
            ),
            'order_status' => $this->renderInlineStatusDropdown(
                $id,
                'order_status',
                (string) ($row['order_status'] ?? $row['status'] ?? 'pending')
            ),
            'shipping_status' => $this->shippingStatusBadge((string) ($row['shipping_status'] ?? 'not_shipped')),
            'actions' => '<a href="' . esc($detailHref) . '" class="btn btn-sm btn-outline-primary">Detay G\u{00F6}r</a>',
        ];
    }

    private function paymentStatusBadge(string $status): string
    {
        $labels = [
            'unpaid' => "\u{00D6}denmedi",
            'paid' => "\u{00D6}dendi",
            'refunded' => "\u{0130}ade Edildi",
            'partial_refund' => "K\u{0131}smi \u{0130}ade",
            'failed' => "Ba\u{015F}ar\u{0131}s\u{0131}z",
        ];
        $label = $labels[$status] ?? $labels['unpaid'];

        return match ($status) {
            'paid' => '<span class="badge bg-light-success text-success">' . esc($label) . '</span>',
            'refunded' => '<span class="badge bg-light-warning text-warning">' . esc($label) . '</span>',
            'partial_refund' => '<span class="badge bg-light-info text-info">' . esc($label) . '</span>',
            'failed' => '<span class="badge bg-light-danger text-danger">' . esc($label) . '</span>',
            default => '<span class="badge bg-light-secondary text-secondary">' . esc($label) . '</span>',
        };
    }

    private function orderStatusBadge(string $status): string
    {
        $labels = [
            'pending' => 'Beklemede',
            'preparing' => "Haz\u{0131}rlan\u{0131}yor",
            'packed' => 'Paketlendi',
            'shipped' => 'Kargoya Verildi',
            'delivered' => 'Teslim Edildi',
            'cancelled' => "\u{0130}ptal Edildi",
            'return_in_progress' => "\u{0130}ade S\u{00FC}recinde",
            'return_done' => "\u{0130}ade Tamamland\u{0131}",
        ];
        $label = $labels[$status] ?? $labels['pending'];

        return match ($status) {
            'preparing', 'shipped' => '<span class="badge bg-light-primary text-primary">' . esc($label) . '</span>',
            'packed' => '<span class="badge bg-light-info text-info">' . esc($label) . '</span>',
            'delivered' => '<span class="badge bg-light-success text-success">' . esc($label) . '</span>',
            'cancelled' => '<span class="badge bg-light-danger text-danger">' . esc($label) . '</span>',
            'return_in_progress' => '<span class="badge bg-light-warning text-warning">' . esc($label) . '</span>',
            'return_done' => '<span class="badge bg-light-dark text-dark">' . esc($label) . '</span>',
            default => '<span class="badge bg-light-secondary text-secondary">' . esc($label) . '</span>',
        };
    }

    private function shippingStatusBadge(string $status): string
    {
        $labels = [
            'not_shipped' => "Haz\u{0131}rlanmad\u{0131}",
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim',
            'returned' => "\u{0130}ade",
        ];
        $label = $labels[$status] ?? $labels['not_shipped'];

        return match ($status) {
            'shipped' => '<span class="badge bg-light-primary text-primary">' . esc($label) . '</span>',
            'delivered' => '<span class="badge bg-light-success text-success">' . esc($label) . '</span>',
            'returned' => '<span class="badge bg-light-warning text-warning">' . esc($label) . '</span>',
            default => '<span class="badge bg-light-secondary text-secondary">' . esc($label) . '</span>',
        };
    }

    private function renderInlineStatusDropdown(string $orderId, string $field, string $current): string
    {
        $currentBadge = $field === 'payment_status'
            ? $this->paymentStatusBadge($current)
            : $this->orderStatusBadge($current);

        $options = $this->statusOptions($field);
        $items = '';
        foreach ($options as $value => $label) {
            $items .= '<li><a href="#" class="dropdown-item js-inline-status-item" data-order-id="' . esc($orderId) . '" data-field="' . esc($field) . '" data-value="' . esc($value) . '">' . esc($label) . '</a></li>';
        }

        return '<div class="dropdown d-inline-block">'
            . '<a href="#" class="text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">' . $currentBadge . '</a>'
            . '<ul class="dropdown-menu">' . $items . '</ul>'
            . '</div>';
    }

    private function statusOptions(string $field): array
    {
        if ($field === 'payment_status') {
            return [
                'unpaid' => "\u{00D6}denmedi",
                'paid' => "\u{00D6}dendi",
                'refunded' => "\u{0130}ade Edildi",
                'partial_refund' => "K\u{0131}smi \u{0130}ade",
                'failed' => "Ba\u{015F}ar\u{0131}s\u{0131}z",
            ];
        }

        return [
            'pending' => 'Beklemede',
            'preparing' => "Haz\u{0131}rlan\u{0131}yor",
            'packed' => 'Paketlendi',
            'shipped' => 'Kargoya Verildi',
            'delivered' => 'Teslim Edildi',
            'cancelled' => "\u{0130}ptal Edildi",
            'return_in_progress' => "\u{0130}ade S\u{00FC}recinde",
            'return_done' => "\u{0130}ade Tamamland\u{0131}",
        ];
    }
}
