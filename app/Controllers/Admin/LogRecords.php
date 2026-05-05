<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class LogRecords extends BaseController
{
    public function index()
    {
        $items = array_map(fn (array $item): array => $this->withFormattedDetail($item), $this->latestAuditLogs());

        return view('admin/log_records/index', [
            'title' => 'Log Kayitlari',
            'summary' => [
                'total' => count($items),
                'today' => $this->countToday($items),
                'actors' => count(array_unique(array_filter(array_map(
                    fn (array $item): string => $this->actorKey($item),
                    $items
                )))),
            ],
            'items' => $items,
            'hasAuditTable' => db_connect()->tableExists('audit_logs'),
        ]);
    }

    private function latestAuditLogs(): array
    {
        $db = db_connect();
        if (! $db->tableExists('audit_logs')) {
            return [];
        }

        return (new AuditLogModel())->getLatestWithActor(200, date('Y-m-d H:i:s', strtotime('-6 months')));
    }

    private function countToday(array $items): int
    {
        $today = date('Y-m-d');
        $count = 0;

        foreach ($items as $item) {
            if (str_starts_with((string) ($item['created_at'] ?? ''), $today)) {
                $count++;
            }
        }

        return $count;
    }

    private function actorKey(array $item): string
    {
        $actor = trim((string) ($item['actor_name'] ?? ($item['actor_email'] ?? '')));
        if ($actor !== '') {
            return $actor;
        }

        $meta = json_decode((string) ($item['meta_json'] ?? ''), true);
        if (is_array($meta)) {
            $actor = trim((string) ($meta['actor_identifier'] ?? ''));
            if ($actor !== '') {
                return $actor;
            }
        }

        return trim((string) ($item['actor_role'] ?? ''));
    }

    private function withFormattedDetail(array $item): array
    {
        $item['detail_text'] = $this->formatAuditLogDetail($item);
        return $item;
    }

    private function formatAuditLogDetail(array $item): string
    {
        $action = trim((string) ($item['action'] ?? ''));
        $meta = json_decode((string) ($item['meta_json'] ?? ''), true);
        $meta = is_array($meta) ? $meta : [];

        if ($action === 'stock.update') {
            $name = trim((string) ($meta['product_name'] ?? 'Urun'));
            $direction = trim((string) ($meta['direction'] ?? ''));
            $qty = (int) ($meta['quantity'] ?? 0);
            $reason = $this->stockReasonLabel((string) ($meta['reason'] ?? ''));

            if ($qty > 0) {
                if ($direction === 'in') {
                    return $reason !== ''
                        ? "{$name} urunu icin stok artirildi (+{$qty}) ({$reason})"
                        : "{$name} urunu icin stok artirildi (+{$qty})";
                }
                if ($direction === 'out') {
                    return $reason !== ''
                        ? "{$name} urunu icin stok azaltildi (-{$qty}) ({$reason})"
                        : "{$name} urunu icin stok azaltildi (-{$qty})";
                }
            }
        }

        if ($action === 'order.status_update') {
            $before = is_array($meta['before'] ?? null) ? $meta['before'] : [];
            $after = is_array($meta['after'] ?? null) ? $meta['after'] : [];
            $from = $this->orderStatusLabel((string) ($before['order_status'] ?? ''));
            $to = $this->orderStatusLabel((string) ($after['order_status'] ?? ''));

            if ($from !== '' && $to !== '') {
                return "Siparis durumu guncellendi: {$from} -> {$to}";
            }
            if ($to !== '') {
                return "Siparis durumu {$to} olarak guncellendi";
            }
        }

        if ($action === 'order.shipping_update') {
            $before = is_array($meta['before'] ?? null) ? $meta['before'] : [];
            $after = is_array($meta['after'] ?? null) ? $meta['after'] : [];
            $from = $this->shippingStatusLabel((string) ($before['shipping_status'] ?? ''));
            $to = $this->shippingStatusLabel((string) ($after['shipping_status'] ?? ''));
            $company = trim((string) ($after['shipping_company'] ?? ''));

            if ($from !== '' && $to !== '') {
                $base = "Kargo durumu guncellendi: {$from} -> {$to}";
                return $company !== '' ? $base . " ({$company})" : $base;
            }
        }

        if ($action === 'permission.update') {
            $permCode = trim((string) ($meta['permission_code'] ?? ''));
            $before = is_array($meta['before'] ?? null) ? $meta['before'] : [];
            $after = is_array($meta['after'] ?? null) ? $meta['after'] : [];
            $from = (bool) ($before['effective'] ?? false) ? 'Acik' : 'Kapali';
            $to = (bool) ($after['effective'] ?? false) ? 'Acik' : 'Kapali';

            if ($permCode !== '') {
                return "Yetki guncellendi ({$permCode}): {$from} -> {$to}";
            }
            return "Yetki guncellendi: {$from} -> {$to}";
        }

        if ($meta !== []) {
            $fallback = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (! is_string($fallback) || trim($fallback) === '') {
                return 'Detay bulunamadi';
            }

            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                if (mb_strlen($fallback, 'UTF-8') > 220) {
                    return mb_substr($fallback, 0, 217, 'UTF-8') . '...';
                }
            } elseif (strlen($fallback) > 220) {
                return substr($fallback, 0, 217) . '...';
            }

            return $fallback;
        }

        return 'Detay bulunamadi';
    }

    private function stockReasonLabel(string $reason): string
    {
        return match (trim($reason)) {
            'depo_girisi' => 'Depo girisi',
            'depo_transferi' => 'Depo transferi',
            'iade_alindi' => 'Iade alindi',
            'tedarikci_girisi' => 'Tedarikci girisi',
            'hasarli_urun' => 'Hasarli urun',
            'kayip_urun' => 'Kayip urun',
            'kampanya_promosyon' => 'Kampanya promosyon',
            'manuel_duzeltme' => 'Manuel duzeltme',
            'hediye_gonderimi' => 'Hediye gonderimi',
            'sayim_duzeltme' => 'Sayim duzeltme',
            default => '',
        };
    }

    private function orderStatusLabel(string $status): string
    {
        return match (trim($status)) {
            'pending' => 'Beklemede',
            'preparing' => 'Hazirlaniyor',
            'shipped' => 'Kargoya verildi',
            'delivered' => 'Teslim edildi',
            'cancelled' => 'Iptal edildi',
            'return_in_progress' => 'Iade surecinde',
            'return_done' => 'Iade tamamlandi',
            default => trim($status),
        };
    }

    private function shippingStatusLabel(string $status): string
    {
        return match (trim($status)) {
            'not_shipped' => 'Hazirlaniyor',
            'shipped' => 'Kargoya verildi',
            'delivered' => 'Teslim edildi',
            'returned' => 'Iade edildi',
            default => trim($status),
        };
    }
}
