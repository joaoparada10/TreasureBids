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
        <link href="{{ url('css/admin.css') }}" rel="stylesheet">
        <link href="{{ asset('css/header.css') }}" rel="stylesheet">
        <link href="{{ asset('css/footer.css') }}" rel="stylesheet">
        <link href="{{ asset('css/auction_details.css') }}" rel="stylesheet">
        <link href="{{ asset('css/homepage.css') }}" rel="stylesheet">
        <link href="{{ asset('css/profile.css') }}" rel="stylesheet">
        <!-- <link href="{{ url('css/app.css') }}" rel="stylesheet">
        
        
         -->
        <script type="text/javascript">
            // Fix for Firefox autofocus CSS bug
            // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
        </script>
        <script type="text/javascript" src="{{ url('js/app.js') }}" defer></script>
        <script type="text/javascript" src="{{ asset('js/auction_details.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/search.js') }}" defer></script>
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