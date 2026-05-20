<?php

namespace App\Services;

use App\Enums\ChannelEnum;
use App\Models\Notification;
use App\Services\Providers\EmailProvider;
use App\Services\Providers\SmsProvider;
use Illuminate\Support\Facades\Log;

class NotificationSender
{
    protected SmsProvider $smsProvider;
    protected EmailProvider $emailProvider;
    
    public function __construct()
    {
        $this->smsProvider = new SmsProvider();
        $this->emailProvider = new EmailProvider();
    }
    
    public function send(Notification $notification): void
    {
        $notification->incrementAttempts();
        
        $result = match($notification->channel) {
            ChannelEnum::SMS => $this->smsProvider->send(
                (string) $notification->subscriber_id,
                $notification->message
            ),
            ChannelEnum::EMAIL => $this->emailProvider->send(
                (string) $notification->subscriber_id,
                $notification->message
            ),
        };
        
        if ($result['success']) {
            $notification->markAsSent();
            
            // Если провайдер сразу подтвердил доставку
            if (($result['status'] ?? '') === 'delivered') {
                $notification->markAsDelivered();
            }
            
            Log::info("Notification {$notification->id} sent successfully");
        } else {
            $notification->markAsFailed($result['error'] ?? 'Unknown error');
            Log::error("Notification {$notification->id} failed: " . ($result['error'] ?? 'unknown'));
        }
    }
}