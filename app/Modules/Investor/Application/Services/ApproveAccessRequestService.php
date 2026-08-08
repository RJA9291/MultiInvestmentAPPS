<?php

namespace App\Modules\Investor\Application\Services;

use App\Modules\DataRoom\Application\Services\GrantDataRoomAccessService;
use App\Modules\DataRoom\Domain\ValueObjects\PermissionTier;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Investor\Domain\Entities\AccessRequest;
use App\Modules\Investor\Domain\Events\AccessRequestApproved;
use App\Modules\Investor\Domain\Repositories\AccessRequestRepositoryInterface;
use RuntimeException;

/**
 * ApproveAccessRequestService — proposed API-026 (POST /v1/access-requests/{id}/approve).
 *
 * THE key reconciliation point in the Investor Module brief. The Project
 * Owner's sketch called `GrantDataRoomAccessService::execute(['project_id'
 * => ..., 'user_id' => ..., 'access_level' => 'VIEW_WITH_WATERMARK',
 * 'can_download' => false, 'expires_at' => ...])` exactly once per approval
 * — but the ALREADY-LOCKED `DataRoomGrant` Aggregate (06_DOMAIN_MODEL.md
 * §3.3, DB-008) is per-DOCUMENT, with a Business Key of
 * (`document_id`, `grantee_user_id`), not per-project, and its
 * `PermissionTier` is a locked 2-value enum (`view_only`/`downloadable`) —
 * there is no `VIEW_WITH_WATERMARK`/`can_download` flag pair (watermarking
 * is a rendering behavior applied to all `ViewOnly` content, not a tier —
 * already decided during the Data Room Module build).
 *
 * Reconciled here by fanning ONE AccessRequest approval out into ONE
 * DataRoomGrant per currently-approved Document under that Project — a
 * cross-Module, interface-only read (PDL-020: `DocumentRepositoryInterface`)
 * followed by calls into the already-built, unmodified
 * `GrantDataRoomAccessService` (API-005). `PermissionTier::ViewOnly` is
 * used unconditionally (matching the sketch's own intent of read-only
 * access), with the same 7-day `expires_at` the sketch specified.
 *
 * FLAGGED GAP, not fabricated: this grants VIEWING eligibility only. NDA
 * acknowledgment (BR-042/BR-133) is still a separate, mandatory step the
 * investor must complete via the already-built `AcknowledgeNdaController`
 * before `DataRoomAccessService::checkView()` will actually let them see a
 * document — approving an AccessRequest does not bypass that gate, by design.
 */
class ApproveAccessRequestService
{
    private const GRANT_DURATION_DAYS = 7;

    public function __construct(
        private readonly AccessRequestRepositoryInterface $accessRequests,
        private readonly DocumentRepositoryInterface $documents,
        private readonly GrantDataRoomAccessService $grantDataRoomAccess,
    ) {
    }

    public function execute(string $accessRequestId, string $decidedBy, string $decidedByRole): AccessRequest
    {
        $request = $this->find($accessRequestId);

        $request->approve($decidedBy);
        $this->accessRequests->save($request);

        $approvedDocuments = array_filter(
            $this->documents->findByProjectId($request->projectId()),
            fn ($document) => $document->isApproved(),
        );

        foreach ($approvedDocuments as $document) {
            $this->grantDataRoomAccess->execute(
                documentId: $document->id(),
                granteeUserId: $request->investorUserId(),
                grantedByUserId: $decidedBy,
                grantedByUserRole: $decidedByRole,
                permissionTier: PermissionTier::ViewOnly,
                expiresAt: now()->addDays(self::GRANT_DURATION_DAYS),
            );
        }

        AccessRequestApproved::dispatch($request->id(), $request->projectId(), $request->investorUserId(), $decidedBy);

        return $request;
    }

    private function find(string $accessRequestId): AccessRequest
    {
        $request = $this->accessRequests->find($accessRequestId);

        if (! $request) {
            throw new RuntimeException("Access request {$accessRequestId} not found.");
        }

        return $request;
    }
}
