<?php

namespace App\Modules\Investor\Application\Services;

use App\Modules\Investor\Domain\Entities\AccessRequest;
use App\Modules\Investor\Domain\Events\AccessRequestRejected;
use App\Modules\Investor\Domain\Repositories\AccessRequestRepositoryInterface;
use RuntimeException;

/** RejectAccessRequestService — proposed API-026's reject counterpart. No Data Room action at all. */
class RejectAccessRequestService
{
    public function __construct(private readonly AccessRequestRepositoryInterface $accessRequests)
    {
    }

    public function execute(string $accessRequestId, string $decidedBy): AccessRequest
    {
        $request = $this->accessRequests->find($accessRequestId);

        if (! $request) {
            throw new RuntimeException("Access request {$accessRequestId} not found.");
        }

        $request->reject($decidedBy);
        $this->accessRequests->save($request);

        AccessRequestRejected::dispatch($request->id(), $request->projectId(), $request->investorUserId(), $decidedBy);

        return $request;
    }
}
