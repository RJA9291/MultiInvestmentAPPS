<?php

namespace App\Modules\Document\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Proposed API-020: POST /v1/documents/{id}/reject — named "reject" to
 * match the Project Owner's expected endpoint shape, but internally maps to
 * DocumentScanFailed (EVT-020), the locked catalog's real negative
 * counterpart to DocumentApproved — see DocumentApprovalService's doc comment.
 */
class RejectDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'error_code' => ['required', 'string'], // EVT-020's ErrorCode field
        ];
    }
}
