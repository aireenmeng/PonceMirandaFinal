@extends('layouts.admin')

@section('content')

    <div class="row mb-4 align-items-center">
        <div class="col-auto">
             <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center shadow-lg" style="width: 50px; height: 50px; font-size: 1.5rem;">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">Doctor Schedule</h1>
            <p class="mb-0 text-muted small">Manage clinic availability, block slots, and view bookings.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-lg mb-4 border-0">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-bottom-0">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Overview</h6>
                    <div class="d-flex align-items-center">
                        <select id="doctorFilter" class="custom-select custom-select-sm shadow-sm border-0 bg-light text-dark font-weight-bold mr-2" style="width: 250px;">
                            <option value="">-- Select Doctor --</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">Dr. {{ $doc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="calendar" class="p-4"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-lg mb-4 h-100 border-0">
                <div class="card-header py-4 d-flex justify-content-between align-items-center bg-primary text-white border-0" style="border-radius: 0.35rem 0.35rem 0 0;">
                    <div>
                        <span class="small text-white-50 text-uppercase font-weight-bold">Selected Date</span>
                        <h5 class="m-0 font-weight-bold" id="selectedDateLabel">Select a Date</h5>
                    </div>
                    
                    <button class="btn btn-sm btn-light text-primary font-weight-bold shadow-sm" 
                            id="smartBtn" onclick="handleSmartAction()" style="display:none;">
                        Action
                    </button>
                </div>
                
                <div class="card-body p-3 bg-light" id="day-details-container" style="overflow-y: auto; max-height: 650px;">
                    <div class="text-center mt-5 text-muted p-4">
                        <i class="fas fa-hand-pointer fa-3x mb-3 text-gray-300"></i>
                        <p>Select a <b>Doctor</b>, then click a date to manage slots.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="initScheduleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title h6 font-weight-bold">Set Working Hours</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="initScheduleForm">
                        @csrf
                        <input type="hidden" name="doctor_id" id="initDoctorId">
                        <input type="hidden" name="date" id="initDate">
                        
                        <div class="form-group">
                            <label class="small font-weight-bold text-uppercase text-muted">Start Time</label>
                            <input type="time" name="start_time" class="form-control bg-light border-0" value="09:00">
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold text-uppercase text-muted">End Time</label>
                            <input type="time" name="end_time" class="form-control bg-light border-0" value="17:00">
                        </div>
                        <div class="alert alert-info small mb-0 border-0 bg-info text-white">
                            <i class="fas fa-info-circle mr-1"></i> Lunch (12-1 PM) is auto-added.
                        </div>
                    </form>
                </div>
                <div class="modal-footer p-2 border-0">
                    <button type="button" class="btn btn-primary btn-block shadow-sm" onclick="submitInitSchedule()">
                        Initialize Day
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <style>
        /* Modern Slot Styling */
        .slot-row { 
            padding: 12px 15px; 
            margin-bottom: 8px;
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
            border: 1px solid #f0f0f0;
            transition: transform 0.15s ease-in-out;
        }
        .slot-row:hover { transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

        .slot-time { font-weight: 800; font-size: 0.85rem; width: 130px; color: #5a5c69; }
        .slot-status { flex-grow: 1; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Status Colors - Cleaner Look */
        .status-booked { border-left: 5px solid #e74a3b; background: #fffdfd; }
        .status-booked .slot-status { color: #e74a3b; }
        
        .status-blocked { border-left: 5px solid #858796; background: #f8f9fc; opacity: 0.8; }
        .status-blocked .slot-status { color: #858796; }

        .status-lunch { border-left: 5px solid #f6c23e; background: #fffae6; }
        .status-lunch .slot-status { color: #d4a017; }

        .status-open { border-left: 5px solid #1cc88a; }
        .status-open .slot-status { color: #1cc88a; }

        /* Calendar Tweaks */
        .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 700; color: #4e73df; }
        .fc-button { background-color: #4e73df !important; border: none !important; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .fc-daygrid-day.fc-day-today { background-color: #f0f4ff !important; }
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
            height: 500,
            validRange: { start: new Date().toISOString().split('T')[0] },
            events: function(info, successCallback, failureCallback) {
                var doctorId = doctorSelect.value;
                if(!doctorId) { successCallback([]); return; }
                fetch("{{ route('api.calendar') }}?doctor_id=" + doctorId + "&start=" + info.startStr + "&end=" + info.endStr)
                    .then(r => r.json()).then(data => successCallback(data));
            },
            dateClick: function(info) {
                if(!doctorSelect.value) { alert("Please select a doctor first."); return; }
                
                document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                info.dayEl.style.backgroundColor = '#d1e3ff'; 
                
                currentSelectedDate = info.dateStr;
                currentDoctorId = doctorSelect.value;
                
                document.getElementById('selectedDateLabel').innerText = new Date(info.dateStr).toDateString();
                isAdjustMode = false;
                fetchDayDetails();
            }
        });

        calendar.render();

        // New: External Next Month Button Listener
        document.getElementById('nextMonthBtn').addEventListener('click', function() {
            calendar.next();
        });

        doctorSelect.addEventListener('change', function() {
            calendar.refetchEvents();
            document.getElementById('day-details-container').innerHTML = `
                <div class="text-center mt-5 text-muted p-4">
                    <i class="fas fa-calendar-check fa-3x mb-3 text-gray-200"></i>
                    <p>Select a date to view slots.</p>
                </div>`;
            document.getElementById('smartBtn').style.display = 'none';
        });
    });

    function fetchDayDetails() {
        const container = document.getElementById('day-details-container');
        const btn = document.getElementById('smartBtn');
        
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        fetch(`{{ route('api.day_details') }}?date=${currentSelectedDate}&doctor_id=${currentDoctorId}`)
            .then(r => r.json())
            .then(data => {
                currentDayStatus = data.status;

                if (data.status === 'closed') {
                    btn.innerText = "Initialize Schedule"; 
                    btn.className = "btn btn-sm btn-light text-primary font-weight-bold shadow-sm";
                    btn.style.display = "block";
                    
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-bed fa-3x mb-3 text-gray-300"></i>
                            <h6 class="text-gray-600 font-weight-bold">Day Off</h6>
                            <p class="small text-muted">No schedule found for this date.</p>
                        </div>`;
                } else {
                    if(isAdjustMode) {
                        btn.innerText = "Done Adjusting";
                        btn.className = "btn btn-sm btn-success shadow-sm";
                    } else {
                        btn.innerText = "Adjust Availability"; 
                        btn.className = "btn btn-sm btn-light text-warning font-weight-bold shadow-sm";
                    }
                    btn.style.display = "block";
                    renderSlots(data.slots);
                }
            });
    }

    function renderSlots(slots) {
        const container = document.getElementById('day-details-container');
        let html = '';

        slots.forEach(slot => {
            // 1. LUNCH
            if (slot.type === 'lunch') {
                html += `
                <div class="slot-row status-lunch">
                    <div class="slot-time"><i class="fas fa-utensils mr-2 opacity-50"></i> ${slot.time_label}</div>
                    <div class="slot-status">Lunch Break</div>
                </div>`;
                return;
            }

            if (isAdjustMode) {
                // 2. ADJUST MODE
                if (slot.type === 'booked') {
                        html += `<div class="slot-row bg-light"><div class="slot-time text-muted">${slot.time_label}</div><div class="slot-status text-muted">BOOKED</div><span class="badge badge-secondary"><i class="fas fa-lock"></i></span></div>`;
                } else {
                    let isBlocked = (slot.type === 'blocked');
                    html += `
                        <div class="slot-row ${isBlocked ? 'status-blocked' : 'status-open'}">
                            <div class="slot-time">${slot.time_label}</div>
                            <div class="btn-group btn-group-sm btn-group-toggle shadow-sm" data-toggle="buttons" style="border-radius: 20px; overflow: hidden;">
                                <label class="btn btn-white border ${!isBlocked ? 'active text-success font-weight-bold' : 'text-muted'}" onclick="updateBlockStatus('${slot.raw_time}', 'available')"><input type="radio"> Open</label>
                                <label class="btn btn-white border ${isBlocked ? 'active text-danger font-weight-bold' : 'text-muted'}" onclick="updateBlockStatus('${slot.raw_time}', 'reserved')"><input type="radio"> Block</label>
                            </div>
                        </div>`;
                }
            } else {
                // 3. VIEW MODE (Standard)
                if (slot.type === 'booked') {
                    // LINK FIX: Now uses slot.appt_id correctly
                    html += `
                    <div class="slot-row status-booked">
                        <div class="slot-time">${slot.time_label}</div>
                        <div class="slot-status">Booked</div>
                        <a href="/admin/appointments/${slot.appt_id}" class="btn btn-sm btn-circle btn-danger shadow-sm"><i class="fas fa-eye"></i></a>
                    </div>`;
                } else if (slot.type === 'blocked') {
                    html += `
                    <div class="slot-row status-blocked">
                        <div class="slot-time">${slot.time_label}</div>
                        <div class="slot-status">Blocked</div>
                    </div>`;
                } else {
                    // LINK FIX: Now uses slot.raw_date correctly
                    // DESIGN FIX: Text removed, only Icon kept
                    html += `
                    <div class="slot-row status-open">
                        <div class="slot-time">${slot.time_label}</div>
                        <div class="slot-status">Available</div>
                        <a href="{{ route('admin.appointments.create') }}?date=${slot.raw_date}&time=${slot.raw_time}&doctor_id=${currentDoctorId}" 
                           class="btn btn-sm btn-success shadow-sm btn-circle">
                           <i class="fas fa-plus"></i>
                        </a>
                    </div>`;
                }
            }
        });
        container.innerHTML = html;
    }

    function handleSmartAction() {
        if (currentDayStatus === 'closed') {
            document.getElementById('initDoctorId').value = currentDoctorId;
            document.getElementById('initDate').value = currentSelectedDate;
            $('#initScheduleModal').modal('show');
        } else {
            isAdjustMode = !isAdjustMode;
            fetchDayDetails();
        }
    }

    function submitInitSchedule() {
        const formData = new FormData(document.getElementById('initScheduleForm'));
        fetch("{{ route('admin.schedules.store') }}", {
            method: "POST",
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if(data.success) {
                $('#initScheduleModal').modal('hide');
                calendar.refetchEvents(); 
                currentDayStatus = 'open'; 
                fetchDayDetails();
            }
        });
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