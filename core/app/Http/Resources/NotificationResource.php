<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscriber_id' => $this->subscriber_id,
            'channel' => $this->channel->label(),
            'message' => $this->message,
            'status' => $this->status->label(),
            'priority' => $this->priority,
            'attempts' => $this->attempts,
            'error_message' => $this->error_message,
            'sent_at' => $this->sent_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}