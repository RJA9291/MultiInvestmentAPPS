<?php

namespace App\Modules\DataRoom\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** API-005: POST /v1/data-room-grants */
class GrantDataRoomAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // FLAGGED: real RBAC pending Identity Module
    }

    public function rules(): array
    {
        return [
            'document_id' => ['required', 'uuid'],
            'grantee_user_id' => ['required', 'uuid'],
            // FLAGGED: stand-ins for real authenticated-user resolution, same
            // pattern as DecideComplianceReviewRequest's acting_user_id/role.
            'granted_by_user_id' => ['required', 'uuid'],
            'granted_by_user_role' => ['required', 'string'],
            'permission_tier' => ['nullable', 'in:view_only,downloadable'],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
