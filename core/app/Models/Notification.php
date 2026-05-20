<?php

namespace App\Models;

use App\Enums\ChannelEnum;
use App\Enums\NotificationStatusEnum;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'subscriber_id',
        'channel',
        'message',
        'status',
        'priority',
        'attempts',
        'error_message',
        'sent_at',
        'delivered_at',
    ];
    
    protected $casts = [
        'channel' => ChannelEnum::class,
        'status' => NotificationStatusEnum::class,
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];
    
    public function markAsSent(): void
    {
        $this->update([
            'status' => NotificationStatusEnum::SENT,
            'sent_at' => now(),
        ]);
    }
    
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => NotificationStatusEnum::DELIVERED,
            'delivered_at' => now(),
        ]);
    }
    
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => NotificationStatusEnum::FAILED,
            'error_message' => $error,
        ]);
    }
    
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}