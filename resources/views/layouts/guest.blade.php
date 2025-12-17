<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ponce Miranda') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/ponce-skin.css') }}" rel="stylesheet">
    
    <style>
        body, html {
            font-family: 'Inter', sans-serif;
        }
        .guest-nav { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .nav-link { color: #5a5c69; font-weight: 600; }
        .nav-link:hover { color: #4e73df; }
    </style>
</head>
<body class="bg-light">


    <nav class="navbar navbar-expand-lg navbar-light guest-nav sticky-top">
        <div class="container">
            <a class="navbar-brand font-weight-bold text-primary d-flex align-items-center" href="{{ route('public.home') }}">
                <img src="{{ asset('poce.jpg') }}" alt="Ponce Miranda Logo" height="30" class="mr-2 rounded-circle">
                Ponce Miranda
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#guestMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="guestMenu">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('public.home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('services.public.index') }}">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('doctors.public.index') }}">Doctors</a></li>
                </ul>
                <div class="form-inline">
                    <a href="{{ route('login') }}" class="text-dark font-weight-bold mr-3" style="text-decoration:none">Log In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm px-4 shadow-sm">Register</a>
                </div>
            </div>
        </div>
    </nav>


    <main>
        @yield('content')
    </main>

 
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="text-uppercase font-weight-bold mb-3" style="letter-spacing: 1px; color: #D1396C;">Ponce Miranda</h5>
                    <p class="small text-white-50">
                        Providing top-tier dental care with a gentle touch. Your smile is our priority.
                    </p>
                    <div class="mt-3">
                        <a href="https://www.facebook.com/drakaren.miranda" target="_blank" class="text-white-50 mr-3"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <h6 class="text-uppercase font-weight-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ route('public.home') }}" class="text-white-50">Home</a></li>
                        <li class="mb-2"><a href="{{ route('services.public.index') }}" class="text-white-50">Services</a></li>
                        <li class="mb-2"><a href="{{ route('doctors.public.index') }}" class="text-white-50">Our Doctors</a></li>
                        <li><a href="{{ route('login') }}" class="text-white-50">Portal Login</a></li>
                    </ul>
                </div>

                <div class="col-md-4 mb-4">
                    <h6 class="text-uppercase font-weight-bold mb-3">Contact Us</h6>
                    <ul class="list-unstyled small text-white-50">
                        <li class="mb-2"><i class="fas fa-map-marker-alt mr-2"></i> Gozar St, Camilmil Calapan City</li>
                        <li class="mb-2"><i class="fas fa-phone mr-2"></i> 09108860336</li>
                        <li><i class="fas fa-envelope mr-2"></i> info@poncemiranda.com</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center small text-white-50">
                &copy; {{ date('Y') }} Ponce Miranda Dental Clinic. All Rights Reserved.
            </div>
        </div>
    </footer>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>