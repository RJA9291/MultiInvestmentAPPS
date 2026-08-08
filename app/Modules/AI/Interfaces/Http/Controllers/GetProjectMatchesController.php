<?php

namespace App\Modules\AI\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\AI\Domain\Repositories\InvestorProjectMatchRepositoryInterface;
use Illuminate\Http\Request;

/**
 * Proposed API-033: GET /v1/projects/{id}/matches?top=5.
 *
 * Project-Owner-facing "Potential Investors" view (brief §8). `{id}` is the
 * Project id.
 */
class GetProjectMatchesController
{
    public function __invoke(string $id, Request $request, InvestorProjectMatchRepositoryInterface $matches)
    {
        $top = min(max((int) $request->query('top', 5), 1), 50);

        $results = array_map(
            fn ($result) => $result->toApiPayload(),
            $matches->findTopForProject($id, $top),
        );

        return ApiResponse::success($results);
    }
}
