<x-guest-layout>



    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Published Media') }}
        </h2>
    </x-slot>

    <div class="md:flex">
        <!-- Sidebar -->
        <x-custom.profile-nav>
            <x-slot name="url">digital-assets</x-slot>
        </x-custom.profile-nav>


        <div class="w-full border-l border-gray-200 p-4">

        <x-validation-errors class="mb-4" />

        <div class="md:col-span-1 flex justify-between">
              <div class="p-1">
                 <h3 class="text-lg font-medium text-gray-900">Published</h3>
                 <p class="mt-1 text-sm text-gray-600">
                    List of your published content
                 </p>
              </div>
              <div class="p-1">
              </div>
           </div>
        <x-custom.digital-asset-layout :digitalAssetId="$digital_asset_id">
        <!-- Media Content -->
        @livewire('profile.digital-assets.published',[
                'digital_asset_id'=>$digital_asset_id
           ])

       </x-custom.digital-asset-layout>

</div>

    </div>
</x-guest-layout>