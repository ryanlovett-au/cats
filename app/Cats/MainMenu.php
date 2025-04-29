<?php

namespace App\Cats;

use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\Window;

use Illuminate\Support\Str;

class MainMenu
{
	public static function show()
	{
		$menu = [];

		$menu[] = Menu::label('Add Site');
        $menu[] = Menu::separator();

        for ($i = 0; $i < 4; $i++) {
			$menu[] = Menu::label(Str::random(8));
		}

        $menu[] = Menu::separator();
        $menu[] = Menu::label(time());
        $menu[] = Menu::link('https://github.com/ryanlovett-au/cats', 'About Cats')
                ->openInBrowser();
        $menu[] = Menu::quit();

		return Menu::make(...$menu);
	}

	protected static function site_list()
	{
		// $menu = new MenuBuilder(Menu::make());

		foreach (['One', 'Two', 'Three'] as $item) {
			$menu[] = Menu::label($item);
		}

		return Menu::make(...$menu);

		// return Menu::label('One');
	}
}