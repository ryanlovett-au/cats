<?php

use Livewire\Volt\Component;

use Native\Laravel\Dialog;

new class extends Component
{
    public string $name;

    public string $path;

    public function error(): bool {
        return (empty($name) || empty($path));
    }

    public function back() {
        return redirect()->route('applications');
    }

    public function save() {
        
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
};

?>

<div class="text-slate-800 dark:text-gray-200 text-sm">
    <h3 class="pb-2 font-semibold ml-2 flex">
        <div class="hover:bg-gray-300 mr-3 rounded" wire:click="back()" wire:navigate>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" class="stroke-slate-600 dark:stroke-slate-200 size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        </div>

        Add Application/Site
    </h3>

    <div class="text-center px-10 my-4">Please enter the name of your application/site and select a folder that represents the path for the applicaiton/site. Service commands will be run in the context of this path.</div>
    

    <form wire:submit="save" class="mt-8 mx-4">
        <input type="text" wire:model.live="name" placeholder="Name" class="border border-gray-300 focus:outline-2 focus:outline-blue-400 bg-white dark:bg-[#303236] placeholder:text-gray-300 w-full px-3 py-1 rounded-lg">
     
        <input type="text" wire:model.live="path" wire:click="find_path()" placeholder="Path" class="border border-gray-300 focus:outline-2 focus:outline-blue-400 bg-white dark:bg-[#303236] placeholder:text-gray-300 w-full px-3 py-1 rounded-lg mt-2">
     
        <div class="flex w-full mt-4">
            <button type="cancel" class="w-1/2 ml-auto bg-white dark:bg-[#303236] border-1 border-gray-300 px-3 py-1 rounded-lg shadow-xs" wire:click="back()" wire:navigate>Cancel</button>
            <button type="submit" class="w-1/2 ml-4 mr-auto bg-blue-400 text-white border-1 border-gray-300 px-3 py-1 rounded-lg shadow-xs disabled:bg-blue-300" @if ($this->error()) disabled @endif>Save</button>
        </div>
    </form>
</div>
