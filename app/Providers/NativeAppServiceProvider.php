<?php

namespace App\Providers;

use App\Cats\ServiceManager;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\MenuBar;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        MenuBar::create()
            ->route('menu')
            ->icon(public_path('menuBarIconTemplate.png'))
            ->width(600)
            ->height(600)
            ->blendBackgroundBehindWindow()
            ->alwaysOnTop()
            ->resizable();

        app(ServiceManager::class)->startAutoStartServices();
    }

    public function phpIni(): array
    {
        return [
            'error_reporting' => E_ALL & ~E_DEPRECATED,
        ];
    }
}
