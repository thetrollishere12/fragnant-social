        <div class="w-full border-l border-gray-200 p-4">

            <x-button primary label="Generate One" wire:click="generate"/>

            <x-validation-errors class="mb-4" />

            <div>
                <div class="md:col-span-1 flex justify-between">
                    <div class="p-1">
                        <h3 class="text-lg font-medium text-gray-900">Media List</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            List of your Media
                        </p>
                    </div>
                    <div class="p-1">
                        <!-- Add any action buttons or filters here -->
                    </div>
                </div>

                <!-- Media Table -->
                <div class="bg-white shadow-md rounded-lg p-6 mt-4">
                    <table class="table-auto w-full border-collapse border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border border-gray-300 text-left">ID</th>
                                <th class="px-4 py-2 border border-gray-300 text-left">File</th>
                                <th class="px-4 py-2 border border-gray-300 text-left">Uploaded</th>
                                <th class="px-4 py-2 border border-gray-300 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($published as $item)
                                <tr>
                                    <td class="px-4 py-2 border border-gray-300">{{ $item->id }}</td>
                                    <td class="px-4 py-2 border border-gray-300">
                                        <a href="{{ Storage::url($item->url) }}" target="_blank" class="text-blue-500 hover:underline">
                                            {{ basename($item->url) }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 border border-gray-300">
                                        {{ $item->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-4 py-2 border border-gray-300">
                                        <a href="{{ route('download.published-media', $item->id) }}" class="text-blue-500 hover:underline">
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 border border-gray-300 text-center">
                                        No Media Found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
