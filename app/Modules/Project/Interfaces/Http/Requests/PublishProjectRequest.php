<?php

namespace App\Modules\Project\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API-011 (12_API_STANDARD.md v1.2.0 §15 — locked as Draft): POST
 * /v1/projects/{id}/publish, completing the Draft->Published flow.
 */
class PublishProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approved_document_count' => ['required', 'integer', 'min:0'],
        ];
    }
}
