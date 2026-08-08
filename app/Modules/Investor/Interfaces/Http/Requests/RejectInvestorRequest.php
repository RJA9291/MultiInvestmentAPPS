<?php

namespace App\Modules\Investor\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectInvestorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejected_by_user_id' => ['required', 'uuid'],
        ];
    }
}
