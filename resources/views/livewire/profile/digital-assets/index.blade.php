<div>


    <!-- Header Section -->
    <div class="flex justify-between items-center mb-4">
        <div></div>
        <x-button primary label="Add New Asset" wire:click="$set('showModal', true)" />
    </div>

<!-- Enhanced Asset Table -->
<div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="min-w-full border-collapse border border-gray-200">
        <thead class="bg-gray-200 text-gray-700">
            <tr>
                <th class="border border-gray-300 px-6 py-3 text-left font-medium">Name</th>
                <th class="border border-gray-300 px-6 py-3 text-left font-medium">Description</th>
                <th class="border border-gray-300 px-6 py-3 text-left font-medium">Image</th>
                <th class="border border-gray-300 px-6 py-3 text-right font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assets as $asset)
                <tr class="hover:bg-gray-100 odd:bg-gray-50">
                    <td class="border border-gray-300 px-6 py-4">{{ $asset->name }}</td>
                    <td class="border border-gray-300 px-6 py-4 text-gray-600">{{ $asset->description }}</td>
                    <td class="border border-gray-300 px-6 py-4">
                        @if ($asset->image)
                            <div>
                                <img src="{{ asset('storage/' . $asset->image) }}" 
                                     alt="Asset Image" 
                                     class="w-16 h-16">
                            </div>
                        @else
                            <span class="text-gray-500 italic">No Image</span>
                        @endif
                    </td>
                    <td class="border border-gray-300 px-6 py-4 text-right">
                        <div class="flex justify-end space-x-2">
                            <x-button flat label="Edit" wire:click="editAsset({{ $asset->id }})" class="text-blue-600 hover:bg-blue-100" />
                            
                            <a href="{{ url('user/digital-assets/'.$asset->id) }}" class="inline-flex">
                                <x-button primary label="Show" class="text-green-600 hover:bg-green-100" />
                            </a>

                            <x-button negative label="Delete" wire:click="deleteAsset({{ $asset->id }})" class="text-red-600 hover:bg-red-100" />
                            
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-8 text-gray-500 italic">No digital assets found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


    <!-- Pagination -->
    <div class="mt-4">
        {{ $assets->links() }}
    </div>

    <!-- Modal -->
    <x-wui-modal.card blur max-width="5xl" wire:model.defer="showModal">

        <div class="px-3">

            <div class="mb-4">
                <x-input label="Name" wire:model="name" placeholder="Enter asset name" />
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <x-textarea label="Description" wire:model="description" placeholder="Enter asset description" />
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
    <x-input label="Image" type="file" wire:model="image" />
    <div wire:loading wire:target="image" class="text-gray-500 text-sm">Uploading...</div>
    @if ($image)
        <img src="{{ $image->temporaryUrl() }}" alt="Image Preview" class="mt-2 w-16 h-16 rounded">
    @endif
    @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
</div>

        </div>

    <x-slot name="footer">
        <div class="flex justify-between gap-x-4">
            <x-button secondary label="Cancel" wire:click="resetForm" />
            <x-button 
                primary 
                label="Save" 
                wire:click="saveAsset" 
                wire:loading.attr="disabled" 
                spinner
                wire:target="saveAsset, image"
            >

            </x-button>
        </div>
    </x-slot>

    </x-wui-modal.card>

</div>