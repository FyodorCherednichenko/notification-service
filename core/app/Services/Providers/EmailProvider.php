<?php

namespace App\Services\Providers;

use Illuminate\Support\Facades\Log;

class EmailProvider
{
    public function send(string $email, string $message): array
    {
        $success = rand(1, 10) > 1;
        
        Log::info("Email Provider: sending to {$email}", [
            'message' => $message,
            'success' => $success
        ]);
        
        if ($success) {
            return [
                'success' => true,
                'provider_id' => 'email_' . uniqid(),
                'status' => 'delivered'
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Email service rate limit exceeded'
        ];
    }
}