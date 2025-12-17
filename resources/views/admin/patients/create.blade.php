@extends('layouts.admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                {{-- FIXED TITLE --}}
                <h6 class="m-0 font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Register New Patient</h6>
            </div>
            <div class="card-body">
                {{-- FIXED FORM ACTION: Points to patients.store --}}
                <form action="{{ route('admin.patients.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Full Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. Juan dela Cruz">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Phone Number</label>
                        <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="09xxxxxxxxx" pattern="^09\d{9}$" maxlength="11">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Temporary Password</label>
                        <input type="text" name="password" class="form-control @error('password') is-invalid @enderror" value="{{ old('password', 'password') }}" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Default is 'password'. The patient can change this later.</small>
                    </div>

                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        {{-- FIXED BACK LINK: Points to patients.index --}}
                        <a href="{{ route('admin.patients.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Register Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection