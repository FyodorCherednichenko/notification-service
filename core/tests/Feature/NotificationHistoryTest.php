<?php

namespace Tests\Feature;

use App\Enums\ChannelEnum;
use App\Enums\NotificationStatusEnum;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NotificationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_notifications_for_subscriber()
    {
        $subscriberId = 1;
        
        Notification::create([
            'subscriber_id' => $subscriberId,
            'channel' => ChannelEnum::SMS->value,
            'message' => 'Hello 1',
            'status' => NotificationStatusEnum::DELIVERED,
            'priority' => 0,
        ]);
        
        Notification::create([
            'subscriber_id' => $subscriberId,
            'channel' => ChannelEnum::EMAIL->value,
            'message' => 'Hello 2',
            'status' => NotificationStatusEnum::SENT,
            'priority' => 10,
        ]);
        
        Notification::create([
            'subscriber_id' => $subscriberId,
            'channel' => ChannelEnum::SMS->value,
            'message' => 'Hello 3',
            'status' => NotificationStatusEnum::FAILED,
            'priority' => 0,
            'error_message' => 'Provider error',
        ]);

        $response = $this->getJson("/api/subscriber/{$subscriberId}/history");

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');

        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'subscriber_id',
                    'channel',
                    'message',
                    'status',
                    'priority',
                    'attempts',
                    'error_message',
                    'sent_at',
                    'delivered_at',
                    'created_at',
                    'updated_at',
                ]
            ],
            'links',
            'meta',
        ]);

        $response->assertJsonFragment([
            'subscriber_id' => 1,
            'message' => 'Hello 1',
            'channel' => 'sms',
            'status' => 'delivered',
        ]);

        $response->assertJsonFragment([
            'message' => 'Hello 2',
            'channel' => 'email',
            'status' => 'sent',
            'priority' => 10,
        ]);

        $response->assertJsonFragment([
            'message' => 'Hello 3',
            'status' => 'failed',
            'error_message' => 'Provider error',
        ]);
    }
}
