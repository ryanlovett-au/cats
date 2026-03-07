<?php

use Livewire\Volt\Component;

use Native\Laravel\Dialog;
use Native\Laravel\Facades\Window;

use App\Models\Application;

new class extends Component
{
    public Application $application;
    public string $name = '';
    public string $path = '';
    public string $emoji = "\u{1F5A5}";

    public function mount($id) {
        if ($id === 'add') {
            $this->application = new Application;
        } else {
            $this->application = Application::findOrFail($id);
            $this->name = $this->application->name;
            $this->path = $this->application->path;
            $this->emoji = $this->application->emoji ?? "\u{1F5A5}";
        }
    }

    public function error(): bool {
        return (empty($this->name) || empty($this->path));
    }

    public function back() {
        return redirect()->route('applications');
    }

    public function save() {
        $this->application->name = $this->name;
        $this->application->path = $this->path;
        $this->application->emoji = $this->emoji;
        $this->application->save();

        return redirect()->route('applications');
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

        {{ $application->exists ? 'Edit Application/Site' : 'Add Application/Site' }}
    </h3>

    <div class="text-center px-10 my-4">Enter the name of your application/site and select the folder containing your command executables. Service commands will run in the context of this path.</div>


    <form wire:submit="save" class="mt-8 mx-4">
        <div class="flex items-center gap-2">
            <div class="relative">
                <button type="button" class="text-2xl w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg bg-white dark:bg-[#303236] hover:bg-gray-100 dark:hover:bg-[#3a3c40]" x-data x-on:click="$refs.emojiPanel.classList.toggle('hidden')">{{ $emoji }}</button>
                <div x-ref="emojiPanel" class="hidden absolute top-11 left-0 z-10 bg-white dark:bg-[#303236] border border-gray-300 rounded-lg p-2 shadow-lg grid grid-cols-7 gap-1 w-64">
                    @foreach(['📦','🌐','🖥','⚙️','🔧','🛠','🚀','💻','📂','🗂','🏗','🧪','📡','🎯','🐳','🐘','☕','🧩','🎨','⭐','❤️','🟣','🔵','🟡','🔒','🛒','📊','🎮','🤖','💬','📧','🔔','📱','🏠','🎵','📸','🍕','🐱','🌿','💡','🔥','🧠'] as $e)
                        <button type="button" class="text-xl w-8 h-8 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-[#404246]" wire:click="$set('emoji', '{{ $e }}')" x-on:click="$refs.emojiPanel.classList.add('hidden')">{{ $e }}</button>
                    @endforeach
                </div>
            </div>
            <input type="text" wire:model.live="name" placeholder="Name" class="border border-gray-300 focus:outline-2 focus:outline-blue-400 bg-white dark:bg-[#303236] placeholder:text-gray-300 flex-1 px-3 py-1 rounded-lg">
        </div>
     
        <input type="text" wire:model.live="path" wire:click="find_path()" placeholder="Path" class="border border-gray-300 focus:outline-2 focus:outline-blue-400 bg-white dark:bg-[#303236] placeholder:text-gray-300 w-full px-3 py-1 rounded-lg mt-2">
     
        <div class="flex w-full mt-4">
            <button type="cancel" class="w-1/2 ml-auto bg-white dark:bg-[#303236] border-1 border-gray-300 px-3 py-1 rounded-lg shadow-xs" wire:click="back()" wire:navigate>Cancel</button>
            <button type="submit" class="w-1/2 ml-4 mr-auto bg-blue-400 text-white border-1 border-gray-300 px-3 py-1 rounded-lg shadow-xs disabled:bg-blue-300" @if ($this->error()) disabled @endif>Save</button>
        </div>
    </form>
</div>
