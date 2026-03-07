<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;

use App\Cats\ServiceManager;
use App\Models\Application;

new class extends Component {

    public Application $application;
    public Collection $services;

    public function mount(int $id) {
        $this->application = Application::findOrFail($id);
        $this->services = $this->application->services;
    }

    public function start(int $serviceId) {
        $service = $this->application->services()->findOrFail($serviceId);
        app(ServiceManager::class)->start($service);
    }

    public function stop(int $serviceId) {
        $service = $this->application->services()->findOrFail($serviceId);
        app(ServiceManager::class)->stop($service);
    }

    public function isRunning(int $serviceId): bool {
        $service = $this->application->services()->find($serviceId);
        return $service ? app(ServiceManager::class)->isRunning($service) : false;
    }

    public function back() {
        return redirect()->route('applications');
    }

    public function click($route, $id = null) {
        return redirect()->route($route, ['id' => $id]);
    }
};

?>

<div class="text-slate-800 dark:text-gray-200" wire:poll.5s>
    <h3 class="pb-2 font-semibold text-sm ml-2 flex items-center">
        <div class="hover:bg-gray-300 mr-3 rounded" wire:click="back()" wire:navigate>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" class="stroke-slate-600 dark:stroke-slate-200 size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        </div>
        {{ $application->name }}
    </h3>

    @if($services->isNotEmpty())
        <div class="w-full border-1 border-gray-300 rounded-lg text-sm bg-[#e7e9eb] dark:bg-[#303236]">
            @foreach($services as $service)
                <div class="px-4 py-3 flex justify-between items-center">
                    <div class="flex items-center">
                        <span class="inline-block w-2 h-2 rounded-full mr-2 {{ $this->isRunning($service->id) ? 'bg-green-400' : 'bg-gray-500' }}"></span>
                        <span class="cursor-default" wire:click="click('service', {{ $service->id }})" wire:navigate>{{ $service->name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($this->isRunning($service->id))
                            <button class="text-xs px-2 py-0.5 rounded bg-red-400/20 text-red-400 hover:bg-red-400/30" wire:click="stop({{ $service->id }})">Stop</button>
                        @else
                            <button class="text-xs px-2 py-0.5 rounded bg-green-400/20 text-green-400 hover:bg-green-400/30" wire:click="start({{ $service->id }})">Start</button>
                        @endif
                        <div class="pt-0.5 cursor-pointer" wire:click="click('service', {{ $service->id }})" wire:navigate>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" class="stroke-slate-400 size-3"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </div>
                    </div>
                </div>

                @if (!$loop->last)
                    <hr class="w-[92%] ml-auto mr-auto border-gray-300" />
                @endif
            @endforeach
        </div>
    @else
        <div class="text-center text-sm text-gray-400 py-6">No services yet. Add one below.</div>
    @endif

    <div class="flex mt-4">
        <div class="bg-white dark:bg-[#303236] dark:text-white w-32 font-medium ml-auto py-1 px-2 text-xs border-1 border-gray-300 rounded-lg shadow-xs text-center cursor-default active:bg-gray-200 select-none" wire:click="click('application', {{ $application->id }})" wire:navigate>Edit App...</div>
        <div class="bg-white dark:bg-[#303236] dark:text-white w-32 font-medium ml-4 py-1 px-2 text-xs border-1 border-gray-300 rounded-lg shadow-xs text-center cursor-default active:bg-gray-200 select-none" wire:click="click('service', 'new-{{ $application->id }}')" wire:navigate>Add Service...</div>
    </div>
</div>
