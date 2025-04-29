<?php

namespace App\Listeners;

use Native\Laravel\Events\Menu\MenuItemClicked;
use Native\Laravel\Facades\Window;

use App\Cats\MainMenu;

class MenuItemClickedListener
{
    public function __construct() {}

    public function handle(MenuItemClicked $event): void 
    {   
        switch($event->item['id']) 
        {
            case 'applications':
                Window::open('applications')
                    ->route('applications')
                    ->title('Cats')
                    ->rememberState();
                break;

            case 'application_add':
                Window::open('add')
                    ->route('application_add')
                    ->title('Cats - Add Application')
                    ->rememberState()
                    ->position(100, 100);
                break;
        }
    }
}
