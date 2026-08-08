<?php

namespace App\Modules\Investor\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterInvestorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // FLAGGED: real RBAC pending Identity Module
    }

    public function rules(): array
    {
        return [
            'investor_user_id' => ['required', 'uuid'], // FLAGGED stand-in, same pattern as other Modules
            'investor_type' => ['required', 'in:INDIVIDUAL,COMPANY'],
            'company_name' => ['nullable', 'string'],
            'investment_range' => ['nullable', 'string'],
            'preferred_industry' => ['nullable', 'string'],
            'risk_appetite' => ['nullable', 'string'],
        ];
    }
}
