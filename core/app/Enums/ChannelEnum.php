<?php

namespace App\Enums;

enum ChannelEnum: int
{
    case SMS = 1;
    case EMAIL = 2;
    
    public function label(): string
    {
        return match($this) {
            self::SMS => 'sms',
            self::EMAIL => 'email',
        };
    }
    
    public static function fromString(string $channel): self
    {
        return match($channel) {
            'sms' => self::SMS,
            'email' => self::EMAIL,
            default => throw new \InvalidArgumentException("Invalid channel: {$channel}"),
        };
    }
}