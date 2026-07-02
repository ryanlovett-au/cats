<?php

namespace App\Providers;

use App\Listeners\MenuItemClickedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Native\Desktop\Events\Menu\MenuItemClicked;

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
