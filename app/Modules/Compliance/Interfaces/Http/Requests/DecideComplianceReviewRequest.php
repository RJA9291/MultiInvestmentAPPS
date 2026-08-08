<?php

namespace App\Modules\Compliance\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** API-004: POST /v1/compliance-reviews/{id}/decide */
class DecideComplianceReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // FLAGGED: real RBAC pending Identity Module — see ComplianceOfficerOnlyPolicy
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected'],
            // FLAGGED: acting_user_role/acting_user_id are stand-ins for real
            // authenticated-user resolution until Identity Module / AuthorizationGate exists.
            'acting_user_role' => ['required', 'string'],
            'acting_user_id' => ['required', 'uuid'], // persisted as decision_made_by, PDL-059
            'decision_source' => ['nullable', 'in:HUMAN,AI_ASSISTED'], // PDL-059, provenance only, defaults to HUMAN
            'comments' => ['nullable', 'array'],
            'comments.*' => ['string'],
        ];
    }
}
