<?php

namespace App\Enums;

enum NotificationStatusEnum: int
{
    case QUEUED = 1;
    case SENT = 2;
    case DELIVERED = 3;
    case FAILED = 4;
    
    public function label(): string
    {
        return match($this) {
            self::QUEUED => 'queued',
            self::SENT => 'sent',
            self::DELIVERED => 'delivered',
            self::FAILED => 'failed',
        };
    }
}