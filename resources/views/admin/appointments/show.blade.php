@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Appointment Details</h1>
    {{-- FIX: Back button now goes to the Appointment List, not Calendar --}}
    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Booking #{{ $appointment->id }}</h6>
        <span class="badge px-3 py-2 {{ $appointment->status == 'confirmed' ? 'badge-success' : ($appointment->status == 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
            {{ ucfirst($appointment->status) }}
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 border-right">
                <h5 class="font-weight-bold text-dark mb-4">Patient Information</h5>
                {{-- FIX: Added '??' checks to prevent crashing on deleted users --}}
                <p><strong>Name:</strong> {{ $appointment->patient->name ?? 'Guest / Deleted User' }}</p>
                <p><strong>Email:</strong> {{ $appointment->patient->email ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $appointment->patient->phone ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6 pl-4">
                <h5 class="font-weight-bold text-dark mb-4">Appointment Info</h5>
                <p><strong>Doctor:</strong> Dr. {{ $appointment->doctor->name ?? 'Unknown' }}</p>
                <p><strong>Date:</strong> {{ $appointment->appointment_date->format('F d, Y') }}</p>
                <p><strong>Time:</strong> {{ $appointment->appointment_time->format('h:i A') }}</p>
                <p><strong>Service:</strong> {{ $appointment->service->name ?? 'Custom Service' }}</p>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        @if(in_array($appointment->status, ['pending', 'confirmed']))
            <hr class="my-4">
            <div class="d-flex justify-content-center">
                @if($appointment->status == 'pending')
                    <form action="{{ route('admin.appointments.confirm', $appointment->id) }}" method="POST" class="mr-2">
                        @csrf
                        <button class="btn btn-success btn-lg"><i class="fas fa-check mr-2"></i> Confirm Arrival</button>
                    </form>
                @elseif($appointment->status == 'confirmed')
                    <form action="{{ route('admin.appointments.complete', $appointment->id) }}" method="POST" class="mr-2">
                        @csrf
                        <button class="btn btn-primary btn-lg"><i class="fas fa-check-double mr-2"></i> Mark Completed</button>
                    </form>
                @endif

                <button class="btn btn-danger btn-lg" data-toggle="modal" data-target="#cancelModal">
                    <i class="fas fa-ban mr-2"></i> Cancel
                </button>
            </div>
        @endif
    </div>
</div>

@endsection