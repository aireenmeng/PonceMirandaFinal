@extends('layouts.admin')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.admin' master layout. --}}
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            {{-- PAGE HEADER --}}
            <div class="mb-4">
                <h1 class="h3 text-gray-800 font-weight-bold">My Profile</h1>
                <p class="text-muted">Manage your account settings and security preferences.</p>
            </div>

            {{-- SUCCESS MESSAGE --}}
            {{-- Displays a success alert after profile or password updates --}}
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-left-success" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    @if(session('status') === 'profile-updated') Profile information updated. @endif
                    @if(session('status') === 'password-updated') Password updated successfully. @endif
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- MAIN CARD CONTAINER for all profile settings --}}
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-5">
                    
                    {{-- 1. PERSONAL INFORMATION SECTION --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h5 class="font-weight-bold text-dark mb-0">
                            <i class="fas fa-user-circle text-primary mr-2"></i> Personal Information
                        </h5>
                        {{-- Button to enable editing of personal information fields --}}
                        <button type="button" id="editProfileBtn" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
                            <i class="fas fa-pen mr-1"></i> Edit Details
                        </button>
                    </div>
                    
                    {{-- Form for updating personal information --}}
                    <form method="post" action="{{ route('profile.update') }}" id="profileForm">
                        @csrf {{-- CSRF token for security --}}
                        @method('patch') {{-- Specifies that this is a PATCH request for update --}}
                        
                        {{-- Form row for Name and Email --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">Full Name</label>
                                {{-- Name input field, initially disabled --}}
                                <input type="text" name="name" class="form-control rounded-pill profile-input" value="{{ old('name', $user->name) }}" required disabled>
                                {{-- Error message for 'name' field --}}
                                @error('name') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">Email Address</label>
                                {{-- Email input field, initially disabled --}}
                                <input type="email" name="email" class="form-control rounded-pill profile-input" value="{{ old('email', $user->email) }}" required disabled>
                                {{-- Error message for 'email' field --}}
                                @error('email') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        {{-- Form row for Phone Number --}}
                        <div class="form-row">
                             <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">Phone Number</label>
                                {{-- Phone input field, initially disabled --}}
                                <input type="text" name="phone" class="form-control rounded-pill profile-input" value="{{ old('phone', $user->phone) }}" disabled>
                            </div>
                        </div>

                        {{-- Container for Save and Cancel buttons, initially hidden --}}
                        <div class="text-right mt-3" id="saveProfileContainer" style="display:none;">
                            {{-- Cancel button to discard changes and re-disable inputs --}}
                            <button type="button" id="cancelEditBtn" class="btn btn-link text-muted mr-2">Cancel</button>
                            {{-- Submit button to save profile changes --}}
                            <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill font-weight-bold shadow-sm">
                                Save Profile Changes
                            </button>
                        </div>
                    </form>

                    <div class="py-4"></div> {{-- Spacer --}}

                    {{-- 2. SECURITY SECTION --}}
                    <h5 class="font-weight-bold text-dark mb-4 pb-2 border-bottom">
                        <i class="fas fa-lock text-primary mr-2"></i> Security
                    </h5>

                    {{-- Form for updating password --}}
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf {{-- CSRF token for security --}}
                        @method('put') {{-- Specifies that this is a PUT request for update --}}

                        {{-- Form group for Current Password --}}
                        <div class="form-group">
                            <label class="small font-weight-bold text-gray-600">Current Password</label>
                            <input type="password" name="current_password" class="form-control rounded-pill w-50" required>
                            {{-- Error message for 'current_password' field specifically for 'updatePassword' context --}}
                            @error('current_password', 'updatePassword') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        {{-- Form row for New Password and Confirm Password --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">New Password</label>
                                <input type="password" name="password" class="form-control rounded-pill" required>
                                {{-- Error message for 'password' field specifically for 'updatePassword' context --}}
                                @error('password', 'updatePassword') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control rounded-pill" required>
                            </div>
                        </div>

                        {{-- Submit button to update password --}}
                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-secondary btn-sm px-4 rounded-pill font-weight-bold shadow-sm">
                                Update Password
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

{{-- Push scripts to the 'scripts' stack defined in the master layout --}}
@push('scripts')
<script>
    // JavaScript for toggling profile information edit mode
    const editBtn = document.getElementById('editProfileBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const saveContainer = document.getElementById('saveProfileContainer');
    const inputs = document.querySelectorAll('.profile-input'); // Selects all inputs with class 'profile-input'

    if(editBtn) {
        // Event listener for the "Edit Details" button
        editBtn.addEventListener('click', function() {
            // Enable all profile input fields
            inputs.forEach(input => input.disabled = false);
            // Show the "Save" and "Cancel" buttons
            saveContainer.style.display = 'block';
            // Hide the "Edit Details" button
            editBtn.style.display = 'none';
            // Focus on the first editable input for better UX
            if(inputs.length > 0) inputs[0].focus(); 
        });

        // Event listener for the "Cancel" button
        cancelBtn.addEventListener('click', function() {
            // Disable all profile input fields
            inputs.forEach(input => input.disabled = true);
            // Hide the "Save" and "Cancel" buttons
            saveContainer.style.display = 'none';
            // Show the "Edit Details" button
            editBtn.style.display = 'block';
        });
    }
</script>
@endpush