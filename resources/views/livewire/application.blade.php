<?php

use Livewire\Volt\Component;

use Native\Desktop\Dialog;
use Native\Desktop\Facades\Window;

use App\Models\Application;
use App\Models\Service;

new class extends Component {

    public ?int $appId = null;
    public string $name = '';
    public string $path = '';
    public bool $isNewApp = false;

    // Each: ['id' => int|null, 'name' => string, 'command' => string, 'auto_start' => bool]
    public array $services = [];

    // Ids of persisted services removed in the UI. Deleted from the DB on save,
    // so closing without saving undoes the removal.
    public array $deletedIds = [];

    // Signature of the last-saved state, compared against the current state for
    // dirty detection.
    public string $clean = '';

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
                ];
            }
        }

        $this->markClean();
    }

    public function appError(): bool {
        return empty($this->name) || empty($this->path);
    }

    // Signature of the current form state; compared against $clean to tell if
    // there are unsaved changes.
    protected function signature(): string {
        $services = array_map(fn ($s) => [
            'id'         => $s['id'],
            'name'       => $s['name'],
            'command'    => $s['command'],
            'auto_start' => (bool) $s['auto_start'],
        ], $this->services);

        return json_encode([
            'name'     => $this->name,
            'path'     => $this->path,
            'services' => $services,
            'deleted'  => $this->deletedIds,
        ]);
    }

    protected function markClean(): void {
        $this->clean = $this->signature();
    }

    public function isDirty(): bool {
        return $this->signature() !== $this->clean;
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

    // Persist the whole window in one action: the application record, all
    // service rows, and any staged deletions.
    public function save(): void {
        if ($this->appError()) return;

        if ($this->appId) {
            Application::where('id', $this->appId)->update([
                'name' => $this->name,
                'path' => $this->path,
            ]);
        } else {
            $app = Application::create([
                'name' => $this->name,
                'path' => $this->path,
            ]);
            $this->appId = $app->id;
            $this->isNewApp = false;
        }

        if (!empty($this->deletedIds)) {
            Service::whereIn('id', $this->deletedIds)->delete();
            $this->deletedIds = [];
        }

        foreach ($this->services as $index => $row) {
            $name = trim($row['name']);
            $command = trim($row['command']);

            // Skip blank or half-filled rows rather than persisting partial data.
            if ($name === '' || $command === '') {
                continue;
            }

            if ($row['id']) {
                Service::where('id', $row['id'])->update([
                    'name'       => $name,
                    'command'    => $command,
                    'auto_start' => (bool) $row['auto_start'],
                ]);
            } else {
                $svc = Service::create([
                    'application_id' => $this->appId,
                    'name'           => $name,
                    'command'        => $command,
                    'auto_start'     => (bool) $row['auto_start'],
                ]);
                $this->services[$index]['id'] = $svc->id;
            }

            $this->services[$index]['name'] = $name;
            $this->services[$index]['command'] = $command;
        }

        $this->markClean();
    }

    public function deleteApp(): void {
        if ($this->appId) {
            Service::where('application_id', $this->appId)->delete();
            Application::destroy($this->appId);
        }
        $this->close();
    }

    public function addService(): void {
        $this->services[] = [
            'id'         => null,
            'name'       => '',
            'command'    => '',
            'auto_start' => false,
        ];

        $this->dispatch('service-added');
    }

    // Remove a row from the UI. Persisted rows are staged in $deletedIds and
    // only deleted from the DB on save.
    public function deleteService(int $index): void {
        $row = $this->services[$index] ?? null;
        if (!$row) return;

        if ($row['id']) {
            $this->deletedIds[] = $row['id'];
        }

        array_splice($this->services, $index, 1);
        $this->services = array_values($this->services);
    }

    public function close(): void {
        Window::close('application');
    }
};

?>

