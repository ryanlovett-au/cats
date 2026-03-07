<?php

namespace App\Cats;

use Native\Laravel\Facades\Menu;

use App\Models\Application;

class MainMenu
{
    private static function dotIcon(string $color): string
    {
        return storage_path("app/icons/{$color}.png");
    }

    public static function show()
    {
        $manager = app(ServiceManager::class);
        $menu = [];

        $menu[] = Menu::label('Applications/Sites')->id('applications');

        $apps = Application::orderBy('name')->with('services')->get();

        foreach ($apps as $app) {
            $menu[] = Menu::separator();
            $emoji = $app->emoji ?? "\u{1F5A5}";
            $menu[] = Menu::label("{$emoji}  {$app->name}")->disabled();

            if ($app->services->isEmpty()) {
                $menu[] = Menu::label('  No services')->disabled();
                continue;
            }

            foreach ($app->services as $service) {
                $running = $manager->isRunning($service);

                $actions = [];
                if ($running) {
                    $actions[] = Menu::label('Stop')->id("stop-{$service->id}");
                    $actions[] = Menu::label('Restart')->id("restart-{$service->id}");
                } else {
                    $actions[] = Menu::label('Start')->id("start-{$service->id}");
                }
                $actions[] = Menu::separator();
                $actions[] = Menu::label('View Log')->id("log-{$service->id}");

                $menu[] = Menu::make(...$actions)
                    ->label("  {$service->name}")
                    ->icon(self::dotIcon($running ? 'green' : 'grey'));
            }
        }

        $menu[] = Menu::separator();
        $menu[] = Menu::link('https://github.com/ryanlovett-au/cats', 'About Cats')
            ->openInBrowser();
        $menu[] = Menu::label('Quit')->id('quit-app');

        return Menu::make(...$menu);
    }
}