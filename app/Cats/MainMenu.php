<?php

namespace App\Cats;

use Native\Laravel\Facades\Menu; 
use Native\Laravel\Facades\Window;
use Illuminate\Support\Str;

use App\Models\Application;

class MainMenu
{
	public static function show()
	{
		$menu = [];

		$menu[] = Menu::route('applications')->label('Applications/Sites')->id('applications');
        $menu[] = Menu::separator();
		// $menu[] = Menu::route('application_add')->label('Add Application')->id('application_add');


        foreach (Application::select()->orderBy('name')->with('services')->get() as $app) {
			$menu[] = Menu::label($app->name);
		}

        $menu[] = Menu::separator();
        $menu[] = Menu::label(time());
        $menu[] = Menu::link('https://github.com/ryanlovett-au/cats', 'About Cats')
                ->openInBrowser();
        $menu[] = Menu::quit()->label('Quit');

		return Menu::make(...$menu);
	}

	protected static function site_list()
	{
		// $menu = new MenuBuilder(Menu::make());

		foreach (Application::select()->orderBy('name')->with('services')->get() as $app) {
			$menu[] = Menu::label($app->name);
		}

		return Menu::make(...$menu);

		// return Menu::label('One');
	}
}