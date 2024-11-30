<a href="{{ url('google-login') }}@if(isset($link))?link={{ $link }}@endif">
    <div class="border border-gray-200 rounded-full p-1 my-3 flex items-center">
        <img class="w-6" src="{{ Storage::disk('public')->url('image/google.svg') }}">
        <div class="text-sm px-1 text-center w-full text-gray-600">Log In With Google</div>
    </div>
</a>