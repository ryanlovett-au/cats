<?php

namespace App\Providers;

use App\Listeners\MenuItemClickedListener;
use App\Listeners\UpdateDownloadedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Native\Desktop\Events\AutoUpdater\UpdateDownloaded;
use Native\Desktop\Events\Menu\MenuItemClicked;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MenuItemClicked::class => [
            MenuItemClickedListener::class,
        ],
        UpdateDownloaded::class => [
            UpdateDownloadedListener::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
