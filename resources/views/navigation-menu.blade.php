<nav x-data="{ open: false }" class="">




    <!-- Primary Navigation Menu -->
    <div class="">

        @if(isset($blocks['HEADER_MESSAGE']))
            <div class="mx-auto px-2 sm:px-4 lg:px-6 py-2 text-center text-white {{ $blocks['HEADER_CLASS_COLOR'] ?? 'main-bg-c' }}">
                {!! $blocks['HEADER_MESSAGE'] !!}
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 flex justify-between py-2.5">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ url('/') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

            </div>
            
        <!-- Settings Dropdown -->

            <!-- hidden sm:flex sm:items-center sm:ml-6 -->

            <div class="hidden space-x-6 sm:-my-px sm:ml-10 sm:flex">

                <x-nav-link href="{{ url('subscription-pricing') }}">
                    {{ __('Pricing') }}
                </x-nav-link>


                <x-nav-link href="{{ url('contact') }}">
                    {{ __('Contact Us') }}
                </x-nav-link>

                @guest
                <x-nav-link href="{{ route('login') }}">
                    {{ __('Login') }}
                </x-nav-link>
                @else
                

                <x-nav-link href="{{ url('user/digital-assets') }}">
                    {{ __('Projects') }}
                </x-nav-link>


                <!-- Settings Dropdown -->
                <div class="ml-3 relative inline-flex items-center">


                    <x-dropdown>
                        

                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                        {{ Auth::user()->name }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>


                        <x-dropdown.header label="{{ __('Manage Account') }}">

                            <x-dropdown.item class="text-xs" href="{{ route('profile.show') }}" label="Profile"/>

                            <div class="border-t border-gray-100"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <x-dropdown.item class="text-xs" href="{{ route('logout') }}" label="Log Out" @click.prevent="$root.submit();"/>

                            </form>

                        </x-dropdown.header>
                            




                    </x-dropdown>

                </div>

                @endguest



                <!-- <a data-bs-toggle="modal" data-bs-target="#search-moodal" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition" href="#"><span class="icon-search cursor-pointer"></span></a> -->



            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex gap-3 items-center sm:hidden">


                <button @click="open = ! open" class="inline-flex items-center justify-center mr-1 p-1.5 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>


    </div>
    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-b">


        <div class="space-y-1">
            
                <x-responsive-nav-link href="{{ url('subscription-pricing') }}">
                    {{ __('Pricing') }}
                </x-responsive-nav-link>

    
                <x-responsive-nav-link href="{{ url('contact') }}">
                    {{ __('Contact Us') }}
                </x-responsive-nav-link>

        </div>

        @guest

        <div>

            <x-responsive-nav-link class="space-y-1" href="{{ route('login') }}">
                {{ __('Login') }}
            </x-responsive-nav-link>

        </div>

        @else
        <!-- Responsive Settings Options -->
        <div>
            <div class="flex items-center p-3">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="flex-shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="space-y-1">
                <!-- Account Management -->
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                    this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

            </div>
        </div>

        @endguest


    </div>




</nav>
