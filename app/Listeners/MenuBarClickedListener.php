<?php

namespace App\Listeners;

use Native\Laravel\Events\MenuBar\MenuBarClicked;
use Native\Laravel\Facades\MenuBar;

use App\Cats\MainMenu;

class MenuBarClickedListener
{
    public function __construct() {}

    public function handle(MenuBarClicked $event): void 
    {
        MenuBar::ContextMenu(MainMenu::show());
    }
}
