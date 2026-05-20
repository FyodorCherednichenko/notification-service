<?php

namespace Tests\Feature;

use App\Enums\ChannelEnum;
use App\Enums\NotificationStatusEnum;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Queue\Connectors\RabbitMQHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class NotificationProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushall();
    }

    public function test_notification_is_processed_and_status_changes()
    {
        $notification = Notification::create([
            'subscriber_id' => 123,
            'channel' => ChannelEnum::SMS->value,
            'message' => 'Test message',
            'status' => NotificationStatusEnum::QUEUED,
            'priority' => 0,
        ]);
        
        $rabbit = new RabbitMQHandler();
        $rabbit->publish('notifications', json_encode(['notification_id' => $notification->id]), 0);

        $rabbit->consume('notifications', function($message) {
            $data = json_decode($message, true);
            dispatch(new SendNotificationJob(Notification::find($data['notification_id'])));
        }, 1);

        $notification->refresh();
        $this->assertNotEquals(NotificationStatusEnum::QUEUED, $notification->status);
    }
}
