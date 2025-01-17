<div class="p-4 bg-white shadow-md rounded-md">

    <h1 class="text-2xl font-bold mb-4">Link My Social Media</h1>

    <div class="flex">
        <!-- YouTube Button -->
        <a href="{{ url('google-youtube-login?project_id=' . $digital_asset_id) }}">
            <div class="rounded-md py-2 px-3 mr-1 text-white" style="background: #b00;">
                <span class="icon-youtube"></span>
            </div>
        </a>

        <!-- TikTok Button -->
        <a href="{{ url('tiktok-login?project_id=' . $digital_asset_id) }}">
            <div class="rounded-md py-2 px-3 mr-1 text-white" style="background: black;">
                <span class="icon-tiktok"></span>
            </div>
        </a>

        <!-- Instagram Button -->
        <a href="{{ url('instagram-login?project_id=' . $digital_asset_id) }}">
            <div class="rounded-md py-2 px-3 mr-1 text-white"  style="background: #c32aa3;">
                <span class="icon-instagram"></span>
            </div>
        </a>

        <!-- Facebook Button -->
        <a href="{{ url('facebook-login?project_id=' . $digital_asset_id) }}">
            <div class="rounded-md py-2 px-3 mr-1 text-white"  style="background: #3b5998;">
                <span class="icon-facebook"></span>
            </div>
        </a>

    </div>

    <!-- YouTube Channels Section -->
    <div class="my-4">
        <h2 class="text-2xl font-bold mb-4">YouTube Channels</h2>

        @if($YoutubeChannels->isEmpty())
            <p class="text-gray-500 mt-4">No YouTube channels linked yet.</p>
        @else
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @foreach($YoutubeChannels as $channel)
                    <div class="bg-gray-100 p-2 rounded-md shadow-md">
                        <div class="icon-youtube-box rounded-md p-2 mr-1 text-white inline-block mb-2" style="background: #b00;">
                            <span class="icon-youtube"></span>
                        </div>

                        @if($channel->channel_image)
                            <img src="{{ $channel->channel_image }}" alt="{{ $channel->channel_name }}" class="w-full h-32 object-cover rounded-md">
                        @else
                            <div class="bg-gray-300 w-full h-32 rounded-md flex items-center justify-center">
                                <span class="text-gray-500">No Image</span>
                            </div>
                        @endif

                        <h3 class="text-lg font-bold mt-2">{{ $channel->channel_name }}</h3>
                        <a href="{{ $channel->channel_url }}" target="_blank" class="text-blue-500 hover:underline">Visit Channel</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- TikTok Accounts Section -->
    <div class="my-4">
        <h2 class="text-2xl font-bold mb-4">TikTok Accounts</h2>

        @if($TiktokAccounts->isEmpty())
            <p class="text-gray-500 mt-4">No TikTok accounts linked yet.</p>
        @else
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @foreach($TiktokAccounts as $account)
                    <div class="bg-gray-100 p-2 rounded-md shadow-md">
                        <div class="icon-tiktok-box rounded-md p-2 mr-1 text-white inline-block mb-2" style="background: black;">
                            <span class="icon-tiktok"></span>
                        </div>

                        @if($account->avatar_url)
                            <img src="{{ $account->avatar_url }}" alt="{{ $account->display_name }}" class="w-full h-32 object-cover rounded-md">
                        @else
                            <div class="bg-gray-300 w-full h-32 rounded-md flex items-center justify-center">
                                <span class="text-gray-500">No Image</span>
                            </div>
                        @endif

                        <h3 class="text-lg font-bold mt-2">{{ $account->display_name }}</h3>
                        <a href="{{ $account->profile_url }}" target="_blank" class="text-blue-500 hover:underline">Visit Profile</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>