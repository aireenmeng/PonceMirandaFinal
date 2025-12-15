@extends('layouts.guest')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.guest' master layout. --}}
@section('content')
<div class="container-fluid p-0">
    
    {{-- 1. HERO SECTION: Main introductory section with clinic branding and calls to action --}}
    <div class="hero-curve text-white text-center position-relative mb-4" style="padding-top: 3rem; padding-bottom: 4rem;">
        <div class="container">
            {{-- Clinic Name/Slogan --}}
            <h1 class="font-weight-bold mb-2" style="font-size: 2rem;">Ponce Miranda Dental</h1>
            
            {{-- Tagline --}}
            <p class="lead mb-4 opacity-90" style="font-size: 1rem;">Experience elegant care for your perfect smile.</p>
            
            {{-- Call-to-action buttons for logging in or registering as a new patient --}}
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('login') }}" class="btn btn-primary btn-md shadow-sm px-4 mr-3 rounded-pill">
                    Log In
                </a>
                <a href="{{ route('register') }}" class="btn btn-info btn-md px-4 rounded-pill">
                    New Patient
                </a>
            </div>
        </div>
    </div>

    {{-- 2. CARDS SECTION: Highlights key aspects or features of the clinic --}}
    <div class="container py-4">
        <div class="row text-center justify-content-center">
            
            {{-- Card for "Expert Team" --}}
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-left-primary bg-white shadow-sm hover-scale">
                    <div class="card-body p-4">
                        {{-- Icon for Expert Team --}}
                        <div class="icon-circle bg-primary text-white mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user-md fa-lg"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark">Expert Team</h5>
                        <p class="text-muted small mb-3">Qualified professionals ready to help.</p>
                        {{-- Button to view doctors --}}
                        <a href="{{ route('doctors.public.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4">See Doctors</a>
                    </div>
                </div>
            </div>

            {{-- Card for "Premium Care" --}}
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-left-success bg-white shadow-sm hover-scale">
                    <div class="card-body p-4">
                        {{-- Icon for Premium Care --}}
                        <div class="icon-circle bg-success text-white mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-tooth fa-lg"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark">Premium Care</h5>
                        <p class="text-muted small mb-3">Treatments tailored to your needs.</p>
                        {{-- Button to view services --}}
                        <a href="{{ route('services.public.index') }}" class="btn btn-sm btn-primary px-4">View Services</a>
                    </div>
                </div>
            </div>

            {{-- Card for "Easy Booking" --}}
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-left-info bg-white shadow-sm hover-scale">
                    <div class="card-body p-4">
                        {{-- Icon for Easy Booking --}}
                        <div class="icon-circle bg-info text-white mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-calendar-check fa-lg"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark">Easy Booking</h5>
                        <p class="text-muted small mb-3">Schedule your visit in clicks.</p>
                        {{-- Button to book an appointment --}}
                        <a href="{{ route('patient.booking.step1') }}" class="btn btn-sm btn-primary px-4">Book Now</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- 3. WHY CHOOSE US SECTION: Details the clinic's unique selling propositions --}}
    <div class="bg-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="font-weight-bold text-gray-800 mb-4">Why Choose Ponce Miranda?</h2>
                    <p class="text-muted mb-4">
                        We combine advanced dental technology with a compassionate approach to ensure your visit is comfortable and effective.
                    </p>
                    
                    {{-- Feature 1: State-of-the-art Facility --}}
                    <div class="d-flex align-items-start mb-3">
                        <div class="icon-circle bg-light text-primary rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-dark h6">State-of-the-art Facility</h5>
                            <p class="small text-muted">Equipped with the latest diagnostic and treatment tools.</p>
                        </div>
                    </div>

                    {{-- Feature 2: Personalized Care Plans --}}
                    <div class="d-flex align-items-start mb-3">
                        <div class="icon-circle bg-light text-primary rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-dark h6">Personalized Care Plans</h5>
                            <p class="small text-muted">Treatments designed specifically for your unique smile.</p>
                        </div>
                    </div>

                    {{-- Feature 3: Flexible Scheduling --}}
                    <div class="d-flex align-items-start">
                        <div class="icon-circle bg-light text-primary rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-dark h6">Flexible Scheduling</h5>
                            <p class="small text-muted">Weekend and evening appointments available for your convenience.</p>
                        </div>
                    </div>
                </div>
                
                {{-- Placeholder for a Clinic Image/Illustration --}}
                <div class="col-lg-6 text-center">
                    <div class="rounded-lg shadow-lg overflow-hidden position-relative bg-light" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clinic-medical fa-5x text-gray-300"></i>
                        <div class="position-absolute w-100 h-100" style="background: rgba(0,0,0,0.03);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Inline styles for specific UI elements, like the hover effect on cards --}}
<style>
    /* Small hover effect for cards */
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-3px); }
</style>
@endsection