@extends('layouts.admin')
@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="d-flex justify-content-between mb-4">
    <h1 class="h3 text-gray-800">Patient Profile: {{ $patient->name }}</h1>
    <div>
        <a href="{{ route('admin.patients.edit', $patient->id) }}" class="btn btn-primary shadow-sm rounded-pill px-4 mr-2">
            <i class="fas fa-edit mr-1"></i> Edit Profile
        </a>
        <a href="{{ route('admin.patients.index') }}" class="btn btn-secondary shadow-sm rounded-pill px-4">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <i class="fas fa-user-circle fa-5x text-gray-300 mb-3"></i>
                <h4>{{ $patient->name }}</h4>
                <p class="text-muted">{{ $patient->email }}</p>
                @if($patient->email === null)
                    <small class="badge badge-info text-white">Walk-in Patient</small>
                @elseif($patient->email_verified_at === null)
                    <small class="badge badge-warning text-dark">Unverified Email</small>
                @else
                    <small class="badge badge-success">Active Account</small>
                @endif
                <hr>
                <div class="text-left">
                    <p><strong>Phone:</strong> 
                        @if($patient->phone)
                            <a href="tel:{{ $patient->phone }}" class="text-dark text-decoration-none">{{ $patient->phone }}</a>
                        @else
                            N/A
                        @endif
                    </p>
                    <p><strong>Address:</strong> {{ $patient->address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Appointment History</h6>
                <ul class="nav nav-pills card-header-pills">
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus == 'all' ? 'active' : '' }}" href="{{ route('admin.patients.show', ['id' => $patient->id, 'status' => 'all']) }}">All</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus == 'incoming' ? 'active' : '' }}" href="{{ route('admin.patients.show', ['id' => $patient->id, 'status' => 'incoming']) }}">Incoming</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus == 'completed' ? 'active' : '' }}" href="{{ route('admin.patients.show', ['id' => $patient->id, 'status' => 'completed']) }}">Completed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus == 'cancelled' ? 'active bg-danger text-white' : 'text-danger' }}" href="{{ route('admin.patients.show', ['id' => $patient->id, 'status' => 'cancelled']) }}">Cancelled</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.patients.show', $patient->id) }}" method="GET" class="form-inline mb-3">
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                    <div class="input-group mr-2">
                        <input type="text" class="form-control rounded-pill" name="search" placeholder="Search Doctor or Service..." value="{{ $search }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary rounded-pill ml-2 px-3" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    @if($search)
                        <a href="{{ route('admin.patients.show', ['id' => $patient->id, 'status' => $currentStatus]) }}" class="btn btn-secondary rounded-pill px-3">Reset</a>
                    @endif
                </form>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Doctor</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th> {{-- Added actions column --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appt)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                    <div class="small text-muted">{{ $appt->appointment_time->format('h:i A') }}</div> {{-- Display time --}}
                                </td>
                                <td>Dr. {{ $appt->doctor->name ?? 'N/A' }}</td>
                                <td>{{ $appt->service->name ?? 'N/A' }}</td>
                                <td><span class="badge badge-secondary">{{ $appt->status }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('admin.appointments.show', $appt->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No appointments found for this patient.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex justify-content-center">
                    {{ $appointments->appends(['status' => $currentStatus, 'search' => $search])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection