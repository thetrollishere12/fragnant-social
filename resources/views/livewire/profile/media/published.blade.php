<div class="w-full border-l border-gray-200 p-6 bg-gray-50">
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
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Preview</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">File</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Uploaded</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($published as $item)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $item->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">
                            <img src="{{ Storage::url($item->url) }}" alt="Preview" class="h-12 w-12 object-cover rounded" />
                        </td>
                        <td class="px-4 py-3 text-sm text-blue-600 hover:underline">
                            <a href="{{ Storage::url($item->url) }}" target="_blank">{{ basename($item->url) }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->created_at->format('M d, Y h:i A') }}</td>
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('download.published-media', $item->id) }}" class="text-blue-500 hover:underline">Download</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No Media Found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>