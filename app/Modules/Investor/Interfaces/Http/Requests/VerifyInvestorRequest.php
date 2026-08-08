<?php

namespace App\Modules\Investor\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyInvestorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'verified_by_user_id' => ['required', 'uuid'],
        ];
    }
}
