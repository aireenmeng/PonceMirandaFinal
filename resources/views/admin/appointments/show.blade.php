@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Appointment Details</h1>
    <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Calendar
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Booking #{{ $appointment->id }}</h6>
        @if($appointment->status == 'confirmed')
            <span class="badge badge-success px-3 py-2">Confirmed</span>
        @elseif($appointment->status == 'cancelled')
            <span class="badge badge-danger px-3 py-2">Cancelled</span>
        @endif
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 border-right">
                <h5 class="font-weight-bold text-dark mb-4">Patient Information</h5>
                <p><strong>Name:</strong> {{ $appointment->patient->name }}</p>
                <p><strong>Email:</strong> {{ $appointment->patient->email }}</p>
                <p><strong>Phone:</strong> {{ $appointment->patient->phone ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6 pl-4">
                <h5 class="font-weight-bold text-dark mb-4">Appointment Info</h5>
                <p><strong>Doctor:</strong> Dr. {{ $appointment->doctor->name }}</p>
                <p><strong>Date:</strong> {{ $appointment->appointment_date->format('F d, Y') }}</p>
                <p><strong>Time:</strong> {{ $appointment->appointment_time->format('h:i A') }}</p>
                <p><strong>Service:</strong> {{ $appointment->service->name ?? 'General' }}</p>
            </div>
        </div>

        @if($appointment->status == 'confirmed')
            <hr class="my-4">
            <div class="text-center">
                <button class="btn btn-danger btn-lg" data-toggle="modal" data-target="#cancelModal">
                    <i class="fas fa-ban mr-2"></i> Cancel Appointment
                </button>
            </div>

            <div class="modal fade" id="cancelModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Cancel Booking</h5>
                            <button class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <form action="{{ route('admin.appointments.cancel', $appointment->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p>Are you sure you want to cancel this appointment?</p>
                                <div class="form-group">
                                    <label>Reason for Cancellation <span class="text-danger">*</span></label>
                                    <textarea name="cancellation_reason" class="form-control" required placeholder="e.g. Patient request, Emergency"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @elseif($appointment->status == 'cancelled')
            <div class="alert alert-danger mt-4">
                <strong>Cancelled By:</strong> {{ $appointment->canceller->name ?? 'System' }}<br>
                <strong>Reason:</strong> {{ $appointment->cancellation_reason }}<br>
                <strong>Date:</strong> {{ $appointment->cancelled_at->format('M d, Y h:i A') }}
            </div>
        @endif
    </div>
</div>
@endsection