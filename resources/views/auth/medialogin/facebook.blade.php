<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ url('fb-linking') }}">
            @csrf

            <div class="mt-2 w-full md:max-w-5xl md:mt-0 md:col-span-2">
 
                <div class="px-2 pb-2">
                    <div class="font-bold">Link account to Facebook</div>
                    <div class="text-xs py-3">{{ $email }} is already associated with a {{ env('APP_NAME') }} account. Please enter your {{ env('APP_NAME') }} password below so we can link your Facebook account and log you in faster.</div>
                </div>

                 <div class="grid place-items-center grid-cols-3 gap-4">

                    <div>

                          <div class="border rounded inline-block p-2">
                             <div class="icon-facebook text-xl p-0.5" style="color: #3b5998;"></div>
                          </div>

                    </div>
                    
                    <div class="text-3xl font-bold inline-block p-2">
                        <span class="icon">+</span>
                    </div>

                   <div>
                      <div class="border rounded inline-block p-2">
                        <x-authentication-card-logo />
                        </div>
                   </div>
           
                 </div>
    
           </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>


            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="ml-4 main-bg-c text-white">
                    {{ __('Link Account') }}
                </x-button>
            </div>

            @if(isset($redirect))
            <input type="hidden" name="link" value="{{ $redirect }}">
            @endif
            
        </form>
    </x-authentication-card>

</x-guest-layout>