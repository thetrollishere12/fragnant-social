<div class="w-full bg-gray-50">
    <!-- Action Button -->
    <div class="flex justify-between items-center mb-6">
        <x-button primary label="Generate One" wire:click="generate" class="shadow" />
        <x-validation-errors class="text-red-500 text-sm" />
    </div>

    <!-- Media List Header -->
    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">Media List</h3>
            <p class="text-sm text-gray-600">Manage your uploaded media files.</p>
        </div>
        <div>
            <!-- Placeholder for action buttons or filters -->
        </div>
    </div>


@if($isProcessing || $stageStatus == 'Completed' || $stageStatus == 'Failed')
<div class="mb-4 p-4 border rounded shadow 
    @if($stageStatus == 'Completed') bg-indigo-100 
    @elseif($stageStatus == 'Failed') bg-red-100 
    @else bg-white 
    @endif"
    role="status" aria-live="polite">
    
    <h3 class="text-lg font-semibold text-gray-700">
        @if($stageStatus == 'Completed')
            Processing Completed
        @elseif($stageStatus == 'Failed')
            Processing Failed
        @else
            Processing Status
        @endif
    </h3>

    <div class="flex items-center space-x-3 mt-2">
        @if($isProcessing && $stageStatus != 'Failed')
            <div class="loader w-8 h-8 border-4 border-t-indigo-500 border-gray-200 rounded-full animate-spin"></div>
        @elseif($stageStatus == 'Failed')
            <div class="w-8 h-8 flex items-center justify-center text-red-600">
                &#x2716; <!-- Cross icon -->
            </div>
        @endif

        <span class="text-sm text-gray-600">
            <strong>Stage:</strong> {{ $stageStatus }} |
            <strong>Message:</strong> {{ $stageMessage }}
        </span>
    </div>

    <!-- Progress Bar -->
    <div class="mt-4 w-full bg-gray-200 rounded-full h-2.5">
        <div 
            class="@if($stageStatus == 'Failed') bg-red-600 @else bg-indigo-600 @endif h-2.5 rounded-full transition-all ease-in-out duration-500" 
            style="width: {{ $progress }}%;">
        </div>
    </div>
</div>
@endif

