<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="{{ asset('favicon.ico') }}" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
  <title>{{ config('app.name') }}</title>
  <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
  @vite(['resources/js/admin/main.js'])
  <script src="/registerSW.js"></script>
  <link rel="apple-touch-icon" sizes="180x180" href="/icons/180x180.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/icons/152x152.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/icons/128x128.png">
  <link rel="apple-touch-icon" sizes="192x192" href="/icons/192x192.png">
  <link rel="apple-touch-icon" sizes="512x512" href="/icons/512x512.png">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="BestWay">
  <meta name="theme-color" content="#ffffff">
  <link rel="manifest" href="/manifest.webmanifest">
</head>

<body>
  <div id="app">
    <div id="loading-bg">
      <div class="loading-logo">
         <img src="{{ asset('images/loader_logo.svg') }}" alt="Logo" />
      </div>
      <div class="loading">
        <div class="effect-1 effects"></div>
        <div class="effect-2 effects"></div>
        <div class="effect-3 effects"></div>
      </div>
    </div>
  </div>
  
  <script>
    const loaderColor = localStorage.getItem('materialize-initial-loader-bg') || '#FFFFFF'
    const primaryColor = localStorage.getItem('materialize-initial-loader-color') || '#F27E00'

    if (loaderColor)
      document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)

    if (primaryColor)
      document.documentElement.style.setProperty('--initial-loader-color', primaryColor)

  </script>
</body>

</html>
