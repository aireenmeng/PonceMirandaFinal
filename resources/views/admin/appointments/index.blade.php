@extends('layouts.admin')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Appointment Management</h1>
        <a href="{{ route('admin.schedules.index') }}" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-calendar-plus fa-sm text-white-50"></i> Book via Calendar
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-left-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab == 'today' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['date' => now()->format('Y-m-d')]) }}">
                        <i class="fas fa-calendar-day mr-1"></i> Today
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab == 'pending' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'pending']) }}">
                        <i class="fas fa-clock mr-1"></i> Pending
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab == 'confirmed' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}">
                        <i class="fas fa-check mr-1"></i> Confirmed
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab == 'completed' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'completed']) }}">
                        <i class="fas fa-history mr-1"></i> History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab == 'cancelled' ? 'active bg-danger text-white' : 'text-danger' }}" href="{{ route('admin.appointments.index', ['status' => 'cancelled']) }}">
                        <i class="fas fa-ban mr-1"></i> Cancelled
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body bg-light border-bottom">
            <form action="{{ route('admin.appointments.index') }}" method="GET" class="form-inline">
                <input type="hidden" name="status" value="{{ $status }}">
                
                <div class="input-group mr-2 mb-2">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-white"><i class="fas fa-search text-gray-400"></i></div>
                    </div>
                    <input type="text" class="form-control" name="search" placeholder="Patient or Doctor..." value="{{ $search }}">
                </div>

                <label class="mr-2 mb-2 text-gray-600 font-weight-bold small">From:</label>
                <input type="date" name="start_date" class="form-control mr-2 mb-2" value="{{ $startDate }}">

                <label class="mr-2 mb-2 text-gray-600 font-weight-bold small">To:</label>
                <input type="date" name="end_date" class="form-control mr-2 mb-2" value="{{ $endDate }}">

                <button type="submit" class="btn btn-primary mb-2 shadow-sm">Filter</button>
                <a href="{{ route('admin.appointments.index', ['status' => $status]) }}" class="btn btn-secondary mb-2 ml-2 shadow-sm">Reset</a>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0" width="100%" cellspacing="0">
                    
                    {{-- SORTING LOGIC START --}}
                    @php
                        // Helper to calculate the opposite direction for the next click
                        $nextDir = $direction == 'asc' ? 'desc' : 'asc';
                        // Icon to show current state
                        $sortIcon = $direction == 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                    @endphp

                    <thead class="bg-gray-200 text-gray-700">
                        <tr>
                            <th class="pl-4">
                                {{-- CLICKABLE HEADER --}}
                                <a href="{{ route('admin.appointments.index', array_merge(request()->all(), ['sort' => 'appointment_date', 'direction' => $nextDir])) }}" class="text-gray-700 text-decoration-none font-weight-bold">
                                    Date & Time 
                                    @if($sort == 'appointment_date') 
                                        <i class="fas {{ $sortIcon }} ml-1"></i> 
                                    @else
                                        <i class="fas fa-sort text-gray-400 ml-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Patient</th>
                            <th>Doctor / Service</th>
                            <th>Duration</th>
                            <th>Status</th>
                            @if($currentTab == 'cancelled')
                                <th>Reason</th>
                            @else
                                <th class="text-right pr-4">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    {{-- SORTING LOGIC END --}}

                    <tbody>
                        @forelse($appointments as $appt)
                        <tr>
                            <td class="pl-4">
                                <div class="font-weight-bold text-dark">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                <div class="small text-primary font-weight-bold">
                                    {{ $appt->appointment_time->format('h:i A') }}
                                </div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $appt->patient->name ?? 'Unknown' }}</div>
                                @if($appt->patient)
                                    @if($appt->patient->email === null)
                                        <small class="badge badge-info text-white">Walk-in</small>
                                    @elseif($appt->patient->email_verified_at === null)
                                        <small class="badge badge-warning text-dark">Unverified</small>
                                    @else
                                        <small class="badge badge-success">Active</small>
                                    @endif
                                @endif
                                <div class="small text-muted">{{ $appt->patient->phone ?? 'No # ' }}</div>
                            </td>
                            <td>
                                <div><i class="fas fa-user-md text-gray-400 mr-1"></i> Dr. {{ $appt->doctor->name ?? 'Unavailable' }}</div>
                                <div class="small text-success font-weight-bold">{{ $appt->service->name ?? 'Custom' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light border">{{ $appt->duration_minutes }} mins</span>
                            </td>
                            <td>
                                @php
                                    $statusClass = '';
                                    switch ($appt->status) {
                                        case 'pending': $statusClass = 'badge-soft-warning'; break;
                                        case 'confirmed': $statusClass = 'badge-soft-primary'; break;
                                        case 'completed': $statusClass = 'badge-soft-success'; break;
                                        case 'cancelled': $statusClass = 'badge-soft-danger'; break;
                                        default: $statusClass = 'badge-secondary'; break;
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">{{ ucfirst($appt->status) }}</span>
                            </td>
                            @if($currentTab == 'cancelled')
                                <td>
                                    <div class="text-danger small font-italic mb-2">"{{ $appt->cancellation_reason }}"</div>
                                    <form action="{{ route('admin.appointments.restore', $appt->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                        <button class="btn btn-success btn-sm rounded-pill px-3"><i class="fas fa-undo"></i> Restore</button>
                                    </form>
                                </td>
                            @else
                                <td class="text-right pr-4">
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $isFutureAppointment = $appt->appointment_date->isFuture();
                                        $queryParams = array_merge(request()->query(), ['id' => $appt->id]); // Preserve current filters
                                    @endphp

                                    @if($isFutureAppointment)
                                        <a href="{{ route('admin.appointments.show', array_merge(['id' => $appt->id], request()->query())) }}" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-eye"></i> View</a>
                                        <a href="{{ route('admin.appointments.edit', array_merge(['id' => $appt->id], request()->query())) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @else
                                        <a href="{{ route('admin.appointments.show', array_merge(['id' => $appt->id], request()->query())) }}" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-eye"></i> View</a>
                                        
                                        @if($appt->status == 'pending')
                                            <form action="{{ route('admin.appointments.confirm', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                                <button class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-check"></i> Confirm</button>
                                            </form>
                                        @elseif($appt->status == 'confirmed')
                                            <form action="{{ route('admin.appointments.complete', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                                <button class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-check-double"></i> Complete</button>
                                            </form>
                                        @endif
                                        {{-- Cancel button moved to show view --}}
                                    @endif
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No appointments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white d-flex justify-content-center"> {{-- Center pagination --}}
            {{ $appointments->links('pagination::bootstrap-4') }} {{-- Explicitly use Bootstrap 4 --}}
        </div>
    </div>

@endsection