<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\NotificationSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;
    
    public $tries = 3;
    public $backoff = [5, 15, 30];
    
    protected Notification $notification;
    
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }
    
    public function handle(NotificationSender $sender): void
    {
        $sender->send($this->notification);
    }
    
    public function failed(\Throwable $e): void
    {
        $this->notification->markAsFailed($e->getMessage());
    }
}