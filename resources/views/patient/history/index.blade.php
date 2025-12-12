@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-4 text-gray-800">My Appointments</h1>
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr><th>Date</th><th>Doctor</th><th>Treatment</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appt)
                    <tr>
                        <td class="font-weight-bold">{{ $appt->appointment_date->format('M d, Y') }} <br> <small>{{ $appt->appointment_time->format('h:i A') }}</small></td>
                        <td>Dr. {{ $appt->doctor->name }}</td>
                        <td>{{ $appt->service->name }}</td>
                        <td>
                            @if($appt->status == 'confirmed') <span class="badge badge-primary">Confirmed</span>
                            @elseif($appt->status == 'completed') <span class="badge badge-success">Completed</span>
                            @elseif($appt->status == 'cancelled') <span class="badge badge-danger">Cancelled</span>
                            @else <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($appt->status == 'pending')
                                <form action="{{ route('patient.cancel', $appt->id) }}" method="POST" onsubmit="return confirm('Cancel request?');">
                                    @csrf
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Cancel</button>
                                </form>
                            @elseif($appt->status == 'completed')
                                {{-- Placeholder for Medical Advice --}}
                                <button class="btn btn-info btn-sm" disabled><i class="fas fa-notes-medical"></i> View Diagnosis</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection