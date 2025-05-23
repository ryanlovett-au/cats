<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;

use App\Models\Application;

new class extends Component {
    
    public Collection $applications;

    public function mount() {
        $this->applications = Application::select()->orderBy('name')->with('services')->get();
    }

    public function click($route, $id = null) {
        return redirect()->route($route, ['id' => $id]);
    }
};

?>

<div class="text-slate-800 dark:text-gray-200">
    <h3 class="pb-2 font-semibold text-sm ml-2 mb-4">Applications/Sites</h3>

        <div class="w-full border-1 border-gray-300 rounded-lg text-sm bg-[#e7e9eb] dark:bg-[#303236]">
            @foreach($this->applications as $app)
                <div class="px-4 py-3 flex justify-between active:bg-gray-300" wire:click="click('application', $app->id)" wire:navigate>
                    <div class="">{{ $app->name }}</div>
                    <div class="flex">
                        <div class="w-5 mr-4 rounded-lg bg-blue-400 text-white text-bold text-center pt-[2px] text-xs cursor-default select-none">{{ $app->services->count() }}</div>
                        <div class="pt-1"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" class="stroke-slate-400 size-3"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg></div>
                    </div>
                </div>

                @if (!$loop->last)
                    <hr class="w-[92%] ml-auto mr-auto border-gray-300" />
                @endif
            @endforeach 
        </div>

        <div class="bg-white dark:text-slate-800 w-32 font-medium ml-auto mt-4 py-1 px-2 text-xs border-1 border-gray-300 rounded-lg shadow-xs text-center cursor-default active:bg-gray-200 select-none" wire:click="click('application_add')" wire:navigate>Add Application...</div>

    </div>
</div>
