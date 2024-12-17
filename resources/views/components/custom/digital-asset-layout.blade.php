<div class="w-full space-y-6 bg-gray-50 p-6 rounded-lg shadow-md">

    <!-- Asset Image Section -->
    <div>
        @if ($digitalAsset->image)
            <img src="{{ asset('storage/' . $digitalAsset->image) }}" 
                 alt="Digital Asset" 
                 class="w-20 h-20 rounded-md object-cover">
        @else
            <span class="text-gray-500 italic">No Image</span>
        @endif
    </div>


    <!-- Grid Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <!-- Media -->
        <a href="{{ url('user/digital-assets/'.$digitalAsset->id.'/media') }}" 
           class="flex items-center justify-center p-2 text-sm bg-white border rounded-md text-gray-800 font-medium shadow-sm hover:bg-gray-100 transition">
            Media
        </a>

        <!-- Publish -->
        <a href="{{ url('user/digital-assets/'.$digitalAsset->id.'/published') }}" 
           class="flex items-center justify-center p-2 text-sm bg-white border rounded-md text-gray-800 font-medium shadow-sm hover:bg-gray-100 transition">
            Published
        </a>

        <!-- Settings -->
        <a href="{{ url('user/digital-assets/'.$digitalAsset->id.'/settings') }}" 
           class="flex items-center justify-center p-2 text-sm bg-white border rounded-md text-gray-800 font-medium shadow-sm hover:bg-gray-100 transition">
            Settings
        </a>

    </div>

    


    <!-- Slot Section -->
    <div>
        {{ $slot }}
    </div>

</div>
