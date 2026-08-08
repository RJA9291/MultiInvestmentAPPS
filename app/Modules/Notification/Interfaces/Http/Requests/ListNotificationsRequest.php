<?php

namespace App\Modules\Notification\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Proposed API-027: GET /v1/notifications?recipient_user_id=... */
class ListNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // FLAGGED: real RBAC/session-derived recipient pending Identity Module
    }

    public function rules(): array
    {
        return [
            'recipient_user_id' => ['required', 'uuid'],
        ];
    }
}
