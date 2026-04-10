<?php

namespace App\Services;

use App\Models\NotificationDeliveryLogModel;

class NotificationLogService
{
    public function __construct(
        private ?NotificationDeliveryLogModel $notificationDeliveryLogModel = null
    ) {
        $this->notificationDeliveryLogModel = $this->notificationDeliveryLogModel ?? new NotificationDeliveryLogModel();
    }

    public function recordEmailAttempt(array $payload): void
    {
        $this->recordAttempt([
            'channel' => 'email',
            'recipient' => (string) ($payload['recipient_email'] ?? ''),
            'subject' => (string) ($payload['subject'] ?? ''),
            'template_type' => (string) ($payload['template_type'] ?? ''),
            'source_type' => (string) ($payload['source_type'] ?? 'manual'),
            'status' => (string) ($payload['status'] ?? 'failed'),
            'error_message' => (string) ($payload['error_message'] ?? ''),
            'created_by' => $payload['created_by'] ?? null,
        ]);
    }

    public function recordAttempt(array $payload): void
    {
        if (! $this->logTableExists()) {
            return;
        }

        $this->notificationDeliveryLogModel->insert([
            'channel' => $this->sanitizeChannel((string) ($payload['channel'] ?? 'email')),
            'recipient_email' => trim((string) ($payload['recipient'] ?? '')),
            'subject' => $this->truncate((string) ($payload['subject'] ?? ''), 180),
            'template_type' => $this->sanitizeTemplateType((string) ($payload['template_type'] ?? '')),
            'source_type' => $this->sanitizeSourceType((string) ($payload['source_type'] ?? 'manual')),
            'status' => $this->sanitizeStatus((string) ($payload['status'] ?? 'failed')),
            'error_message' => $this->normalizeError((string) ($payload['error_message'] ?? '')),
            'sent_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->cleanActorId($payload['created_by'] ?? null),
        ], false);
    }

    public function getDeliveryHistorySummary(int $savedTemplateDraftCount = 0): array
    {
        if (! $this->logTableExists()) {
            return [
                ['label' => 'Bugün gönderilen', 'value' => '0'],
                ['label' => 'Son başarılı gönderim', 'value' => 'Henüz yok'],
                ['label' => 'Taslak şablon', 'value' => (string) $savedTemplateDraftCount],
            ];
        }

        $builder = $this->notificationDeliveryLogModel->builder();
        $todayCount = (int) $builder->where('DATE(sent_at)', date('Y-m-d'))
            ->countAllResults();

        $lastSuccess = $this->notificationDeliveryLogModel->where('status', 'success')
            ->orderBy('sent_at', 'DESC')
            ->first();

        $lastSuccessLabel = 'Henüz yok';
        if (is_array($lastSuccess)) {
            $time = $this->formatDateLabel((string) ($lastSuccess['sent_at'] ?? ''));
            $channel = $this->formatChannel((string) ($lastSuccess['channel'] ?? 'email'));
            $recipient = trim((string) ($lastSuccess['recipient_email'] ?? ''));
            $lastSuccessLabel = trim($time . ($recipient !== '' ? ' · ' . $channel . ' · ' . $recipient : ''));
        }

        return [
            ['label' => 'Bugün gönderilen', 'value' => (string) $todayCount],
            ['label' => 'Son başarılı gönderim', 'value' => $lastSuccessLabel],
            ['label' => 'Taslak şablon', 'value' => (string) $savedTemplateDraftCount],
        ];
    }

    public function getRecentDeliveryLogs(int $limit = 5): array
    {
        if (! $this->logTableExists()) {
            return [];
        }

        $rows = $this->notificationDeliveryLogModel
            ->orderBy('sent_at', 'DESC')
            ->findAll($limit);

        $items = [];
        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? 'failed');
            $sourceType = (string) ($row['source_type'] ?? 'manual');
            $templateType = trim((string) ($row['template_type'] ?? ''));
            $channel = (string) ($row['channel'] ?? 'email');

            $items[] = [
                'channel' => $channel,
                'channel_label' => $this->formatChannel($channel),
                'channel_class' => $channel === 'sms' ? 'bg-light-warning text-warning' : 'bg-light-primary text-primary',
                'recipient' => (string) ($row['recipient_email'] ?? ''),
                'subject' => (string) ($row['subject'] ?? ''),
                'template_type' => $templateType !== '' ? $templateType : '-',
                'template_type_label' => $this->formatTemplateType($templateType),
                'source_type' => $sourceType,
                'source_type_label' => $this->formatSourceType($sourceType),
                'status' => $status,
                'status_label' => $status === 'success' ? 'Başarılı' : 'Hatalı',
                'status_class' => $status === 'success' ? 'bg-light-success text-success' : 'bg-light-danger text-danger',
                'error_message' => (string) ($row['error_message'] ?? ''),
                'sent_at' => (string) ($row['sent_at'] ?? ''),
                'sent_at_label' => $this->formatDateLabel((string) ($row['sent_at'] ?? '')),
            ];
        }

        return $items;
    }

    private function formatDateLabel(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'Henüz yok';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return date('d.m.Y H:i', $timestamp);
    }

    private function formatChannel(string $value): string
    {
        return match ($value) {
            'sms' => 'SMS',
            default => 'E-posta',
        };
    }

    private function formatTemplateType(string $value): string
    {
        return match ($value) {
            'welcome' => 'Hoş Geldiniz',
            'campaign' => 'Kampanya Duyurusu',
            'reminder' => 'Hatırlatma',
            'custom' => 'Manuel İçerik',
            default => 'Belirtilmedi',
        };
    }

    private function formatSourceType(string $value): string
    {
        return match ($value) {
            'template' => 'Hazır Şablon',
            'sms_test' => 'SMS Test',
            default => 'Manuel Gönderim',
        };
    }

    private function sanitizeChannel(string $value): string
    {
        $value = trim($value);

        return in_array($value, ['email', 'sms'], true) ? $value : 'email';
    }

    private function sanitizeTemplateType(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $this->truncate($value, 32) : null;
    }

    private function sanitizeSourceType(string $value): string
    {
        $value = trim($value);

        return in_array($value, ['manual', 'template', 'sms_test'], true) ? $value : 'manual';
    }

    private function sanitizeStatus(string $value): string
    {
        $value = trim($value);

        return in_array($value, ['success', 'failed'], true) ? $value : 'failed';
    }

    private function normalizeError(string $value): ?string
    {
        $value = $this->truncate(trim((string) preg_replace('/\s+/', ' ', $value)), 255);

        return $value !== '' ? $value : null;
    }

    private function cleanActorId(mixed $actorId): ?string
    {
        $actorId = trim((string) $actorId);

        return $actorId !== '' ? $actorId : null;
    }

    private function truncate(string $value, int $maxLength): string
    {
        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $maxLength - 3)) . '...';
    }

    private function logTableExists(): bool
    {
        return db_connect()->tableExists('notification_delivery_logs');
    }
}
