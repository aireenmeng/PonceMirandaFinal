@extends('layouts.admin')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.admin' master layout. --}}
@section('content')

    {{-- Header section with page title and a button to book appointments via calendar --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        {{-- Page title for Appointment Management --}}
        <h1 class="h3 mb-0 text-gray-800">Appointment Management</h1>
        {{-- Button to navigate to the schedule view for booking new appointments --}}
        <a href="{{ route('admin.schedules.index') }}" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-calendar-plus fa-sm text-white-50"></i> Book via Calendar
        </a>
    </div>

    {{-- Conditional display for success messages after an action --}}
    @if(session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    {{-- Conditional display for error messages after an action --}}
    @if(session('error'))
        <div class="alert alert-danger border-left-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Main card container for the appointment listing and filters --}}
    <div class="card shadow mb-4">
        {{-- Card header for navigation tabs and secondary filters --}}
        <div class="card-header py-3">
            {{-- Primary navigation tabs for filtering appointments by date (Today) or status (Pending, Confirmed, Completed, Cancelled) --}}
            <ul class="nav nav-pills card-header-pills">
                {{-- Tab for viewing today's appointments --}}
                <li class="nav-item">
                    <a class="nav-link {{ request('date') == now()->format('Y-m-d') ? 'active' : '' }}" 
                    href="{{ route('admin.appointments.index', ['date' => now()->format('Y-m-d')]) }}">
                    Today
                    </a>
                </li>
                {{-- Tab for viewing pending appointments --}}
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab == 'pending' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'pending']) }}">
                        <i class="fas fa-clock mr-1"></i> Pending
                    </a>
                </li>
                {{-- Tab for viewing confirmed appointments --}}
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab == 'confirmed' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}">
                        <i class="fas fa-check mr-1"></i> Confirmed
                    </a>
                </li>
                {{-- Tab for viewing completed appointments (history) --}}
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab == 'completed' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'completed']) }}">
                        <i class="fas fa-history mr-1"></i> History
                    </a>
                </li>
                {{-- Tab for viewing cancelled appointments --}}
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab == 'cancelled' ? 'active bg-danger text-white' : 'text-danger' }}" href="{{ route('admin.appointments.index', ['status' => 'cancelled']) }}">
                        <i class="fas fa-ban mr-1"></i> Cancelled
                    </a>
                </li>
            </ul>

            {{-- Secondary filter tabs, displayed only when the "Today" primary tab is active --}}
            @if(request('date'))
                <hr class="mt-3 mb-2 border-0">
                <div class="d-flex align-items-center bg-light rounded p-2">
                    <span class="small font-weight-bold text-gray-600 mr-2 text-uppercase">Today's Filter:</span>
                    <ul class="nav nav-pills nav-fill flex-grow-1">
                        {{-- Sub-tab for all appointments today --}}
                        <li class="nav-item mx-1">
                            <a class="nav-link {{ !request('status') ? 'active bg-secondary text-white' : 'bg-white text-secondary border' }} py-1 small shadow-sm" 
                               href="{{ route('admin.appointments.index', ['date' => request('date')]) }}">All</a>
                        </li>
                        {{-- Sub-tab for pending appointments today --}}
                        <li class="nav-item mx-1">
                            <a class="nav-link {{ request('status') == 'pending' ? 'active bg-warning text-dark' : 'bg-white text-warning border' }} py-1 small shadow-sm" 
                               href="{{ route('admin.appointments.index', ['date' => request('date'), 'status' => 'pending']) }}">Pending</a>
                        </li>
                        {{-- Sub-tab for confirmed appointments today --}}
                        <li class="nav-item mx-1">
                            <a class="nav-link {{ request('status') == 'confirmed' ? 'active bg-primary text-white' : 'bg-white text-primary border' }} py-1 small shadow-sm" 
                               href="{{ route('admin.appointments.index', ['date' => request('date'), 'status' => 'confirmed']) }}">Confirmed</a>
                        </li>
                        {{-- Sub-tab for completed appointments today --}}
                        <li class="nav-item mx-1">
                            <a class="nav-link {{ request('status') == 'completed' ? 'active bg-success text-white' : 'bg-white text-success border' }} py-1 small shadow-sm" 
                               href="{{ route('admin.appointments.index', ['date' => request('date'), 'status' => 'completed']) }}">Completed</a>
                        </li>
                        {{-- Sub-tab for cancelled appointments today --}}
                        <li class="nav-item mx-1">
                            <a class="nav-link {{ request('status') == 'cancelled' ? 'active bg-danger text-white' : 'bg-white text-danger border' }} py-1 small shadow-sm" 
                               href="{{ route('admin.appointments.index', ['date' => request('date'), 'status' => 'cancelled']) }}">Cancelled</a>
                        </li>
                    </ul>
                </div>
            @endif
        </div>

        {{-- Filter and search form that preserves the current view context (Date or Status) --}}
        <div class="card-body bg-light border-bottom">
            <form action="{{ route('admin.appointments.index') }}" method="GET" class="form-inline">
                
                {{-- Hidden input to maintain the current 'date' or 'status' filter across form submissions --}}
                @if(request()->has('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @else
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                
                {{-- Search input for patient or doctor names --}}
                <div class="input-group mr-2 mb-2">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-white"><i class="fas fa-search text-gray-400"></i></div>
                    </div>
                    <input type="text" class="form-control" name="search" placeholder="Patient or Doctor..." value="{{ $search }}" onchange="this.form.submit()">
                </div>

                {{-- Sorting Dropdown --}}
                <select name="sort_by" class="form-control mr-2 mb-2" onchange="this.form.submit()">
                    <option value="">Sort By...</option>
                    <option value="date_asc" {{ request('sort_by') == 'date_asc' ? 'selected' : '' }}>Date (Earliest)</option>
                    <option value="date_desc" {{ request('sort_by') == 'date_desc' ? 'selected' : '' }}>Date (Latest)</option>
                    <option value="created_desc" {{ request('sort_by') == 'created_desc' ? 'selected' : '' }}>Booking (Newest)</option>
                    @if($currentTab == 'cancelled')
                        <option value="cancelled_desc" {{ request('sort_by') == 'cancelled_desc' ? 'selected' : '' }}>Cancelled (Recent)</option>
                    @endif
                </select>

                {{-- Date range filter, only shown if not viewing 'Today's' appointments --}}
                @if(!request()->has('date')) 
                    <label class="mr-2 mb-2 text-gray-600 font-weight-bold small">From:</label>
                    <input type="date" name="start_date" class="form-control mr-2 mb-2" value="{{ $startDate }}" onchange="this.form.submit()">

                    <label class="mr-2 mb-2 text-gray-600 font-weight-bold small">To:</label>
                    <input type="date" name="end_date" class="form-control mr-2 mb-2" value="{{ $endDate }}" onchange="this.form.submit()">
                @endif

                {{-- Submit and Reset buttons for the filter form --}}
                {{-- Reset button navigates back to the current date view or pending status view --}}
                <a href="{{ route('admin.appointments.index', request()->has('date') ? ['date' => request('date')] : ['status' => 'pending']) }}" class="btn btn-secondary mb-2 ml-2 shadow-sm">Reset</a>
            </form>
        </div>
        
        {{-- Appointment listing table --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0" width="100%" cellspacing="0">
                    
                    {{-- PHP block to determine sorting direction and icon for interactive table headers --}}
                    @php
                        $nextDir = $direction == 'asc' ? 'desc' : 'asc'; // Toggles sort direction
                        $sortIcon = $direction == 'asc' ? 'fa-sort-up' : 'fa-sort-down'; // Sets icon based on direction
                    @endphp

                    {{-- Table header --}}
                    <thead class="bg-gray-200 text-gray-700">
                        <tr>
                            {{-- Date & Time column, sortable --}}
                            <th class="pl-4">
                                <span class="text-gray-700 text-decoration-none font-weight-bold">
                                    Date & Time 
                                    @if($sort == 'appointment_date') 
                                        {{-- Display specific sort icon if currently sorted by date --}}
                                        <i class="fas {{ $sortIcon }} ml-1"></i> 
                                    @else
                                        {{-- Default sort icon if not sorted by date --}}
                                        <i class="fas fa-sort text-gray-400 ml-1"></i>
                                    @endif
                                </span>
                            </th>
                            <th>Patient</th>
                            <th>Doctor / Service</th>
                            <th>Duration</th>
                            <th>Status</th>
                            {{-- Column header changes based on whether 'Cancelled' tab is active --}}
                            @if($currentTab == 'cancelled')
                                <th>Reason</th>
                            @else
                                <th class="text-right pr-4">Actions</th>
                            @endif
                        </tr>
                    </thead>

                    {{-- Table body, iterates through the 'appointments' collection --}}
                    <tbody>
                        @forelse($appointments as $appt)
                        <tr>
                            {{-- Date and Time display --}}
                            <td class="pl-4">
                                <div class="font-weight-bold text-dark">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                <div class="small text-primary font-weight-bold">
                                    {{ $appt->appointment_time->format('h:i A') }}
                                </div>
                            </td>
                            {{-- Patient information --}}
                            <td>
                                <div class="font-weight-bold">{{ $appt->patient->name ?? 'Unknown' }}</div>
                                @if($appt->patient)
                                    {{-- Patient status badges (Walk-in, Unverified, Active) --}}
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
                            {{-- Doctor and Service information --}}
                            <td>
                                <div><i class="fas fa-user-md text-gray-400 mr-1"></i> Dr. {{ $appt->doctor->name ?? 'Unavailable' }}</div>
                                <div class="small text-success font-weight-bold">{{ $appt->service->name ?? 'Custom' }}</div>
                            </td>
                            {{-- Appointment duration --}}
                            <td>
                                <span class="badge badge-light border">{{ $appt->duration_minutes }} mins</span>
                            </td>
                            {{-- Appointment status with dynamic styling --}}
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
                            {{-- Actions column, varies based on 'Cancelled' tab or standard view --}}
                            @if($currentTab == 'cancelled')
                                {{-- Displays cancellation reason and a restore button for cancelled appointments --}}
                                <td>
                                    <div class="text-danger small font-italic mb-2">"{{ $appt->cancellation_reason }}"</div>
                                    {{-- Form to restore a cancelled appointment --}}
                                    @if($appt->appointment_date->isFuture() || $appt->appointment_date->isToday())
                                        <form action="{{ route('admin.appointments.restore', $appt->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            {{-- Hidden inputs to pass current filters back to the same page after restore --}}
                                            @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                            <button class="btn btn-success btn-sm rounded-pill px-3"><i class="fas fa-undo"></i> Restore</button>
                                        </form>
                                    @endif
                                </td>
                            @else
                                {{-- Standard actions (View, Edit, Confirm, Complete) --}}
                                <td class="text-right pr-4">
                                    {{-- Determines if the appointment is in the future --}}
                                    @php
                                        $isFutureAppointment = $appt->appointment_date->isFuture();
                                    @endphp

                                    @if($isFutureAppointment)
                                        {{-- Actions for future appointments: View and Edit --}}
                                        <a href="{{ route('admin.appointments.show', array_merge(['id' => $appt->id], request()->query())) }}" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-eye"></i> View</a>
                                        <a href="{{ route('admin.appointments.edit', $appt->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @else
                                        {{-- Actions for past or current day appointments: View, Confirm, Complete --}}
                                        <a href="{{ route('admin.appointments.show', array_merge(['id' => $appt->id], request()->query())) }}" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-eye"></i> View</a>
                                        
                                        {{-- Confirm button for pending appointments --}}
                                        @if($appt->status == 'pending')
                                            <form action="{{ route('admin.appointments.confirm', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                                <button class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-check"></i> Confirm</button>
                                            </form>
                                        {{-- Complete button for confirmed appointments --}}
                                        @elseif($appt->status == 'confirmed')
                                            <form action="{{ route('admin.appointments.complete', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                                <button class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-check-double"></i> Complete</button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            @endif
                        </tr>
                        @empty
                        {{-- Message displayed when no appointments are found --}}
                        <tr><td colspan="6" class="text-center py-5 text-muted">No appointments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Card footer for pagination links --}}
        <div class="card-footer bg-white d-flex justify-content-center"> 
            {{-- This links() call is smart enough to preserve query string because of appends() in controller --}}
            {{ $appointments->links('pagination::bootstrap-4') }} 
        </div>
    </div>

@endsection