<?php

namespace App\Modules\Analytics;

use App\Modules\Analytics\Application\Listeners\RecordContentView;
use App\Modules\Analytics\Domain\Events\DocumentViewed;
use App\Modules\Analytics\Domain\Events\ProjectViewed;
use App\Modules\Analytics\Domain\Repositories\ContentViewCountRepositoryInterface;
use App\Modules\Analytics\Infrastructure\Repositories\EloquentContentViewCountRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Analytics Module's own Service Provider (14_LARAVEL_BLUEPRINT.md §6).
 *
 * Analytics is NOT a Domain Bounded Context (06_DOMAIN_MODEL.md §1 — no
 * Aggregates, no invariants of its own) — it sits alongside Platform
 * Services/Administration as a cross-cutting reporting layer (PDL-021:
 * infrastructure-only, no business logic; new PDL-060 formalizes the
 * read-only Repository fan-in pattern this Module uses). It owns exactly
 * one small write-side concern (`content_view_counts`, DB-048) and three
 * read-only dashboard query Services that depend on OTHER Modules'
 * Repository interfaces directly — the interface-only half of PDL-020,
 * the same pattern already used for `ApproveAccessRequestService`
 * (Investor Module) calling `GrantDataRoomAccessService` (DataRoom
 * Module). No Event::listen wiring is needed for the three Dashboard
 * Services themselves — they are called synchronously, on demand, per
 * request, not reactively.
 *
 * WAJIB: register this provider in bootstrap/providers.php / config/app.php.
 */
class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContentViewCountRepositoryInterface::class, EloquentContentViewCountRepository::class);
    }

    public function boot(): void
    {
        Event::listen(ProjectViewed::class, [RecordContentView::class, 'handleProjectViewed']);
        Event::listen(DocumentViewed::class, [RecordContentView::class, 'handleDocumentViewed']);
    }
}
