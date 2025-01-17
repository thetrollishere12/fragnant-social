<x-guest-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Projects') }}
        </h2>
    </x-slot>

    <div class="md:flex">

        <x-custom.profile-nav>
            <x-slot name="url">digital-assets</x-slot>
        </x-custom.profile-nav>

        <div class="w-full border-l border-gray-200 p-4">

        <x-validation-errors class="mb-4" />

        <div>
           
           @livewire('profile.digital-assets.index')

        </div>

        </div>
    </div>



</x-guest-layout>