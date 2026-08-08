<?php

namespace App\Modules\DataRoom;

use App\Modules\DataRoom\Domain\Repositories\DataRoomAccessLogRepositoryInterface;
use App\Modules\DataRoom\Domain\Repositories\DataRoomGrantRepositoryInterface;
use App\Modules\DataRoom\Domain\Repositories\NdaAcknowledgmentRepositoryInterface;
use App\Modules\DataRoom\Infrastructure\Repositories\EloquentDataRoomAccessLogRepository;
use App\Modules\DataRoom\Infrastructure\Repositories\EloquentDataRoomGrantRepository;
use App\Modules\DataRoom\Infrastructure\Repositories\EloquentNdaAcknowledgmentRepository;
use Illuminate\Support\ServiceProvider;

/**
 * DataRoom Module's own Service Provider (14_LARAVEL_BLUEPRINT.md §6).
 * No cross-Module event listeners registered here yet — DataRoom does not
 * currently react to Project/Compliance events (a grant is created
 * explicitly via API-005, not automatically on Project publish; that
 * automation, if wanted later, is a scoped future change, not assumed here).
 *
 * WAJIB: register this provider in bootstrap/providers.php / config/app.php.
 */
class DataRoomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DataRoomGrantRepositoryInterface::class, EloquentDataRoomGrantRepository::class);
        $this->app->bind(NdaAcknowledgmentRepositoryInterface::class, EloquentNdaAcknowledgmentRepository::class);
        $this->app->bind(DataRoomAccessLogRepositoryInterface::class, EloquentDataRoomAccessLogRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
