@extends('layouts.admin')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Patient Database</h1><a href="{{ route('admin.patients.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-user-plus fa-sm text-white-50"></i> Register Patient
    </a>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item"><a class="nav-link {{ $view == 'active' ? 'active' : '' }}" href="{{ route('admin.patients.index') }}">Active Patients</a></li>
                <li class="nav-item"><a class="nav-link {{ $view == 'archived' ? 'active bg-secondary text-white' : 'text-secondary' }}" href="{{ route('admin.patients.index', ['view' => 'archived']) }}">Archived</a></li>
            </ul>
        </div>
        <div class="card-body">
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
                                    <form action="{{ route('admin.patients.restore', $patient->id) }}" method="POST">
                                        @csrf <button class="btn btn-success btn-sm">Restore</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-primary btn-sm">View</a>
                                    <a href="{{ route('admin.patients.edit', $patient->id) }}" class="btn btn-info btn-sm">Edit</a>
                                    <form action="{{ route('admin.patients.destroy', $patient->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Archive?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-secondary btn-sm">Archive</button>
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