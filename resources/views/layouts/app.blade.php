<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'TreasureBids')</title>

  <!-- Styles -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link href="{{ url('css/base.css') }}" rel="stylesheet">
  <link href="{{ asset('css/header.css') }}" rel="stylesheet">
  <link href="{{ asset('css/footer.css') }}" rel="stylesheet">
  <link href="{{ asset('css/auction_details.css') }}" rel="stylesheet">
  <link href="{{ asset('css/homepage.css') }}" rel="stylesheet">
  <link href="{{ asset('css/profile.css') }}" rel="stylesheet">
  <link href="{{ asset('css/category.css') }}" rel="stylesheet">
  <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
  <script src="{{ url('js/app.js') }}" defer></script>
  <script src="{{ asset('js/auction_details.js') }}"></script>
  <script src="{{ asset('js/search.js') }}" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const PUSHER_APP_KEY = '{{ env('PUSHER_APP_KEY') }}';
    const PUSHER_APP_CLUSTER = '{{ env('PUSHER_APP_CLUSTER') }}';
    const AUTH_USER_ID = '{{ Auth::id() }}';
  </script>
  
  

</head>

<body class="d-flex flex-column min-vh-100">

  @include('partials.header')
  <main>
    <section id="content">
      @include('partials.alerts')
      @yield('content')
    </section>
  </main>
  @include('partials.footer')
</body>

</html>