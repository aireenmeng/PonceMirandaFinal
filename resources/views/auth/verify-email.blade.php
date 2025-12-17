@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5 text-center">
                    
                    <div class="mb-4">
                        <i class="fas fa-envelope-open-text fa-4x text-primary mb-3"></i>
                        <h4 class="text-gray-900 font-weight-bold">Verify Your Email</h4>
                        <p class="text-muted">
                            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?
                        </p>
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success" role="alert">
                            A new verification link has been sent to the email address you provided during registration.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold shadow-sm py-2">
                            Resend Verification Email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-danger">
                            Log Out
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
