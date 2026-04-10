<?php

namespace App\Services;

interface NotificationSmsProviderInterface
{
    public function send(string $phoneNumber, string $message, array $context = []): array;
}
