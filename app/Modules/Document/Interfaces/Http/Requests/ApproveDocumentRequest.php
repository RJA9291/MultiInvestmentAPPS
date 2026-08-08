<?php

namespace App\Modules\Document\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Proposed API-019: POST /v1/documents/{id}/approve */
class ApproveDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approved_by_user_id' => ['required', 'uuid'],
        ];
    }
}
