<?php

namespace App\Modules\Document\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** API-010: POST /v1/documents (locked path — NOT /v1/documents/upload) */
class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // FLAGGED: real RBAC pending Identity Module
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'uuid'],
            'document_type' => ['required', 'string'],
            'file' => ['required', 'file'],
            'uploaded_by_user_id' => ['required', 'uuid'], // FLAGGED stand-in, same pattern as other Modules
        ];
    }
}
