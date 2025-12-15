@extends('layouts.admin')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.admin' master layout. --}}
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            {{-- Card header for the 'Add New Doctor' form --}}
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Add New Doctor</h6>
            </div>
            {{-- Card body containing the form elements --}}
            <div class="card-body">
                {{-- Form for creating a new staff member (doctor).
                     It submits a POST request to the 'admin.staff.store' route. --}}
                <form action="{{ route('admin.staff.store') }}" method="POST">
                    @csrf {{-- CSRF token for security --}}
                    
                    {{-- Form group for Full Name input --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Dr. John Doe">
                        {{-- Laravel's @error directive can be used here for validation feedback if needed --}}
                        {{-- @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror --}}
                    </div>
                    
                    {{-- Form group for Email Address input --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                        {{-- @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror --}}
                    </div>
                    
                    {{-- Form row for phone and hidden role input --}}
                    <div class="form-row">
                        {{-- Hidden input field to automatically set the role as 'doctor' for new staff --}}
                        <input type="hidden" name="role" value="doctor"> 
                        {{-- Form group for Phone input, takes full column width --}}
                        <div class="form-group col-md-12"> 
                            <label class="font-weight-bold">Phone</label>
                            <input type="text" name="phone" class="form-control">
                            {{-- @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror --}}
                        </div>
                    </div>
                    <hr>
                    {{-- Action buttons: Cancel and Invite Doctor --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Invite Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection