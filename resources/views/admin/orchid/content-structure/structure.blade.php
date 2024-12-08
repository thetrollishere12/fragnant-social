<div>
    <?php
    $disk = $structure->storage;
    $path = $structure->folder . '/' . $structure->filename;
    $imageUrl = Storage::disk($disk)->url($path);

    // Check if the file exists
    $fileExists = Storage::disk($disk)->exists($path);

    // Get file extension to determine media type
    $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
    $mediaType = 'unknown';

    // Define known media types
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $videoExtensions = ['mp4', 'avi', 'mov'];
    $audioExtensions = ['mp3', 'wav', 'ogg'];

    if (in_array(strtolower($fileExtension), $imageExtensions)) {
        $mediaType = 'image';
    } elseif (in_array(strtolower($fileExtension), $videoExtensions)) {
        $mediaType = 'video';
    } elseif (in_array(strtolower($fileExtension), $audioExtensions)) {
        $mediaType = 'audio';
    }
    ?>


    <div class="p-3 bg-white rounded mb-6">
        @if($fileExists)
            @if($mediaType == 'image')
                <img style="width:300px;" src="{{ $imageUrl }}" alt="Media Image"/>
            @elseif($mediaType == 'video')
                <video width="300" controls>
                    <source src="{{ $imageUrl }}" type="video/{{ $fileExtension }}">
                    Your browser does not support the video tag.
                </video>
            @elseif($mediaType == 'audio')
                <audio controls>
                    <source src="{{ $imageUrl }}" type="audio/{{ $fileExtension }}">
                    Your browser does not support the audio element.
                </audio>
            @else
                <p class="font-bold">Media type is unknown or unsupported.</p>
            @endif
        @else
            <p class="font-bold">Media does not exist.</p>
        @endif
    </div>

    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-button {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #e2e8f0;
            border: none;
            border-radius: 4px;
            margin-right: 5px;
        }
        .tab-button.active {
            background-color: #cbd5e0;
        }
    </style>

<div class="sticky top-0" x-data="{ tab: 'html' }">
        <div class="tabs mb-4">
            <span @click="tab = 'html'" :class="{ 'active': tab === 'html' }" class="tab-button">HTML</span>
            <span @click="tab = 'json'" :class="{ 'active': tab === 'json' }" class="tab-button">JSON</span>
        </div>

        <div class="tab-content" :class="{ 'active': tab === 'html' }">
            <div class="px-2 py-0.5 rounded shadow main-bg-c">
                @foreach($structure->structure as $ib => $block)
                    @include('components.custom.generated_block', ['block' => $block])
                @endforeach
            </div>
        </div>

         <div class="tab-content" :class="{ 'active': tab === 'json' }">
            <div class="pl-4 py-3 mt-4 rounded shadow bg-black">
                <?php 
                // Decode the JSON structure and convert Unicode sequences to their respective characters
                $decodedStructure = json_decode(json_encode($structure->structure), true);
                $prettyJson = json_encode($decodedStructure, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                // Function to highlight JSON
                function highlightJson($json) {
                    // Match JSON keys
                    $json = preg_replace('/"([^"]+)":/', '"<span class="text-indigo-300">$1</span>":', $json);
                    // Match JSON string values
                    $json = preg_replace('/:\s*"([^"]*)"/', ': <span class="text-green-300">"$1"</span>', $json);
                    // Match JSON numbers
                    $json = preg_replace('/:\s*([-+]?\d*\.?\d+)/', ': <span class="text-white">$1</span>', $json);
                    return $json;
                }

                $highlightedJson = highlightJson($prettyJson);
                ?>
                <pre class="bg-black text-indigo-300 text-sm tracking-wide">{!! $highlightedJson !!}</pre>
            </div>
        </div>
    </div>



</div>