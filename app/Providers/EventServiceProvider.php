<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Native\Laravel\Events\Menu\MenuItemClicked;

use App\Listeners\MenuItemClickedListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MenuItemClicked::class => [
            MenuItemClickedListener::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
