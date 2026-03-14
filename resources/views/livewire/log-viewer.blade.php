<?php

use Livewire\Volt\Component;

use App\Cats\ServiceManager;
use App\Models\Service;

new class extends Component {

    public Service $service;
    public string $output = '';
    public bool $running = false;

    public function mount(int $id) {
        $this->service = Service::with('application')->findOrFail($id);
        $this->refreshLog();
    }

    public function refreshLog() {
        $manager = app(ServiceManager::class);
        $path = $manager->logPath($this->service);
        $this->running = $manager->isRunning($this->service);

        if (file_exists($path)) {
            $lines = file($path);
            $tail = array_slice($lines, -200);
            $this->output = implode('', $tail);
        } else {
            $this->output = '';
        }
    }

    public function clearLog() {
        app(ServiceManager::class)->clearLog($this->service);
        $this->output = '';
    }

    public function start() {
        $manager = app(ServiceManager::class);
        $path = $manager->logPath($this->service);
        file_put_contents($path, "\n", FILE_APPEND);
        $manager->start($this->service);
        $this->running = $manager->isRunning($this->service);
    }

    public function stop() {
        $manager = app(ServiceManager::class);
        $manager->stop($this->service);
        $this->running = $manager->isRunning($this->service);

        $path = $manager->logPath($this->service);
        $timestamp = now()->format('Y-m-d H:i:s');
        file_put_contents($path, "\n\e[33m[{$timestamp}] Process stopped.\e[0m\n", FILE_APPEND);
        $this->refreshLog();
    }

    public function restart() {
        $manager = app(ServiceManager::class);
        $manager->restart($this->service);
        $this->running = $manager->isRunning($this->service);
    }
};

?>

<div class="-m-4 h-screen flex flex-col bg-[#1e1e1e] text-gray-200 text-xs" wire:poll.1s="refreshLog">
    <div class="flex items-center justify-between px-3 py-2 bg-[#ebedf0] border-b border-[#dcdfe3] text-slate-800 shrink-0">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full {{ $running ? 'bg-green-400' : 'bg-gray-300' }}"></span>
            <span class="font-semibold">{{ $service->name }}</span>
            <span class="text-slate-400">{{ $service->command }}</span>
        </div>
        <div class="flex items-center gap-1.5">
            @if($running)
                {{-- Stop --}}
                <button wire:click="stop"
                    class="w-6 h-6 flex items-center justify-center rounded-full bg-red-50 text-red-500 hover:bg-red-100 border border-red-200 transition-colors"
                    title="Stop">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                        <rect x="5" y="5" width="10" height="10" rx="1" />
                    </svg>
                </button>

                {{-- Restart --}}
                <button wire:click="restart"
                    class="w-6 h-6 flex items-center justify-center rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 border border-blue-200 transition-colors"
                    title="Restart">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H4.598a.75.75 0 0 0-.75.75v3.634a.75.75 0 0 0 1.5 0v-2.033l.312.311a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm-1.262-5.273a7 7 0 0 0-11.712 3.138.75.75 0 0 0 1.449.39 5.5 5.5 0 0 1 9.201-2.466l.312.311H11.867a.75.75 0 0 0 0 1.5h3.634a.75.75 0 0 0 .75-.75V4.64a.75.75 0 0 0-1.5 0v2.033l-.312-.311a6.972 6.972 0 0 0-.389-.211Z" clip-rule="evenodd" />
                    </svg>
                </button>
            @else
                {{-- Start --}}
                <button wire:click="start"
                    class="w-6 h-6 flex items-center justify-center rounded-full bg-green-50 text-green-600 hover:bg-green-100 border border-green-200 transition-colors"
                    title="Start">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                        <path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.841Z" />
                    </svg>
                </button>

                {{-- Restart (disabled) --}}
                <button disabled
                    class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-50 text-slate-300 border border-gray-200 cursor-not-allowed"
                    title="Restart (service not running)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H4.598a.75.75 0 0 0-.75.75v3.634a.75.75 0 0 0 1.5 0v-2.033l.312.311a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm-1.262-5.273a7 7 0 0 0-11.712 3.138.75.75 0 0 0 1.449.39 5.5 5.5 0 0 1 9.201-2.466l.312.311H11.867a.75.75 0 0 0 0 1.5h3.634a.75.75 0 0 0 .75-.75V4.64a.75.75 0 0 0-1.5 0v2.033l-.312-.311a6.972 6.972 0 0 0-.389-.211Z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endif

            {{-- Clear Log --}}
            <button wire:click="clearLog"
                class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 border border-gray-200 transition-colors"
                title="Clear Log">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Hidden raw output for ANSI conversion --}}
    <div id="log-raw" class="hidden">{{ $output }}</div>

    {{-- Visible rendered output --}}
    <div class="flex-1 overflow-y-auto p-3 font-mono leading-relaxed whitespace-pre-wrap break-all" style="font-size: 10px;" id="log-output" wire:ignore></div>

    <script>
        function initLogViewer() {
            if (!window.AnsiUp) {
                setTimeout(initLogViewer, 50);
                return;
            }

            const ansi = new window.AnsiUp();
            ansi.use_classes = false;

            function renderAnsi() {
                const raw = document.getElementById('log-raw');
                const out = document.getElementById('log-output');
                if (!raw || !out) return;

                const text = raw.textContent;
                if (text) {
                    out.innerHTML = ansi.ansi_to_html(text);
                } else {
                    out.innerHTML = '<span style="color:#ffffff">No output yet...</span>';
                }
            }

            function scrollToBottom() {
                const el = document.getElementById('log-output');
                if (el) el.scrollTop = el.scrollHeight;
            }

            // Initial render
            renderAnsi();
            scrollToBottom();

            // Re-render after each Livewire update
            const rawEl = document.getElementById('log-raw');
            if (rawEl) {
                const observer = new MutationObserver(() => {
                    renderAnsi();
                    requestAnimationFrame(scrollToBottom);
                });
                observer.observe(rawEl, { childList: true, characterData: true, subtree: true });
            }

            // Ctrl-C to stop service (Ctrl only, not Cmd which is copy on Mac)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && !e.metaKey && e.key === 'c') {
                    e.preventDefault();
                    const comp = Livewire.all()[0];
                    if (comp && comp.$wire) {
                        comp.$wire.stop();
                    }
                }
            });
        }
        initLogViewer();
    </script>
</div>
