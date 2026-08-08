<?php

namespace App\Modules\DataRoom\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\DataRoom\Application\Services\DataRoomAccessService;
use App\Modules\DataRoom\Interfaces\Http\Requests\AccessDocumentRequest;
use Illuminate\Http\Request;

/**
 * Proposed API-015: GET /v1/projects/{id}/documents/{docId}/download
 *
 * DOWNLOAD CONTROL (WAJIB, Project Owner's brief §9): denies with 403 if
 * the grant's permission_tier is not Downloadable — enforced via
 * DataRoomAccessService::checkDownload(), not a raw `if (!$grant->can_download)`
 * check scattered in the Controller.
 */
class DownloadDocumentController
{
    public function __invoke(
        string $id,
        string $docId,
        AccessDocumentRequest $request,
        DataRoomAccessService $access,
        Request $rawRequest,
    ) {
        $validated = $request->validated();

        $check = $access->checkDownload(
            documentId: $docId,
            granteeUserId: $validated['grantee_user_id'],
            actingUserRole: $validated['acting_user_role'],
            hasLoggedSupportException: $validated['has_logged_support_exception'] ?? false,
        );

        if (! $check['allowed']) {
            return ApiResponse::error('DATA_ROOM_DOWNLOAD_DENIED', $check['reason'], status: 403);
        }

        $access->recordDownload($check['grant']->id(), $rawRequest->ip());

        return ApiResponse::success([
            'document_id' => $docId,
            'note' => 'File streaming is not yet wired to a Document Module storage backend in this Sprint.',
        ]);
    }
}
