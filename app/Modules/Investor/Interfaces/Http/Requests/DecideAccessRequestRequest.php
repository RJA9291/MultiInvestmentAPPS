<?php

namespace App\Modules\Investor\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Shared by both approve and reject — reject simply ignores `decided_by_user_role`. */
class DecideAccessRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decided_by_user_id' => ['required', 'uuid'],
            'decided_by_user_role' => ['required', 'string'], // e.g. 'business_owner', 'compliance_officer' — DefaultViewOnlyPolicy check
        ];
    }
}
