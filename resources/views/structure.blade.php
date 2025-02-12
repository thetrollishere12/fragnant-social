<x-guest-layout>
    <div class="max-w-full mx-auto bg-gray-100 shadow-lg rounded-lg p-6">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">🎬 Advanced Video Editing Timeline</h2>
        
        <!-- Full Timeline Wrapper -->
        <div class="relative bg-gray-800 p-4 rounded-lg w-full h-96">
            <div class="relative w-full h-full bg-gray-900 rounded-lg overflow-hidden flex flex-col px-4 py-2">
                
                <!-- Time Markers -->
                <div class="absolute top-0 left-0 w-full flex justify-between text-gray-400 text-xs">
                    @for($i = 0; $i <= 100; $i+=10)
                        <div class="absolute" style="left: {{ $i }}%">{{ $i }}s</div>
                    @endfor
                </div>

                <!-- Video Tracks -->
                <div class="relative w-full flex flex-col space-y-2">
                    @foreach($project['timeline']['tracks']['video'] as $track)
                        <div class="relative w-full h-20 bg-gray-700 rounded-lg flex items-center px-2 overflow-hidden">
                            @foreach($track['clips'] as $clip)
                                <div 
                                    class="absolute bg-blue-500 text-white px-4 py-2 rounded-md shadow-md text-xs border border-blue-300 flex items-center space-x-2"
                                    style="left: {{ (int)str_replace(':', '', $clip['start_time']) / 10 }}%; width: {{ (int)str_replace(':', '', $clip['end_time']) / 10 }}%; bottom: 10px;">
                                    <img src="{{ asset('thumbnails/'.basename($clip['file_path']).'.jpg') }}" class="h-12 w-auto rounded-sm shadow-md" alt="Clip Thumbnail">
                                    <div>
                                        🎞 {{ $clip['clip_id'] }}<br>
                                        🕒 {{ $clip['start_time'] }} - {{ $clip['end_time'] }}<br>
                                        📂 {{ basename($clip['file_path']) }}<br>
                                        🔲 Opacity: {{ $clip['opacity'] }}<br>
                                        🔄 Scale: {{ $clip['scale'] }}<br>
                                        ✖ Position X: {{ $clip['position_x'] }}, Y: {{ $clip['position_y'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Audio Tracks -->
        <div class="relative bg-gray-800 p-4 rounded-lg w-full h-32 mt-4">
            <h3 class="text-lg font-semibold text-green-400 mb-2">🎵 Audio Tracks</h3>
            <div class="relative w-full h-full bg-gray-900 rounded-lg overflow-hidden flex flex-col px-4 py-2">
                @foreach($project['timeline']['tracks']['audio'] as $track)
                    <div class="relative w-full h-12 bg-gray-700 rounded-lg flex items-center px-2 overflow-hidden">
                        @foreach($track['clips'] as $clip)
                            <div 
                                class="absolute bg-green-500 text-white px-4 py-2 rounded-md shadow-md text-xs border border-green-300 flex items-center space-x-2"
                                style="left: {{ (int)str_replace(':', '', $clip['start_time']) / 10 }}%; width: {{ (int)str_replace(':', '', $clip['end_time']) / 10 }}%; bottom: 0px;">
                                🎵 {{ $clip['clip_id'] }}<br>
                                🕒 {{ $clip['start_time'] }} - {{ $clip['end_time'] }}<br>
                                📂 {{ basename($clip['file_path']) }}
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Project Export Settings -->
        <div class="bg-gray-200 p-4 rounded-lg mt-6">
            <h3 class="text-lg font-semibold text-gray-700">⚙ Export Settings</h3>
            <p>🎞 Format: {{ $project['export_settings']['format'] }}</p>
            <p>📡 Bitrate: {{ $project['export_settings']['bitrate'] }}</p>
            <p>🔊 Audio Codec: {{ $project['export_settings']['audio_codec'] }}</p>
            <p>🎥 Video Codec: {{ $project['export_settings']['video_codec'] }}</p>
            <p>📁 Output Path: {{ $project['export_settings']['output_path'] }}</p>
        </div>
    </div>
</x-guest-layout>
