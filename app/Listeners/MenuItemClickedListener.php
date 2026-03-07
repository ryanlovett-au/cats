<?php

namespace App\Listeners;

use Native\Laravel\Events\Menu\MenuItemClicked;
use Native\Laravel\Facades\Window;
use Native\Laravel\Alert;

use App\Cats\ServiceManager;
use App\Models\Service;

class MenuItemClickedListener
{
    public function __construct(protected ServiceManager $manager) {}

    public function handle(MenuItemClicked $event): void
    {
        $id = $event->item['id'] ?? '';

        if ($id === 'quit-app') {
            $response = Alert::new()
                ->type('warning')
                ->title('Quit Cats')
                ->detail('All running services will be stopped.')
                ->buttons(['Cancel', 'Quit'])
                ->defaultId(1)
                ->cancelId(0)
                ->show('Are you sure?');

            if ($response === 1) {
                $services = Service::with('application')->get();
                foreach ($services as $service) {
                    $this->manager->stop($service);
                }
                \Native\Laravel\Facades\App::quit();
            }
            return;
        }

        if ($id === 'applications') {
            Window::open('applications')
                ->route('applications')
                ->title('Cats')
                ->rememberState();
            return;
        }

        if (preg_match('/^(start|stop|restart|log)-(\d+)$/', $id, $matches)) {
            $action = $matches[1];
            $service = Service::with('application')->find($matches[2]);

            if (!$service) {
                return;
            }

            match ($action) {
                'start' => $this->manager->start($service),
                'stop' => $this->manager->stop($service),
                'restart' => $this->manager->restart($service),
                'log' => Window::open("log-{$service->id}")
                    ->route('log', ['id' => $service->id])
                    ->title("Cats - {$service->name}")
                    ->width(700)
                    ->height(500)
                    ->rememberState(),
            };
        }
    }
}
