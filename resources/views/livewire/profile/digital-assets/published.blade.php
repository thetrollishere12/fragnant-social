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

    <!-- Media Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="table-auto w-full border-collapse">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Preview</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Uploaded</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
<tbody>
    @forelse ($published as $item)
        <tr class="border-b hover:bg-gray-50 slideshow-row" 
            data-images='@json($item->thumbnail_url_all_files)'>
            <td class="px-4 py-3 text-sm text-gray-800">
                <div class="relative h-32 w-32 overflow-hidden rounded">
                    <img src="{{ Storage::url($item->thumbnail_url_all_files[0]) }}"
                         alt="Thumbnail Preview"
                         class="h-full w-full object-cover slideshow-image">
                </div>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">
                {{ $item->created_at->format('M d, Y h:i A') }}
            </td>
            <td class="px-4 py-3 text-sm">
                <a href="{{ route('download.published-media', $item->id) }}"
                   class="text-blue-500 hover:underline">Download</a>

                   <div wire:click="preview('{{ $item->id }}')">Preview</div>

            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                No Media Found.
            </td>
        </tr>
    @endforelse
</tbody>

        </table>






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

    rows.forEach(row => {
        let slideshowInterval;
        const images = JSON.parse(row.getAttribute('data-images'));
        const imageElement = row.querySelector('.slideshow-image');
        let currentIndex = 0;

        // Start slideshow on hover
        row.addEventListener('mouseenter', () => {
            if (images.length > 1) {
                slideshowInterval = setInterval(() => {
                    currentIndex = (currentIndex + 1) % images.length;
                    imageElement.src = `/storage/${images[currentIndex]}`;
                }, 200); // Change images every 500ms
            }
        });

        // Stop slideshow and reset image
        row.addEventListener('mouseleave', () => {
            clearInterval(slideshowInterval);
            imageElement.src = `/storage/${images[0]}`; // Reset to the first image
        });
    });
});
</script>




</div>