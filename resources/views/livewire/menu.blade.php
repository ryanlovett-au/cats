<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;

use App\Cats\ServiceManager;
use App\Models\Application;
use App\Models\Service;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Shell;
use Native\Laravel\Facades\Window;

new class extends Component {

    public function getAppsProperty(): Collection {
        return Application::orderBy('sort_order')->orderBy('name')->with('services')->get();
    }

    public function moveUp(int $id) {
        $apps = Application::orderBy('sort_order')->orderBy('name')->get();
        $index = $apps->search(fn($a) => $a->id === $id);
        if ($index === false || $index === 0) return;

        $prev = $apps[$index - 1];
        $current = $apps[$index];

        // Swap sort_order values
        $tempOrder = $current->sort_order;
        $current->sort_order = $prev->sort_order;
        $prev->sort_order = $tempOrder;

        // If they were equal, force different values
        if ($current->sort_order === $prev->sort_order) {
            $current->sort_order = $prev->sort_order - 1;
        }

        $current->save();
        $prev->save();
    }

    public function moveDown(int $id) {
        $apps = Application::orderBy('sort_order')->orderBy('name')->get();
        $index = $apps->search(fn($a) => $a->id === $id);
        if ($index === false || $index === $apps->count() - 1) return;

        $next = $apps[$index + 1];
        $current = $apps[$index];

        $tempOrder = $current->sort_order;
        $current->sort_order = $next->sort_order;
        $next->sort_order = $tempOrder;

        if ($current->sort_order === $next->sort_order) {
            $current->sort_order = $next->sort_order + 1;
        }

        $current->save();
        $next->save();
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
        MenuBar::show();
    }

    public function addApplication() {
        Window::open('application')
            ->route('application', ['id' => 'add'])
            ->title('Cats - Add Application')
            ->width(440)
            ->height(700);
        MenuBar::show();
    }

    public function editApplication(int $id) {
        Window::open('application')
            ->route('application', ['id' => $id])
            ->title('Cats - Edit Application')
            ->width(440)
            ->height(700);
        MenuBar::show();
    }

    public function openAbout() {
        Shell::openExternal('https://github.com/ryanlovett-au/cats');
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

<div class="select-none text-slate-800 dark:text-gray-200 flex flex-col" style="height: calc(100vh - 32px);" wire:poll.3s>

    <style>
        /* Hide scrollbars but keep scroll functionality */
        body, .scroll-hidden { overflow-y: auto; }
        body::-webkit-scrollbar, .scroll-hidden::-webkit-scrollbar { display: none; }
        body { -ms-overflow-style: none; scrollbar-width: none; }
        .scroll-hidden { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- Top bar --}}
    <div class="flex items-center justify-between mb-3 flex-shrink-0">
        <h1 class="text-base font-semibold flex items-center gap-1.5">
            <img src="/cat.png" class="dark:invert" style="width: 18px; height: 18px;" alt="">
            Cats
        </h1>
        <button wire:click="addApplication"
            class="text-xs font-medium px-2.5 py-1 rounded-md bg-[#007AFF] text-white hover:bg-[#0063CC] active:bg-[#004EA3] shadow-sm transition-colors">
            + Add Application
        </button>
    </div>

    {{-- App groups (scrollable middle) --}}
    <div class="scroll-hidden flex-1 overflow-y-auto">
        @forelse($this->apps as $app)
            {{-- Top rule --}}
            <hr class="border-gray-400/30 dark:border-gray-400/20" />

            <div class="py-3">
                {{-- App header --}}
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-semibold text-slate-700 dark:text-gray-300">{{ $app->name }}</h2>
                    <div class="flex items-center gap-1">
                        {{-- Move up --}}
                        @if(!$loop->first)
                            <button wire:click="moveUp({{ $app->id }})"
                                class="w-5 h-5 flex items-center justify-center rounded text-slate-400 hover:text-slate-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                                title="Move up">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3 h-3">
                                    <path fill-rule="evenodd" d="M11.78 9.78a.75.75 0 0 1-1.06 0L8 7.06 5.28 9.78a.75.75 0 0 1-1.06-1.06l3.25-3.25a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif

                        {{-- Move down --}}
                        @if(!$loop->last)
                            <button wire:click="moveDown({{ $app->id }})"
                                class="w-5 h-5 flex items-center justify-center rounded text-slate-400 hover:text-slate-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                                title="Move down">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3 h-3">
                                    <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif

                        {{-- Edit --}}
                        <button wire:click="editApplication({{ $app->id }})"
                            class="w-6 h-6 flex items-center justify-center rounded-md border border-gray-300/60 dark:border-gray-500/50 bg-white/70 dark:bg-white/10 text-slate-500 hover:text-slate-700 hover:border-slate-400 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:border-gray-400 transition-colors"
                            title="Edit Application">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
                                <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Service tiles --}}
                @if($app->services->isEmpty())
                    <p class="text-xs font-extralight text-slate-400 dark:text-gray-500 pl-1">No services configured</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($app->services as $service)
                            @php $running = $this->isRunning($service->id); @endphp
                            <div class="
                                relative rounded-lg border bg-white/60 dark:bg-white/10 shadow-sm backdrop-blur-sm
                                {{ $running ? 'border-green-400/60' : 'border-gray-300/60 dark:border-gray-500/40' }}
                                w-[120px] p-2
                            ">
                                {{-- Status dot + name --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $running ? 'bg-green-400' : 'bg-gray-300 dark:bg-gray-500' }}"></span>
                                    <span class="text-xs font-medium truncate" title="{{ $service->name }}">{{ $service->name }}</span>
                                </div>

                                {{-- Action buttons --}}
                                <div class="flex items-center gap-1.5">
                                    @if($running)
                                        {{-- Stop --}}
                                        <button wire:click="stop({{ $service->id }})"
                                            class="w-6 h-6 flex items-center justify-center rounded-full bg-red-50 text-red-500 hover:bg-red-100 border border-red-200 dark:bg-red-500/10 dark:border-red-500/30 dark:hover:bg-red-500/20 transition-colors"
                                            title="Stop">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                                <rect x="5" y="5" width="10" height="10" rx="1" />
                                            </svg>
                                        </button>

                                        {{-- Restart (blue when enabled) --}}
                                        <button wire:click="restart({{ $service->id }})"
                                            class="w-6 h-6 flex items-center justify-center rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 border border-blue-200 dark:bg-blue-500/10 dark:border-blue-500/30 dark:hover:bg-blue-500/20 transition-colors"
                                            title="Restart">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                                <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H4.598a.75.75 0 0 0-.75.75v3.634a.75.75 0 0 0 1.5 0v-2.033l.312.311a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm-1.262-5.273a7 7 0 0 0-11.712 3.138.75.75 0 0 0 1.449.39 5.5 5.5 0 0 1 9.201-2.466l.312.311H11.867a.75.75 0 0 0 0 1.5h3.634a.75.75 0 0 0 .75-.75V4.64a.75.75 0 0 0-1.5 0v2.033l-.312-.311a6.972 6.972 0 0 0-.389-.211Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @else
                                        {{-- Start --}}
                                        <button wire:click="start({{ $service->id }})"
                                            class="w-6 h-6 flex items-center justify-center rounded-full bg-green-50 text-green-600 hover:bg-green-100 border border-green-200 dark:bg-green-500/10 dark:border-green-500/30 dark:hover:bg-green-500/20 transition-colors"
                                            title="Start">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                                <path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.841Z" />
                                            </svg>
                                        </button>

                                        {{-- Restart (disabled) --}}
                                        <button disabled
                                            class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-50 text-slate-300 border border-gray-200 dark:bg-gray-700 dark:text-gray-600 dark:border-gray-600 cursor-not-allowed"
                                            title="Restart (service not running)">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                                <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H4.598a.75.75 0 0 0-.75.75v3.634a.75.75 0 0 0 1.5 0v-2.033l.312.311a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm-1.262-5.273a7 7 0 0 0-11.712 3.138.75.75 0 0 0 1.449.39 5.5 5.5 0 0 1 9.201-2.466l.312.311H11.867a.75.75 0 0 0 0 1.5h3.634a.75.75 0 0 0 .75-.75V4.64a.75.75 0 0 0-1.5 0v2.033l-.312-.311a6.972 6.972 0 0 0-.389-.211Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- View Log --}}
                                    <button wire:click="viewLog({{ $service->id }})"
                                        class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 border border-gray-200 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500 transition-colors"
                                        title="View Log">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                            <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Bottom rule for last item --}}
            @if($loop->last)
                <hr class="border-gray-400/30 dark:border-gray-400/20" />
            @endif
        @empty
            <div class="text-center py-8">
                <p class="text-sm text-slate-400 dark:text-gray-500">No applications configured</p>
                <button wire:click="addApplication"
                    class="mt-2 text-xs font-medium px-2.5 py-1 rounded-md bg-[#007AFF] text-white hover:bg-[#0063CC] active:bg-[#004EA3] shadow-sm transition-colors">
                    + Add your first application
                </button>
            </div>
        @endforelse
    </div>

    {{-- Bottom bar (pinned) --}}
    <div class="flex items-center justify-between pt-3 mt-auto flex-shrink-0 border-t border-gray-400/30 dark:border-gray-400/20">
        <button wire:click="openAbout"
            class="text-xs font-medium px-2.5 py-1 rounded-md border border-gray-300/60 dark:border-gray-500/50 bg-white/70 dark:bg-white/10 text-slate-600 hover:text-slate-800 hover:border-slate-400 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-400 shadow-sm transition-colors">
            About Cats
        </button>
        <button wire:click="quit" wire:confirm="All running services will be stopped. Are you sure?"
            class="text-xs font-medium px-2.5 py-1 rounded-md border border-gray-300/60 dark:border-gray-500/50 bg-white/70 dark:bg-white/10 text-slate-600 hover:text-red-500 hover:border-red-300 dark:text-gray-300 dark:hover:text-red-400 dark:hover:border-red-500/50 shadow-sm transition-colors">
            Quit
        </button>
    </div>
</div>
