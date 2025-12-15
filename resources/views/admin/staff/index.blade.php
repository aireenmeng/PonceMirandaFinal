@extends('layouts.admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Doctor Management</h1>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-user-plus fa-sm text-white-50"></i> Add New Doctor
    </a>
</div>

@if(session('success')) <div class="alert alert-success border-left-success">{{ session('success') }}</div> @endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <ul class="nav nav-pills card-header-pills">
            <li class="nav-item">
                <a class="nav-link {{ $view == 'active' ? 'active' : '' }}" href="{{ route('admin.staff.index') }}">Active Doctors</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $view == 'pending' ? 'active bg-warning text-dark' : 'text-warning' }}" href="{{ route('admin.staff.index', ['view' => 'pending']) }}">
                    <i class="fas fa-envelope mr-1"></i> Pending Invite
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $view == 'archived' ? 'active bg-danger text-white' : 'text-danger' }}" href="{{ route('admin.staff.index', ['view' => 'archived']) }}">
                    <i class="fas fa-user-slash mr-1"></i> Archived Doctors
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($staff as $user)
                    <tr>
                        <td class="font-weight-bold">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-gray-200 d-flex align-items-center justify-content-center mr-3" style="width:35px; height:35px;">
                                    <i class="fas fa-user-md text-gray-500"></i>
                                </div>
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td class="text-right">
                            @if($view == 'archived')
                                <div class="d-inline-flex">
                                    <form action="{{ route('admin.staff.restore', $user->id) }}" method="POST" class="mr-1">
                                        @csrf
                                        <button class="btn btn-success btn-sm shadow-sm">
                                            <i class="fas fa-trash-restore mr-1"></i> Restore
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.staff.forceDelete', $user->id) }}" method="POST" onsubmit="return confirm('Permanently delete this doctor? This action cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm shadow-sm">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ route('admin.staff.edit', $user->id) }}" class="btn btn-info btn-sm shadow-sm mr-1">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <form action="{{ route('admin.staff.destroy', $user->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Archive doctor: {{ $user->name }}?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-secondary btn-sm shadow-sm">
                                        <i class="fas fa-archive"></i> Archive
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-end">
        {{ $staff->appends(['view' => $view])->links() }}
    </div>
</div>
@endsection