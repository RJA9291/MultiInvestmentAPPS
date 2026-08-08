<?php

namespace App\Modules\Investor\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestProjectAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'investor_user_id' => ['required', 'uuid'],
        ];
    }
}
