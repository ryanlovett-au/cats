<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;

use App\Cats\ServiceManager;
use App\Models\Application;
use App\Models\Service;
use Native\Laravel\Facades\Window;

new class extends Component {

    public function getAppsProperty(): Collection {
        return Application::orderBy('name')->with('services')->get();
    }

    public function isRunning(int $serviceId): bool {
        $service = Service::find($serviceId);
        return $service ? app(ServiceManager::class)->isRunning($service) : false;
    }

    public function start(int $serviceId) {
        $service = Service::with('application')->findOrFail($serviceId);
        app(ServiceManager::class)->start($service);
    }

    public function stop(int $serviceId) {
        $service = Service::with('application')->findOrFail($serviceId);
        app(ServiceManager::class)->stop($service);
    }

    public function restart(int $serviceId) {
        $service = Service::with('application')->findOrFail($serviceId);
        app(ServiceManager::class)->restart($service);
    }

    public function viewLog(int $serviceId) {
        $service = Service::findOrFail($serviceId);
        Window::open("log-{$service->id}")
            ->route('log', ['id' => $service->id])
            ->title("Cats - {$service->name}")
            ->width(700)
            ->height(500)
            ->rememberState();
    }

    public function openApplications() {
        Window::open('applications')
            ->route('applications')
            ->title('Cats')
            ->rememberState();
    }

    public function quit() {
        $services = Service::with('application')->get();
        foreach ($services as $service) {
            app(ServiceManager::class)->stop($service);
        }
        \Native\Laravel\Facades\App::quit();
    }
};

?>

<div style="user-select: none;" wire:poll.3s>
    <style>
        body {
            background: transparent !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden;
            font: 13.5px/1.2 -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .mi {
            display: block;
            width: 100%;
            text-align: left;
            padding: 6px 18px;
            color: rgba(0,0,0,0.85);
            cursor: default;
            text-decoration: none;
            box-sizing: border-box;
        }
        .mi:hover { background: rgba(0,0,0,0.05); }
        .mi.disabled {
            color: rgba(0,0,0,0.4);
            pointer-events: none;
        }
        .mi-svc {
            display: flex;
            align-items: center;
            width: 100%;
            text-align: left;
            padding: 6px 18px;
            color: rgba(0,0,0,0.85);
            cursor: default;
            box-sizing: border-box;
        }
        .mi-svc:hover { background: rgba(0,0,0,0.05); }
        .sep {
            height: 1px;
            background: rgba(0,0,0,0.1);
            margin: 5px 0;
        }
        .dot-on {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #34d058;
            margin-right: 10px;
            flex-shrink: 0;
        }
        .dot-off {
            width: 8px; height: 8px;
            border-radius: 50%;
            border: 1.5px solid rgba(0,0,0,0.2);
            margin-right: 10px;
            flex-shrink: 0;
            box-sizing: border-box;
        }
        .chevron {
            margin-left: auto;
            opacity: 0.3;
            transition: transform 0.15s;
        }
        .sub {
            margin: 0;
            padding: 0 0 0 36px;
        }
        .sub .mi {
            padding: 5px 18px 5px 0;
            font-size: 13px;
            color: rgba(0,0,0,0.7);
        }
        .sub .mi:hover {
            color: rgba(0,0,0,0.85);
        }
        .sub-sep {
            height: 1px;
            background: rgba(0,0,0,0.06);
            margin: 3px 18px 3px 0;
        }
        @media (prefers-color-scheme: dark) {
            .mi, .mi-svc { color: rgba(255,255,255,0.85); }
            .mi:hover, .mi-svc:hover { background: rgba(255,255,255,0.08); }
            .mi.disabled { color: rgba(255,255,255,0.4); }
            .sep { background: rgba(255,255,255,0.12); }
            .dot-off { border-color: rgba(255,255,255,0.25); }
            .sub .mi { color: rgba(255,255,255,0.6); }
            .sub .mi:hover { color: rgba(255,255,255,0.85); }
            .sub-sep { background: rgba(255,255,255,0.08); }
        }
    </style>

    <div style="padding: 6px 0;">
        <button class="mi" style="font-weight: 500;" wire:click="openApplications">Applications/Sites</button>

        @foreach($this->apps as $app)
            <div class="sep"></div>

            <div class="mi disabled">{{ ($app->emoji ?? '💻') }}&ensp;{{ $app->name }}</div>

            @if($app->services->isEmpty())
                <div class="mi disabled" style="font-size: 12.5px; font-style: italic; padding-left: 36px;">No services</div>
            @else
                @foreach($app->services as $service)
                    @php $running = $this->isRunning($service->id); @endphp
                    <div x-data="{ open: false }">
                        <button class="mi-svc" x-on:click="open = !open">
                            <span class="{{ $running ? 'dot-on' : 'dot-off' }}"></span>
                            <span>{{ $service->name }}</span>
                            <svg class="chevron" :style="open && 'transform:rotate(90deg)'" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>

                        <div x-show="open" x-collapse class="sub">
                            @if($running)
                                <button class="mi" wire:click="stop({{ $service->id }})">Stop</button>
                                <div class="sub-sep"></div>
                                <button class="mi" wire:click="restart({{ $service->id }})">Restart</button>
                            @else
                                <button class="mi" wire:click="start({{ $service->id }})">Start</button>
                            @endif
                            <div class="sub-sep"></div>
                            <button class="mi" wire:click="viewLog({{ $service->id }})">View Log</button>
                        </div>
                    </div>
                @endforeach
            @endif
        @endforeach

        <div class="sep"></div>

        <a href="https://github.com/ryanlovett-au/cats" target="_blank" class="mi">About Cats</a>
        <button class="mi" wire:click="quit" wire:confirm="All running services will be stopped. Are you sure?">Quit</button>
    </div>
</div>
