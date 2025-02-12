<div class="p-4 bg-white shadow-md rounded-md">
    <h1 class="text-2xl font-bold mb-4">Link My Platform</h1>

    <div class="flex space-x-3">
        <!-- Etsy Button -->
        <a href="{{ url('connect-platform-etsy-login?project_id=' . $digital_asset_id) }}">
            <div class="rounded-md py-2 px-3 text-white" style="background: #f5700a;">
                <span class="text-lg icon-etsy"></span>
            </div>
        </a>


        

        <!-- Amazon Button -->
        <a href="{{ url('connect-platform-amazon-login?project_id=' . $digital_asset_id) }}">
            <div class="rounded-md py-2 px-3 text-white" style="background: #FF9900;">
                <span class="text-lg icon-amazon"></span>
            </div>
        </a>

        <!-- Amazon Button -->
        <a href="{{ url('connect-platform-shopify-login?project_id=' . $digital_asset_id) }}">
            <div class="rounded-md py-2 px-3 text-white" style="background: #95BF47;">
                <span class="text-lg icon-shopify"></span>
            </div>
        </a>



        <!-- eBay Button -->
        <a href="{{ url('connect-platform-ebay-login?project_id=' . $digital_asset_id) }}">
            <div class="rounded-md py-2 px-3 text-white bg-gray-100">
                <img class="w-8" src="{{ Storage::disk('public')->url('image/logo/ebay.svg') }}" />
            </div>
        </a>



        <!-- Open Modal Button -->
        <x-button label="Add Feed" x-on:click="$openModal('cardModal')" primary />
    </div>






        <!-- Lists for Connected eBay Accounts and Import Feeds -->
    <div class="grid grid-cols-2 gap-4 mt-6">
        <!-- eBay Accounts List -->
        <div class="bg-gray-100 p-4 rounded-md">
            <h2 class="text-lg font-semibold">Connected eBay Accounts</h2>
            <ul>
                @forelse ($ebay as $account)
                    <div class="rounded-md py-2 px-3 text-white bg-gray-100 shadow inline-block my-2">
                        <img class="w-8" src="{{ Storage::disk('public')->url('image/logo/ebay.svg') }}" />
                    </div>
                    <li class="p-2 border-b border-gray-300 flex justify-between items-center">

                        <div class="flex items-center">
                            <img src="{{ $account->avatar_url }}" class="w-8 h-8 rounded-full mr-3" alt="Avatar">
                            <span>{{ $account->name }} ({{ $account->account_id }})</span>
                        </div>
                    </li>
                @empty
                    <li class="p-2 text-gray-500">No eBay accounts connected.</li>
                @endforelse
            </ul>
        </div>

        <!-- Import List -->
        <div class="bg-gray-100 p-4 rounded-md">
            <h2 class="text-lg font-semibold mb-2">Imported Product Feeds</h2>
            <ul>
                @forelse ($import as $feed)
                    <li class="p-2 border-b border-gray-300 flex justify-between items-center">
                        <div>
                            <span class="font-medium">{{ $feed->name }}</span> 
                            <span class="text-sm text-gray-600">({{ $feed->file_type }})</span>
                        </div>
                        <a href="{{ $feed->url }}" target="_blank" class="text-blue-500 hover:underline">View</a>
                    </li>
                @empty
                    <li class="p-2 text-gray-500">No imported product feeds.</li>
                @endforelse
            </ul>
        </div>
    </div>






    <!-- WireUI Modal for CSV Input -->
<x-wui-modal.card wire:model="url_modal" title="Edit Customer" name="cardModal">
    <div class="grid grid-cols-1 gap-4">
        
        <x-input wire:model="name" label="File Name" placeholder="Enter Name..." />
        <x-input wire:model="url" label="File URL" placeholder="Enter URL..." />

    </div>
 
    <x-slot name="footer" class="flex justify-between gap-x-4">
        <x-button flat negative label="Delete" x-on:click="close" />
 
        <div class="flex gap-x-4">
            <x-button flat label="Cancel" x-on:click="close" />
 
            <x-button primary label="Save" wire:click="save_url" />
        </div>
    </x-slot>
</x-wui-modal.card>





</div>