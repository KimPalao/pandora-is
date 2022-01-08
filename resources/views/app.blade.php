<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title inertia>{{ config('app.name', 'Laravel') }}</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="{{ mix('css/app.css') }}">

  <!-- Scripts -->
  @routes
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.26.0/moment.min.js"></script>
  <script src="{{ mix('js/app.js') }}" defer></script>

  <link rel="apple-touch-icon-precomposed" sizes="57x57" href="{{ asset('ico/apple-touch-icon-57x57.png') }}" />
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{ asset('ico/apple-touch-icon-114x114.png') }}" />
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{ asset('ico/apple-touch-icon-72x72.png') }}" />
  <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{ asset('ico/apple-touch-icon-144x144.png') }}" />
  <link rel="apple-touch-icon-precomposed" sizes="60x60" href="{{ asset('ico/apple-touch-icon-60x60.png') }}" />
  <link rel="apple-touch-icon-precomposed" sizes="120x120" href="{{ asset('ico/apple-touch-icon-120x120.png') }}" />
  <link rel="apple-touch-icon-precomposed" sizes="76x76" href="{{ asset('ico/apple-touch-icon-76x76.png') }}" />
  <link rel="apple-touch-icon-precomposed" sizes="152x152" href="{{ asset('ico/apple-touch-icon-152x152.png') }}" />
  <link rel="icon" type="image/png" href="{{ asset('ico/favicon-196x196.png') }}" sizes="196x196" />
  <link rel="icon" type="image/png" href="{{ asset('ico/favicon-96x96.png') }}" sizes="96x96" />
  <link rel="icon" type="image/png" href="{{ asset('ico/favicon-32x32.png') }}" sizes="32x32" />
  <link rel="icon" type="image/png" href="{{ asset('ico/favicon-16x16.png') }}" sizes="16x16" />
  <link rel="icon" type="image/png" href="{{ asset('ico/favicon-128.png') }}" sizes="128x128" />
  <meta name="application-name" content="&nbsp;" />
  <meta name="msapplication-TileColor" content="#FFFFFF" />
  <meta name="msapplication-TileImage" content="{{ asset('ico/mstile-144x144.png') }}" />
  <meta name="msapplication-square70x70logo" content="{{ asset('ico/mstile-70x70.png') }}" />
  <meta name="msapplication-square150x150logo" content="{{ asset('ico/mstile-150x150.png') }}" />
  <meta name="msapplication-wide310x150logo" content="{{ asset('ico/mstile-310x150.png') }}" />
  <meta name="msapplication-square310x310logo" content="{{ asset('ico/mstile-310x310.png') }}" />

</head>

<body class="bg-light">
  @inertia
</body>

</html>