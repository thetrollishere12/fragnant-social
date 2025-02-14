<footer class="px-4 bg-neutral-100 border-t">
    


    <div class="grid md:grid-cols-3 py-12 max-w-7xl mx-auto sm:px-3 gap-6">

        <!-- About Us Section -->
        <div class="w-full">
            <b class="py-2 italic">ABOUT US</b>
            <div class="text-xs py-0.5"><a href="{{ url('/contact') }}">Contact Us</a></div>
        </div>

        <!-- Support Section -->
        <div class="w-full">
            <b class="py-2 italic">SUPPORT</b>
            <div class="text-xs py-0.5"><a href="{{ url('/faq') }}">FAQs</a></div>
            <div class="text-xs py-0.5"><a href="{{ url('/terms-of-service') }}">Terms of Service</a></div>
            <div class="text-xs py-0.5"><a href="{{ url('/privacy-policy') }}">Privacy Policy</a></div>
            <div class="text-xs py-0.5"><a href="{{ url('/contact') }}">Help & Support</a></div>
        </div>

        <!-- Newsletter Section -->
        <div class="w-full">


            <!-- Social Media Links -->
            <b class="py-2 italic">FOLLOW US</b>
            <div class="social-media-box flex text-md pt-2" style="color: #E0B0AB;">
                @if(!empty($blocks["SOCIAL_MEDIA_FACEBOOK"]))
                    <a href='{{ $blocks["SOCIAL_MEDIA_FACEBOOK"] }}'>
                        <div class="icon-facebook-box rounded-md p-2 mr-1" style="background: #3b5998;">
                            <span class="icon-facebook"></span>
                        </div>
                    </a>
                @endif

                @if(!empty($blocks["SOCIAL_MEDIA_PINTEREST"]))
                    <a href='{{ $blocks["SOCIAL_MEDIA_PINTEREST"] }}'>
                        <div class="icon-pinterest-box rounded-md p-2 mr-1" style="background: #cb2027;">
                            <span class="icon-pinterest"></span>
                        </div>
                    </a>
                @endif

                @if(!empty($blocks["SOCIAL_MEDIA_YOUTUBE"]))
                    <a href='{{ $blocks["SOCIAL_MEDIA_YOUTUBE"] }}'>
                        <div class="icon-youtube-box rounded-md p-2 mr-1" style="background: #b00;">
                            <span class="icon-youtube"></span>
                        </div>
                    </a>
                @endif

                @if(!empty($blocks["SOCIAL_MEDIA_WHATSAPP"]))
                    <a href='{{ $blocks["SOCIAL_MEDIA_WHATSAPP"] }}'>
                        <div class="icon-whatsapp-box rounded-md p-2 mr-1" style="background: #00e676;">
                            <span class="icon-whatsapp"></span>
                        </div>
                    </a>
                @endif

                @if(!empty($blocks["SOCIAL_MEDIA_INSTAGRAM"]))
                    <a href='{{ $blocks["SOCIAL_MEDIA_INSTAGRAM"] }}'>
                        <div class="icon-instagram-box rounded-md p-2 mr-1" style="background: #c32aa3;">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                @endif

                @if(!empty($blocks["SOCIAL_MEDIA_TIKTOK"]))
                    <a href='{{ $blocks["SOCIAL_MEDIA_TIKTOK"] }}'>
                        <div class="icon-tiktok-box rounded-md p-2 mr-1" style="background: black;">
                            <span class="icon-tiktok"></span>
                        </div>
                    </a>
                @endif

                @if(!empty($blocks["SOCIAL_MEDIA_DISCORD"]))
                    <a href='{{ $blocks["SOCIAL_MEDIA_DISCORD"] }}'>
                        <div class="icon-discord-box rounded-md p-2 mr-1" style="background: #5865f2;">
                            <span class="icon-discord"></span>
                        </div>
                    </a>
                @endif 
            </div>
        </div>

    </div>



    <div class="flex gap-1 justify-center py-2">
        <div><img src="{{ Storage::disk('public')->url('image/logo/amex.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/diners.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/discover.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/jcb.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/mastercard.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/paypal.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/unionpay.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/visa.svg') }}"></div>
    </div>

    <div class="copyright text-center text-xs pt-4 pb-10">&copy <?php echo date("Y"); ?> Copyright {{ env('APP_NAME') }}. All Rights Reserved.</div>

</footer>