<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class LogRecords extends BaseController
{
    public function index()
    {
        $items = $this->latestAuditLogs();

        return view('admin/log_records/index', [
            'title' => 'Log Kayitlari',
            'summary' => [
                'total' => count($items),
                'today' => $this->countToday($items),
                'actors' => count(array_unique(array_filter(array_map(
                    static fn (array $item): string => (string) ($item['actor_name'] ?? $item['actor_role'] ?? ''),
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

        return (new AuditLogModel())->getLatestWithActor(200);
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
}
