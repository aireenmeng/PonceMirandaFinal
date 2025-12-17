@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Appointments</h1>
        <a href="{{ route('patient.booking.step1') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Book New
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Bookings</h6>
            
            <form action="{{ route('patient.history') }}" method="GET" class="form-inline">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control bg-light border-0 small" 
                           placeholder="Search treatment or doctor..." aria-label="Search" 
                           value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" onclick="this.form.submit()">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
                @if(request('search'))
                    <a href="{{ route('patient.history') }}" class="btn btn-sm btn-secondary ml-2">Reset</a>
                @endif
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light text-gray-700">
                        <tr>
                            <th class="border-top-0">Status</th>
                            <th class="border-top-0">Date & Time</th>
                            <th class="border-top-0">Treatment</th>
                            <th class="border-top-0">Doctor</th>
                            <th class="border-top-0">Diagnosis / Advice</th> {{-- NEW COLUMN --}}
                            <th class="border-top-0 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appt)
                        <tr>
                            {{-- STATUS COLUMN --}}
                            <td>
                                @if($appt->status == 'pending')
                                    <span class="badge badge-warning text-dark px-3 py-2"><i class="fas fa-clock mr-1"></i> Pending</span>
                                @elseif($appt->status == 'confirmed')
                                    <span class="badge badge-primary px-3 py-2"><i class="fas fa-calendar-check mr-1"></i> Confirmed</span>
                                @elseif($appt->status == 'completed')
                                    <span class="badge badge-success px-3 py-2"><i class="fas fa-check-circle mr-1"></i> Done</span>
                                @elseif($appt->status == 'cancelled')
                                    <span class="badge badge-secondary px-3 py-2"><i class="fas fa-ban mr-1"></i> Cancelled</span>
                                @endif
                            </td>

                            {{-- DATE COLUMN --}}
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ $appt->appointment_time->format('h:i A') }}</div>
                            </td>

                            {{-- TREATMENT COLUMN --}}
                            <td class="align-middle font-weight-bold text-gray-600">
                                {{ $appt->service->name }}
                            </td>

                            {{-- DOCTOR COLUMN --}}
                            <td class="align-middle">
                                <i class="fas fa-user-md text-gray-400 mr-1"></i> Dr. {{ $appt->doctor->name }}
                            </td>

                            {{-- DIAGNOSIS / ADVICE COLUMN (NEW) --}}
                            <td class="align-middle">
                                @if($appt->status == 'completed' && ($appt->diagnosis || $appt->prescription))
                                    <button class="btn btn-info btn-sm rounded-pill px-3" data-toggle="modal" data-target="#diagnosisModal-{{ $appt->id }}">
                                        View Details
                                    </button>
                                    {{-- Diagnosis Modal --}}
                                    <div class="modal fade" id="diagnosisModal-{{ $appt->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info text-white">
                                                    <h5 class="modal-title">Diagnosis & Advice for {{ $appt->service->name }}</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Date:</strong> {{ $appt->appointment_date->format('M d, Y') }}</p>
                                                    <p><strong>Doctor:</strong> Dr. {{ $appt->doctor->name }}</p>
                                                    <hr>
                                                    <h6>Diagnosis:</h6>
                                                    <p>{{ $appt->diagnosis ?? 'No diagnosis recorded.' }}</p>
                                                    <h6>Prescription/Advice:</h6>
                                                    <p>{{ $appt->prescription ?? 'No prescription/advice recorded.' }}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            {{-- ACTIONS COLUMN --}}
                            <td class="align-middle text-right">
                                {{-- CANCEL BUTTON (Only for Pending or Confirmed) --}}
                                @if(in_array($appt->status, ['pending', 'confirmed']))
                                    <form action="{{ route('patient.cancel', $appt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');" style="display:inline;">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                            Cancel
                                        </button>
                                    </form>
                                @elseif($appt->status == 'completed')
                                    <span class="text-success small font-weight-bold"><i class="fas fa-check"></i> Complete</span>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5"> {{-- Changed colspan to 6 --}}
                                <img src="https://img.icons8.com/clouds/100/000000/todo-list.png" class="mb-3 opacity-50">
                                <p class="text-muted mb-0">You haven't booked any appointments yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $appointments->links() }}
        </div>
    </div>
</div>
@endsection