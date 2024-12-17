<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />



        @if(session('teamInvitation'))
  
            <h4 class="pb-2">
                Register or <a class="underline hover:text-gray-900" href="{{ route('login') }}">Log In</a>
                to join <strong class="text-blue-800">{{ session('teamInvitation') }}</strong>.
            </h4>
           
        @endif



        <x-custom.google-login-button>
            @if(isset($redirect))
            <x-slot name="link">{{ $redirect }}</x-slot>
            @endif
        </x-custom.google-login-button>
       
        <br>
        <div class="border-b border-gray-200"></div>
        <br>

        <form method="POST" action="{{ route('register') }}">

            @if(isset($blocks['WEBSITE_LOGIN']) && $blocks['WEBSITE_LOGIN'] == 'TRUE')

            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" placeholder="Name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" placeholder="Email" :value="old('email')" required />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" placeholder="Password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" placeholder="Confirm Password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms"/>

                            <div class="ml-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}@if(isset($redirect))?link={{ $redirect }}@endif">
                    {{ __('Already registered?') }}
                </a>

                @if(isset($blocks['WEBSITE_LOGIN']) && $blocks['WEBSITE_LOGIN'] == 'TRUE')
                <x-button type="submit" class="ml-4 main-bg-c text-white">
                    {{ __('Register') }}
                </x-button>
                @endif
            </div>

            @if(isset($redirect))
            <input type="hidden" name="link" value="{{ $redirect }}">
            @endif
            
        </form>
    </x-authentication-card>
</x-guest-layout>