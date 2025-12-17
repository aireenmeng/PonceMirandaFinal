@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="h3 font-weight-bold text-gray-800">Our Treatments</h1>
        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">Back Home</a>
    </div>

    <div class="row">
        @foreach($services as $service)
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-1">{{ $service->name }}</h5>
                            <span class="badge badge-light border"><i class="fas fa-clock mr-1"></i> {{ $service->duration_minutes }} mins</span>
                        </div>
                        <h4 class="text-success font-weight-bold">₱{{ number_format($service->price) }}</h4>
                    </div>
                    <hr>
                    <p class="text-muted mb-4">
                        {{ $service->description ?? 'Professional dental procedure performed by our experts.' }}
                    </p>
                    <a href="{{ route('patient.booking.step1') }}" class="btn btn-primary btn-sm">Book Now</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection