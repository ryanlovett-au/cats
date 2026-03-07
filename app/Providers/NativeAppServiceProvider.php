<?php

namespace App\Providers;

use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\MenuBar;

use App\Cats\MainMenu;
use App\Cats\ServiceManager;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        MenuBar::create()
            ->withContextMenu(MainMenu::show())
            ->onlyShowContextMenu()
            ->icon(storage_path('app/menuBarIconTemplate.png'));

        app(ServiceManager::class)->startAutoStartServices();
    }

    public function phpIni(): array
    {
        return [
            'error_reporting' => E_ALL & ~E_DEPRECATED,
        ];
    }
}
