<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;

class TwilioSmsProvider implements NotificationSmsProviderInterface
{
    public function __construct(
        private string $accountSid,
        private string $authToken,
        private string $fromNumber,
        private ?string $messagingServiceSid = null,
        private ?CURLRequest $client = null
    ) {
        $this->client = $this->client ?? service('curlrequest', [
            'baseURI' => 'https://api.twilio.com',
            'timeout' => 15,
        ]);
    }

    public function send(string $phoneNumber, string $message, array $context = []): array
    {
        $payload = [
            'To' => $phoneNumber,
            'Body' => $message,
        ];

        if ($this->messagingServiceSid !== null && $this->messagingServiceSid !== '') {
            $payload['MessagingServiceSid'] = $this->messagingServiceSid;
        } else {
            $payload['From'] = $this->fromNumber;
        }

        try {
            $response = $this->client->post('/2010-04-01/Accounts/' . rawurlencode($this->accountSid) . '/Messages.json', [
                'auth' => [$this->accountSid, $this->authToken],
                'form_params' => $payload,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'error' => $this->shortenError($exception->getMessage()),
            ];
        }

        $statusCode = $response->getStatusCode();
        $body = json_decode((string) $response->getBody(), true);

        if ($statusCode < 200 || $statusCode >= 300) {
            $messageText = is_array($body) ? (string) ($body['message'] ?? '') : '';

            return [
                'success' => false,
                'error' => $this->shortenError($messageText !== '' ? $messageText : 'SMS gönderimi başarısız oldu.'),
            ];
        }

        return [
            'success' => true,
            'provider_message_id' => is_array($body) ? (string) ($body['sid'] ?? '') : '',
            'provider_status' => is_array($body) ? (string) ($body['status'] ?? '') : '',
        ];
    }

    private function shortenError(string $message): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $message));

        if (mb_strlen($message) <= 220) {
            return $message;
        }

        return rtrim(mb_substr($message, 0, 217)) . '...';
    }
}
