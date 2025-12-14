@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between mb-4">
    <h1 class="h3 text-gray-800">Patient Profile: {{ $patient->name }}</h1>
    <a href="{{ route('admin.patients.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <i class="fas fa-user-circle fa-5x text-gray-300 mb-3"></i>
                <h4>{{ $patient->name }}</h4>
                <p class="text-muted">{{ $patient->email }}</p>
                <hr>
                <div class="text-left">
                    <p><strong>Phone:</strong> 
                        @if($patient->phone)
                            <a href="tel:{{ $patient->phone }}" class="text-dark text-decoration-none">{{ $patient->phone }}</a>
                        @else
                            N/A
                        @endif
                    </p>
                    <p><strong>Address:</strong> {{ $patient->address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white"><h6 class="m-0 font-weight-bold">History</h6></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Date</th><th>Doctor</th><th>Service</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($patient->appointments as $appt)
                        <tr>
                            <td>{{ $appt->appointment_date->format('M d, Y') }}</td>
                            <td>Dr. {{ $appt->doctor->name }}</td>
                            <td>{{ $appt->service->name }}</td>
                            <td><span class="badge badge-secondary">{{ $appt->status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection