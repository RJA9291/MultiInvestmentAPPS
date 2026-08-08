<?php

namespace App\Modules\DataRoom\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared by AccessDocumentController (API-006) and DownloadDocumentController
 * (proposed API-015) — GET requests, so these are query parameters.
 * FLAGGED: grantee_user_id/acting_user_role are stand-ins for real
 * authenticated-user resolution, same pattern used throughout this Sprint's
 * other Modules pending the Identity Module.
 */
class AccessDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grantee_user_id' => ['required', 'uuid'],
            'acting_user_role' => ['required', 'string'],
            // BR-047: an Admin's ONLY path around NDA gating — must already
            // be true from a real, separately logged support-exception
            // process (Administration Context's AdminAccessGrant, not built
            // this Sprint). Accepting it as a request flag here is a
            // deliberate placeholder for that missing enforcement, not a
            // real bypass mechanism — see AdminNeverBypassesNdaPolicy's doc
            // comment on why isExemptFromNdaGating() is conservative by default.
            'has_logged_support_exception' => ['nullable', 'boolean'],
        ];
    }
}
