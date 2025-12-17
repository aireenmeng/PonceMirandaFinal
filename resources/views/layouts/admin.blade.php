<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ponce Miranda | Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/ponce-skin.css') }}" rel="stylesheet">

    <style>
        body, html {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        @include('partials.sidebar')

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                @include('partials.topbar')

                <div class="container-fluid">
                    @yield('content')
                </div>

            </div>

        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.querySelector('body');
            
            // Apply stored state on load
            if (localStorage.getItem('sidebar-toggled') === 'true') {
                body.classList.add('sidebar-toggled');
                if(sidebar) sidebar.classList.add('toggled');
            }

            // Listen for toggle clicks
            const toggles = document.querySelectorAll('#sidebarToggle, #sidebarToggleTop');
            
            toggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    // Let SB Admin 2 script handle the class toggling first
                    // Then, immediately store the new state
                    const isToggled = body.classList.contains('sidebar-toggled') || (sidebar && sidebar.classList.contains('toggled'));
                    localStorage.setItem('sidebar-toggled', isToggled);
                });
            });
        });
    </script>

    @stack('scripts')

</body>
</html>