
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Modon')</title>
   

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
        <!-- Custom CSS -->
        <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

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
            body, html {
                overflow-x: hidden;
            }
            .tooltip .tooltip-inner {
            background-color: #47D16C !important;
            color: rgb(250, 250, 246) !important; /* Optional: Change text color */
        }
        </style>
        
</head>
<body >
    <div class="container-fluid vh-100 d-flex flex-column padded-container ">
        @include('partials.header')

        <main>
            @yield('content')
        </main>
    </div>


    <!-- Scripts -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/lang.js') }}"></script>
    <script src="{{ asset('js/notification.js') }}"></script>
    <script src="{{ asset('js/lang.js') }}"></script>
  

    @stack('scripts')


   <script>

    document.addEventListener("DOMContentLoaded", function () {
        const toggle = document.getElementById("profileToggle");
        const dropdown = document.getElementById("profileDropdown");

        if (toggle && dropdown) {
            toggle.addEventListener("click", function (e) {
                e.stopPropagation();
                dropdown.classList.toggle("active");
            });

            document.addEventListener("click", function (e) {
                if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
                    dropdown.classList.remove("active");
                }
            });
        }
    });
    $(document).on('click', '#logoutButton', function (e) {
    e.preventDefault();

    $.ajax({
        url: '{{ route("logout") }}',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.redirect) {
                window.location.href = response.redirect;
            }
        },
        error: function (xhr, status, error) {
            console.error('Logout failed!', error);
            console.log(xhr.responseText); // Helpful debug info
        }
    });

   
    
});

</script>

</body>
</html>
