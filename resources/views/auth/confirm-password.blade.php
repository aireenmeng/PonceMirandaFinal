@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h4 class="text-gray-900 font-weight-bold">Confirm Password</h4>
                        <p class="text-muted small">This is a secure area. Please confirm your password.</p>
                    </div>

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="form-group mb-4">
                            <label class="small text-muted font-weight-bold">Password</label>
                            <input type="password" name="password" class="form-control rounded-pill px-3 py-2" required autocomplete="current-password">
                            @error('password') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold shadow-sm py-2">
                            Confirm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
