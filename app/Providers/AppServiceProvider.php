<?php

namespace App\Providers;

use App\Cats\ServiceManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ServiceManager::class);
    }

    public function boot(): void
    {
        //
    }
}
