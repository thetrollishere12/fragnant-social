<x-guest-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Media Setting') }}
        </h2>
    </x-slot>

    <div class="md:flex">

        <x-custom.profile-nav>
            <x-slot name="url">digital-assets</x-slot>
        </x-custom.profile-nav>

        <div class="w-full border-l border-gray-200 p-4">

        <x-validation-errors class="mb-4" />

        <div>
           <div class="md:col-span-1 flex justify-between">
              <div class="p-1">
                 <h3 class="text-lg font-medium text-gray-900">Media Setting</h3>
                 <p class="mt-1 text-sm text-gray-600">
                    List of your Media Settings
                 </p>
              </div>
              <div class="p-1">
              </div>
           </div>
           
           <x-custom.digital-asset-layout :digitalAssetId="$digital_asset_id">

               @livewire('profile.digital-assets.setting',[
                    'digital_asset_id'=>$digital_asset_id
               ])

           </x-custom.digital-asset-layout>

        </div>

        </div>
    </div>



</x-guest-layout>