<?php

namespace App\Services;

use App\Models\AuditLogModel;
use Throwable;

class AuditLogger
{
    public function log(string $action, string $entityType, ?string $entityId = null, array $meta = [], ?array $actor = null): void
    {
        $db = db_connect();
        if (! $db->tableExists('audit_logs')) {
            return;
        }

        $actor = $actor ?? (session()->get('user') ?? []);
        $actorId = trim((string) ($actor['id'] ?? ($actor['user_id'] ?? '')));
        $actorRole = trim((string) ($actor['role'] ?? ''));
        $actorIdentifier = trim((string) ($actor['email'] ?? ($actor['username'] ?? ($actor['name'] ?? ''))));

        $meta = $this->sanitizeMeta(array_merge($meta, [
            'actor_user_id' => $actorId !== '' ? $actorId : null,
            'actor_identifier' => $actorIdentifier !== '' ? $actorIdentifier : null,
        ]));

        try {
            (new AuditLogModel())->insert([
                'actor_id' => ctype_digit($actorId) ? (int) $actorId : null,
                'actor_role' => $actorRole !== '' ? $actorRole : null,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable) {
        }
    }

    private function sanitizeMeta(array $meta): array
    {
        $blocked = ['password', 'token', 'csrf', 'session', 'cookie', 'secret'];
        $clean = [];

        foreach ($meta as $key => $value) {
            $keyText = strtolower((string) $key);
            foreach ($blocked as $blockedPart) {
                if (str_contains($keyText, $blockedPart)) {
                    continue 2;
                }
            }

            $clean[$key] = is_array($value) ? $this->sanitizeMeta($value) : $value;
        }

        return $clean;
    }
}
