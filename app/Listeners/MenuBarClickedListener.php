<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Native\Laravel\Events\MenuBar\MenuBarClicked;
use Native\Laravel\Facades\MenuBar;

use App\Cats\MainMenu;

class MenuBarClickedListener
{
    public function __construct() {}

    public function handle(MenuBarClicked $event): void
    {
        try {
            MenuBar::contextMenu(MainMenu::show());
        } catch (\Throwable $e) {
            Log::error('Failed to build menu: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
