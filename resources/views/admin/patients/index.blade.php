@extends('layouts.admin')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.admin' master layout. --}}
@section('content')
    {{-- Header section with page title and a button to register new patients --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        {{-- Page title for Patient Database --}}
        <h1 class="h3 mb-0 text-gray-800">Patient Database</h1>
        {{-- Button to navigate to the patient registration form --}}
        <a href="{{ route('admin.patients.create') }}" class="d-none d-sm-inline-block btn btn-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Register Patient
        </a>
    </div>

    {{-- Conditional display for success messages after an action --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Conditional display for error messages after an action --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Main card container for the patient listing and filter tabs --}}
    <div class="card shadow mb-4">
        {{-- Card header containing navigation tabs to filter patients by status --}}
        <div class="card-header py-3">
            <ul class="nav nav-pills card-header-pills">
                {{-- Tab for All Patients --}}
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'all' ? 'active' : '' }}" href="{{ route('admin.patients.index', ['view' => 'all']) }}">All Patients</a>
                </li>
                {{-- Tab for Active Patients (usually those with verified emails) --}}
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'active' ? 'active' : '' }}" href="{{ route('admin.patients.index', ['view' => 'active']) }}">Active Patients</a>
                </li>
                {{-- Tab for Walk-In Patients (those without registered emails) --}}
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'walkin' ? 'active' : '' }}" href="{{ route('admin.patients.index', ['view' => 'walkin']) }}">Walk-In</a>
                </li>
                {{-- Tab for Patients with Pending Invites (awaiting email verification) --}}
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'pending' ? 'active bg-warning text-dark' : 'text-warning' }}" href="{{ route('admin.patients.index', ['view' => 'pending']) }}">
                        <i class="fas fa-envelope mr-1"></i> Pending Invite
                    </a>
                </li>
                {{-- Tab for Archived Patients --}}
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'archived' ? 'active bg-secondary text-white' : 'text-secondary' }}" href="{{ route('admin.patients.index', ['view' => 'archived']) }}">Archived</a>
                </li>
            </ul>
        </div>

        {{-- Card body containing the search form and patient table --}}
        <div class="card-body">
            {{-- Search form for filtering patients by name or email --}}
            <form action="{{ route('admin.patients.index') }}" method="GET" class="form-inline mb-3">
                {{-- Hidden input to maintain the current view filter across form submissions --}}
                <input type="hidden" name="view" value="{{ $view }}">
                <div class="input-group mr-2">
                    <input type="text" class="form-control rounded-pill" name="search" placeholder="Search by name or email..." value="{{ $search }}">
                    <div class="input-group-append">
                        {{-- Search button --}}
                        <button class="btn btn-primary rounded-pill ml-2 px-3" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                {{-- Reset button for search, only shown if a search term is active --}}
                @if($search)
                    <a href="{{ route('admin.patients.index', ['view' => $view]) }}" class="btn btn-secondary rounded-pill px-3">Reset</a>
                @endif
            </form>

            {{-- Table to display patient records --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        {{-- Table headers for Name, Contact, Visits, and Actions --}}
                        <tr><th>Name</th><th>Contact</th><th>Visits</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        {{-- Loop through each patient and display their details --}}
                        @foreach($patients as $patient)
                        <tr>
                            <td class="font-weight-bold">
                                {{ $patient->name }}
                                @if($patient->trashed())
                                    <span class="badge badge-secondary ml-2">Archived</span>
                                @elseif($patient->email === null)
                                    <span class="badge badge-info ml-2">Walk-in</span>
                                @elseif($patient->email_verified_at === null)
                                    <span class="badge badge-warning ml-2 text-dark">Pending</span>
                                @else
                                    <span class="badge badge-success ml-2">Active</span>
                                @endif
                            </td>
                            <td>
                                {{ $patient->email }}<br>
                                <small>{{ $patient->phone }}</small>
                            </td>
                            <td>{{ $patient->appointments_count }} Records</td>
                            <td>
                                {{-- Conditional actions based on the current view (Archived or others) --}}
                                @if($view == 'archived')
                                    {{-- Form to restore an archived patient --}}
                                    <form action="{{ route('admin.patients.restore', $patient->id) }}" method="POST" class="d-inline">
                                        @csrf <button class="btn btn-primary btn-sm rounded-pill px-3">Restore</button>
                                    </form>
                                    {{-- Form to permanently delete a patient, with a confirmation dialog --}}
                                    <form action="{{ route('admin.patients.forceDelete', $patient->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this patient? This action cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm rounded-pill px-3 ml-1">Delete</button>
                                    </form>
                                @else
                                    {{-- Actions for active/walk-in/pending patients: View and Archive --}}
                                    <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-eye"></i> View</a>
                                    {{-- Form to archive a patient, with a confirmation dialog --}}
                                    <form action="{{ route('admin.patients.destroy', $patient->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Archive patient: {{ $patient->name }}?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-secondary btn-sm rounded-pill px-3"><i class="fas fa-archive"></i> Archive</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Pagination links for the patient list --}}
            <div class="d-flex justify-content-center mt-4">
                {{ $patients->appends(['view' => $view, 'search' => $search])->links() }}
            </div>
        </div>
    </div>
@endsection