<?php

namespace App\Modules\Document;

use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Document\Domain\Repositories\DocumentVersionRepositoryInterface;
use App\Modules\Document\Infrastructure\Repositories\EloquentDocumentRepository;
use App\Modules\Document\Infrastructure\Repositories\EloquentDocumentVersionRepository;
use App\Modules\Document\Infrastructure\Storage\FileStorageGatewayInterface;
use App\Modules\Document\Infrastructure\Storage\LocalFileStorageGateway;
use Illuminate\Support\ServiceProvider;

/**
 * Document Module's own Service Provider (14_LARAVEL_BLUEPRINT.md §6).
 *
 * No cross-Module event listeners registered here — Document Module does
 * not react to any other Module's events in this build. The AI-on-upload
 * hook is the reverse direction (AI Module reacts to THIS Module's
 * DocumentUploaded event) and is registered in AIServiceProvider, per
 * PDL-020's "Module reacts to another Module's event via its OWN
 * ServiceProvider" pattern already used for DataRoom/Compliance.
 *
 * WAJIB: register this provider in bootstrap/providers.php / config/app.php.
 */
class DocumentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DocumentRepositoryInterface::class, EloquentDocumentRepository::class);
        $this->app->bind(DocumentVersionRepositoryInterface::class, EloquentDocumentVersionRepository::class);
        $this->app->bind(FileStorageGatewayInterface::class, LocalFileStorageGateway::class);
    }

    public function boot(): void
    {
        //
    }
}
