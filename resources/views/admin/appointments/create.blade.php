@extends('layouts.admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-calendar-plus mr-2"></i>Book Appointment</h6>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.appointments.store') }}" method="POST">
                        @csrf
                        {{-- Hidden Fields passed from Calendar --}}
                        <input type="hidden" name="doctor_id" value="{{ request('doctor_id') }}">
                        <input type="hidden" name="appointment_date" value="{{ request('date') }}">
                        <input type="hidden" name="appointment_time" value="{{ request('time') }}">

                        <div class="alert alert-light border-left-primary mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase font-weight-bold">Doctor</small><br>
                                    <span class="h5 text-dark font-weight-bold">Dr. {{ $doctors->find(request('doctor_id'))->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <small class="text-muted text-uppercase font-weight-bold">Date</small><br>
                                    <span class="h5 text-primary font-weight-bold">
                                        {{ \Carbon\Carbon::parse(request('date'))->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Select Patient</label>
                            <select name="user_id" class="form-control form-control-lg">
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->phone ?? 'No Phone' }})</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted"><a href="#">+ Register new patient</a></small>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Service</label>
                            <select name="service_id" class="form-control">
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} - ₱{{ number_format($s->price) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr>

                        <div class="row align-items-end mb-4">
                            <div class="col-md-5">
                                <label class="font-weight-bold">From (Start)</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ \Carbon\Carbon::parse(request('time'))->format('h:i A') }}" readonly>
                            </div>
                            
                            <div class="col-md-2 text-center py-2">
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>

                            <div class="col-md-5">
                                <label class="font-weight-bold text-success">To (End Time)</label>
                                <select name="duration_minutes" class="form-control font-weight-bold text-success">
                                    @php
                                        // Calculate 30 min increments
                                        $start = \Carbon\Carbon::parse(request('time'));
                                    @endphp
                                    
                                    {{-- Loop to generate options: 30m, 1h, 1h30m, 2h --}}
                                    @for($i = 30; $i <= 240; $i += 30) 
                                        @php 
                                            $end = $start->copy()->addMinutes($i);
                                            $label = $end->format('h:i A');
                                            
                                            // Optional: You could hide options here if they overlap lunch/closing
                                            // For now, validation handles conflicts.
                                        @endphp
                                        <option value="{{ $i }}" {{ $i == 60 ? 'selected' : '' }}>
                                            {{ $label }} ({{ $i / 60 }} hrs)
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm">
                            <i class="fas fa-check-circle mr-2"></i> Confirm Appointment
                        </button>
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-link btn-block text-muted">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection