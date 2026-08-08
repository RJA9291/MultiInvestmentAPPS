<?php

namespace App\Modules\Document\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Proposed API-021: DELETE /v1/documents/{id} (soft delete, BR-032) */
class DeleteDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'deleted_by_user_id' => ['required', 'uuid'],
        ];
    }
}
