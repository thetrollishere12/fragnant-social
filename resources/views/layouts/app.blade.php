<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta name="robots" content="index, follow">
        <x-custom.header-tag>
          <x-slot name="title">@yield('title')</x-slot>
          <x-slot name="description">@yield('description')</x-slot>
        </x-custom.header-tag>
    </head>
    <body class="bg-neutral-100">
        @livewire('navigation-menu')
        <x-banner />
        <div>
      
            @if (isset($header))
                <header class="border-b bg-white">
                    <div class="max-w-7xl mx-auto py-6 px-2.5">
                        {{ $header }}
                    </div>
                </header>
            @endif

        <main class="md:min-h-screen font-sans text-gray-900 antialiased">
            {{ $slot }}
        </main>
        <x-custom.footer></x-custom.footer>
        </div>
        <x-custom.footer-tag></x-custom.footer-tag>
    </body>
</html>