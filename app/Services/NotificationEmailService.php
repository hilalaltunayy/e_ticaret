<?php

namespace App\Services;

class NotificationEmailService
{
    public function __construct(
        private ?NotificationLogService $notificationLogService = null
    ) {
        $this->notificationLogService = $this->notificationLogService ?? new NotificationLogService();
    }

    public function sendTestEmail(string $toEmail, string $subject, string $message, array $context = []): array
    {
        $emailConfig = config('Email');
        $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
        $fromName = trim((string) ($emailConfig->fromName ?? ''));

        if ($fromEmail === '') {
            $result = [
                'success' => false,
                'error' => 'E-posta gönderimi için `fromEmail` yapılandırması eksik.',
            ];
            $this->recordLog($toEmail, $subject, $context, $result);

            return $result;
        }

        $plainMessage = $this->normalizePlainText($message);
        $htmlMessage = $this->buildHtmlMessage($plainMessage);

        $email = service('email');
        $email->clear(true);
        $email->setFrom($fromEmail, $fromName !== '' ? $fromName : null);
        $email->setTo($toEmail);
        $email->setSubject($subject);
        $email->setMailType('html');
        $email->setMessage($htmlMessage);
        $email->setAltMessage($plainMessage);

        if (! $email->send()) {
            $debugMessage = trim(strip_tags((string) $email->printDebugger(['headers'])));
            $result = [
                'success' => false,
                'error' => $debugMessage !== ''
                    ? 'Test e-postası gönderilemedi. ' . $debugMessage
                    : 'Test e-postası gönderilemedi.',
            ];
            $this->recordLog($toEmail, $subject, $context, $result);

            return $result;
        }

        $result = ['success' => true];
        $this->recordLog($toEmail, $subject, $context, $result);

        return $result;
    }

    private function buildHtmlMessage(string $message): string
    {
        $escaped = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $paragraphs = preg_split("/\R{2,}/u", $escaped) ?: [];
        $paragraphHtml = [];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }

            $paragraphHtml[] = '<p style="margin:0 0 16px; line-height:1.7; color:#1f2937;">'
                . nl2br($paragraph, false)
                . '</p>';
        }

        if ($paragraphHtml === []) {
            $paragraphHtml[] = '<p style="margin:0; line-height:1.7; color:#1f2937;">&nbsp;</p>';
        }

        return '<div style="font-family:Segoe UI, Arial, sans-serif; font-size:14px; color:#1f2937;">'
            . implode('', $paragraphHtml)
            . '</div>';
    }

    private function normalizePlainText(string $message): string
    {
        $message = str_replace(["\r\n", "\r"], "\n", $message);

        return trim($message);
    }

    private function recordLog(string $toEmail, string $subject, array $context, array $result): void
    {
        try {
            $this->notificationLogService->recordEmailAttempt([
                'recipient_email' => $toEmail,
                'subject' => $subject,
                'template_type' => (string) ($context['template_type'] ?? ''),
                'source_type' => (string) ($context['source_type'] ?? 'manual'),
                'status' => ($result['success'] ?? false) ? 'success' : 'failed',
                'error_message' => (string) ($result['error'] ?? ''),
                'created_by' => $context['created_by'] ?? null,
            ]);
        } catch (\Throwable $exception) {
        }
    }
}
