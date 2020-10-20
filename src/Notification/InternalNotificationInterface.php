<?php

namespace App\Notification;

interface InternalNotificationInterface
{
    public function sendMessageLoginAsInfo(string $webhook, string $message, ?array $attachments): void;
}
