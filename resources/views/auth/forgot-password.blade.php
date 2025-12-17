@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h4 class="text-gray-900 font-weight-bold">Forgot Password?</h4>
                        <p class="text-muted small">Enter your email and we'll send you a reset link.</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group mb-4">
                            <label class="small text-muted font-weight-bold">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-pill px-3 py-2" required autofocus value="{{ old('email') }}">
                            @error('email') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold shadow-sm py-2">
                            Email Password Reset Link
                        </button>
                    </form>
                    
                    <hr>
                    <div class="text-center small">
                        <a href="{{ route('login') }}" class="text-primary font-weight-bold">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
