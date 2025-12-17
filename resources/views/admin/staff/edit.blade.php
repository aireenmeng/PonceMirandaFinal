@extends('layouts.admin')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.admin' master layout. --}}
@section('content')
<div class="container-fluid">
    {{-- Page title for editing doctor details --}}
    <h1 class="h3 mb-4 text-gray-800">Edit Doctor Details</h1>

    {{-- Row to center the edit form --}}
    <div class="row justify-content-center">
        {{-- Column to control the width of the form card --}}
        <div class="col-lg-8">
            {{-- Card container for the doctor details form --}}
            <div class="card shadow mb-4">
                {{-- Card header with a primary title --}}
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Doctor Details</h6>
                </div>
                {{-- Card body containing the form --}}
                <div class="card-body">
                    {{-- Form for updating staff (doctor) details.
                         It submits a PUT request to the 'admin.staff.update' route,
                         passing the staff member's ID. --}}
                    <form action="{{ route('admin.staff.update', $staff->id) }}" method="POST">
                        @csrf {{-- CSRF token for security --}}
                        @method('PUT') {{-- Specifies that this is a PUT request for update --}}

                        {{-- Form group for Name input --}}
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $staff->name) }}" required>
                            {{-- Error message display for 'name' field --}}
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Form group for Email input --}}
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $staff->email) }}" required>
                            {{-- Error message display for 'email' field --}}
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Form group for Phone input --}}
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" name="phone" id="phone" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $staff->phone) }}">
                            {{-- Error message display for 'phone' field --}}
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Form group for Role dropdown --}}
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select name="role" id="role" 
                                    class="form-control @error('role') is-invalid @enderror" required>
                                {{-- Option for 'Doctor' role, pre-selected if current staff role is 'doctor' --}}
                                <option value="doctor" {{ old('role', $staff->role) == 'doctor' ? 'selected' : '' }}>Doctor</option>
                                {{-- Additional roles could be added here if applicable --}}
                            </select>
                            {{-- Error message display for 'role' field --}}
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Submit button to save changes --}}
                        <button type="submit" class="btn btn-primary shadow-sm">Save Changes</button>
                        {{-- Cancel button, redirects to the staff index page --}}
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary shadow-sm ml-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection