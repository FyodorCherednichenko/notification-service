<?php

namespace App\Http\Controllers;

use App\Enums\ChannelEnum;
use App\Enums\NotificationStatusEnum;
use App\Http\Requests\BroadcastRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Queue\Connectors\RabbitMQHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class NotificationController extends Controller
{
    protected RabbitMQHandler $rabbit;
    
    public function __construct()
    {
        $this->rabbit = new RabbitMQHandler();
    }
    
    public function broadcast(BroadcastRequest $request)
    {
        $channel = ChannelEnum::fromString($request->channel);
        $priority = $request->boolean('is_critical') ? 10 : 0;
        $text = $request->text;
        
        $notifications = [];
        $duplicates = [];
        
        foreach ($request->subscriber_ids as $subscriberId) {
            $messageHash = md5($subscriberId . $text . $channel->value);
            $dedupKey = "notification:{$messageHash}";

            if (Redis::setnx($dedupKey, 1)) {
                Redis::expire($dedupKey, 86400);

                $notification = Notification::create([
                    'subscriber_id' => $subscriberId,
                    'channel' => $channel,
                    'message' => $text,
                    'status' => NotificationStatusEnum::QUEUED,
                    'priority' => $priority,
                ]);

                $this->rabbit->publish(
                    'notifications',
                    json_encode(['notification_id' => $notification->id]),
                    $priority
                );

                $notifications[] = $notification;
            } else {
                $duplicates[] = $subscriberId;
            }
        }
        
        return response()->json([
            'message' => 'Broadcast processed',
            'notifications' => NotificationResource::collection(collect($notifications)),
            'duplicates_skipped' => $duplicates,
            'total_queued' => count($notifications),
            'total_duplicates' => count($duplicates),
        ], 202);
    }
    
    /**
     * GET /api/subscriber/{id}/history - история статусов
     */
    public function history(Request $request, int $subscriberId)
    {
        $notifications = Notification::where('subscriber_id', $subscriberId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return NotificationResource::collection($notifications);
    }
}