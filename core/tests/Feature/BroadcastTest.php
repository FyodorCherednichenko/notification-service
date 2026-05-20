<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class BroadcastTest extends TestCase
{
    use RefreshDatabase;
    

    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushall();
    }

    public function test_broadcast(): void
    {
        $payload = [
            'channel' => 'sms',
            'text' => 'Test message',
            'subscriber_ids' => [10, 20, 30],
            'is_critical' => false,
        ];

        $response = $this->postJson('/api/broadcast', $payload);

        $response->assertStatus(202);
        
        $response->assertJsonStructure([
            'message',
            'notifications',
            'duplicates_skipped',
            'total_queued',
            'total_duplicates',
        ]);

        $response->assertJsonCount(3, 'notifications');
        
        $response->assertJson([
            'duplicates_skipped' => [],
            'total_duplicates' => 0,
            'total_queued' => 3,
        ]);

        $this->assertDatabaseCount('notifications', 3);
        $this->assertDatabaseHas('notifications', [
            'subscriber_id' => 10,
            'message' => 'Test message',
            'channel' => 1,
            'status' => 1,
            'priority' => 0,
        ]);
    }
}
