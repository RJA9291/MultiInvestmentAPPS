<?php

namespace App\Modules\Project\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** API-001: POST /v1/projects */
class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // FLAGGED: real authorization pending Identity Module / AuthorizationGate
    }

    public function rules(): array
    {
        return [
            'owner_user_id' => ['required', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }
}
