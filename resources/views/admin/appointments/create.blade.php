@extends('layouts.admin')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: calc(1.5em + 1rem + 2px) !important;
            padding: 0.5rem 1rem !important;
            font-size: 1.25rem !important;
            line-height: 1.5 !important;
            border-radius: 0.3rem !important;
            border: 1px solid #d1d3e2 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 1rem + 2px) !important;
            right: 10px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5 !important;
            color: #6e707e !important;
            padding-left: 0 !important;
        }
    </style>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Book Walk-In Appointment</h6>
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm btn-light text-primary font-weight-bold">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.appointments.store') }}" method="POST">
                        @csrf
                        {{-- Hidden Fields from Calendar Link --}}
                        <input type="hidden" name="appointment_date" value="{{ request('date') }}">
                        
                        <div class="alert alert-primary border-0 d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <small class="text-uppercase font-weight-bold opacity-70">Selected Date</small><br>
                                <span class="h5 font-weight-bold" id="appointmentDateDisplay">
                                    {{ \Carbon\Carbon::parse(request('date'))->format('M d, Y') }}
                                </span>
                            </div>
                        </div>

                        {{-- Doctor Selection (Conditional) --}}
                        <div class="form-group mb-4">
                            @if(request('doctor_id'))
                                <label class="font-weight-bold text-gray-700">Doctor</label>
                                <input type="hidden" name="doctor_id" id="doctorSelect" value="{{ request('doctor_id') }}">
                                <span class="form-control-plaintext form-control-lg font-weight-bold">Dr. {{ $doctors->find(request('doctor_id'))->name ?? 'Unknown' }}</span>
                            @else
                                <label class="font-weight-bold text-gray-700">Select Doctor</label>
                                <select name="doctor_id" id="doctorSelect" class="form-control form-control-lg border-left-primary @error('doctor_id') is-invalid @enderror">
                                    <option value="">-- Choose Doctor --</option>
                                    @foreach($doctors as $d)
                                        <option value="{{ $d->id }}" {{ (old('doctor_id', request('doctor_id')) == $d->id) ? 'selected' : '' }}>Dr. {{ $d->name }}</option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <hr class="my-4">

                        {{-- Patient Selection Type --}}
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-gray-700">Patient Type</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="patient_type" id="existingPatientRadio" value="existing" checked>
                                <label class="form-check-label" for="existingPatientRadio">
                                    Existing Patient
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="patient_type" id="newPatientRadio" value="new">
                                <label class="form-check-label" for="newPatientRadio">
                                    New Walk-in Patient
                                </label>
                            </div>
                        </div>

                        {{-- Existing Patient Fields --}}
                        <div id="existingPatientSection" class="form-group mb-4">
                            <label class="font-weight-bold text-gray-700">Select Existing Patient</label>
                            <select name="user_id" id="existingPatientSelect" class="form-control form-control-lg border-left-primary @error('user_id') is-invalid @enderror">
                                <option value="">-- Choose Patient --</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ old('user_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->phone ?? 'No # ' }})</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted mt-2">
                                <a href="{{ route('admin.patients.index') }}" target="_blank"><i class="fas fa-plus"></i> Register new account</a> (Opens in new tab)
                            </small>
                        </div>

                        {{-- New Patient Fields (Initially Hidden) --}}
                        <div id="newPatientSection" class="form-group mb-4" style="display:none;">
                            <label class="font-weight-bold text-gray-700">New Patient Details</label>
                            <input type="text" name="new_patient_name" id="newPatientName" class="form-control form-control-lg mb-2 @error('new_patient_name') is-invalid @enderror" placeholder="Patient Name" value="{{ old('new_patient_name') }}">
                            @error('new_patient_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <input type="tel" name="new_patient_phone" id="newPatientPhone" class="form-control form-control-lg @error('new_patient_phone') is-invalid @enderror" placeholder="Phone Number" value="{{ old('new_patient_phone') }}">
                            @error('new_patient_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-gray-700">Service / Treatment</label>
                            <select name="service_id" id="serviceSelect" class="form-control form-control-lg @error('service_id') is-invalid @enderror">
                                <option value="" data-duration="30">-- Select Service --</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}" data-duration="{{ $s->duration_minutes }}" {{ old('service_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }} ({{ $s->duration_minutes }} mins) - ₱{{ number_format($s->price) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row align-items-end mb-4">
                            <div class="col-md-6">
                                <label class="font-weight-bold text-gray-700">Duration</label>
                                <input type="hidden" name="duration_minutes" id="durationSelect" value="{{ old('duration_minutes', 60) }}">
                                <span class="form-control-plaintext font-weight-bold text-success" id="durationDisplay">60 Minutes (1 hr)</span>
                                @error('duration_minutes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div> {{-- d-block to show for hidden field --}}
                                @enderror
                                <small class="text-muted">Auto-selected based on service.</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="font-weight-bold text-gray-700">Calculated End Time</label>
                                <input type="text" id="endTimeDisplay" class="form-control bg-light" readonly>
                            </div>
                        </div>

                        {{-- Time Slot Selection (Conditional) --}}
                        <div class="form-group mb-4">
                            @if(request('time'))
                                <label class="font-weight-bold text-gray-700">Appointment Time</label>
                                <input type="hidden" name="appointment_time" id="appointmentTimeSelect" value="{{ request('time') }}">
                                <span class="form-control-plaintext form-control-lg font-weight-bold">{{ \Carbon\Carbon::parse(request('time'))->format('h:i A') }}</span>
                            @else
                                <label class="font-weight-bold text-gray-700">Available Time Slots</label>
                                <select name="appointment_time" id="appointmentTimeSelect" class="form-control form-control-lg @error('appointment_time') is-invalid @enderror">
                                    <option value="">-- Select Time --</option>
                                </select>
                                @error('appointment_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted mt-2" id="slotsMessage">Loading available slots...</small>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm">
                            <i class="fas fa-check-circle mr-2"></i> Confirm Appoinment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const appointmentDate = "{{ request('date') }}";
        const jsRequestedDoctorId = "{{ request('doctor_id') }}"; 
        const jsRequestedTime = "{{ request('time') }}"; 
        
        const doctorSelect = document.getElementById('doctorSelect'); 
        const appointmentTimeSelect = document.getElementById('appointmentTimeSelect');

        const durationSelect = document.getElementById('durationSelect');
        const serviceSelect = document.getElementById('serviceSelect');
        const slotsMessage = document.getElementById('slotsMessage');
        const endTimeDisplay = document.getElementById('endTimeDisplay');

        // Patient Type Selection Elements
        const existingPatientRadio = document.getElementById('existingPatientRadio');
        const newPatientRadio = document.getElementById('newPatientRadio');
        const existingPatientSection = document.getElementById('existingPatientSection');
        const newPatientSection = document.getElementById('newPatientSection');
        const existingPatientSelect = document.getElementById('existingPatientSelect');
        const newPatientNameInput = document.getElementById('newPatientName');
        const newPatientPhoneInput = document.getElementById('newPatientPhone');

        // Function to update the calculated end time
        function updateEndTime(selectedTime = null) {
            const duration = parseInt(durationSelect.value);
            let startTime = selectedTime || (jsRequestedTime || (appointmentTimeSelect ? appointmentTimeSelect.value : null));
            
            if (!startTime) {
                endTimeDisplay.value = 'N/A';
                return;
            }
            
            let date = new Date("2000-01-01 " + startTime); 
            date.setMinutes(date.getMinutes() + duration);
            
            let hours = date.getHours();
            let minutes = date.getMinutes();
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; 
            minutes = minutes < 10 ? '0'+minutes : minutes;
            
            endTimeDisplay.value = hours + ':' + minutes + ' ' + ampm;
        }

        // Function to fetch and display available slots
        async function fetchAvailableSlots() {
            if (jsRequestedTime) {
                if (slotsMessage) slotsMessage.textContent = 'Time already pre-selected.';
                updateEndTime(jsRequestedTime); 
                return;
            }

            if (!appointmentTimeSelect) return; 

            slotsMessage.textContent = 'Loading available slots...';
            appointmentTimeSelect.innerHTML = '<option value="">-- Select Time --</option>'; 
            endTimeDisplay.value = 'N/A';

            // Check if doctorSelect is a select element or hidden input
            const currentDoctorId = doctorSelect.value;
            const currentAppointmentDate = appointmentDate; 

            if (!currentDoctorId || !currentAppointmentDate) {
                slotsMessage.textContent = 'Please select a Doctor and Date.';
                return;
            }

            try {
                const duration = parseInt(durationSelect.value);
                const response = await fetch("{{ route('admin.appointments.availableSlots') }}?" + new URLSearchParams({
                    doctor_id: currentDoctorId, 
                    date: currentAppointmentDate,
                    duration: duration
                }));
                const data = await response.json();

                if (data.error) {
                    slotsMessage.textContent = 'Error: ' + data.error;
                    return;
                }
                if (data.message) {
                    slotsMessage.textContent = data.message;
                    return;
                }

                if (data.slots && data.slots.length > 0) {
                    data.slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot.time;
                        option.textContent = slot.display;
                        appointmentTimeSelect.appendChild(option);
                    });
                    slotsMessage.textContent = `${data.slots.length} slots available.`;

                    appointmentTimeSelect.value = data.slots[0].time;
                    updateEndTime(data.slots[0].time);
                } else {
                    slotsMessage.textContent = 'No available slots for this duration.';
                }
            } catch (error) {
                console.error('Error fetching available slots:', error);
                slotsMessage.textContent = 'Failed to load slots.';
            }
        }

        // Function to update duration based on selected service and then fetch slots
        function updateDuration() {
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            const recommendedDuration = selectedOption.getAttribute('data-duration');

            if (recommendedDuration) {
                durationSelect.value = recommendedDuration;
                durationDisplay.textContent = `${recommendedDuration} Minutes (${recommendedDuration / 60} hr)`;
            }
            fetchAvailableSlots(); 
        }

        // --- Patient Type Selection Logic ---
        function togglePatientTypeFields() {
            if (existingPatientRadio.checked) {
                existingPatientSection.style.display = 'block';
                newPatientSection.style.display = 'none';
                existingPatientSelect.setAttribute('required', 'required');
                newPatientNameInput.removeAttribute('required');
                newPatientPhoneInput.removeAttribute('required');
            } else {
                existingPatientSection.style.display = 'none';
                newPatientSection.style.display = 'block';
                existingPatientSelect.removeAttribute('required');
                newPatientNameInput.setAttribute('required', 'required');
                newPatientPhoneInput.setAttribute('required', 'required');
            }
        }

        // Event Listeners
        // Doctor Select only triggers fetchAvailableSlots if not pre-filled
        @if(!request('doctor_id'))
            if (doctorSelect) { 
                doctorSelect.addEventListener('change', fetchAvailableSlots); 
            }
        @endif
        serviceSelect.addEventListener('change', updateDuration); 
        // Time slot select only triggers updateEndTime if not pre-filled
        @if(!request('time'))
            if (appointmentTimeSelect) { 
                appointmentTimeSelect.addEventListener('change', () => updateEndTime(appointmentTimeSelect.value));
            }
        @endif

        existingPatientRadio.addEventListener('change', togglePatientTypeFields);
        newPatientRadio.addEventListener('change', togglePatientTypeFields);

        // Initial calls on page load
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Select2 for Patient Search
            $('#existingPatientSelect').select2({
                placeholder: "-- Choose Patient --",
                allowClear: true,
                width: '100%'
            });

            if (doctorSelect.value) { 
                updateDuration(); 
            } else {
                if (slotsMessage) slotsMessage.textContent = 'Please select a Doctor and Date to see available slots.';
            }
            updateDuration(); 

            // Initialize patient type fields visibility
            togglePatientTypeFields();
        });
    </script>
@endpush