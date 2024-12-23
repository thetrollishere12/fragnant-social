<div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Manage Media Templates</h1>



        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input 
                    type="text" 
                    id="title" 
                    wire:model="title" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="Enter title">
                @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Platform -->
            <div>
                <label for="platform" class="block text-sm font-medium text-gray-700">Platform</label>
                <input 
                    type="text" 
                    id="platform" 
                    wire:model="platform" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="Enter platform">
                @error('platform') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <br>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- URL -->
            <div>
                <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                <input 
                    type="text" 
                    id="url" 
                    wire:model="url" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="Enter URL">
                @error('url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- File Upload -->
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700">File Upload</label>
                <input 
                    type="file" 
                    id="file" 
                    wire:model="file" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <br>




        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            

        <x-select
            label="Select Template Type"
            placeholder="Select One"
            wire:model.defer="type"
            :options="[
                'clip-template',
                'clip-template-slideshow',
                'template-slideshow'
            ]"
        />


        </div>



        <br>





        <button 
            
            wire:click="save"

            class="w-full inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Save
        </button>
  

    @if (session()->has('success'))
        <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <h2 class="mt-8 text-xl font-semibold text-gray-800">Current Media Templates</h2>
    <div class="overflow-x-auto bg-white shadow rounded-lg mt-4">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($mediaTemplates as $template)
                    <tr>
                        <td>
                            @if (!empty($template->thumbnail_url))
                            <img class="w-24 p-2 rounded" src="{{ $template->thumbnail_url }}">
                            @else
                                <div class="flex items-center justify-center h-full w-full bg-gray-200 text-gray-500">
                                    No Image
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $template->id }}</td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $template->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $template->type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $template->platform }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                            @if ($template->thumbnail_url)
                                <a href="{{ Storage::disk($template->storage)->url($template->folder . '/' . $template->filename) }}" 
                                   target="_blank" 
                                   class="hover:underline">
                                    View
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                 
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                            <button 
                                wire:click="deleteTemplate({{ $template->id }})" 
                                class="hover:underline"
                                onclick="return confirm('Are you sure you want to delete this template?')">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
