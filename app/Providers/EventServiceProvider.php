<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Native\Laravel\Events\MenuBar\MenuBarClicked;
use Native\Laravel\Events\Menu\MenuItemClicked;

use App\Listeners\MenuBarClickedListener;
use App\Listeners\MenuItemClickedListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MenuBarClicked::class => [
            MenuBarClickedListener::class,
        ],

        MenuItemClicked::class => [
            MenuItemClickedListener::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
