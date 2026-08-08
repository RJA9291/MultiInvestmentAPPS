<?php

namespace App\Modules\Document\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Proposed API-018: POST /v1/documents/{id}/versions */
class UploadDocumentVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
            'created_by_user_id' => ['required', 'uuid'],
        ];
    }
}
