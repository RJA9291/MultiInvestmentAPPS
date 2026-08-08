<?php

namespace App\Modules\DataRoom\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\DataRoom\Application\Services\DataRoomAccessService;
use App\Modules\DataRoom\Application\Services\WatermarkService;
use App\Modules\DataRoom\Interfaces\Http\Requests\AccessDocumentRequest;
use Illuminate\Http\Request;

/**
 * API-006: GET /v1/projects/{id}/documents/{docId}
 * {id} = projectId (kept for URL/routing context per the locked path); the
 * actual grant lookup is per-document+user (DB-008's Business Key), not
 * per-project — see DataRoomGrantRepositoryInterface's doc comment.
 *
 * SECURITY RULE (WAJIB): never expose a raw file URL. This endpoint
 * currently returns the access decision + computed watermark text as JSON
 * metadata, NOT a file stream or a storage URL — actual file bytes require
 * the Document Module's storage abstraction, which has not been built this
 * Sprint (flagged, not fabricated; see WatermarkService's doc comment for
 * the equivalent flag on watermark *application* vs. watermark *text*).
 */
class AccessDocumentController
{
    public function __invoke(
        string $id,
        string $docId,
        AccessDocumentRequest $request,
        DataRoomAccessService $access,
        WatermarkService $watermark,
        Request $rawRequest,
    ) {
        $validated = $request->validated();

        $check = $access->checkView(
            documentId: $docId,
            granteeUserId: $validated['grantee_user_id'],
            actingUserRole: $validated['acting_user_role'],
            hasLoggedSupportException: $validated['has_logged_support_exception'] ?? false,
        );

        if (! $check['allowed']) {
            return ApiResponse::error('DATA_ROOM_ACCESS_DENIED', $check['reason'], status: 403);
        }

        $access->recordView($check['grant']->id(), $rawRequest->ip());

        return ApiResponse::success([
            'document_id' => $docId,
            'permission_tier' => $check['grant']->permissionTier()->value,
            'can_download' => $check['grant']->canDownload(),
            'watermark_text' => $watermark->computeWatermarkText($validated['grantee_user_id'], now()),
            'note' => 'File streaming is not yet wired to a Document Module storage backend in this Sprint.',
        ]);
    }
}
