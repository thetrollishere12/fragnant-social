<div class="p-6 bg-white shadow-md rounded-md">
    <h1 class="text-2xl font-bold mb-4">Imported Product Feeds</h1>

@foreach($productImportFeeds as $feed)

    <div class="mb-6">

        @php
            $paginatedProducts = $feed->products()->simplePaginate(20);
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($paginatedProducts as $product)
                <div class="bg-gray-100 p-2.5 rounded-lg shadow-sm">
                    <div class="mb-1">
                        <span class="text-gray-800 font-semibold">{{ $product->detail->name ?? 'Unnamed Product' }}</span>
                    </div>
                    
                    <div class="text-indigo-500 text-sm"><a target="#" href="{{ $feed->url }}">Source: {{ $feed->name }}</a></div>

                    <div class="grid grid-cols-4 gap-1 rounded-lg overflow-hidden my-2">
                        @foreach($product->media as $media)
                        <div>
                            <img src="{{ $media->url }}" class="w-full h-auto" />
                        </div>
                        @endforeach
                    </div>

                    <x-button wire:click="generate" spinner="sleeping" primary label="Generate" />

                    <!-- Open Modal Button -->
                    <x-button wire:click="editProduct({{ $product->id }})" outline label="Edit" />

                </div>
            @endforeach
        </div>

        <!-- Pagination Links -->
        <div class="mt-4">
            {{ $paginatedProducts->links() }}
        </div>
    </div>

@endforeach








    <!-- WireUI Modal for CSV Input -->
<x-wui-modal.card wire:model="edit_modal" title="Edit Product">
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