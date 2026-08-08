<?php

namespace App\Modules\DataRoom\Application\Services;

use App\Modules\DataRoom\Domain\Entities\DataRoomGrant;
use App\Modules\DataRoom\Domain\Events\DataRoomDocumentDownloaded;
use App\Modules\DataRoom\Domain\Events\DataRoomDocumentViewed;
use App\Modules\DataRoom\Domain\Policies\AdminNeverBypassesNdaPolicy;
use App\Modules\DataRoom\Domain\Repositories\DataRoomAccessLogRepositoryInterface;
use App\Modules\DataRoom\Domain\Repositories\DataRoomGrantRepositoryInterface;
use App\Modules\DataRoom\Domain\Repositories\NdaAcknowledgmentRepositoryInterface;
use App\Modules\DataRoom\Domain\ValueObjects\DataRoomAccessAction;

/**
 * DataRoomAccessService — "the enforcement point in front of document
 * reads" (06_DOMAIN_MODEL.md §3.3, ADR-002). This is the ONLY class a
 * Controller may ask "is this allowed?" — replaces the Project Owner's
 * DataRoomAccessPolicy sketch (which used raw DB::table() calls and no NDA
 * check at all).
 *
 * SECURITY RULE (Project Owner's brief, WAJIB): "semua access melalui
 * controller, semua access log, semua enforce policy" — this class is how
 * that is actually satisfied: Controllers must call check() before ever
 * touching a file, and must call recordView()/recordDownload() after,
 * never bypassing either step.
 */
class DataRoomAccessService
{
    public function __construct(
        private readonly DataRoomGrantRepositoryInterface $grants,
        private readonly NdaAcknowledgmentRepositoryInterface $acknowledgments,
        private readonly DataRoomAccessLogRepositoryInterface $accessLogs,
        private readonly AdminNeverBypassesNdaPolicy $adminNeverBypassesNdaPolicy,
    ) {
    }

    /**
     * @return array{allowed: bool, grant: ?DataRoomGrant, reason: ?string}
     */
    public function checkView(
        string $documentId,
        string $granteeUserId,
        string $actingUserRole,
        bool $hasLoggedSupportException = false,
    ): array {
        $grant = $this->grants->findForDocumentAndUser($documentId, $granteeUserId);

        if (! $grant) {
            return ['allowed' => false, 'grant' => null, 'reason' => 'No Data Room grant exists for this user/document.'];
        }

        if (! $grant->isActive()) {
            return ['allowed' => false, 'grant' => $grant, 'reason' => 'Grant is revoked or expired.'];
        }

        if ($this->acknowledgments->hasAcknowledgment($grant->id())) {
            return ['allowed' => true, 'grant' => $grant, 'reason' => null];
        }

        // NDA not yet acknowledged (BR-042) — the ONLY bypass is an Admin
        // going through a separately logged support-exception process
        // (BR-047). No other role may proceed past this point.
        if ($this->adminNeverBypassesNdaPolicy->isExemptFromNdaGating($actingUserRole, $hasLoggedSupportException)) {
            return ['allowed' => true, 'grant' => $grant, 'reason' => null];
        }

        return ['allowed' => false, 'grant' => $grant, 'reason' => 'NDA has not been acknowledged for this grant (BR-042).'];
    }

    /** @return array{allowed: bool, grant: ?DataRoomGrant, reason: ?string} */
    public function checkDownload(
        string $documentId,
        string $granteeUserId,
        string $actingUserRole,
        bool $hasLoggedSupportException = false,
    ): array {
        $viewCheck = $this->checkView($documentId, $granteeUserId, $actingUserRole, $hasLoggedSupportException);

        if (! $viewCheck['allowed']) {
            return $viewCheck;
        }

        if (! $viewCheck['grant']->canDownload()) {
            return ['allowed' => false, 'grant' => $viewCheck['grant'], 'reason' => 'Grant permission tier does not allow download.'];
        }

        return $viewCheck;
    }

    public function recordView(string $grantId, ?string $ipAddress): void
    {
        $this->accessLogs->record($grantId, DataRoomAccessAction::Viewed, $ipAddress);
        DataRoomDocumentViewed::dispatch($grantId, $this->grants->find($grantId)?->documentId() ?? '', (string) now());
    }

    public function recordDownload(string $grantId, ?string $ipAddress): void
    {
        $this->accessLogs->record($grantId, DataRoomAccessAction::Downloaded, $ipAddress);
        DataRoomDocumentDownloaded::dispatch($grantId, $this->grants->find($grantId)?->documentId() ?? '', (string) now());
    }
}
