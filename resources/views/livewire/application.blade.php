<?php

use Livewire\Volt\Component;

use Native\Laravel\Dialog;
use Native\Laravel\Facades\Window;

use App\Models\Application;
use App\Models\Service;

new class extends Component {

    public ?int $appId = null;
    public string $name = '';
    public string $path = '';
    public bool $isNewApp = false;

    // Array of ['id' => int|null, 'name' => '', 'command' => '', 'auto_start' => false, 'isNew' => bool]
    public array $services = [];

    public function mount($id): void {
        if ($id === 'add') {
            $this->isNewApp = true;
        } else {
            $app = Application::with('services')->findOrFail($id);
            $this->appId = $app->id;
            $this->name = $app->name;
            $this->path = $app->path;

            foreach ($app->services as $svc) {
                $this->services[] = [
                    'id'         => $svc->id,
                    'name'       => $svc->name,
                    'command'    => $svc->command,
                    'auto_start' => (bool) $svc->auto_start,
                    'isNew'      => false,
                ];
            }
        }
    }

    public function appError(): bool {
        return empty($this->name) || empty($this->path);
    }

    public function find_path(): void {
        $folder = Dialog::new()
            ->folders()
            ->asSheet()
            ->open();

        if (!empty($folder)) {
            $this->path = $folder;
        }
    }

    public function saveApp(): void {
        if ($this->appError()) return;

        if ($this->isNewApp) {
            $app = Application::create([
                'name' => $this->name,
                'path' => $this->path,
            ]);
            $this->appId = $app->id;
            $this->isNewApp = false;
        } else {
            Application::where('id', $this->appId)->update([
                'name' => $this->name,
                'path' => $this->path,
            ]);
        }
    }

    public function deleteApp(): void {
        if ($this->appId) {
            Service::where('application_id', $this->appId)->delete();
            Application::destroy($this->appId);
        }
        $this->close();
    }

    public function addService(): void {
        // Auto-save the app first if it's new
        if ($this->isNewApp) {
            $this->saveApp();
            if ($this->isNewApp) return; // save failed (validation)
        }

        $this->services[] = [
            'id'         => null,
            'name'       => '',
            'command'    => '',
            'auto_start' => false,
            'isNew'      => true,
        ];
    }

    public function saveService(int $index): void {
        $row = $this->services[$index] ?? null;
        if (!$row) return;
        if (empty($row['name']) || empty($row['command'])) return;

        // Ensure app is saved first
        if (!$this->appId) {
            $this->saveApp();
            if (!$this->appId) return;
        }

        if ($row['id']) {
            Service::where('id', $row['id'])->update([
                'name'       => $row['name'],
                'command'    => $row['command'],
                'auto_start' => $row['auto_start'],
            ]);
        } else {
            $svc = Service::create([
                'application_id' => $this->appId,
                'name'           => $row['name'],
                'command'        => $row['command'],
                'auto_start'     => $row['auto_start'],
            ]);
            $this->services[$index]['id'] = $svc->id;
        }

        $this->services[$index]['isNew'] = false;
    }

    public function deleteService(int $index): void {
        $row = $this->services[$index] ?? null;
        if (!$row) return;

        if ($row['id']) {
            Service::destroy($row['id']);
        }

        array_splice($this->services, $index, 1);
        $this->services = array_values($this->services);
    }

    public function close(): void {
        Window::close('application');
    }
};

?>

