<div>

    <!-- Loading Overlay -->
    <!-- <div wire:loading.flex wire:target="image, preview, edit" class="fixed inset-0 z-50 bg-black bg-opacity-50 items-center justify-center">
        <div class="flex items-center space-x-2">
            <span class="icon icon-spinner animate-spin text-white text-3xl"></span>
            <p class="text-white text-lg">Processing your media, please wait...</p>
        </div>
    </div> -->

    <div class="flex flex-wrap gap-2 py-4">
    @foreach($userMedia as $i)

    <div>
        <div class="media-container rounded-lg overflow-hidden
                shadow relative @if(in_array($i->code_id, $selectedMedia)) bg-blue-200 @endif">

             @if($role != 'View')

             <span wire:click="select_media('{{ $i->code_id }}')"  class="media-container icon-checkmark absolute cursor-pointer top-1 left-1 z-20 rounded-full text-xs py-1 px-1.5 @if(in_array($i->code_id, $selectedMedia)) text-white bg-blue-500 block @else hidden bg-gray-100 text-gray-500 opacity-50 @endif"></span>

             @endif

             @if($i->type != "pending")

             <span class="span-icon cursor-pointer icon icon-pencil absolute bottom-1 left-1 z-20 rounded-full text-xs py-1 px-1.5 bg-gray-100 text-gray-500  opacity-50 hover:text-white hover:bg-blue-500 hidden" wire:click="edit('{{ $i->code_id }}')"></span>

             <span class="span-icon cursor-pointer icon icon-search absolute bottom-1 right-1 z-20 rounded-full text-xs py-1 px-1.5 bg-gray-100 text-gray-500  opacity-50 hover:text-white hover:bg-blue-500 hidden" wire:click="preview('{{ $i->code_id }}')"></span>

             @endif

        @if($i->type == "image")
            <img class="
                w-40
                h-40
                cursor-pointer
                transition duration-150 ease-in-out
                object-cover
                @if(in_array($i->code_id, $selectedMedia)) scale-75 @endif "
                @if($role != 'View') wire:click="select_media('{{ $i->code_id }}')" @endif
                src="{{ Storage::disk($i->storage)->url($i->folder.'/'.$i->filename) }}" 
            />
        @elseif($i->type == "video")
            <img class="
                w-40
                h-40
                cursor-pointer
                transition duration-150 ease-in-out
                object-cover
                @if(in_array($i->code_id, $selectedMedia)) scale-75 @endif "
                @if($role != 'View') wire:click="select_media('{{ $i->code_id }}')" @endif
                src="{{ $i->thumbnail_url }}" 
            />
        @elseif($i->type == "audio")
            <div class="w-40 h-40 cursor-pointer rounded-lg bg-gray-200 shadow-sm flex justify-center items-center"
                @if($role != 'View') wire:click="select_media('{{ $i->code_id }}')" @endif
            >
                <span class="icon text-4xl icon-music"></span>
            </div>
        @elseif($i->type == "file")
            <div class="w-40 h-40 cursor-pointer rounded-lg bg-gray-200 shadow-sm flex justify-center items-center"
                @if($role != 'View') wire:click="select_media('{{ $i->code_id }}')" @endif
            >
                <span class="icon text-4xl icon-folder-download"></span>
            </div>
        @elseif($i->type == "pending")
            <!-- Gray box for pending media -->
            <div class="w-40 h-40 cursor-pointer flex justify-center items-center bg-gray-200 shadow-sm rounded-lg"
            @if($role != 'View') wire:click="select_media('{{ $i->code_id }}')" @endif
            >
                <div class="loader w-10 h-10 border-4 border-t-gray-400 border-gray-200 rounded-full animate-spin"></div>
            </div>
        @endif
        </div>

        <div class="text-xs text-center pt-2">{{ substr(pathinfo($i->filename, PATHINFO_FILENAME), 0, 10) }}...{{ pathinfo($i->filename, PATHINFO_EXTENSION) }}</div>
    </div>
    @endforeach
</div>

<style type="text/css">
    
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
.loader {
    border-top-color: #4a5568; /* Adjust for a darker gray */
    border-radius: 50%;
    animation: spin 1s linear infinite;
}


</style>

    <x-errors/>

    <!-- Image Upload -->
    <input wire:loading.attr="disabled" type="file" wire:model="image" multiple accept="*" id="image-upload" class="hidden" name="image"/>

    <x-button primary label="Add Media" class="upload-case mt-4 w-full"/>

    <!-- Preview Modal -->
    <div class="@if($showPreviewModal) block @else hidden @endif fixed z-50 left-0 top-0 w-full h-full bg-black bg-opacity-75">
        <div class="flex items-center justify-center h-full">
            <div class="p-4 rounded-lg max-w-lg w-full">
                @if($previewMedia)
                    @if($previewMedia->type == "image")
                        <img src="{{ Storage::disk($previewMedia->storage)->url($previewMedia->folder . '/' . rawurlencode($previewMedia->filename)) }}" alt="Preview Image" class="max-w-full h-auto">
                    @elseif($previewMedia->type == "video")
                        <video controls autoplay>
                            <source src="{{ Storage::disk($previewMedia->storage)->url($previewMedia->folder . '/' . rawurlencode($previewMedia->filename)) }}" type="video/mp4">
                        </video>
                    @elseif($previewMedia->type == "audio")
                        <audio controls autoplay>
                            <source src="{{ Storage::disk($previewMedia->storage)->url($previewMedia->folder . '/' . rawurlencode($previewMedia->filename)) }}" type="audio/mpeg">
                        </audio>
                    @endif
                @endif
                <button class="icon icon-close text-lg absolute top-4 right-4 text-white" wire:click="closePreview"></button>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <x-wui-modal.card wire:model.defer="showEditModal" title="Edit Media" blur>
        <div class="grid grid-cols-1 gap-4">
            <x-textarea placeholder="Write what this media component is about" label="Description" wire:model.defer="mediaDescription" />
            @if(isset($editMedia) && $editMedia->type == 'image')
                <x-button primary label="AI generate" wire:click="ai_description"/>
            @endif
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <x-button flat negative label="Delete" wire:click="delete" />
                <div class="flex">
                    <x-button flat label="Cancel" x-on:click="close" />
                    <x-button primary label="Save" wire:click="updateMedia" />
                </div>
            </div>
        </x-slot>
    </x-wui-modal.card>



@if($selectedMedia)

<div class="fixed top-0 left-0 shadow bg-white w-full py-4 px-10 z-30">
    
    <div class="flex justify-between">
        
        <div>{{ count($selectedMedia) }} Selected</div>

        <div class="flex gap-8 text-xl">
            @if($role != 'View') 
            <div class="icon icon-folder-download cursor-pointer" wire:click="download"></div>
            <div class="icon icon-bin2 cursor-pointer" wire:click="delete"></div>
            @endif
        </div>

    </div>

</div>

@endif








    <style>
        .media-container:hover span {
            display: block;
        }

        .span-icon:hover {
            opacity: 1 !important;
        }
    </style>

    <script type="text/javascript">
        $('.upload-case').click(function(){
            $(this).prev().trigger('click');
        });
    </script>
</div>