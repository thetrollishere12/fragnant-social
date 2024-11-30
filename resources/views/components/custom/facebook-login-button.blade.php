<a href="{{ url('fb-login') }}@if(isset($link))?link={{ $link }}@endif">
    <div class="border border-gray-200 rounded-full p-1 my-3 flex items-center">
        <span class="icon-facebook text-xl p-0.5" style="color: #3b5998;"></span>
        <div class="text-sm px-1 text-center w-full text-gray-600">Log In With Facebook</div>
    </div>
</a>