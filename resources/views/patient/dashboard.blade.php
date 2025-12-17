@extends('layouts.admin') 


@section('content')

<div class="container-fluid">


    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
   
            <h1 class="h3 mb-0 text-gray-800">Hello, {{ Auth::user()->name }}!</h1>
            <p class="mb-0 text-muted">Welcome to your personal health portal.</p>
        </div>

        <a href="{{ route('patient.booking.step1') }}" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-plus-circle fa-sm text-white-50 mr-2"></i> Book Appointment
        </a>
    </div>

    <div class="row">
        
     
        <div class="col-lg-5 mb-4">
        
            @if($upcoming)
                <div class="card shadow mb-4 border-left-{{ $upcoming->status == 'confirmed' ? 'success' : 'warning' }}">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-{{ $upcoming->status == 'confirmed' ? 'success' : 'warning' }}">
                            Your Next Visit
                        </h6>
                        @if($upcoming->status == 'pending')
                            <span class="badge badge-warning text-dark">Pending Confirmation</span>
                        @else
                            <span class="badge badge-success">Confirmed</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="h1 font-weight-bold text-gray-800 mb-1">
                            </div>
                            <div class="h5 text-primary">
                            </div>
                        </div>

                        <div class="row text-center mb-3">
                            <div class="col-6 border-right">
                                <small class="text-uppercase text-muted font-weight-bold">Doctor</small>
                                <div class="font-weight-bold text-dark">Dr. {{ $upcoming->doctor->name }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-uppercase text-muted font-weight-bold">Service</small>
                                <div class="font-weight-bold text-dark">{{ $upcoming->service->name }}</div>
                            </div>
                        </div>

                        <hr>

                        <div class="text-center">
                            @if($upcoming->status == 'pending')
                                <p class="small text-muted mb-0">We are reviewing your request.</p>
                            @else
                                <p class="small text-success mb-0"><i class="fas fa-check-circle mr-1"></i> You are all set!</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="card shadow mb-4">
                    <div class="card-body text-center py-5">
                        {{-- Placeholder image for no upcoming appointments --}}
                        <img src="https://img.icons8.com/clouds/100/000000/calendar.png" class="mb-3" style="opacity: 0.7;">
                        <h5 class="text-gray-800 font-weight-bold">No Upcoming Visits</h5>
                        <p class="text-muted mb-4">It looks like your schedule is clear.</p>
                        <a href="{{ route('patient.booking.step1') }}" class="btn btn-outline-primary">
                            Schedule a Checkup
                        </a>
                    </div>
                </div>
            @endif

        </div>
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="bg-light">
                                {{-- Table headers for past appointments --}}
                                <tr>
                                    <th>Date</th>
                                    <th>Treatment</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $appt)
                                <tr>
                                    <td class="font-weight-bold">{{ $appt->appointment_date->format('M d, Y') }}</td>
                                    <td>{{ $appt->service->name }}</td>
                                    <td class="small">Dr. {{ $appt->doctor->name }}</td>
                                    <td>
                                        @if($appt->status == 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($appt->status == 'cancelled')
                                            <span class="badge badge-danger">Cancelled</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($appt->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No past appointments found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($history->count() > 0)
                        <div class="card-footer bg-white text-center">
                            <a href="{{ route('patient.history') }}" class="small font-weight-bold">View Full History &rarr;</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>
@endsection