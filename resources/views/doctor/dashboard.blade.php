@extends('layouts.admin') 

@section('content')
<div class="container-fluid fade-in-up">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Doctor Workspace</h1>
        
        <div class="d-flex align-items-center">
            @if($schedule)
                <span class="mr-3 badge badge-soft-info px-3 py-2 rounded-pill">
                    <i class="fas fa-clock mr-1"></i> 
                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                </span>
            @else
                <span class="mr-3 badge badge-soft-warning px-3 py-2 rounded-pill">
                    <i class="fas fa-exclamation-triangle mr-1"></i> No Schedule Set
                </span>
            @endif
            
            <span class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm rounded-pill px-3">
                <i class="fas fa-calendar fa-sm text-white-50 mr-1"></i> {{ now()->format('F d, Y') }}
            </span>
        </div>
    </div>

    {{-- 1. "UP NEXT" HERO CARD --}}
    <div class="card shadow mb-4 border-left-primary bg-gradient-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="text-primary font-weight-bold text-uppercase mb-1">Up Next</h5>
                    @if($nextPatient)
                        <h2 class="font-weight-bold text-gray-900 mb-1">{{ $nextPatient->patient->name ?? 'Unknown' }}</h2>
                        <p class="mb-0 text-muted">
                            <i class="fas fa-notes-medical mr-1"></i> {{ $nextPatient->service->name }} 
                            <span class="mx-2">|</span> 
                            <i class="far fa-clock mr-1"></i> {{ $nextPatient->appointment_time->format('h:i A') }}
                        </p>
                    @else
                        <h2 class="font-weight-bold text-gray-700 mb-1">No Patient Up Next</h2>
                        <p class="mb-0 text-muted">All confirmed appointments for today have been completed or are in the future.</p>
                    @endif
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('doctor.todaysConsultations') }}" class="btn btn-primary btn-lg shadow-sm rounded-pill px-4">
                        Go to Today's Consultations <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. KPI CARDS --}}
    <div class="row">
        {{-- Today's Count --}}
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Appointments Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todaysAppointments->count() }} Patients</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upcoming Count --}}
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Upcoming (7 Days)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $upcomingCount }} Scheduled</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. TODAY'S AGENDA --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Today's Agenda</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Time</th>
                            <th>Patient Name</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todaysAppointments as $appt)
                        <tr class="{{ $nextPatient && $nextPatient->id == $appt->id ? 'bg-soft-primary' : '' }}">
                            <td class="font-weight-bold align-middle">{{ $appt->appointment_time->format('h:i A') }}</td>
                            <td class="align-middle">
                                <div class="font-weight-bold">{{ $appt->patient->name ?? 'Unknown' }}</div>
                                @if($appt->patient) {{-- Check if patient relationship exists --}}
                                    @if($appt->patient->email === null)
                                        <small class="badge badge-info text-white">Walk-in</small>
                                    @elseif($appt->patient->email_verified_at === null)
                                        <small class="badge badge-warning text-dark">Unverified</small>
                                    @else
                                        <small class="badge badge-success">Active</small>
                                    @endif
                                @endif
                                <div class="small text-muted">#{{ $appt->patient->id ?? '-' }}</div>
                            </td>
                            <td class="align-middle">{{ $appt->service->name }}</td>
                            <td class="align-middle">
                                @if($appt->status == 'confirmed')
                                    <span class="badge badge-soft-success px-3 py-2 rounded-pill">Confirmed</span>
                                @elseif($appt->status == 'pending')
                                    <span class="badge badge-soft-warning px-3 py-2 rounded-pill">Pending</span>
                                @elseif($appt->status == 'completed')
                                    <span class="badge badge-secondary px-3 py-2 rounded-pill">Completed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-mug-hot fa-3x mb-3 text-gray-300"></i><br>
                                No appointments scheduled for today.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 4. UPCOMING SCHEDULE (Next 7 Days) --}}
    <div class="card shadow mb-4">
        <a href="#collapseUpcoming" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true">
            <h6 class="m-0 font-weight-bold text-success">Upcoming Schedule (Next 7 Days)</h6>
        </a>
        <div class="collapse show" id="collapseUpcoming">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Service</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingAppointments as $up)
                            <tr>
                                <td class="font-weight-bold">{{ $up->appointment_date->format('M d (D)') }}</td>
                                <td>{{ $up->appointment_time->format('h:i A') }}</td>
                                <td>{{ $up->patient->name ?? 'Unknown' }}</td>
                                <td>{{ $up->service->name }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No upcoming appointments for the next week.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection