<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta name="robots" content="noindex, nofollow">
    
    @yield('include')
    <x-custom.header-tag>
      <x-slot name="title">@yield('title')</x-slot>
    </x-custom.header-tag>
   
</head>
<body>
    <x-custom.payment-processing></x-custom.payment-processing>
    @yield('main')
    <x-custom.footer-tag></x-custom.footer-tag>
</body>
</html>