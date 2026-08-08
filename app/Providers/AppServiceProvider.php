<?php

namespace App\Providers;

use App\Core\Shared\Exceptions\ApiExceptionHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // PDL-045 error shape, platform-wide (see ApiExceptionHandler's own docblock).
        $this->app->singleton(ExceptionHandler::class, ApiExceptionHandler::class);
    }

    public function boot(): void
    {
        //
    }
}
