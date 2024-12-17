<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Digital Asset Details') }}
        </h2>
    </x-slot>

    <div class="md:flex">
        <!-- Sidebar -->
        <x-custom.profile-nav>
            <x-slot name="url">digital-assets</x-slot>
        </x-custom.profile-nav>

        <!-- Main Content -->
        <div class="w-full border-l border-gray-200 p-4">
            <x-validation-errors class="mb-4" />

            <x-custom.digital-asset-layout :digitalAssetId="$asset->id">
<div class="container mx-auto p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900">Digital Asset Details</h1>
        <a href="{{ url('digital-assets.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition duration-200">
            &larr; Back to List
        </a>
    </div>

    <!-- Asset Details Card -->
    <div class="bg-white border border-gray-200 shadow-lg rounded-xl overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
            <!-- Text Content -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Name</h2>
                    <p class="text-gray-600 text-base">{{ $asset->name }}</p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Description</h2>
                    <p class="text-gray-600 text-base leading-relaxed">{{ $asset->description }}</p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Storage Used</h2>
                    <p class="text-gray-600 text-base">{{ $storageUsedGB }} GB</p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Videos Uploaded This Month</h2>
                    <p class="text-gray-600 text-base">{{ $monthlyVideoCount }}</p>
                </div>
            </div>

            <!-- Image Section -->
            <div class="flex justify-center items-center">
                @if ($asset->image)
                    <div class="relative w-64 h-64 md:w-80 md:h-80">
                        <img src="{{ asset('storage/' . $asset->image) }}" alt="Asset Image"
                             class="w-full h-full object-cover rounded-lg shadow-md hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 rounded-lg"></div>
                    </div>
                @else
                    <div class="w-full flex items-center justify-center p-8 bg-gray-100 border rounded-lg">
                        <p class="text-gray-500 text-sm">No image available.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
            </x-custom.digital-asset-layout>
        </div>
    </div>
</x-guest-layout>
