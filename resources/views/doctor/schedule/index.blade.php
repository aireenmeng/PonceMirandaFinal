@extends('layouts.admin')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Schedule Manager</h1>
    </div>

    <div class="row">
        {{-- CALENDAR CARD --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Availability</h6>
                </div>
                <div class="card-body p-0">
                    <div id="calendar" class="p-3"></div>
                </div>
            </div>

            {{-- SCHEDULED PATIENTS LIST (Moved Here) --}}
            <div class="card shadow mb-4" id="patientListCard" style="display:none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success" id="patientListTitle">Scheduled Patients</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Patient Name</th>
                                    <th>Service</th>
                                </tr>
                            </thead>
                            <tbody id="dayAppointmentsList">
                                {{-- JS will populate this --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTIONS CARD --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Day Actions</h6>
                </div>
                <div class="card-body">
                    <h5 id="selectedDateLabel" class="font-weight-bold text-center mb-4">Select a Date</h5>
                    
                    <div id="actionContainer" style="display:none;">
                        
                        <form id="scheduleForm">
                            @csrf
                            <input type="hidden" name="date" id="inputDate">
                            
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">START TIME</label>
                                <input type="time" name="start_time" id="inputStart" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">END TIME</label>
                                <input type="time" name="end_time" id="inputEnd" class="form-control">
                            </div>

                            <div class="custom-control custom-switch mb-4">
                                <input type="checkbox" class="custom-control-input" id="dayOffSwitch" name="is_day_off">
                                <label class="custom-control-label text-danger font-weight-bold" for="dayOffSwitch">Mark as Day Off</label>
                                <small class="form-text text-muted">This will prevent new bookings.</small>
                            </div>

                            <button type="button" id="saveBtn" onclick="saveSchedule()" class="btn btn-primary btn-block rounded-pill">
                                Update Availability
                            </button>

                            {{-- Re-Open Button (Hidden by default) --}}
                            <button type="button" id="reOpenBtn" onclick="reOpenDay()" class="btn btn-primary btn-block rounded-pill" style="display:none;">
                                <i class="fas fa-door-open mr-2"></i> Remove Day Off
                            </button>
                        </form>
                    </div>
                    
                    <div id="placeholder" class="text-center text-muted">
                        <i class="fas fa-calendar-alt fa-3x mb-3 text-gray-300"></i>
                        <p>Click a date on the calendar to modify your hours.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    var calendar;
    var doctorId = "{{ Auth::id() }}"; 

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        // Toggle Inputs based on Switch
        const dayOffSwitch = document.getElementById('dayOffSwitch');
        dayOffSwitch.addEventListener('change', function() {
            const isOff = this.checked;
            document.getElementById('inputStart').disabled = isOff;
            document.getElementById('inputEnd').disabled = isOff;
            
            const btn = document.getElementById('saveBtn');
            if(isOff) {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-secondary');
                btn.innerText = "Confirm Day Off";
            } else {
                btn.classList.add('btn-primary');
                btn.classList.remove('btn-secondary');
                btn.innerText = "Update Availability";
            }
        });

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 500,
            selectable: true,
            events: `{{ route('api.calendar') }}?doctor_id=${doctorId}`,
            
            dateClick: function(info) {
                document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                info.dayEl.style.backgroundColor = '#eaecf4';

                document.getElementById('selectedDateLabel').innerText = new Date(info.dateStr).toDateString();
                document.getElementById('inputDate').value = info.dateStr;
                
                document.getElementById('placeholder').style.display = 'none';
                document.getElementById('actionContainer').style.display = 'block';

                document.getElementById('inputStart').value = "09:00";
                document.getElementById('inputEnd').value = "17:00";
                
                // CHECK IF DAY IS ALREADY OFF
                const events = calendar.getEvents();
                const dayEvent = events.find(e => e.startStr === info.dateStr);
                const isDayOff = dayEvent && dayEvent.title === "DAY OFF";

                const dayOffSwitch = document.getElementById('dayOffSwitch');
                dayOffSwitch.checked = isDayOff;
                dayOffSwitch.dispatchEvent(new Event('change')); // Trigger UI update

                // Toggle Buttons
                if (isDayOff) {
                    document.getElementById('saveBtn').style.display = 'none';
                    document.getElementById('reOpenBtn').style.display = 'block';
                } else {
                    document.getElementById('saveBtn').style.display = 'block';
                    document.getElementById('reOpenBtn').style.display = 'none';
                }

                // FETCH & DISPLAY PATIENT LIST
                document.getElementById('patientListCard').style.display = 'block';
                document.getElementById('patientListTitle').innerText = "Scheduled Patients for " + new Date(info.dateStr).toDateString();
                
                const listEl = document.getElementById('dayAppointmentsList');
                listEl.innerHTML = '<tr><td colspan="3" class="text-center">Loading...</td></tr>';

                fetch(`{{ route('api.day_details') }}?date=${info.dateStr}&doctor_id=${doctorId}`)
                    .then(res => res.json())
                    .then(data => {
                        listEl.innerHTML = '';
                        
                        if(data.appointments && data.appointments.length > 0) {
                            data.appointments.forEach(appt => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td class="font-weight-bold">${appt.time}</td>
                                    <td class="text-primary font-weight-bold">${appt.patient_name}</td>
                                    <td>${appt.service}</td>
                                `;
                                listEl.appendChild(tr);
                            });
                        } else {
                            listEl.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No patients scheduled.</td></tr>';
                        }
                    });
            }
        });
        calendar.render();
    });

    function reOpenDay() {
        if(!confirm("Are you sure you want to re-open this day for bookings?")) return;

        // Reset Switch
        const dayOffSwitch = document.getElementById('dayOffSwitch');
        dayOffSwitch.checked = false;
        
        // Trigger Save (which will read is_day_off = 0 and start_time = 09:00)
        // We ensure times are set
        document.getElementById('inputStart').value = "09:00";
        document.getElementById('inputEnd').value = "17:00";
        
        saveSchedule();
    }

    function saveSchedule() {
        const isOff = document.getElementById('dayOffSwitch').checked;
        if (isOff) {
            if (!confirm("Are you sure you want to mark this day as OFF? This will stop patients from booking.")) {
                return; 
            }
        }

        const formData = new FormData(document.getElementById('scheduleForm'));
        formData.set('is_day_off', isOff ? 1 : 0);

        fetch("{{ route('doctor.schedule.updateDate') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok) {
                alert("Schedule updated!");
                calendar.refetchEvents();
            } else {
                alert(data.error || "Error updating schedule.");
            }
        })
        .catch(err => alert("Network error."));
    }
</script>
@endpush