<style>
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
    .loader {
        border-top-color: #4a90e2;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
</style>


    <!-- Media Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">






<table class="table-auto w-full border-collapse shadow rounded-lg overflow-hidden">
    <thead class="bg-gray-100 border-b border-gray-200">
        <tr>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Preview</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Published</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Type</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($published as $item)
            <!-- Main Row -->
            <tr class="border-b hover:bg-gray-50 slideshow-row"
                data-images='@json($item->thumbnail_url_all_files ?? [])'>
                <td class="px-4 py-3 text-sm text-gray-800">
                    <div class="relative h-32 w-32 overflow-hidden rounded border border-gray-200">
                        @if (!empty($item->thumbnail_url))
                            <img src="{{ $item->thumbnail_url }}"
                                alt="Thumbnail Preview"
                                class="h-full w-full object-cover slideshow-image">
                        @else
                            <div class="flex items-center justify-center h-full w-full bg-gray-200 text-gray-500">
                                No Image
                            </div>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    {{ $item->created_at->diffForHumans() }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-600 capitalize">{{ $item->details->type }}</td>
                <td class="px-4 py-3 text-sm space-y-2">
                    <a href="{{ route('download.published-media', $item->id) }}"
                       class="text-indigo-500 hover:underline">Download</a>
                    <div wire:click="preview('{{ $item->id }}')" 
                         class="cursor-pointer text-indigo-500 hover:underline">Preview</div>
                    <button onclick="toggleDetails('{{ $item->id }}')"
                            class="text-blue-500 hover:underline focus:outline-none">
                        Details
                    </button>
                </td>
            </tr>

            <!-- Details Row -->
            <tr id="details-{{ $item->id }}" class="hidden">
                <td colspan="4">
                    <div class="p-4 bg-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Media Clips</h4>
                        <div class="flex flex-wrap gap-4">

                            @if(isset($item->details->mediaTemplate))

                                <div class="flex flex-col items-center">
                                    <!-- Media Thumbnail -->

                                    <div class="w-20 h-20 overflow-hidden rounded border border-gray-200">
                                        @if (!empty($item->details->mediaTemplate->thumbnail_url))
                                        <img class="w-full h-full object-cover"
                                             src="{{ $item->details->mediaTemplate->thumbnail_url }}"
                                             alt="Media Thumbnail">
                                        @else
                                            <div class="flex items-center justify-center h-full w-full bg-gray-200 text-gray-500">
                                                No Image
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Weight Indicator -->
                                    <div class="text-center mt-2">
                                        <div class="text-xs bg-blue-500 text-white flex items-center justify-center rounded-full px-2 py-1">Template</div>
                                    </div>
                                </div>

                            @endif

                            @foreach ($item->assetMaps as $media)
                                <div class="flex flex-col items-center">
                                    <!-- Media Thumbnail -->
                                    <div class="w-20 h-20 overflow-hidden rounded border border-gray-200">
                                        
                                        @if (!empty($media->userMedia->thumbnail_url))
                                        <img class="w-full h-full object-cover"
                                             src="{{ $media->userMedia->thumbnail_url }}"
                                             alt="Media Thumbnail">
                                        @else
                                            <div class="flex items-center justify-center h-full w-full bg-gray-200 text-gray-500">
                                                No Image
                                            </div>
                                        @endif

                                    </div>

                                    <!-- Weight Indicator -->
                                    <div class="text-center mt-2">
                                        <div class="text-xs bg-blue-500 text-white w-6 h-6 flex items-center justify-center rounded-full">
                                            {{ $media->weight }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                    No Media Found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<script>
    function toggleDetails(itemId) {
        const detailsRow = document.getElementById(`details-${itemId}`);
        if (detailsRow) {
            detailsRow.classList.toggle('hidden');
        }
    }
</script>






    </div>


<!-- Preview Modal -->
<div class="@if($showPreviewModal) block @else hidden @endif fixed z-50 left-0 top-0 w-full h-full bg-black bg-opacity-75">
    <div class="flex items-center justify-center h-full">
        <div class="p-4 rounded-lg w-full max-w-6xl"> <!-- Adjust max-w for wide videos -->
            @if($previewMedia)
                <video 
                    controls 
                    autoplay 
                    class="w-full h-auto max-h-[90vh] object-contain">
                    <source src="{{ Storage::disk($previewMedia->storage)->url($previewMedia->url) }}" type="video/mp4">
                </video>
            @endif
            <button class="icon icon-close text-lg absolute top-4 right-4 text-white" wire:click="closePreview"></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('.slideshow-row');

    // Function to preload images
    const preloadImages = (imagePaths) => {
        const loadedImages = [];
        imagePaths.forEach((path, index) => {
            const img = new Image();
            img.src = `/storage/${path}`;
            loadedImages[index] = img; // Store preloaded images for reuse
        });
        return loadedImages;
    };

    rows.forEach(row => {
        let slideshowInterval;
        let currentIndex = 0;

        const images = JSON.parse(row.getAttribute('data-images') || '[]');
        const imageElement = row.querySelector('.slideshow-image');

        if (!images.length) return; // Exit if no images

        // Preload images and store preloaded Image objects
        const preloadedImages = preloadImages(images);

        // Start slideshow on hover
        row.addEventListener('mouseenter', () => {
            if (images.length > 1) {
                slideshowInterval = setInterval(() => {
                    currentIndex = (currentIndex + 1) % images.length;
                    imageElement.src = preloadedImages[currentIndex].src; // Use preloaded image
                }, 200); // Change images every 1 second
            }
        });

        // Stop slideshow and reset to the first image
        row.addEventListener('mouseleave', () => {
            clearInterval(slideshowInterval);
            currentIndex = 0; // Reset index
            imageElement.src = preloadedImages[0].src; // Reset to the first image
        });
    });
});
</script>





</div>