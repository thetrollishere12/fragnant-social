<!DOCTYPE html>
<html lang="{{  app()->getLocale() }}" data-controller="html-load" dir="{{ \Orchid\Support\Locale::currentDir() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no">
    <title>
        @yield('title', config('app.name'))
        @hasSection('title')
            - {{ config('app.name') }}
        @endif
    </title>
    <meta name="csrf_token" content="{{ csrf_token() }}" id="csrf_token">
    <meta name="auth" content="{{ Auth::check() }}" id="auth">
    @if(\Orchid\Support\Locale::currentDir(app()->getLocale()) == "rtl")
        <link rel="stylesheet" type="text/css" href="{{  mix('/css/orchid.rtl.css','vendor/orchid') }}">
    @else
        <link rel="stylesheet" type="text/css" href="{{  mix('/css/orchid.css','vendor/orchid') }}">
    @endif

    @stack('head')

    <meta name="view-transition" content="same-origin">
    <meta name="turbo-root" content="{{  Dashboard::prefix() }}">
    <meta name="turbo-refresh-method" content="{{ config('platform.turbo.refresh-method', 'replace') }}">
    <meta name="turbo-refresh-scroll" content="{{ config('platform.turbo.refresh-scroll', 'reset') }}">
    <meta name="turbo-prefetch" content="{{ var_export(config('platform.turbo.prefetch', true)) }}">
    <meta name="dashboard-prefix" content="{{  Dashboard::prefix() }}">

    @if(!config('platform.turbo.cache', false))
        <meta name="turbo-cache-control" content="no-cache">
    @endif

    <script src="{{ mix('/js/manifest.js','vendor/orchid') }}" type="text/javascript"></script>
    <script src="{{ mix('/js/vendor.js','vendor/orchid') }}" type="text/javascript"></script>
    <script src="{{ mix('/js/orchid.js','vendor/orchid') }}" type="text/javascript"></script>

    @foreach(Dashboard::getResource('stylesheets') as $stylesheet)
        <link rel="stylesheet" href="{{  $stylesheet }}">
    @endforeach

    @stack('stylesheets')

    @foreach(Dashboard::getResource('scripts') as $scripts)
        <script src="{{  $scripts }}" defer type="text/javascript"></script>
    @endforeach

    
    @if(!empty(config('platform.vite', [])))
        @vite(config('platform.vite'))
    @endif










<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/general.css?'.time().'') }}">


<!-- Script -->
@wireUiScripts


@vite(['resources/css/app.css', 'resources/js/app.js'])

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- <script type="text/javascript" src="{{ asset('js/general.js?'.time().'') }}"></script> -->

@livewireStyles



    <x-notifications z-index="z-50" />
    <x-dialog z-index="z-50" blur="md" align="center" />








</head>

<body class="{{ \Orchid\Support\Names::getPageNameClass() }}" data-controller="pull-to-refresh">

<div class="container-fluid" data-controller="@yield('controller')" @yield('controller-data')>

    <div class="row justify-content-center d-md-flex h-100">
        @yield('aside')

        <div class="col-xxl col-xl-9 col-12">
            @yield('body')
        </div>
    </div>


    @include('platform::partials.toast')
</div>

@livewireScripts

    <script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v0.x.x/dist/livewire-sortable.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>



    <script type="text/javascript" src="{{ asset('js/general.js?'.time().'') }}"></script>

    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

@stack('scripts')


</body>
</html>
