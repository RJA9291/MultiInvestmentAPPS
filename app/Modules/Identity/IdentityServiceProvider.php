<?php

namespace App\Modules\Identity;

use App\Modules\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Identity\Infrastructure\Repositories\EloquentUserRepository;
use App\Modules\Identity\Infrastructure\Services\JwtTokenCodec;
use App\Modules\Identity\Interfaces\Console\Commands\CreateAccountCommand;
use App\Modules\Identity\Interfaces\Http\Middleware\EnsureRole;
use App\Modules\Identity\Interfaces\Http\Middleware\JwtAuthenticate;
use Illuminate\Support\ServiceProvider;

/**
 * Identity Module's own Service Provider (14_LARAVEL_BLUEPRINT.md §6).
 *
 * Binds the repository, the JWT codec (secret/TTL from config/security.php,
 * never hardcoded per SEC-011), the `accounts:create` Artisan command, and
 * the two route middleware aliases (`jwt.auth`, `role`) every other Module's
 * routes now depend on.
 *
 * WAJIB: register this provider in bootstrap/providers.php.
 */
class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->singleton(JwtTokenCodec::class, function ($app) {
            return new JwtTokenCodec(
                secret: config('security.jwt_secret'),
                ttlMinutes: (int) config('security.session_ttl_minutes', 60),
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateAccountCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('jwt.auth', JwtAuthenticate::class);
        $router->aliasMiddleware('role', EnsureRole::class);
    }
}
