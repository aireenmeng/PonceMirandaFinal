@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Consultations for {{ $patient->name }}</h1>
        <a href="{{ route('doctor.consultations') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Patient List
        </a>
    </div>

    {{-- Patient Information Card --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-gradient-primary text-white">
            <h6 class="m-0 font-weight-bold">Patient Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Name:</strong> {{ $patient->name }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $patient->email }}</p>
                    <p class="mb-1"><strong>Phone:</strong> 
                        @if($patient->phone)
                            <a href="tel:{{ $patient->phone }}" class="text-dark text-decoration-none">{{ $patient->phone }}</a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="col-md-6 text-right">
                    {{-- Optionally add more patient details or actions here --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Search Form for Appointments --}}
    <div class="row">
        <div class="col-md-6 mb-4">
            <form action="{{ route('doctor.patient.consultations', $patient->id) }}" method="GET">
                <div class="input-group border rounded">
                    <input type="search" name="search" class="form-control bg-light small border-0" placeholder="Search by service name..."
                        aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary rounded-right" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Consultation History Table --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Completed Appointments @if(request('search')) (Filtered by "{{ request('search') }}") @endif</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Service</th>
                            <th>Consulting Doctor</th>
                            <th>Diagnosis</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appt)
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ $appt->appointment_time->format('h:i A') }}</div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-light border">{{ $appt->service->name }}</span>
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold">{{ $appt->doctor->name ?? 'N/A' }}</div>
                            </td>
                            <td class="align-middle">
                                <span class="d-inline-block text-truncate" style="max-width: 250px;">{{ Str::limit($appt->diagnosis ?? 'No Diagnosis Recorded', 70) }}</span>
                            </td>
                            <td class="align-middle text-right">
                                {{-- Button to view/edit full diagnosis --}}
                                <button class="btn btn-sm btn-info shadow-sm" data-toggle="modal" data-target="#diagnosisModal-{{ $appt->id }}">
                                    <i class="fas fa-notes-medical mr-1"></i> View Details
                                </button>

                                {{-- DIAGNOSIS MODAL --}}
                                <div class="modal fade text-left" id="diagnosisModal-{{ $appt->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title font-weight-bold">Medical Notes: {{ $patient->name }} ({{ $appt->appointment_date->format('M d, Y') }})</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('doctor.appointments.updateDiagnosis', $appt->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <p class="mb-1"><strong>Service:</strong> {{ $appt->service->name }}</p>
                                                            <p class="mb-1"><strong>Doctor:</strong> {{ $appt->doctor->name ?? 'N/A' }}</p>
                                                            <p class="mb-0"><strong>Date & Time:</strong> {{ $appt->appointment_date->format('F d, Y') }} @ {{ $appt->appointment_time->format('h:i A') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-bold text-dark">Clinical Diagnosis</label>
                                                        <textarea name="diagnosis" class="form-control" rows="5" @if(Auth::id() != $appt->doctor_id) readonly @endif>{{ $appt->diagnosis }}</textarea>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold text-dark">Prescription / Advice</label>
                                                        <textarea name="prescription" class="form-control" rows="4" @if(Auth::id() != $appt->doctor_id) readonly @endif>{{ $appt->prescription }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Close</button>
                                                    @if(Auth::id() == $appt->doctor_id) {{-- Only allow editing by the original doctor --}}
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                                                            <i class="fas fa-save mr-1"></i> Save Record
                                                        </button>
                                                    @endif
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-gray-300"></i><br>
                                No completed consultations found for this patient.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $appointments->appends(['search' => request('search')])->links() }}
        </div>
    </div>
</div>
@endsection