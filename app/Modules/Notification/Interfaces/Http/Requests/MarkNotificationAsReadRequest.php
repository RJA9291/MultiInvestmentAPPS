<?php

namespace App\Modules\Notification\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Proposed API-028: POST /v1/notifications/{id}/read */
class MarkNotificationAsReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requesting_user_id' => ['required', 'uuid'],
        ];
    }
}
