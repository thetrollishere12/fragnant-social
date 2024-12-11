<div>
    <div class="m-auto p-3 bg-white">
        <x-input label="Key" wire:model.defer="key" placeholder="Title like - ANONYMOUS_SHOPPING, HEADER_CLASS_COLOR, FREE_SHIPPING_AMOUNT, SOCIAL_MEDIA_INSTAGRAM, HEADER_MESSAGE"/>
        <br>
        <x-select
            label="Select Type"
            placeholder="Select one type"
            :options="$types"
            wire:model="type"
        />
        <br>
        <div>
            <label class="font-bold pt-2">Value</label>
            <div class="py-2">
                @if($type == 'File')
                    <input id="image-upload" type="file" wire:model="value">
                @else
                    <x-textarea placeholder="Write something here. Make sure it's a {{ $type }}" wire:model.defer="value" />
                @endif
            </div>
        </div>

        <x-button wire:click="submit" class="w-full" spinner primary label="Save Block" />

        <div>
            <h1 class="font-bold text-3xl py-4">Block List</h1>
        </div>

        <div>
            @foreach($webblocks as $block)
                <div class="grid grid-cols-6 py-2 gap-2 text-sm border-b">
                    <div>{{ $block->block_key }}</div>
                    <div>({{ $block->block_type }})</div>
                    <div class="col-span-3">{{ $block->block_value }}</div>
                    <div class="text-right">
                        <x-button wire:click="edit({{ $block->id }})" xs spinner primary label="Edit" />
                        <x-button wire:click="delete({{ $block->id }})" xs spinner red label="Delete" />
                    </div>
                </div>
            @endforeach
        </div>

        <x-wui-modal.card title="Edit Block" max-width="3xl" blur wire:model.defer="editModal">
            <x-input label="Key" wire:model.defer="editKey" placeholder="Title"/>
            <br>
            <x-select
                label="Select Type"
                placeholder="Select one type"
                :options="$types"
                wire:model="editType"
            />
            <br>
            <div>
                <label class="font-bold pt-2">Value</label>
                <div class="py-2">
                    @if($editType == 'File')
                        <input id="image-upload-edit" type="file" wire:model="editValue">
                    @else
                        <x-textarea placeholder="Write something here. Make sure it's a {{ $editType }}" wire:model.defer="editValue" />
                    @endif
                </div>
            </div>

            <x-button wire:click="update" class="w-full" spinner primary label="Update Block" />
        </x-wui-modal.card>
    </div>
</div>