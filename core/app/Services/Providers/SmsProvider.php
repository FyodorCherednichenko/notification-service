<?php

namespace App\Services\Providers;

use Illuminate\Support\Facades\Log;

class SmsProvider
{
    public function send(string $phone, string $message): array
    {
        $success = rand(1, 10) > 2; // 80% успеха, 20% ошибок
        
        Log::info("SMS Provider: sending to {$phone}", [
            'message' => $message,
            'success' => $success
        ]);
        
        if ($success) {
            return [
                'success' => true,
                'provider_id' => 'sms_' . uniqid(),
                'status' => 'sent'
            ];
        }
        
        return [
            'success' => false,
            'error' => 'SMS gateway temporary unavailable'
        ];
    }
}