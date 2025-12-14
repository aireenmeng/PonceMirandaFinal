@extends('layouts.admin')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Patient Database</h1>
        <a href="{{ route('admin.patients.create') }}" class="d-none d-sm-inline-block btn btn-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Register Patient
        </a>
    </div>

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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'active' ? 'active' : '' }}" href="{{ route('admin.patients.index') }}">Active Patients</a>
                </li>
                {{-- NEW PENDING TAB --}}
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'pending' ? 'active bg-warning text-dark' : 'text-warning' }}" href="{{ route('admin.patients.index', ['view' => 'pending']) }}">
                        <i class="fas fa-envelope mr-1"></i> Pending Invite
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'archived' ? 'active bg-secondary text-white' : 'text-secondary' }}" href="{{ route('admin.patients.index', ['view' => 'archived']) }}">Archived</a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.patients.index') }}" method="GET" class="form-inline mb-3">
                <input type="hidden" name="view" value="{{ $view }}">
                <div class="input-group mr-2">
                    <input type="text" class="form-control rounded-pill" name="search" placeholder="Search by name or email..." value="{{ $search }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary rounded-pill ml-2 px-3" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                @if($search)
                    <a href="{{ route('admin.patients.index', ['view' => $view]) }}" class="btn btn-secondary rounded-pill px-3">Reset</a>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead><tr><th>Name</th><th>Contact</th><th>Visits</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach($patients as $patient)
                        <tr>
                            <td class="font-weight-bold">{{ $patient->name }}</td>
                            <td>{{ $patient->email }}<br><small>{{ $patient->phone }}</small></td>
                            <td>{{ $patient->appointments_count }} Records</td>
                            <td>
                                @if($view == 'archived')
                                    <form action="{{ route('admin.patients.restore', $patient->id) }}" method="POST" class="d-inline">
                                        @csrf <button class="btn btn-primary btn-sm rounded-pill px-3">Restore</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-eye"></i> View</a>
                                    <a href="{{ route('admin.patients.edit', $patient->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="fas fa-edit"></i> Edit</a>
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
            {{ $patients->links() }}
        </div>
    </div>
@endsection