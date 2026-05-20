<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => 'required|string|in:sms,email',
            'text' => 'required|string|max:1000',
            'subscriber_ids' => 'required|array|min:1|max:1000',
            'subscriber_ids.*' => 'integer|min:1',
            'is_critical' => 'boolean',
        ];
    }
}