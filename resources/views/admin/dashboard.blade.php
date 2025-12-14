@extends('layouts.admin')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Clinic Dashboard</h1>
        
                    <div class="d-flex align-items-center">
                        <span class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm rounded-pill px-3 mr-3">
                            <i class="fas fa-calendar fa-sm text-white-50 mr-1"></i> {{ now()->format('F d, Y') }}
                        </span>
                        <a href="{{ route('admin.reports.index') }}" class="d-none d-sm-inline-block btn btn-outline-primary btn-sm shadow-sm rounded-pill px-3">
                            <i class="fas fa-download fa-sm text-primary mr-1"></i> Generate Report
                        </a>
                    </div>    </div>

    {{-- SECTION: OVERVIEW KPIs --}}
    <h2 class="h5 mb-3 text-gray-800 font-weight-bold border-bottom pb-2">Overview</h2>

    {{-- 1. "UP NEXT" HERO CARD for Admin --}}
    <div class="card shadow mb-4 border-left-primary bg-gradient-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="text-primary font-weight-bold text-uppercase mb-1">Clinic's Next Appointment</h5>
                    @if($nextPatient)
                        <h2 class="font-weight-bold text-gray-900 mb-1">
                            {{ $nextPatient->patient->name ?? 'Unknown Patient' }}
                            <small class="text-muted ml-2">with Dr. {{ $nextPatient->doctor->name ?? 'Unknown Doctor' }}</small>
                        </h2>
                        <p class="mb-0 text-muted">
                            <i class="fas fa-notes-medical mr-1"></i> {{ $nextPatient->service->name }} 
                            <span class="mx-2">|</span> 
                            <i class="far fa-clock mr-1"></i> {{ $nextPatient->appointment_date->format('M d, Y') }} @ {{ $nextPatient->appointment_time->format('h:i A') }}
                        </p>
                    @else
                        <h2 class="font-weight-bold text-gray-700 mb-1">No Upcoming Appointments</h2>
                        <p class="mb-0 text-muted">There are no confirmed appointments scheduled in the near future.</p>
                    @endif
                </div>
                <div class="col-md-4 text-right">
                    @if($nextPatient)
                        <a href="{{ route('admin.appointments.show', $nextPatient->id) }}" class="btn btn-primary btn-lg shadow-sm rounded-pill px-4">
                            View Details <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    @else
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary btn-lg shadow-sm rounded-pill px-4">
                            View All Appointments <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <a href="{{ route('admin.reports.index') }}" class="text-success stretched-link" style="text-decoration: none;">Total Earnings</a>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($earnings, 2) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <a href="{{ route('admin.patients.index') }}" class="text-info stretched-link" style="text-decoration: none;">Active Patients</a>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPatients }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <a href="{{ route('admin.appointments.index', ['date' => now()->format('Y-m-d')]) }}" class="text-primary stretched-link" style="text-decoration: none;">Today's Visits</a>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayAppointments }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}" class="text-warning stretched-link" style="text-decoration: none;">Pending Requests</a>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingCount }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-comments fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="card shadow border-0 py-2">
                <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-center justify-content-md-between">
                    <div class="text-center text-md-left mb-3 mb-md-0">
                        <h5 class="font-weight-bold text-primary mb-1">Quick Actions</h5>
                        <span class="small text-muted">Manage walk-ins and daily operations with ease.</span>
                    </div>
                    <div class="d-flex flex-column flex-md-row">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-primary shadow-sm rounded-pill px-4 py-2 mr-0 mr-md-2 mb-2 mb-md-0">
                            <i class="fas fa-calendar-check mr-2"></i> Book Walk-In
                        </a>
                        <a href="{{ route('admin.patients.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4 py-2 mr-0 mr-md-2 mb-2 mb-md-0">
                            <i class="fas fa-user-plus mr-2"></i> Register New Patient
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary shadow-sm rounded-pill px-4 py-2">
                            <i class="fas fa-chart-area mr-2"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION: ANALYTICS & REPORTS --}}
    <h2 class="h5 mb-3 mt-5 text-gray-800 font-weight-bold border-bottom pb-2">Analytics & Reports</h2>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Overview (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 300px;">
                        <canvas id="myAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Appointment Status</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2"><i class="fas fa-circle text-success"></i> Completed</span>
                        <span class="mr-2"><i class="fas fa-circle text-primary"></i> Confirmed</span>
                        <span class="mr-2"><i class="fas fa-circle text-danger"></i> Cancelled</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // --- BAR CHART (Revenue) ---
        var ctx = document.getElementById("myAreaChart");
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($months),
                datasets: [{
                    label: "Earnings (₱)",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: @json($revenueData),
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: { legend: { display: false } }
            }
        });

        // --- PIE CHART (Status) ---
        var ctxPie = document.getElementById("myPieChart");
        var myPieChart = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ["Completed", "Confirmed", "Cancelled"],
                datasets: [{
                    data: @json($pieData),
                    backgroundColor: ['#1cc88a', '#4e73df', '#e74a3b'],
                    hoverBackgroundColor: ['#17a673', '#2e59d9', '#e02d1b'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                cutout: '70%',
            },
        });
    </script>
@endpush