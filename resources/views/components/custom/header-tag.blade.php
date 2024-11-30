<link rel="canonical" href="{{url()->full()}}">
<meta property="og:url" content="{{url()->full()}}">



<!-- 60 -->
@if(isset($set_title))

<title>{{ $set_title }}</title>
<meta property="og:title" content="{{ $set_title }}">
<meta name="twitter:title" content="{{ $set_title }}">


@else



@if(isset($title))
<title>{{ $title }}</title>
<meta property="og:title" content="{{ $title }}">
<meta name="twitter:title" content="{{ $title }}">
@endif



@endif


<!-- 155 -->



@if(isset($set_description))

<meta name="description" content="{{ $set_description }}">
<meta property="og:description" content="{{ $set_description }}">
<meta name="twitter:description" content="{{ $set_description }}">


@else



@if(isset($description))
<meta name="description" content="{{ $description }}">
<meta property="og:description" content="{{ $description }}">
<meta name="twitter:description" content="{{ $description }}">
@endif



@endif





<meta name="author" content="">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta property="og:type" content="website" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-language" content="en-us">
<meta http-equiv="Cache-control" content="public">

<!-- logo -->
<link rel='shortcut icon' type='image/x-icon' href="{{ Storage::disk('public')->url('image/logo/logo.svg?1') }}" />
<meta property="og:image" content="{{ Storage::disk('public')->url('image/logo/logo.png?1') }}">
<meta name="twitter:url" content="{{ Storage::disk('public')->url('image/logo/logo.png?1') }}">

<!-- Fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/general.css?'.time().'') }}">


<!-- Script -->
@wireUiScripts


@vite(['resources/css/app.css', 'resources/js/app.js'])

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- <script type="text/javascript" src="{{ asset('js/general.js?'.time().'') }}"></script> -->

@livewireStyles


<x-notifications z-index="z-50" />
<x-dialog z-index="z-50" blur="md" align="center" />


<script>
    window.userId = {{ Auth::id() }};
</script>