<?php

namespace App\Modules\AI\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\AI\Domain\Repositories\InvestorProjectMatchRepositoryInterface;
use Illuminate\Http\Request;

/**
 * Proposed API-032: GET /v1/investors/{id}/matches?top=5.
 *
 * `{id}` is the InvestorProfile id. `top` defaults to 5 per the brief's own
 * "Top Matches Only" example; capped at 50 to avoid an unbounded query.
 */
class GetInvestorMatchesController
{
    public function __invoke(string $id, Request $request, InvestorProjectMatchRepositoryInterface $matches)
    {
        $top = min(max((int) $request->query('top', 5), 1), 50);

        $results = array_map(
            fn ($result) => $result->toApiPayload(),
            $matches->findTopForInvestor($id, $top),
        );

        return ApiResponse::success($results);
    }
}
