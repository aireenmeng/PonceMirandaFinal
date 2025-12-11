@extends('layouts.admin')

@section('content')

    <div class="row mb-4 align-items-center">
        <div class="col-auto">
             <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                <i class="fas fa-user-md"></i>
            </div>
        </div>
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">Doctor Schedule</h1>
            <p class="mb-0 text-muted small">Manage clinic availability and bookings</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Overview</h6>
                    
                    <select id="doctorFilter" class="form-control form-control-sm border-primary" style="width: 250px; font-weight: bold;">
                        <option value="">-- Select Doctor --</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}">Dr. {{ $doc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body p-0">
                    <div id="calendar" class="p-3"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
                    <h6 class="m-0 font-weight-bold" id="selectedDateLabel">Select a Date</h6>
                    
                    <button class="btn btn-sm btn-light text-primary font-weight-bold shadow-sm" 
                            id="smartBtn" onclick="handleSmartAction()" style="display:none;">
                        Action
                    </button>
                </div>
                
                <div class="card-body p-0" id="day-details-container" style="overflow-y: auto; max-height: 650px; background-color: #f8f9fc;">
                    <div class="text-center mt-5 text-muted p-4">
                        <i class="fas fa-calendar-alt fa-3x mb-3 text-gray-300"></i>
                        <p>Please select a <b>Doctor</b> first, then click a date to view slots.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="initScheduleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title h6">Set Working Hours</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="initScheduleForm">
                        @csrf
                        <input type="hidden" name="doctor_id" id="initDoctorId">
                        <input type="hidden" name="date" id="initDate">
                        
                        <div class="form-group">
                            <label class="small font-weight-bold">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="09:00">
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="17:00">
                        </div>
                        <p class="small text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> 12:00 PM - 1:00 PM will be automatically set as Lunch Break.</p>
                    </form>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-primary btn-block btn-sm" onclick="submitInitSchedule()">Save Availability</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <style>
        .slot-row { border-bottom: 1px solid #eaecf4; padding: 12px 15px; display: flex; align-items: center; justify-content: space-between; background: white; }
        .slot-time { font-weight: bold; font-size: 0.85rem; width: 140px; }
        .slot-status { flex-grow: 1; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.5px; }
        
        .bg-booked { background-color: #fff3f3; border-left: 4px solid #e74a3b; }
        .bg-blocked { background-color: #f8f9fc; border-left: 4px solid #858796; }
        .bg-lunch { background-color: #fff3cd; border-left: 4px solid #f6c23e; }
        .bg-available { background-color: #ffffff; border-left: 4px solid #1cc88a; }

        .fc-dayGridMonth-button { display: none !important; }
        .fc-toolbar-title { font-size: 1.2rem !important; }
    </style>

    <script>
    var calendar;
    var currentSelectedDate = null;
    var currentDoctorId = null;
    var currentDayStatus = 'closed'; 
    var isAdjustMode = false;

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var doctorSelect = document.getElementById('doctorFilter');

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev,next', center: 'title', right: '' },
            height: 550,
            events: function(info, successCallback, failureCallback) {
                var doctorId = doctorSelect.value;
                if(!doctorId) { successCallback([]); return; }
                fetch("{{ route('admin.api.calendar') }}?doctor_id=" + doctorId + "&start=" + info.startStr + "&end=" + info.endStr)
                    .then(r => r.json()).then(data => successCallback(data));
            },
            dateClick: function(info) {
                if(!doctorSelect.value) { alert("Please select a doctor first."); return; }
                
                // Highlight Day
                document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                info.dayEl.style.backgroundColor = '#e8f0fe'; 
                
                currentSelectedDate = info.dateStr;
                currentDoctorId = doctorSelect.value;
                
                // Update Header
                document.getElementById('selectedDateLabel').innerText = new Date(info.dateStr).toDateString();
                
                // Reset View
                isAdjustMode = false;
                fetchDayDetails();
            }
        });

        calendar.render();

        doctorSelect.addEventListener('change', function() {
            calendar.refetchEvents();
            document.getElementById('day-details-container').innerHTML = '';
            document.getElementById('smartBtn').style.display = 'none';
        });
    });

    // 1. FETCH & DECIDE STATE
    function fetchDayDetails() {
        const container = document.getElementById('day-details-container');
        const btn = document.getElementById('smartBtn');
        
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        fetch(`{{ route('admin.api.day_details') }}?date=${currentSelectedDate}&doctor_id=${currentDoctorId}`)
            .then(r => r.json())
            .then(data => {
                currentDayStatus = data.status;

                if (data.status === 'closed') {
                    // SCENARIO A: EMPTY DAY (Blue Button)
                    btn.innerText = "Set Working Hours"; 
                    btn.className = "btn btn-sm btn-primary shadow-sm font-weight-bold";
                    btn.style.display = "block";
                    
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x mb-3 text-gray-300"></i>
                            <h6 class="text-gray-600 font-weight-bold mt-2">No Schedule</h6>
                            <p class="small text-muted">Doctor is not working on this day.</p>
                        </div>`;
                } else {
                    // SCENARIO B: HAS SCHEDULE (Yellow Button)
                    if(isAdjustMode) {
                        btn.innerText = "Done Adjusting";
                        btn.className = "btn btn-sm btn-success font-weight-bold shadow-sm";
                    } else {
                        btn.innerText = "Adjust Availability"; 
                        btn.className = "btn btn-sm btn-warning text-dark font-weight-bold shadow-sm";
                    }
                    btn.style.display = "block";
                    renderSlots(data.slots);
                }
            });
    }

    // 2. RENDER SLOTS
    function renderSlots(slots) {
        const container = document.getElementById('day-details-container');
        let html = '';

        slots.forEach(slot => {
            if (slot.type === 'lunch') {
                html += `<div class="slot-row bg-lunch"><div class="slot-time text-dark">${slot.time_label}</div><div class="slot-status text-dark">LUNCH BREAK</div></div>`;
                return;
            }

            if (isAdjustMode) {
                // ADJUST MODE UI
                if (slot.type === 'booked') {
                        html += `<div class="slot-row bg-light opacity-50"><div class="slot-time">${slot.time_label}</div><div class="slot-status text-dark">BOOKED</div><span class="badge badge-secondary">Locked</span></div>`;
                } else {
                    let isBlocked = (slot.type === 'blocked');
                    html += `
                        <div class="slot-row">
                            <div class="slot-time">${slot.time_label}</div>
                            <div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-success ${!isBlocked ? 'active' : ''}" onclick="updateBlockStatus('${slot.raw_time}', 'available')"><input type="radio"> Open</label>
                                <label class="btn btn-outline-secondary ${isBlocked ? 'active' : ''}" onclick="updateBlockStatus('${slot.raw_time}', 'reserved')"><input type="radio"> Block</label>
                            </div>
                        </div>`;
                }
            } else {
                // NORMAL VIEW UI
                if (slot.type === 'booked') {
                    html += `<div class="slot-row bg-booked"><div class="slot-time">${slot.time_label}</div><div class="slot-status text-danger">${slot.details}</div><a href="/admin/appointments/${slot.appt_id}" class="btn btn-sm btn-circle btn-light text-danger border"><i class="fas fa-eye"></i></a></div>`;
                } else if (slot.type === 'blocked') {
                    html += `<div class="slot-row bg-blocked"><div class="slot-time text-muted">${slot.time_label}</div><div class="slot-status text-muted">UNAVAILABLE</div></div>`;
                } else {
                    html += `<div class="slot-row bg-available"><div class="slot-time text-success">${slot.time_label}</div><div class="slot-status text-success">AVAILABLE</div><a href="{{ route('admin.appointments.create') }}?date=${slot.raw_date}&time=${slot.raw_time}&doctor_id=${currentDoctorId}" class="btn btn-sm btn-success shadow-sm" style="font-size: 0.75rem;">Set Appt</a></div>`;
                }
            }
        });
        container.innerHTML = html;
    }

    // 3. SMART BUTTON ACTION
    function handleSmartAction() {
        if (currentDayStatus === 'closed') {
            // Open Modal
            document.getElementById('initDoctorId').value = currentDoctorId;
            document.getElementById('initDate').value = currentSelectedDate;
            $('#initScheduleModal').modal('show');
        } else {
            // Toggle Edit Mode
            isAdjustMode = !isAdjustMode;
            fetchDayDetails();
        }
    }

    // 4. SUBMIT SCHEDULE (Fixed JSON Handling)
    function submitInitSchedule() {
        const formData = new FormData(document.getElementById('initScheduleForm'));
        
        fetch("{{ route('admin.schedules.store') }}", {
            method: "POST",
            headers: { 
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json" // Force JSON response
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            
            if (!response.ok) {
                // Handle Errors
                if (response.status === 422) {
                    let msg = "Validation Error:\n";
                    for(let key in data.errors) msg += data.errors[key][0] + "\n";
                    alert(msg);
                } else {
                    alert(data.message || "Error saving schedule");
                }
                return;
            }

            // Success
            if(data.success) {
                $('#initScheduleModal').modal('hide');
                calendar.refetchEvents(); 
                currentDayStatus = 'open'; 
                fetchDayDetails(); // Refresh list immediately
            }
        })
        .catch(err => console.error(err));
    }

    function updateBlockStatus(time, status) {
        fetch("{{ route('admin.appointments.block') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ doctor_id: currentDoctorId, date: currentSelectedDate, time: time, status: status })
        });
    }
</script>
@endpush