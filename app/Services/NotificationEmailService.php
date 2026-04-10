<?php

namespace App\Services;

class NotificationEmailService
{
    public function sendTestEmail(string $toEmail, string $subject, string $message): array
    {
        $emailConfig = config('Email');
        $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
        $fromName = trim((string) ($emailConfig->fromName ?? ''));

        if ($fromEmail === '') {
            return [
                'success' => false,
                'error' => 'E-posta gönderimi için `fromEmail` yapılandırması eksik.',
            ];
        }

        $email = service('email');
        $email->clear(true);
        $email->setFrom($fromEmail, $fromName !== '' ? $fromName : null);
        $email->setTo($toEmail);
        $email->setSubject($subject);
        $email->setMessage($message);

        if (! $email->send()) {
            $debugMessage = trim(strip_tags((string) $email->printDebugger(['headers'])));

            return [
                'success' => false,
                'error' => $debugMessage !== ''
                    ? 'Test e-postasi gonderilemedi. ' . $debugMessage
                    : 'Test e-postasi gonderilemedi.',
            ];
        }

        return ['success' => true];
    }
}
