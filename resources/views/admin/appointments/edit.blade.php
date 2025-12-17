@extends('layouts.admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10"> {{-- Wider column for calendar --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-edit mr-2"></i> Edit Appointment #{{ $appointment->id }}</h6>
                    <a href="{{ route('admin.appointments.index', request()->query()) }}" class="btn btn-sm btn-light text-primary font-weight-bold rounded-pill px-3">
                        <i class="fas fa-arrow-left"></i> Back to List
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

                    <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST" id="editAppointmentForm">
                        @csrf
                        @method('PUT')
                        @foreach(request()->query() as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach

                        <input type="hidden" name="user_id" value="{{ $appointment->user_id }}">
                        <input type="hidden" name="appointment_date" id="input_date" value="{{ $appointment->appointment_date->format('Y-m-d') }}">
                        <input type="hidden" name="appointment_time" id="input_time" value="{{ $appointment->appointment_time->format('H:i') }}">
                        
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-gray-700">Patient Name</label>
                            <input type="text" class="form-control rounded-pill" value="{{ $appointment->patient->name ?? 'Unknown' }}" readonly>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold text-gray-700">Select Doctor</label>
                                <select name="doctor_id" id="doctorSelect" class="form-control rounded-pill" required>
                                    <option value="">-- Choose Doctor --</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ $appointment->doctor_id == $doc->id ? 'selected' : '' }}>
                                            Dr. {{ $doc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold text-gray-700">Service / Treatment</label>
                                <select name="service_id" id="serviceSelect" class="form-control rounded-pill" required>
                                    <option value="">-- Select Service --</option>
                                    @foreach($services as $s)
                                        <option value="{{ $s->id }}" 
                                                data-duration="{{ $s->duration_minutes }}"
                                                {{ $appointment->service_id == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }} ({{ $s->duration_minutes }} mins) - ₱{{ number_format($s->price) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <h6 class="font-weight-bold text-dark mb-3 mt-4 pb-2 border-bottom"><i class="fas fa-calendar-alt mr-2 text-primary"></i> Reschedule Date & Time</h6>
                        
                        <div class="row no-gutters mb-4">
                            <div class="col-md-6 border-right p-3">
                                <div id="calendar"></div>
                            </div>
                            <div class="col-md-6 p-3 bg-light">
                                <h6 class="font-weight-bold text-center mb-3" id="selectedDateLabel">
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('F d, Y') }}
                                </h6>
                                <div id="slotsContainer" class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                    <div class="text-center text-muted mt-5 small">
                                        Select a date to view available slots.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-gray-700">Duration</label>
                            <select name="duration_minutes" id="durationSelect" class="form-control rounded-pill font-weight-bold text-success" required>
                                @for($i = 30; $i <= 240; $i += 30) 
                                    <option value="{{ $i }}" {{ $appointment->duration_minutes == $i ? 'selected' : '' }}>
                                        {{ $i }} Minutes
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-gray-700">Calculated End Time</label>
                            <input type="text" id="endTimeDisplay" class="form-control rounded-pill bg-light" readonly>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm rounded-pill px-5">
                            <i class="fas fa-save mr-2"></i> Update Appointment
                        </button>
                        <a href="{{ route('admin.appointments.index', request()->query()) }}" class="btn btn-secondary btn-lg btn-block shadow-sm rounded-pill px-5 mt-3">
                            <i class="fas fa-times-circle mr-2"></i> Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <style>
        /* General Calendar Styling */
        #calendar {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .fc-toolbar-title { font-size: 1.2em; font-weight: bold; color: var(--primary); }
        .fc-button-group .fc-button {
            border-radius: 50px !important;
            border: 1px solid var(--primary) !important;
            background-color: transparent !important;
            color: var(--primary) !important;
            transition: all 0.2s;
        }
        .fc-button-group .fc-button:hover {
            background-color: var(--primary) !important;
            color: white !important;
        }
        .fc-button-group .fc-button-active {
            background-color: var(--primary) !important;
            color: white !important;
        }
        .fc-day-past { background-color: #f8f9fc; pointer-events: none; opacity: 0.6; }
        .fc-day-today { background-color: #fffde7 !important; } /* Highlight today */

        /* Slot Button Styling */
        .slot-btn { 
            font-weight: bold; 
            border-left: 6px solid transparent; 
            transition: all 0.2s; 
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8f9fc;
            color: #444444;
            border-radius: 8px; /* Consistent with other cards/inputs */
            margin-bottom: 8px !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 10px 15px;
        }
        .slot-btn:hover { border-left: 6px solid var(--primary); background-color: var(--primary-soft); color: var(--primary); }
        .slot-btn.active { 
            border-left: 6px solid var(--primary); 
            background-color: var(--primary) !important; 
            color: white !important; 
            box-shadow: 0 4px 10px rgba(209, 57, 108, 0.3);
        }
        .slot-btn.active small { color: rgba(255,255,255,0.8) !important; }
        .slot-btn.active i { color: white !important; }
        .slot-btn small { color: #6c757d; }
        .slot-btn i { color: #aaaaaa; }
    </style>
    <script>
        var calendar;
        var selectedDoctorId = "{{ $appointment->doctor_id }}";
        var selectedServiceId = "{{ $appointment->service_id }}";
        var selectedDuration = "{{ $appointment->duration_minutes }}"; // Initial duration
        var currentAppointmentDate = "{{ $appointment->appointment_date->format('Y-m-d') }}";
        var currentAppointmentTime = "{{ $appointment->appointment_time->format('H:i') }}";

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var today = new Date().toISOString().split('T')[0]; 
            var twoMonthsLater = new Date();
            twoMonthsLater.setMonth(twoMonthsLater.getMonth() + 2);
            twoMonthsLater.setDate(twoMonthsLater.getDate() + 1);
            var maxDate = twoMonthsLater.toISOString().split('T')[0];

            // Initialize selectDoctorId and selectedServiceId based on current appointment
            selectedDoctorId = document.getElementById('doctorSelect').value;
            selectedServiceId = document.getElementById('serviceSelect').value;
            selectedDuration = parseInt(document.getElementById('durationSelect').value);
            
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 400,
                headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                initialDate: currentAppointmentDate, // Start calendar on appointment date
                selectable: true,
                validRange: { 
                    start: today, 
                    end: maxDate 
                },
                events: function(info, successCallback, failureCallback) {
                    if (!selectedDoctorId) { successCallback([]); return; }
                    fetch(`{{ route('api.calendar') }}?doctor_id=${selectedDoctorId}&start=${info.startStr}&end=${info.endStr}`)
                        .then(r => r.json()).then(data => successCallback(data));
                },
                dateClick: function(info) {
                    if (!selectedDoctorId) {
                        alert('Please select a doctor first.');
                        return;
                    }
                    if (info.dateStr < today) {
                        alert('Cannot book in the past.');
                        return;
                    }
                    if (info.dateStr >= maxDate) {
                        alert('Cannot book more than two months in advance.');
                        return;
                    }

                    document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                    info.dayEl.style.backgroundColor = '#eaecf4';
                    
                    document.getElementById('selectedDateLabel').innerText = new Date(info.dateStr).toDateString();
                    document.getElementById('input_date').value = info.dateStr; // Update hidden input
                    updateEndTime(); // CALL THIS TO UPDATE END TIME
                    
                    fetchSlots(info.dateStr);
                }
            });
            calendar.render();

            // Initial slot fetch for current appointment date
            fetchSlots(currentAppointmentDate, currentAppointmentTime);

            // Event Listeners for Doctor/Service/Duration changes
            document.getElementById('doctorSelect').addEventListener('change', function() {
                selectedDoctorId = this.value;
                calendar.refetchEvents(); // Refresh calendar for new doctor
                // After doctor change, clear existing slots and date selection
                document.getElementById('slotsContainer').innerHTML = '<div class="text-center text-muted mt-5 small">Select a date to view slots.</div>';
                document.getElementById('selectedDateLabel').innerText = 'Select a Date';
                document.getElementById('input_date').value = '';
                document.getElementById('input_time').value = '';
                updateEndTime(); // Clear end time
            });
            document.getElementById('serviceSelect').addEventListener('change', function() {
                selectedServiceId = this.value;
                selectedDuration = parseInt(this.options[this.selectedIndex].getAttribute('data-duration'));
                document.getElementById('durationSelect').value = selectedDuration;
                // Re-fetch slots for currently selected date if doctor is selected
                if (selectedDoctorId && document.getElementById('input_date').value) {
                    fetchSlots(document.getElementById('input_date').value);
                }
            });
            document.getElementById('durationSelect').addEventListener('change', function() {
                selectedDuration = parseInt(this.value);
                // Re-fetch slots for currently selected date
                if (selectedDoctorId && document.getElementById('input_date').value) {
                    fetchSlots(document.getElementById('input_date').value);
                }
            });
        });

        // --- CALENDAR SLOTS LOGIC (Copied from patient booking) ---
        function fetchSlots(date, preselectTime = null) {
            const container = document.getElementById('slotsContainer');
            container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary spinner-border-sm"></div></div>';

            // Verify selectedDoctorId
            if (!selectedDoctorId) {
                container.innerHTML = '<div class="alert alert-warning small m-2">Please select a doctor to view slots.</div>';
                document.getElementById('input_time').value = "";
                updateEndTime();
                return;
            }

            fetch(`{{ route('api.day_details') }}?date=${date}&doctor_id=${selectedDoctorId}`)
                .then(response => {
                    if (!response.ok) { // Check for HTTP errors
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json(); // Try to parse JSON
                })
                .then(data => {
                    container.innerHTML = '';
                    
                    if (data.status === 'closed') {
                        container.innerHTML = '<div class="alert alert-warning small m-2">Doctor not available.</div>';
                        document.getElementById('input_time').value = ""; // Clear time
                        updateEndTime();
                        return;
                    }

                    let slotsNeeded = Math.ceil(selectedDuration / 30);
            // Initial slot fetch for current appointment date
            fetchSlots(currentAppointmentDate, currentAppointmentTime);

            // Event Listeners for Doctor/Service/Duration changes
            document.getElementById('doctorSelect').addEventListener('change', function() {
                selectedDoctorId = this.value;
                calendar.refetchEvents(); // Refresh calendar for new doctor
                // After doctor change, clear existing slots and date selection
                document.getElementById('slotsContainer').innerHTML = '<div class="text-center text-muted mt-5 small">Select a date to view slots.</div>';
                document.getElementById('selectedDateLabel').innerText = 'Select a Date';
                document.getElementById('input_date').value = '';
                document.getElementById('input_time').value = '';
                updateEndTime(); // Clear end time
            });
            document.getElementById('serviceSelect').addEventListener('change', function() {
                selectedServiceId = this.value;
                selectedDuration = parseInt(this.options[this.selectedIndex].getAttribute('data-duration'));
                document.getElementById('durationSelect').value = selectedDuration;
                // Re-fetch slots for currently selected date if doctor is selected
                if (selectedDoctorId && document.getElementById('input_date').value) {
                    fetchSlots(document.getElementById('input_date').value, document.getElementById('input_time').value); // Pass current input_time as preselect
                }
            });
            document.getElementById('durationSelect').addEventListener('change', function() {
                selectedDuration = parseInt(this.value);
                // Re-fetch slots for currently selected date
                if (selectedDoctorId && document.getElementById('input_date').value) {
                    fetchSlots(document.getElementById('input_date').value, document.getElementById('input_time').value); // Pass current input_time as preselect
                }
            });
        });

        // --- CALENDAR SLOTS LOGIC (THE SMART MERGING) ---
        function fetchSlots(date, preselectTime = null) {
            const container = document.getElementById('slotsContainer');
            const dateLabel = document.getElementById('selectedDateLabel');
            
            dateLabel.innerText = new Date(date).toDateString();
            container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary spinner-border-sm"></div></div>';

            // Verify selectedDoctorId
            if (!selectedDoctorId) {
                container.innerHTML = '<div class="alert alert-warning small m-2">Please select a doctor to view slots.</div>';
                document.getElementById('input_time').value = "";
                updateEndTime();
                return;
            }

            fetch(`{{ route('api.day_details') }}?date=${date}&doctor_id=${selectedDoctorId}&duration_minutes=${selectedDuration}&exclude_appointment_id={{ $appointment->id }}`)
                .then(response => {
                    if (!response.ok) { // Check for HTTP errors
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json(); // Try to parse JSON
                })
                .then(data => {
                    container.innerHTML = '';
                    
                    if (data.status === 'closed') {
                        container.innerHTML = '<div class="alert alert-warning small m-2">Doctor not available.</div>';
                        document.getElementById('input_time').value = ""; // Clear time
                        updateEndTime();
                        return;
                    }

                    let slotsNeeded = Math.ceil(selectedDuration / 30);
                    let hasSlots = false;
                    let generatedSlotElements = []; // Declare array to hold generated slot buttons

                    for (let i = 0; i <= data.slots.length - slotsNeeded; i++) {
                        let isSequenceOpen = true;

                        for (let j = 0; j < slotsNeeded; j++) {
                            if (data.slots[i + j].type !== 'available') {
                                isSequenceOpen = false;
                                break;
                            }
                        }

                        if (isSequenceOpen) {
                            hasSlots = true;
                            let startSlot = data.slots[i];
                            // No need for endSlot here unless for display label

                            let startLabel = startSlot.time_label.split(' - ')[0]; // "1:30 PM"
                            let displayLabel = `${startLabel} - ${startSlot.time_label.split(' - ')[1]}`; // Full label

                            let btn = document.createElement('button');
                            btn.className = 'list-group-item list-group-item-action py-3 mb-2 shadow-sm rounded slot-btn';
                            btn.innerHTML = `
                                <div>
                                    <div class="h6 mb-0 text-dark">${displayLabel}</div>
                                    <small class="text-muted">Duration: ${selectedDuration} mins</small>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            `;
                            btn.type = 'button';
                            btn.onclick = function() { selectTime(this, date, startSlot.raw_time, displayLabel); };
                            container.appendChild(btn);

                            generatedSlotElements.push({
                                btn: btn,
                                raw_time: startSlot.raw_time
                            });
                        }
                    }

                    // --- FINAL TIME SELECTION LOGIC ---
                    let selectedInitialTime = false;
                    
                    // 1. Try to pre-select the provided preselectTime (current appointment time) if it's available
                    if (preselectTime) {
                        for (const slotEl of generatedSlotElements) {
                            if (slotEl.raw_time === preselectTime) {
                                slotEl.btn.click(); // Simulate click to mark active and set inputs
                                selectedInitialTime = true;
                                break;
                            }
                        }
                    }

                    // 2. If no time was explicitly selected (e.g., preselectTime was null or not available),
                    // default to the first available slot if there are any.
                    if (!selectedInitialTime && hasSlots && generatedSlotElements.length > 0) {
                        generatedSlotElements[0].btn.click();
                        selectedInitialTime = true;
                    }

                    // 3. If no slots available at all, clear input_time
                    if (!hasSlots) {
                        container.innerHTML = `<div class="alert alert-secondary small m-2 text-center">
                            No continuous ${selectedDuration}-minute slot available.<br>Please try another date.
                        </div>`;
                        document.getElementById('input_time').value = ""; // Clear if no slots at all
                    } else if (!selectedInitialTime) {
                        // This case means there are slots, but no selection was made (should be covered by step 2).
                        // Defensive clear to ensure something is always set or cleared.
                        document.getElementById('input_time').value = ""; 
                    }
                    
                    updateEndTime(); // Final call after all input_time adjustments
                })
                .catch(err => {
                    console.error("Fetch slots error:", err); // Log the actual error
                    container.innerHTML = `<div class="alert alert-danger small m-2 text-center">
                        Error loading slots. Details: ${err.message || err}. Please try again or select another doctor/date.
                    </div>`;
                    document.getElementById('input_time').value = ""; // Clear on error
                    updateEndTime(); // Update display for error state
                });
        }

        function selectTime(el, date, rawTime, label) {
            document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('active', 'bg-success', 'text-white'));
            el.classList.add('active', 'bg-success', 'text-white');

            document.getElementById('input_date').value = date;
            document.getElementById('input_time').value = rawTime;
            
            updateEndTime(); // CALL THIS TO UPDATE END TIME
        }

        // --- UTILITY FOR END TIME DISPLAY (Original) ---
        function updateEndTime() {
            const startTimeStr = document.getElementById('input_time').value; 
            const duration = parseInt(document.getElementById('durationSelect').value);
            const appointmentDateStr = document.getElementById('input_date').value;

            if (!startTimeStr || !duration || !appointmentDateStr) {
                document.getElementById('endTimeDisplay').value = "N/A";
                return;
            }

            let dateTime = new Date(`${appointmentDateStr}T${startTimeStr}:00`); 
            dateTime.setMinutes(dateTime.getMinutes() + duration);
            
            let hours = dateTime.getHours();
            let minutes = dateTime.getMinutes();
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; 
            minutes = minutes < 10 ? '0'+minutes : minutes;
            
            document.getElementById('endTimeDisplay').value = hours + ':' + minutes + ' ' + ampm;
        }
    </script>
@endsection