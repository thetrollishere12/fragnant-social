<div class="mx-auto p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Media Settings</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-4 text-green-800 bg-green-100 border border-green-200 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="saveSettings" class="">

        <div class="grid gap-4 grid-cols-2">

        <!-- Video Types -->
 <!--        <div>
            <label for="videoTypes" class="block text-sm font-medium text-gray-700">Type of Video</label>
            <input type="text" id="videoTypes" wire:model.defer="videoTypes" 
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                   placeholder="e.g., Educational, Music Video" />
        </div> -->

        <!-- Music Genres -->
        <div>
<x-select
        label="Type of Music"
        placeholder="Select one or more genres"
        multiselect
        wire:model.defer="musicGenres"
        :options="$genres->map(fn($name, $id) => ['value' => $id, 'label' => $name])->values()"
        option-label="label"
        option-value="value"
    />
        </div>










        <!-- <div>
            <label for="frequency" class="block text-sm font-medium text-gray-700">How Often</label>
            <input type="number" id="frequency" wire:model.defer="frequency" 
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                   placeholder="e.g., 1, 2, 3" />
        </div> -->



        <div>

            <x-select
                label="Frequency Type"
                placeholder="Select Frequency"
                wire:model.defer="frequencyType"
                :options="[
                    ['value' => 'hourly', 'label' => 'Hourly'],
                    ['value' => 'daily', 'label' => 'Daily'],
                    ['value' => 'weekly', 'label' => 'Weekly'],
                    ['value' => 'monthly', 'label' => 'Monthly'],
                    ['value' => 'yearly', 'label' => 'Yearly'],
                ]"
                option-label="label"
                option-value="value"
            />

        </div>


<!--         <div>
            <label for="quantity" class="block text-sm font-medium text-gray-700">How Many</label>
            <input type="number" id="quantity" wire:model.defer="quantity" 
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                   min="1" />
        </div> -->













<x-select
    label="Select Video Type"
    placeholder="Select multiple"
    wire:model.defer="videoTypes"
    multiselect
    :options="[
        'clipTemplatePair',
        'clipTemplateSlideshow',
        'templateSlideshow',
        'customSlideShow'
    ]"
/>


<div class="flex items-end mb-1">
@if($own_music == true)
<x-toggle id="rounded-full" wire:model.defer="user_audio" rounded="full" label="Use Own Audio" xl />
@endif
</div>

</div>


        <x-errors/>

        <!-- Submit Button -->
        <div>
            <button type="submit" 
                    class="w-full bg-blue-600 text-white mt-4 px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Save Settings
            </button>
        </div>
    </form>
</div>