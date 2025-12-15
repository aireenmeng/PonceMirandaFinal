@extends('layouts.admin')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.admin' master layout. --}}
@section('content')

    {{-- Header section with page title and a button to add new services --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        {{-- Page title for Services Inventory --}}
        <h1 class="h3 mb-0 text-gray-800">Services Inventory</h1>
        {{-- Button to navigate to the form for adding a new service --}}
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary shadow-sm rounded-pill px-3">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Service
        </a>
    </div>

    {{-- Conditional display for success messages after an action --}}
    @if(session('success'))
        <div class="alert alert-success border-left-success">{{ session('success') }}</div>
    @endif

    {{-- Main card container for the service listing and filter tabs --}}
    <div class="card shadow mb-4">
        {{-- Card header containing navigation tabs to filter services by status --}}
        <div class="card-header py-3">
            <ul class="nav nav-pills card-header-pills">
                {{-- Tab for Active Services --}}
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'active' ? 'active' : '' }}" href="{{ route('admin.services.index') }}">Active Services</a>
                </li>
                {{-- Tab for Archived Services --}}
                <li class="nav-item">
                    <a class="nav-link {{ $view == 'archived' ? 'active bg-secondary text-white' : 'text-secondary' }}" href="{{ route('admin.services.index', ['view' => 'archived']) }}">
                        <i class="fas fa-archive mr-1"></i> Archived
                    </a>
                </li>
            </ul>
        </div>

        {{-- Card body containing the table of services --}}
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    {{-- Table header --}}
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop through each service and display its details --}}
                        @foreach($services as $service)
                        <tr>
                            <td class="font-weight-bold">{{ $service->name }}</td>
                            <td class="text-success">₱{{ number_format($service->price, 2) }}</td>
                            <td>{{ $service->duration_minutes }} mins</td>
                            <td>
                                {{-- Display status badge based on whether the service is archived or active --}}
                                @if($view == 'archived')
                                    <span class="badge badge-secondary">Archived</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                            <td>
                                {{-- Conditional actions based on the current view (Active or Archived) --}}
                                @if($view == 'archived')
                                    {{-- Actions for archived services: Restore and Permanent Delete --}}
                                    <form action="{{ route('admin.services.restore', $service->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button class="btn btn-primary btn-sm rounded-pill px-3" title="Restore">
                                            <i class="fas fa-trash-restore"></i> Restore
                                        </button>
                                    </form>
                                    {{-- Form to permanently delete a service, with a confirmation dialog --}}
                                    <form action="{{ route('admin.services.forceDelete', $service->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this service? This action cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm rounded-pill px-3 ml-1" title="Permanently Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @else
                                    {{-- Actions for active services: Edit and Archive --}}
                                    <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3" title="Edit">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>
                                    {{-- Form to archive a service, with a confirmation dialog --}}
                                    <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Archive this service?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-secondary btn-sm rounded-pill px-3" title="Archive">
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
            {{ $services->appends(['view' => $view])->links() }}
        </div>
    </div>
@endsection