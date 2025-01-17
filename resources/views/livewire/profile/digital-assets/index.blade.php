<div>




<!-- Enhanced Asset Table -->

        <div class="grid grid-cols-1 gap-3">
            @forelse ($assets as $asset)
                <div class="hover:bg-gray-100 bg-white rounded flex items-center justify-between">

                    <a class="w-full cursor-pointer hover:bg-pink-100 duration-150 ease-out" href="{{ url('user/digital-assets/'.$asset->id) }}">
                        <div class="p-3 flex gap-4 items-center">
                            
                            @if ($asset->image)
                                <div>
                                    <img src="{{ asset('storage/' . $asset->image) }}" 
                                         alt="Asset Image" 
                                         class="w-8 h-8">
                                </div>
                            @endif

                            <div class="font-bold text-lg">{{ $asset->name }}</div>

                        </div>
                    </a>


                    <div class="p-3">

      
                        <x-dropdown>
                            
                                <a href="{{ url('user/digital-assets/'.$asset->id) }}">
                                    <x-dropdown.item icon="eye" label="Show" />
                                </a>


                                <x-dropdown.item icon="pencil" wire:click="editAsset({{ $asset->id }})"  label="Edit" />

                                <x-dropdown.item icon="trash" wire:click="deleteAsset({{ $asset->id }})" label="Delete" />

         
                        </x-dropdown>

                    </div>
                </div>
            @empty
                <div>
                    <div colspan="4" class="text-center py-8 text-gray-500 italic">No digital assets found.</div>
                </div>
            @endforelse
        </div>

        <x-button class="w-full mt-3 py-2 text-lg" primary label="Add New Asset" wire:click="$set('showModal', true)" />

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