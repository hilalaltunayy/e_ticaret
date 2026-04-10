<?php

namespace App\Services;

class NotificationSmsService
{
    public function __construct(
        private ?NotificationLogService $notificationLogService = null
    ) {
        $this->notificationLogService = $this->notificationLogService ?? new NotificationLogService();
    }

    public function sendTestSms(string $phoneNumber, string $recipientName, string $message, array $context = []): array
    {
        $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);
        if ($normalizedPhone === null) {
            $result = [
                'success' => false,
                'error' => 'Telefon numarasını uluslararası formatta girin. Örn: +905551112233',
            ];
            $this->recordLog($phoneNumber, $context, $result);

            return $result;
        }

        $normalizedMessage = $this->normalizeMessage($message, $recipientName);
        if ($normalizedMessage === '') {
            $result = [
                'success' => false,
                'error' => 'SMS mesajı boş olamaz.',
            ];
            $this->recordLog($normalizedPhone, $context, $result);

            return $result;
        }

        $provider = $this->buildProvider();
        if (! ($provider['success'] ?? false)) {
            $result = [
                'success' => false,
                'error' => (string) ($provider['error'] ?? 'SMS sağlayıcısı yapılandırılamadı.'),
            ];
            $this->recordLog($normalizedPhone, $context, $result);

            return $result;
        }

        /** @var NotificationSmsProviderInterface $adapter */
        $adapter = $provider['provider'];
        $result = $adapter->send($normalizedPhone, $normalizedMessage, $context);
        $this->recordLog($normalizedPhone, $context, $result);

        if (! ($result['success'] ?? false)) {
            return [
                'success' => false,
                'error' => (string) ($result['error'] ?? 'SMS gönderilemedi.'),
            ];
        }

        return [
            'success' => true,
            'provider_message_id' => (string) ($result['provider_message_id'] ?? ''),
        ];
    }

    private function buildProvider(): array
    {
        $providerName = strtolower(trim((string) env('notifications.sms.provider', '')));

        if ($providerName === '') {
            return [
                'success' => false,
                'error' => 'SMS provider ayarı eksik. `.env` içinde `notifications.sms.provider` tanımlayın.',
            ];
        }

        if ($providerName !== 'twilio') {
            return [
                'success' => false,
                'error' => 'Bu sürümde yalnızca `twilio` sağlayıcısı desteklenir.',
            ];
        }

        $accountSid = trim((string) env('notifications.sms.twilio.accountSid', ''));
        $authToken = trim((string) env('notifications.sms.twilio.authToken', ''));
        $fromNumber = trim((string) env('notifications.sms.twilio.fromNumber', ''));
        $messagingServiceSid = trim((string) env('notifications.sms.twilio.messagingServiceSid', ''));

        if ($accountSid === '' || $authToken === '') {
            return [
                'success' => false,
                'error' => 'Twilio hesap bilgileri eksik. `.env` içinde accountSid ve authToken tanımlayın.',
            ];
        }

        if ($fromNumber === '' && $messagingServiceSid === '') {
            return [
                'success' => false,
                'error' => 'Twilio için `fromNumber` veya `messagingServiceSid` ayarlarından biri gerekli.',
            ];
        }

        return [
            'success' => true,
            'provider' => new TwilioSmsProvider(
                $accountSid,
                $authToken,
                $fromNumber,
                $messagingServiceSid !== '' ? $messagingServiceSid : null
            ),
        ];
    }

    private function normalizePhoneNumber(string $phoneNumber): ?string
    {
        $phoneNumber = trim($phoneNumber);
        if ($phoneNumber === '') {
            return null;
        }

        $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);
        if ($phoneNumber === null || $phoneNumber === '') {
            return null;
        }

        if (str_starts_with($phoneNumber, '00')) {
            $phoneNumber = '+' . substr($phoneNumber, 2);
        }

        if (! str_starts_with($phoneNumber, '+')) {
            $digits = preg_replace('/\D/', '', $phoneNumber);
            if ($digits === null) {
                return null;
            }

            if (strlen($digits) === 10 && str_starts_with($digits, '5')) {
                $phoneNumber = '+90' . $digits;
            } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
                $phoneNumber = '+9' . $digits;
            } elseif (strlen($digits) >= 10 && strlen($digits) <= 15) {
                $phoneNumber = '+' . $digits;
            }
        }

        if (! preg_match('/^\+[1-9]\d{9,14}$/', $phoneNumber)) {
            return null;
        }

        return $phoneNumber;
    }

    private function normalizeMessage(string $message, string $recipientName): string
    {
        $message = str_replace(["\r\n", "\r"], "\n", trim($message));
        $recipientName = trim($recipientName);

        if ($recipientName !== '' && str_contains($message, '{name}')) {
            $message = str_replace('{name}', $recipientName, $message);
        }

        return $message;
    }

    private function recordLog(string $phoneNumber, array $context, array $result): void
    {
        try {
            $this->notificationLogService->recordAttempt([
                'channel' => 'sms',
                'recipient' => $phoneNumber,
                'subject' => 'SMS test gönderimi',
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
