@extends('layouts.guest')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.guest' master layout. --}}
@section('content')
<div class="container py-5">
    {{-- Header section with page title and a back button --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        {{-- Page title for "Our Treatments" --}}
        <h1 class="h3 font-weight-bold text-gray-800">Our Treatments</h1>
        {{-- Button to navigate back to the home page --}}
        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">Back Home</a>
    </div>

    {{-- Row to display the list of services --}}
    <div class="row">
        {{-- Loop through each service provided by the controller --}}
        @foreach($services as $service)
        <div class="col-md-6 mb-4">
            {{-- Card container for each service --}}
            <div class="card shadow-sm border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            {{-- Service name --}}
                            <h5 class="font-weight-bold text-dark mb-1">{{ $service->name }}</h5>
                            {{-- Service duration --}}
                            <span class="badge badge-light border"><i class="fas fa-clock mr-1"></i> {{ $service->duration_minutes }} mins</span>
                        </div>
                        {{-- Service price --}}
                        <h4 class="text-success font-weight-bold">₱{{ number_format($service->price) }}</h4>
                    </div>
                    <hr>
                    {{-- Service description, with a fallback default text --}}
                    <p class="text-muted mb-4">
                        {{ $service->description ?? 'Professional dental procedure performed by our experts.' }}
                    </p>
                    {{-- Button to book the service, navigates to the patient booking step 1 --}}
                    <a href="{{ route('patient.booking.step1') }}" class="btn btn-primary btn-sm">Book Now</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection