<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class DuplicateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushall();
    }

    public function test_duplicate_request_is_skipped()
    {
        $payload = [
            'channel' => 'sms',
            'text' => 'Duplicate test message',
            'subscriber_ids' => [100],
            'is_critical' => false,
        ];

        $firstResponse = $this->postJson('/api/broadcast', $payload);
        $secondResponse = $this->postJson('/api/broadcast', $payload);

        $firstResponse->assertStatus(202);
        $firstResponse->assertJsonCount(1, 'notifications');
        $firstResponse->assertJson([
            'total_queued' => 1,
            'total_duplicates' => 0,
        ]);

        $secondResponse->assertStatus(202);
        $secondResponse->assertJsonCount(0, 'notifications');  // 0 новых
        $secondResponse->assertJson([
            'total_queued' => 0,
            'total_duplicates' => 1,
            'duplicates_skipped' => [100],
        ]);

        $this->assertDatabaseCount('notifications', 1);
    }
}
