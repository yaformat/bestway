<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="{{ asset('favicon.ico') }}" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
  <title>{{ config('app.name') }}</title>
  <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
  @vite(['resources/js/main.js'])
  <script src="/registerSW.js"></script>
  <link rel="apple-touch-icon" sizes="180x180" href="/icons/180x180.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/icons/152x152.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/icons/128x128.png">
  <link rel="apple-touch-icon" sizes="192x192" href="/icons/192x192.png">
  <link rel="apple-touch-icon" sizes="512x512" href="/icons/512x512.png">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="House Manage">
  <meta name="theme-color" content="#ffffff">
  <link rel="manifest" href="/manifest.webmanifest">
</head>

<body>
  <div id="app">
    <div id="loading-bg">
      <div class="loading-logo">
        <!-- svg logo -->
        <svg width="86" height="46" viewBox="0 0 268 150" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="50.289" height="143.953" rx="25.144" transform="matrix(-.8652 .50142 .49859 .86684 195.571 0)"
            fill="var(--initial-loader-color)" />
          <rect width="50.289" height="143.953" rx="25.144" transform="matrix(-.8652 .50142 .49859 .86684 196.084 0)"
            fill="url(#a)" fill-opacity=".4" />
          <rect width="50.289" height="143.953" rx="25.144" transform="rotate(30.094 86.573 322.042) skewX(.187)"
            fill="var(--initial-loader-color)" />
          <rect width="50.289" height="143.953" rx="25.144" transform="matrix(-.8652 .50142 .49859 .86684 94.197 0)"
            fill="var(--initial-loader-color)" />
          <rect width="50.289" height="143.953" rx="25.144" transform="matrix(-.8652 .50142 .49859 .86684 94.197 0)"
            fill="url(#b)" fill-opacity=".4" />
          <rect width="50.289" height="143.953" rx="25.144" transform="rotate(30.094 35.886 133.493) skewX(.187)"
            fill="var(--initial-loader-color)" />
          <defs>
            <linearGradient id="a" x1="25.144" y1="0" x2="25.144" y2="143.953" gradientUnits="userSpaceOnUse">
              <stop />
              <stop offset="1" stop-opacity="0" />
            </linearGradient>
            <linearGradient id="b" x1="25.144" y1="0" x2="25.144" y2="143.953" gradientUnits="userSpaceOnUse">
              <stop />
              <stop offset="1" stop-opacity="0" />
            </linearGradient>
          </defs>
        </svg>
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
    const primaryColor = localStorage.getItem('materialize-initial-loader-color') || '#666CFF'

    if (loaderColor)
      document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)

    if (primaryColor)
      document.documentElement.style.setProperty('--initial-loader-color', primaryColor)
  </script>
</body>

</html>
