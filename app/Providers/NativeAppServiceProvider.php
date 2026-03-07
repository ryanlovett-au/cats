<?php

namespace App\Providers;

use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\MenuBar;

use App\Cats\ServiceManager;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        MenuBar::create()
            ->route('menu')
            ->icon(storage_path('app/menuBarIconTemplate.png'))
            ->width(280)
            ->height(600)
            ->blendBackgroundBehindWindow()
            ->resizable(false)
            ->alwaysOnTop();

        app(ServiceManager::class)->startAutoStartServices();
    }

    public function phpIni(): array
    {
        return [
            'error_reporting' => E_ALL & ~E_DEPRECATED,
        ];
    }
}
