@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h4 class="text-gray-900 font-weight-bold">Reset Password</h4>
                        <p class="text-muted small">Choose a new password for your account.</p>
                    </div>

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="form-group mb-3">
                            <label class="small text-muted font-weight-bold">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-pill px-3 py-2" required autofocus value="{{ old('email', $request->email) }}">
                            @error('email') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="small text-muted font-weight-bold">New Password</label>
                            <input type="password" name="password" class="form-control rounded-pill px-3 py-2" required autocomplete="new-password">
                            @error('password') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="small text-muted font-weight-bold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control rounded-pill px-3 py-2" required autocomplete="new-password">
                            @error('password_confirmation') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold shadow-sm py-2">
                            Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
