<div class="w-full space-y-6 bg-gray-50 p-6 rounded-lg shadow-md">

    <!-- Asset Image Section -->

    <a href="{{ url('user/digital-assets/'.$digitalAsset->id) }}">

            
            
                <div class="flex items-center">

                    @if ($digitalAsset->image)
                        <img src="{{ asset('storage/' . $digitalAsset->image) }}" 
                             alt="Digital Asset" 
                             class="w-14 h-14 p-2 object-cover">
                    @endif

                    <h3 class="text-3xl font-bold text-gray-700 ml-3">{{ $digitalAsset->name }}</h3>

                </div>
      

    </a>

    <!-- Grid Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Media -->
        <a href="{{ url('user/digital-assets/'.$digitalAsset->id.'/media') }}" 
           class="flex items-center justify-center p-2 text-sm font-bold bg-white rounded-md text-gray-800 shadow-sm hover:bg-gray-100 transition">
            Media
        </a>

        <!-- Publish -->
        <a href="{{ url('user/digital-assets/'.$digitalAsset->id.'/published') }}" 
           class="flex items-center justify-center p-2 text-sm font-bold bg-white rounded-md text-gray-800 shadow-sm hover:bg-gray-100 transition">
            Published
        </a>


    </div>

    


    <!-- Slot Section -->
    <div>
        {{ $slot }}
    </div>

</div>