<div data-app-window data-dirty="{{ $this->isDirty() ? '1' : '0' }}"
    class="select-none text-slate-800 dark:text-gray-200 text-xs grid grid-cols-[210px_1fr] gap-4"
    style="height: calc(100vh - 2rem);">

    <style>
        .services-scroll::-webkit-scrollbar { display: none; }
        .services-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- Left column: application details --}}
    <div class="rounded-xl border border-gray-300/60 dark:border-gray-500/40 bg-white/60 dark:bg-white/10 backdrop-blur-sm p-3 flex flex-col">

        {{-- Name --}}
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
                class="flex-1 min-w-0 px-3 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-[#007AFF] focus:border-[#007AFF] text-xs cursor-pointer transition-colors truncate">
            <button type="button"
                wire:click="find_path"
                class="flex-shrink-0 px-2.5 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200 hover:border-slate-400 dark:hover:border-gray-400 text-xs transition-colors">
                Browse
            </button>
        </div>

        {{-- Spacer pushes the actions to the bottom --}}
        <div class="flex-1"></div>

        {{-- Actions --}}
        <div class="flex flex-col gap-2 pt-3 mt-3 border-t border-gray-300/40 dark:border-gray-500/30">
            {{-- Save (only shown when there are unsaved changes) --}}
            @if($this->isDirty())
                <button type="button"
                    wire:click="save"
                    @if($this->appError()) disabled @endif
                    class="flex items-center justify-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-[#007AFF] text-white hover:bg-[#0063CC] active:bg-[#004EA3] disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-white/90"></span>
                    {{ $isNewApp ? 'Create Application' : 'Save Application' }}
                </button>
            @endif

            {{-- Delete --}}
            @if(!$isNewApp)
                <button type="button"
                    wire:click="deleteApp"
                    wire:confirm="Delete {{ $name ?: 'this application' }} and all its services?"
                    class="flex items-center justify-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-red-300/60 dark:border-red-500/40 bg-red-50/70 dark:bg-red-500/10 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                    </svg>
                    Delete Application
                </button>
            @endif

            {{-- Close. Routes through the JS guard so the native traffic-light /
                 Cmd-W and this button share one confirm dialog. --}}
            <button type="button"
                onclick="catsGuardedClose()"
                class="flex items-center justify-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200 hover:border-slate-400 dark:hover:border-gray-400 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
                Close
            </button>
        </div>
    </div>

    {{-- Right column: services --}}
    <div class="flex flex-col min-h-0">

        {{-- Services header --}}
        <div class="flex items-center justify-between mb-2 flex-shrink-0">
            <h2 class="text-xs font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wide">Services</h2>
            <button type="button"
                wire:click="addService"
                class="w-6 h-6 flex items-center justify-center rounded-full bg-[#007AFF] text-white hover:bg-[#0063CC] transition-colors shadow-sm"
                data-tooltip="Add service">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                    <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                </svg>
            </button>
        </div>

        {{-- Services list --}}
        <div class="services-scroll flex flex-col gap-2 overflow-y-auto flex-1 min-h-0 pb-1"
            x-data
            @service-added.window="$nextTick(() => {
                $el.scrollTo({ top: $el.scrollHeight, behavior: 'smooth' });
                const rows = $el.children;
                const lastRow = rows[rows.length - 1];
                lastRow?.querySelector('input[type=text]')?.focus();
            })">
            @if(empty($services))
                <div class="flex items-center justify-center py-6 rounded-xl border border-dashed border-gray-300/60 dark:border-gray-500/40">
                    <p class="text-xs text-slate-400 dark:text-gray-500">No services yet. Click + to add one.</p>
                </div>
            @else
                @foreach($services as $index => $svc)
                    <div class="rounded-xl border backdrop-blur-sm p-3
                        {{ $svc['id'] === null
                            ? 'border-[#007AFF]/40 bg-blue-50/50 dark:bg-blue-500/5'
                            : 'border-gray-300/60 dark:border-gray-500/40 bg-white/60 dark:bg-white/10' }}">

                        {{-- Name + command --}}
                        <div class="flex items-center gap-2 mb-2">
                            <input type="text"
                                wire:model.live="services.{{ $index }}.name"
                                placeholder="Service name"
                                class="w-2/5 px-2.5 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-[#007AFF] focus:border-[#007AFF] text-xs transition-colors">
                            <input type="text"
                                wire:model.live="services.{{ $index }}.command"
                                placeholder="Command (e.g. npm run dev)"
                                class="flex-1 min-w-0 px-2.5 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-1 focus:ring-[#007AFF] focus:border-[#007AFF] text-xs transition-colors">
                        </div>

                        {{-- Auto-start + remove --}}
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox"
                                    wire:model.live="services.{{ $index }}.auto_start"
                                    class="rounded border-gray-300 accent-[#007AFF] w-3 h-3">
                                <span class="text-xs text-slate-500 dark:text-gray-400">Auto-start</span>
                            </label>

                            <button type="button"
                                wire:click="deleteService({{ $index }})"
                                class="w-6 h-6 flex items-center justify-center rounded-full bg-red-50/80 dark:bg-red-500/10 border border-red-200/60 dark:border-red-500/30 text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 hover:text-red-500 transition-colors"
                                data-tooltip="Remove service">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Discard-confirm dialog. Shown when a close is attempted with unsaved
         changes. Its Discard button closes via window.close() inside the real
         click gesture -- Electron blocks a programmatic close right after a
         cancelled beforeunload, so the close must come from a user click. --}}
    <div id="cats-discard-modal" wire:ignore
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black/30 backdrop-blur-sm">
        <div class="w-64 rounded-xl border border-gray-300/60 dark:border-gray-500/40 bg-white dark:bg-[#2a2a2a] shadow-xl p-4 text-center">
            <p class="text-sm font-medium mb-1">Discard unsaved changes?</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mb-4">Your changes will be lost.</p>
            <div class="flex gap-2">
                <button type="button" onclick="catsHideDiscard()"
                    class="flex-1 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-300/60 dark:border-gray-500/40 bg-white/70 dark:bg-white/10 text-slate-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/20 transition-colors">Cancel</button>
                <button type="button" onclick="catsConfirmDiscard()"
                    class="flex-1 text-xs font-medium px-3 py-1.5 rounded-lg bg-red-500 text-white hover:bg-red-600 transition-colors">Discard</button>
            </div>
        </div>
    </div>

    {{-- Unsaved-changes guard. beforeunload can cancel the native close but
         Electron shows no dialog of its own, so we cancel it and show ours;
         the Discard button then closes via window.close() in its click gesture. --}}
    <script>
        function catsAppDirty() {
            var root = document.querySelector('[data-app-window]');
            return !!(root && root.dataset.dirty === '1');
        }
        function catsShowDiscard() {
            var m = document.getElementById('cats-discard-modal');
            if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
        }
        function catsHideDiscard() {
            var m = document.getElementById('cats-discard-modal');
            if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
        }
        function catsClose() {
            window.__catsAllowClose = true;
            window.close();
        }
        function catsConfirmDiscard() {
            catsHideDiscard();
            catsClose();
        }
        window.catsGuardedClose = function () {
            if (catsAppDirty()) { catsShowDiscard(); return; }
            catsClose();
        };
        if (!window.__catsAppWindowBound) {
            window.__catsAppWindowBound = true;
            window.addEventListener('beforeunload', function (e) {
                if (window.__catsAllowClose) return;   // close already confirmed
                // Only guard a close of the focused window (the red button /
                // Cmd-W act on it). When Cats quits from the menu bar this
                // window is in the background, so we let it close and the app
                // quits cleanly instead of orphaning this window.
                if (!document.hasFocus()) return;
                if (!catsAppDirty()) return;           // clean: allow the native close
                e.preventDefault();                    // dirty: cancel it and ask
                e.returnValue = '';
                catsShowDiscard();
            });
        }
    </script>
</div>
