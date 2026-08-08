<?php

namespace App\Modules\DataRoom\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Proposed API-013: PATCH /v1/data-room-grants/{id}/revoke */
class RevokeDataRoomAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'revoked_by_user_id' => ['required', 'uuid'],
        ];
    }
}
