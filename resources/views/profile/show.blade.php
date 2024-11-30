<!-- Change in vendor livewire -->
<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="md:flex">

        <x-custom.profile-nav>
            <x-slot name="url">profile</x-slot>
        </x-custom.profile-nav>

        <div class="w-full border-l border-gray-200 p-2">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>


            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />         
            @endif








<div class="md:grid md:grid-cols-3 md:gap-6">
    <div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <h3 class="text-lg font-medium text-gray-900">My Social Login</h3>

        <p class="mt-1 text-sm text-gray-600">
            Log in with your social media accounts.
        </p>
    </div>

    <div class="px-4 sm:px-0">
        
    </div>
</div>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="px-4 py-5 sm:p-6 bg-white shadow sm:rounded-lg">
            
               <div>
                   <div class="md:col-span-1 flex justify-between">
                      <div class="p-1">
                         <h3 class="text-lg font-medium text-gray-900">My Social Login</h3>
                         <p class="mt-1 text-sm text-gray-600">
                            Log in with your social media accounts.
                         </p>
                      </div>
                      <div class="p-1">
                      </div>
                   </div>
                   <div class="mt-2 w-full md:max-w-5xl md:mt-0 md:col-span-2">
                   
                         <div class="grid grid-cols-1 gap-4">
                            <div class="flex justify-between">
                               <div class="flex items-center">
                                  <div class="rounded inline-block p-2">
                                     <img class="w-6" src="{{ Storage::disk('public')->url('image/google.svg') }}">
                                  </div>
                                  <div class="px-4">Google</div>
                               </div>
                               <div class="flex items-center px-4">@if(Auth::user()->google_id) <span class="text-green-500">Connected</span> @else Not Connected @endif</div>
                            </div>
                            <div class="flex justify-between">
                               <div class="flex items-center">
                                  <div class="rounded inline-block p-2">
                                     <div class="icon-facebook text-xl p-0.5" style="color: #3b5998;"></div>
                                  </div>
                                  <div class="px-4">Facebook</div>
                               </div>
                               <div class="flex items-center px-4">@if(Auth::user()->fb_id) <span class="text-green-500">Connected</span> @else Not Connected @endif</div>
                            </div>
                         </div>
                    
                   </div>
                </div>    

        </div>
    </div>
</div>




<br>




        </div>
    </div>
</x-guest-layout>