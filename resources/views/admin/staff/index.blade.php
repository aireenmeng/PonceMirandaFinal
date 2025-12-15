@extends('layouts.admin')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.admin' master layout. --}}
@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    {{-- Page title for Doctor Management --}}
    <h1 class="h3 mb-0 text-gray-800">Doctor Management</h1>
    {{-- Button to navigate to the form for adding a new doctor --}}
    <a href="{{ route('admin.staff.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-user-plus fa-sm text-white-50"></i> Add New Doctor
    </a>
</div>

{{-- Conditional display for success messages after an action --}}
@if(session('success')) <div class="alert alert-success border-left-success">{{ session('success') }}</div> @endif

{{-- Main card container for the doctor listing and filter tabs --}}
<div class="card shadow mb-4">
    {{-- Card header containing navigation tabs to filter doctors by status --}}
    <div class="card-header py-3">
        <ul class="nav nav-pills card-header-pills">
            {{-- Tab for Active Doctors --}}
            <li class="nav-item">
                <a class="nav-link {{ $view == 'active' ? 'active' : '' }}" href="{{ route('admin.staff.index') }}">Active Doctors</a>
            </li>
            {{-- Tab for Doctors with Pending Invites --}}
            <li class="nav-item">
                <a class="nav-link {{ $view == 'pending' ? 'active bg-warning text-dark' : 'text-warning' }}" href="{{ route('admin.staff.index', ['view' => 'pending']) }}">
                    <i class="fas fa-envelope mr-1"></i> Pending Invite
                </a>
            </li>
            {{-- Tab for Archived Doctors --}}
            <li class="nav-item">
                <a class="nav-link {{ $view == 'archived' ? 'active bg-danger text-white' : 'text-danger' }}" href="{{ route('admin.staff.index', ['view' => 'archived']) }}">
                    <i class="fas fa-user-slash mr-1"></i> Archived Doctors
                </a>
            </li>
        </ul>
    </div>

    {{-- Card body containing the table of doctors --}}
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    {{-- Table headers for Name, Email, and Actions --}}
                    <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    {{-- Loop through each staff member (doctor) and display their details --}}
                    @foreach($staff as $user)
                    <tr>
                        <td class="font-weight-bold">
                            {{-- Display doctor's name with an icon --}}
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-gray-200 d-flex align-items-center justify-content-center mr-3" style="width:35px; height:35px;">
                                    <i class="fas fa-user-md text-gray-500"></i>
                                </div>
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td class="text-right">
                            {{-- Conditional actions based on the current view (Active, Pending, or Archived) --}}
                            @if($view == 'archived')
                                {{-- Actions for archived doctors: Restore and Permanent Delete --}}
                                <div class="d-inline-flex">
                                    {{-- Form to restore an archived doctor --}}
                                    <form action="{{ route('admin.staff.restore', $user->id) }}" method="POST" class="mr-1">
                                        @csrf
                                        <button class="btn btn-success btn-sm shadow-sm">
                                            <i class="fas fa-trash-restore mr-1"></i> Restore
                                        </button>
                                    </form>
                                    {{-- Form to permanently delete a doctor, with a confirmation dialog --}}
                                    <form action="{{ route('admin.staff.forceDelete', $user->id) }}" method="POST" onsubmit="return confirm('Permanently delete this doctor? This action cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm shadow-sm">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            @else
                                {{-- Actions for active or pending doctors: Edit and Archive --}}
                                <a href="{{ route('admin.staff.edit', $user->id) }}" class="btn btn-info btn-sm shadow-sm mr-1">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                {{-- Form to archive a doctor, with a confirmation dialog --}}
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

    {{-- Card footer for pagination links --}}
    <div class="card-footer bg-white d-flex justify-content-end">
        {{-- Pagination links, preserving the current 'view' filter --}}
        {{ $staff->appends(['view' => $view])->links() }}
    </div>
</div>
@endsection