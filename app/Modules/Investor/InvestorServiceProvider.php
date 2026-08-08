<?php

namespace App\Modules\Investor;

use App\Modules\Investor\Domain\Repositories\AccessRequestRepositoryInterface;
use App\Modules\Investor\Domain\Repositories\InvestorProfileRepositoryInterface;
use App\Modules\Investor\Infrastructure\Repositories\EloquentAccessRequestRepository;
use App\Modules\Investor\Infrastructure\Repositories\EloquentInvestorProfileRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Investor Module's own Service Provider (14_LARAVEL_BLUEPRINT.md §6).
 *
 * No cross-Module event listeners registered here — this Module does not
 * react to another Module's events (it is a downstream consumer of Project's
 * `findPublished()`/Document's `findByProjectId()` via interface calls, and
 * an upstream trigger of DataRoom's `GrantDataRoomAccessService`, both
 * synchronous interface calls per PDL-020's "interface-only" half, not
 * event reactions).
 *
 * WAJIB: register this provider in bootstrap/providers.php / config/app.php.
 */
class InvestorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvestorProfileRepositoryInterface::class, EloquentInvestorProfileRepository::class);
        $this->app->bind(AccessRequestRepositoryInterface::class, EloquentAccessRequestRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
