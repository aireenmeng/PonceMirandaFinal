@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Patients</h1>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4"> {{-- Constrain width to 50% and add bottom margin --}}
            <form action="{{ route('doctor.consultations') }}" method="GET">
                <div class="input-group border rounded"> {{-- Add border and rounded corners --}}
                    <input type="search" name="search" class="form-control bg-light small border-0" placeholder="Search patient name..."
                        aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary rounded-right" type="submit"> {{-- Apply rounded-right to match input-group --}}
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Patient List @if(request('search')) (Filtered by "{{ request('search') }}") @endif</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Patient Name</th>
                            <th>Contact Info</th>
                            <th>Last Consultation (Your Record)</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                        <tr onclick="window.location='{{ route('doctor.patient.consultations', $patient->id) }}'" style="cursor:pointer;">
                            {{-- Patient Info --}}
                            <td class="align-middle">
                                <div class="font-weight-bold">{{ $patient->name }}</div>
                                @if($patient->email === null)
                                    <small class="badge badge-info text-white">Walk-in</small>
                                @elseif($patient->email_verified_at === null)
                                    <small class="badge badge-warning text-dark">Unverified</small>
                                @else
                                    <small class="badge badge-success">Active</small>
                                @endif
                                <div class="small text-muted">{{ $patient->email }}</div>
                            </td>

                            {{-- Contact Info --}}
                            <td class="align-middle">
                                @if($patient->phone)
                                    <div class="font-weight-bold"><a href="tel:{{ $patient->phone }}" class="text-dark text-decoration-none">{{ $patient->phone }}</a></div>
                                @else
                                    <div class="font-weight-bold text-muted">N/A</div>
                                @endif
                            </td>

                            {{-- Last Consultation Date (for current doctor only) --}}
                            <td class="align-middle">
                                @php
                                    $lastAppt = $patient->appointments()->where('doctor_id', Auth::id())->where('status', 'completed')->orderByDesc('appointment_date')->first();
                                @endphp
                                @if($lastAppt)
                                    <div class="font-weight-bold">{{ $lastAppt->appointment_date->format('M d, Y') }}</div>
                                    <div class="small text-muted">{{ $lastAppt->appointment_time->format('h:i A') }}</div>
                                @else
                                    <span class="text-muted">No records with you</span>
                                @endif
                            </td>

                            {{-- Action Button --}}
                            <td class="align-middle text-right">
                                <a href="{{ route('doctor.patient.consultations', $patient->id) }}" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-eye mr-1"></i> View All Records
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-gray-300"></i><br>
                                No patients with completed consultations found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $patients->appends(['search' => request('search')])->links() }}
        </div>
    </div>
</div>
@endsection