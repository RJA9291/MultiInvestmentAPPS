<?php

namespace App\Modules\DataRoom\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Proposed API-014: POST /v1/data-room-grants/{id}/nda-acknowledgment */
class AcknowledgeNdaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nda_version_hash' => ['required', 'string'], // BR-133 — a content hash, never a boolean
        ];
    }
}