<div class="select-none text-slate-800 dark:text-gray-200 text-xs flex flex-col" style="height: calc(100vh - 2rem);">

    <style>
        .services-scroll::-webkit-scrollbar { display: none; }
        .services-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- Title bar --}}
    <div class="flex items-center justify-between mb-4 flex-shrink-0">
        <h1 class="text-base font-semibold">
            {{ $isNewApp ? 'Add Application' : 'Edit Application' }}
        </h1>
        <button wire:click="close"
            class="w-6 h-6 flex items-center justify-center rounded-full bg-white/70 dark:bg-white/10 border border-gray-300/60 dark:border-gray-500/40 text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 transition-colors"
            title="Close">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
            </svg>
        </button>
    </div>

    {{-- App details card --}}
    <div class="rounded-xl border border-gray-300/60 dark:border-gray-500/40 bg-white/60 dark:bg-white/10 backdrop-blur-sm p-3 flex-shrink-0">

        {{-- Name input --}}
        <input type="text"
            wire:model.live="name"
            placeholder="Application name"
            class="w-full px-3 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-[#007AFF] focus:border-[#007AFF] text-xs transition-colors">

        {{-- Path picker --}}
        <div class="flex items-center gap-2 mt-2">
            <input type="text"
                wire:model.live="path"
                placeholder="Project path"
                readonly
                wire:click="find_path"
                class="flex-1 px-3 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-[#007AFF] focus:border-[#007AFF] text-xs cursor-pointer transition-colors truncate">
            <button type="button"
                wire:click="find_path"
                class="flex-shrink-0 px-2.5 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200 hover:border-slate-400 dark:hover:border-gray-400 text-xs transition-colors">
                Browse
            </button>
        </div>

        {{-- Save / Delete buttons --}}
        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-300/40 dark:border-gray-500/30">
            @if(!$isNewApp)
                <button type="button"
                    wire:click="deleteApp"
                    wire:confirm="Delete {{ $name ?: 'this application' }} and all its services?"
                    class="text-xs px-2.5 py-1 rounded-lg border border-red-300/60 dark:border-red-500/40 bg-red-50/70 dark:bg-red-500/10 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                    Delete Application
                </button>
            @else
                <div></div>
            @endif

            <button type="button"
                wire:click="saveApp"
                @if($this->appError()) disabled @endif
                class="text-xs font-medium px-3 py-1 rounded-lg bg-[#007AFF] text-white hover:bg-[#0063CC] active:bg-[#004EA3] disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm">
                {{ $isNewApp ? 'Create Application' : 'Save Application' }}
            </button>
        </div>
    </div>

    {{-- Services section --}}
    <div class="mt-4 flex flex-col flex-1 min-h-0">

        {{-- Services header --}}
        <div class="flex items-center justify-between mb-2 flex-shrink-0">
            <h2 class="text-xs font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wide">Services</h2>
            <button type="button"
                wire:click="addService"
                class="w-6 h-6 flex items-center justify-center rounded-full bg-[#007AFF] text-white hover:bg-[#0063CC] transition-colors shadow-sm"
                title="Add service">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                    <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                </svg>
            </button>
        </div>

        {{-- Services list --}}
        <div class="services-scroll flex flex-col gap-2 overflow-y-auto flex-1 pb-1">
            @if(empty($services))
                <div class="flex items-center justify-center py-6 rounded-xl border border-dashed border-gray-300/60 dark:border-gray-500/40">
                    <p class="text-xs text-slate-400 dark:text-gray-500">
                        @if($isNewApp)
                            Create the application first, then add services
                        @else
                            No services yet — click + to add one
                        @endif
                    </p>
                </div>
            @else
                @foreach($services as $index => $svc)
                    <div class="rounded-xl border backdrop-blur-sm p-3
                        {{ $svc['isNew']
                            ? 'border-[#007AFF]/40 bg-blue-50/50 dark:bg-blue-500/5'
                            : 'border-gray-300/60 dark:border-gray-500/40 bg-white/60 dark:bg-white/10' }}">

                        {{-- Name + command inputs --}}
                        <div class="flex items-center gap-2 mb-2">
                            <input type="text"
                                wire:model.live="services.{{ $index }}.name"
                                placeholder="Service name"
                                class="w-2/5 px-2.5 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-[#007AFF] focus:border-[#007AFF] text-xs transition-colors">
                            <input type="text"
                                wire:model.live="services.{{ $index }}.command"
                                placeholder="Command (e.g. npm run dev)"
                                class="flex-1 px-2.5 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-[#007AFF] focus:border-[#007AFF] text-xs transition-colors">
                        </div>

                        {{-- Auto-start + actions --}}
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox"
                                    wire:model.live="services.{{ $index }}.auto_start"
                                    class="rounded border-gray-300 accent-[#007AFF] w-3 h-3">
                                <span class="text-xs text-slate-500 dark:text-gray-400">Auto-start</span>
                            </label>

                            <div class="flex items-center gap-1.5">
                                {{-- Delete --}}
                                <button type="button"
                                    wire:click="deleteService({{ $index }})"
                                    @if(!$svc['isNew']) wire:confirm="Delete this service?" @endif
                                    class="w-6 h-6 flex items-center justify-center rounded-full bg-red-50/80 dark:bg-red-500/10 border border-red-200/60 dark:border-red-500/30 text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 hover:text-red-500 transition-colors"
                                    title="Delete service">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                {{-- Save --}}
                                <button type="button"
                                    wire:click="saveService({{ $index }})"
                                    @if(empty($svc['name']) || empty($svc['command'])) disabled @endif
                                    class="w-6 h-6 flex items-center justify-center rounded-full bg-[#007AFF]/90 text-white hover:bg-[#0063CC] disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm"
                                    title="Save service">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
