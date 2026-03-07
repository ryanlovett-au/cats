<?php

use Livewire\Volt\Component;

use App\Models\Application;
use App\Models\Service;

new class extends Component
{
    public ?Service $service = null;
    public ?Application $application = null;

    public string $name = '';
    public string $command = '';
    public bool $auto_start = false;

    public function mount($id) {
        if (str_starts_with($id, 'new-')) {
            $appId = str_replace('new-', '', $id);
            $this->application = Application::findOrFail($appId);
            $this->service = new Service;
        } else {
            $this->service = Service::with('application')->findOrFail($id);
            $this->application = $this->service->application;
            $this->name = $this->service->name;
            $this->command = $this->service->command;
            $this->auto_start = $this->service->auto_start;
        }
    }

    public function error(): bool {
        return empty($this->name) || empty($this->command);
    }

    public function back() {
        return redirect()->route('services', ['id' => $this->application->id]);
    }

    public function save() {
        $this->service->application_id = $this->application->id;
        $this->service->name = $this->name;
        $this->service->command = $this->command;
        $this->service->auto_start = $this->auto_start;
        $this->service->save();

        return redirect()->route('services', ['id' => $this->application->id]);
    }

    public function delete() {
        $appId = $this->application->id;
        $this->service->delete();

        return redirect()->route('services', ['id' => $appId]);
    }
};

?>

<div class="text-slate-800 dark:text-gray-200 text-sm">
    <h3 class="pb-2 font-semibold ml-2 flex">
        <div class="hover:bg-gray-300 mr-3 rounded" wire:click="back()" wire:navigate>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" class="stroke-slate-600 dark:stroke-slate-200 size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        </div>

        {{ $service->exists ? 'Edit Service' : 'Add Service' }}
    </h3>

    <div class="text-center px-10 my-4">Enter the service name and the command to run. The command will be executed in the context of <span class="font-semibold">{{ $application->path }}</span>.</div>

    <form wire:submit="save" class="mt-8 mx-4">
        <input type="text" wire:model.live="name" placeholder="Service name" class="border border-gray-300 focus:outline-2 focus:outline-blue-400 bg-white dark:bg-[#303236] placeholder:text-gray-300 w-full px-3 py-1 rounded-lg">

        <input type="text" wire:model.live="command" placeholder="Command (e.g. npm run dev)" class="border border-gray-300 focus:outline-2 focus:outline-blue-400 bg-white dark:bg-[#303236] placeholder:text-gray-300 w-full px-3 py-1 rounded-lg mt-2">

        <label class="flex items-center mt-3 ml-1 cursor-pointer select-none">
            <input type="checkbox" wire:model.live="auto_start" class="rounded border-gray-300 mr-2 accent-blue-400">
            Start automatically
        </label>

        <div class="flex w-full mt-4">
            <button type="button" class="w-1/3 bg-white dark:bg-[#303236] border-1 border-gray-300 px-3 py-1 rounded-lg shadow-xs" wire:click="back()" wire:navigate>Cancel</button>

            @if($service->exists)
                <button type="button" class="w-1/3 ml-4 bg-red-400 text-white border-1 border-gray-300 px-3 py-1 rounded-lg shadow-xs" wire:click="delete()" wire:confirm="Delete this service?">Delete</button>
            @endif

            <button type="submit" class="w-1/3 ml-4 bg-blue-400 text-white border-1 border-gray-300 px-3 py-1 rounded-lg shadow-xs disabled:bg-blue-300" @if ($this->error()) disabled @endif>Save</button>
        </div>
    </form>
</div>
