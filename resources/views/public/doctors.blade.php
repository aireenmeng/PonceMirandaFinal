@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-gray-800">Our Specialists</h1>
            <p class="text-muted small mb-0">Meet the experts behind your smile.</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <div class="row">
        @forelse($doctors as $doc)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0 text-center p-4">
                <div class="card-body">
                    <div class="rounded-circle bg-light mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user-md fa-2x text-primary"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-1">Dr. {{ $doc->name }}</h5>
                    <p class="text-muted small mb-3">{{ $doc->specialty ?? 'General Dentist' }}</p>
                    
                    <a href="{{ route('patient.booking.step1') }}" class="btn btn-outline-primary btn-sm btn-block">
                        Book Appointment
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">No doctors found matching your criteria.</p>
        </div>
        @endforelse
    </div>
    
    <div class="d-flex justify-content-center mt-4">
        {{ $doctors->links() }}
    </div>
</div>
@endsection