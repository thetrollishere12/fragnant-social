<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Published Media') }}
        </h2>
    </x-slot>

    <div class="md:flex">
        <!-- Sidebar -->
        <x-custom.profile-nav>
            <x-slot name="url">published</x-slot>
        </x-custom.profile-nav>

        <!-- Media Content -->
        @livewire('profile.media.published')

    </div>
</x-guest-layout>