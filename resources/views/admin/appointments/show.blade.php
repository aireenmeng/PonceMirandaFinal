@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Appointment Details</h1>
    @if(request('from') === 'calendar')
        <a href="{{ route('admin.schedules.index', ['doctor_id' => request('doctor_id'), 'date' => request('date')]) }}" class="btn btn-secondary shadow-sm rounded-pill px-4">
            <i class="fas fa-arrow-left mr-1"></i> Back to Schedule
        </a>
    @else
        <a href="{{ route('admin.appointments.index', request()->query()) }}" class="btn btn-secondary shadow-sm rounded-pill px-4">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    @endif
</div>

<div class="row justify-content-center">
    <div class="col-lg-10"> {{-- Wider main column --}}
        <div class="card shadow border-0 mb-5">
            <div class="card-header py-4 bg-light d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-clipboard-check mr-2 text-primary"></i> Booking #{{ $appointment->id }}</h5>
                @php
                    $statusClass = '';
                    switch ($appointment->status) {
                        case 'pending': $statusClass = 'badge-soft-warning'; break;
                        case 'confirmed': $statusClass = 'badge-soft-primary'; break;
                        case 'completed': $statusClass = 'badge-soft-success'; break;
                        case 'cancelled': $statusClass = 'badge-soft-danger'; break;
                        default: $statusClass = 'badge-secondary'; break;
                    }
                @endphp
                <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill font-weight-bold" style="font-size: 0.9rem;">
                    {{ ucfirst($appointment->status) }}
                </span>
            </div>
            <div class="card-body p-5">
                
                {{-- KEY APPOINTMENT HIGHLIGHT --}}
                <div class="text-center p-4 mb-5 border rounded bg-light">
                    <h2 class="text-primary font-weight-bold mb-1">{{ $appointment->appointment_date->format('F d, Y') }}</h2>
                    <h3 class="text-gray-800 font-weight-bold mb-3">{{ $appointment->appointment_time->format('h:i A') }}</h3>
                    <p class="h5 text-dark mb-0">Dr. {{ $appointment->doctor->name ?? 'Unknown' }} - {{ $appointment->service->name ?? 'Custom' }}</p>
                    <p class="text-muted small mb-0">Duration: {{ $appointment->duration_minutes }} minutes</p>
                </div>

                {{-- PATIENT INFORMATION --}}
                <h6 class="font-weight-bold text-dark mb-3 pb-2 border-bottom"><i class="fas fa-user-circle mr-2 text-primary"></i> Patient Information</h6>
                <div class="mb-5">
                    <p class="mb-2"><strong>Name:</strong> {{ $appointment->patient->name ?? 'Guest / Deleted User' }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $appointment->patient->email ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Phone:</strong> {{ $appointment->patient->phone ?? 'N/A' }}</p>
                </div>

                {{-- ACTION BUTTONS --}}
                @if(in_array($appointment->status, ['pending', 'confirmed']))
                    <h6 class="font-weight-bold text-dark mb-3 pb-2 border-bottom"><i class="fas fa-cogs mr-2 text-primary"></i> Actions</h6>
                    <div class="text-center mb-5">
                        @if($appointment->status == 'pending')
                            <form action="{{ route('admin.appointments.confirm', $appointment->id) }}" method="POST" class="d-inline mr-2">
                                @csrf
                                @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                <button class="btn btn-primary btn-lg rounded-pill px-5"><i class="fas fa-check mr-2"></i> Confirm Arrival</button>
                            </form>
                        @elseif($appointment->status == 'confirmed')
                            <form action="{{ route('admin.appointments.complete', $appointment->id) }}" method="POST" class="d-inline mr-2">
                                @csrf
                                @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                <button class="btn btn-primary btn-lg rounded-pill px-5"><i class="fas fa-check-double mr-2"></i> Mark Completed</button>
                            </form>
                        @endif

                        <button class="btn btn-secondary btn-lg rounded-pill px-5" data-toggle="modal" data-target="#cancelModal">
                            <i class="fas fa-ban mr-2"></i> Cancel Appointment
                        </button>
                    </div>
                @endif

                {{-- CANCELLATION DETAILS (If cancelled) --}}
                @if($appointment->status == 'cancelled')
                    <h6 class="font-weight-bold text-danger mb-3 pb-2 border-bottom"><i class="fas fa-exclamation-triangle mr-2"></i> Cancellation Details</h6>
                    <p class="mb-2"><strong>Cancelled By:</strong> {{ $appointment->canceller->name ?? 'System' }}</p>
                    <p class="mb-2"><strong>Cancelled At:</strong> {{ $appointment->cancelled_at->format('F d, Y h:i A') }}</p>
                    <p class="mb-0"><strong>Reason:</strong> {{ $appointment->cancellation_reason ?? 'No reason provided.' }}</p>
                @endif

                {{-- DIAGNOSIS & PRESCRIPTION (If completed) --}}
                @if($appointment->status == 'completed')
                    <h6 class="font-weight-bold text-success mb-3 pb-2 border-bottom"><i class="fas fa-notes-medical mr-2"></i> Medical Record</h6>
                    
                    <h6 class="font-weight-bold text-dark">Diagnosis:</h6>
                    <p class="mb-3">{{ $appointment->diagnosis ?? 'No diagnosis recorded.' }}</p>
                    
                    <h6 class="font-weight-bold text-dark">Prescription/Advice:</h6>
                    <p class="mb-0">{{ $appointment->prescription ?? 'No prescription/advice recorded.' }}</p>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- MODAL FOR CANCELLATION --}}
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.appointments.cancel', $appointment->id) }}" method="POST">
                @csrf
                @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                <div class="modal-body">
                    <p>Are you sure you want to cancel the appointment for <strong>{{ $appointment->patient->name ?? 'Unknown' }}</strong> on {{ $appointment->appointment_date->format('M d, Y') }} at {{ $appointment->appointment_time->format('h:i A') }}?</p>
                    <div class="form-group">
                        <label for="cancellation_reason">Reason for Cancellation</label>
                        <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-secondary rounded-pill px-4">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection