
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Modon')</title>
   

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
        <!-- Custom CSS -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="{{ asset('css/missions.css') }}">
    
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            html, body {
                height: 100%;
                margin: 0;
            }
            body {
                display: flex;
                flex-direction: column;
            }
            .padded-container {
                flex-grow: 1;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }
            main {
                flex-grow: 1;
                display: flex;
                flex-direction: column;
            }
        
            .image-preview {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            .image-preview img {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border-radius: 5px;
            }

 
        </style>
</head>
<body>
    <div class="container-fluid vh-100 d-flex flex-column padded-container">
        @include('partials.header')

        <main>
            @yield('content')
        </main>
    </div>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/app.js') }}"></script>
   
    @stack('scripts')
    {{-- <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/locations.js') }}"></script>
    <script src="{{ asset('js/pilot.js') }}"></script>
    <script src="{{ asset('js/missions.js') }}"></script> --}}

   <!-- SweetAlert2 -->


</body>
</html>
