@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h4 class="text-gray-900 font-weight-bold">Create an Account</h4>
                        <p class="text-muted small">Join Ponce Miranda Dental Clinic today!</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label class="small text-muted font-weight-bold">Full Name</label>
                            <input type="text" name="name" class="form-control rounded-pill px-3 py-2" required placeholder="Juan dela Cruz" value="{{ old('name') }}">
                            @error('name') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="small text-muted font-weight-bold">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-pill px-3 py-2" required placeholder="juan@example.com" value="{{ old('email') }}">
                            @error('email') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group mb-3 position-relative">
                            <label class="small text-muted font-weight-bold">Password</label>
                            <input type="password" name="password" id="reg_pass" class="form-control rounded-pill px-3 py-2" required style="padding-right: 40px;">
                            
                            <button type="button" onclick="togglePassword('reg_pass')" class="btn btn-link position-absolute" style="top: 32px; right: 10px; text-decoration: none; color: #aaa;">
                                <i class="fas fa-eye" id="reg_pass-icon"></i>
                            </button>
                            @error('password') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group mb-4 position-relative">
                            <label class="small text-muted font-weight-bold">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="reg_confirm" class="form-control rounded-pill px-3 py-2" required style="padding-right: 40px;">
                            
                            <button type="button" onclick="togglePassword('reg_confirm')" class="btn btn-link position-absolute" style="top: 32px; right: 10px; text-decoration: none; color: #aaa;">
                                <i class="fas fa-eye" id="reg_confirm-icon"></i>
                            </button>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold shadow-sm py-2">
                            Register Account
                        </button>
                    </form>

                    <hr>
                    <div class="text-center small">
                        <a href="{{ route('login') }}" class="text-primary font-weight-bold">Already have an account? Login!</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection