<?php

namespace App\Console\Commands;

use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Queue\Connectors\RabbitMQHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RabbitMQWorker extends Command
{
    protected $signature = 'rabbitmq:work {queue=notifications}';
    protected $description = 'Process messages from RabbitMQ queue';

    public function handle()
    {
        $queue = $this->argument('queue');
        
        $this->info("Starting RabbitMQ worker for queue: {$queue}");
        
        $rabbit = new RabbitMQHandler();
        
        $rabbit->consume($queue, function(string $message) {
            $data = json_decode($message, true);
            
            if (!isset($data['notification_id'])) {
                $this->error('Invalid message: missing notification_id');
                return;
            }
            
            $notificationId = $data['notification_id'];
            $processKey = "processing:{$notificationId}";

            if (!Redis::setnx($processKey, 1)) {
                $this->warn("Notification {$notificationId} is already being processed, skipping");
                return;
            }

            Redis::expire($processKey, 300);

            $notification = Notification::find($notificationId);
            
            if (!$notification) {
                $this->error("Notification {$notificationId} not found");
                Redis::del($processKey);
                return;
            }
            
            $this->info("Processing notification {$notification->id}");
            
            try {
                dispatch(new SendNotificationJob($notification));
                Redis::del($processKey);
            } catch (\Exception $e) {
                $this->error("Failed to dispatch job: {$e->getMessage()}");
                Redis::del($processKey);
                
                // Отправляем обратно в очередь для повтора
                if ($notification->attempts < 3) {
                    $rabbit->publish($queue, $message, $notification->priority);
                }
            }
        });
    }
}