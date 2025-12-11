@extends('layouts.admin')

@section('content')

    <h1 class="h3 mb-4 text-gray-800">Set Clinic Availability</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">New Schedule Block</h6>
        </div>
        <div class="card-body">
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.schedules.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="font-weight-bold">Select Doctor</label>
                    <select name="doctor_id" class="form-control" required>
                        <option value="">-- Choose Doctor --</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" 
                                {{ (isset($prefilledDoctorId) && $prefilledDoctorId == $doc->id) ? 'selected' : '' }}>
                                Dr. {{ $doc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Date</label>
                    <input type="date" name="date" class="form-control" required value="{{ $prefilledDate ?? '' }}">
                    <small class="form-text text-muted">Select the day the doctor is available.</small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="09:00" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">End Time</label>
                        <input type="time" name="end_time" class="form-control" value="17:00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Max Patients (Capacity)</label>
                    <input type="number" name="max_appointments" class="form-control" value="10" min="1" required>
                    <small class="form-text text-muted">How many appointments can be accepted this day?</small>
                </div>

                <button type="submit" class="btn btn-primary">Save Schedule</button>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

@endsection