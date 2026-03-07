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

    public function toggle() {
        $manager = app(ServiceManager::class);

        if ($manager->isRunning($this->service)) {
            $manager->stop($this->service);
        } else {
            $manager->start($this->service);
        }

        $this->running = $manager->isRunning($this->service);
    }
};

?>

<div class="-m-4 h-screen flex flex-col bg-[#1e1e1e] text-gray-200 text-xs" wire:poll.1s="refreshLog">
    <div class="flex items-center justify-between px-3 py-2 bg-[#2d2d2d] border-b border-[#404040] shrink-0">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full {{ $running ? 'bg-green-400' : 'bg-gray-500' }}"></span>
            <span class="font-semibold">{{ $service->name }}</span>
            <span class="text-gray-500">{{ $service->command }}</span>
        </div>
        <div class="flex gap-2">
            <button class="px-2 py-0.5 rounded text-gray-400 hover:text-white hover:bg-[#404040]" wire:click="toggle">
                {{ $running ? 'Stop' : 'Start' }}
            </button>
            <button class="px-2 py-0.5 rounded text-gray-400 hover:text-white hover:bg-[#404040]" wire:click="clearLog">
                Clear
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-3 font-mono leading-relaxed whitespace-pre-wrap break-all" id="log-output">{{ $output ?: 'No output yet...' }}</div>

    <script>
        function scrollToBottom() {
            const el = document.getElementById('log-output');
            if (el) el.scrollTop = el.scrollHeight;
        }

        // Scroll on initial load
        scrollToBottom();

        // Scroll after every Livewire update (poll refresh)
        document.addEventListener('livewire:update', () => {
            requestAnimationFrame(scrollToBottom);
        });

        // Also observe DOM changes as a fallback
        const observer = new MutationObserver(scrollToBottom);
        const target = document.getElementById('log-output');
        if (target) observer.observe(target, { childList: true, characterData: true, subtree: true });
    </script>
</div>
