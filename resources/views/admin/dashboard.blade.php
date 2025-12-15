@extends('layouts.admin')

{{-- This section defines the content to be inserted into the 'content' yield of the 'layouts.admin' master layout. --}}
@section('content')

    {{-- Page header with title and a button to generate reports --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        {{-- Page title for the Clinic Dashboard --}}
        <h1 class="h3 mb-0 text-gray-800">Clinic Dashboard</h1>
        
        {{-- Button to navigate to the report generation page --}}
        <a href="{{ route('admin.reports.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>

    {{-- "Up Next" Section: Displays a list of immediate upcoming appointments --}}
    <div class="card shadow mb-4 border-left-primary bg-gradient-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-12">
                    {{-- Title for the "Up Next" section, including count of upcoming patients --}}
                    <h5 class="text-primary font-weight-bold text-uppercase mb-3">Up Next ({{ $nextPatients->count() }})</h5>
                    
                    {{-- Conditional display: if there are upcoming patients --}}
                    @if($nextPatients->count() > 0)
                        <div class="row">
                            {{-- Loop through each upcoming appointment to display patient and service details --}}
                            @foreach($nextPatients as $appt)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="mr-3">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="font-weight-bold text-gray-900 mb-0">{{ $appt->patient->name ?? 'Unknown' }}</h6>
                                                    <small class="text-muted">{{ $appt->service->name }}</small>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                                <small class="text-primary font-weight-bold">
                                                    <i class="far fa-clock mr-1"></i> {{ $appt->appointment_time->format('h:i A') }}
                                                </small>
                                                <small class="text-secondary">
                                                    <i class="fas fa-user-md mr-1"></i> {{ $appt->doctor->name ?? 'Any' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Message when no patients are "Up Next" --}}
                        <div class="py-3">
                            <h2 class="font-weight-bold text-gray-700 mb-1">No Patient Up Next</h2>
                            <p class="mb-0 text-muted">All confirmed appointments for today have been completed or are in the future.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Key Performance Indicators (KPIs) Section --}}
    <div class="row">
        {{-- KPI Card: Total Earnings --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Earnings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($earnings, 2) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Card: Active Patients --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Patients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPatients }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Card: Today's Visits --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Today's Visits</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayAppointments }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Card: Pending Requests --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingCount }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-comments fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Section: Buttons for common administrative tasks --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="font-weight-bold text-primary mb-1">Quick Actions</h5>
                        <span class="small text-muted">Manage walk-ins and daily operations</span>
                    </div>
                    <div>
                        {{-- Button to book a walk-in appointment (navigates to schedules) --}}
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-success shadow-sm mr-2">
                            <i class="fas fa-calendar-check mr-2"></i> Book Walk-In
                        </a>
                        {{-- Button to register a new patient --}}
                        <a href="{{ route('admin.patients.create') }}" class="btn btn-info shadow-sm">
                            <i class="fas fa-user-plus mr-2"></i> New Patient
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section: Visual representation of clinic data --}}
    <div class="row">
        {{-- Revenue Overview Chart (Area Chart) --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Overview (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 300px;">
                        {{-- Canvas element for the Chart.js line chart --}}
                        <canvas id="myAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Appointment Status Chart (Pie Chart) --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Appointment Status</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        {{-- Canvas element for the Chart.js doughnut chart --}}
                        <canvas id="myPieChart"></canvas>
                    </div>
                    {{-- Legend for the pie chart --}}
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

{{-- Push scripts to the 'scripts' stack defined in the master layout --}}
@push('scripts')
    {{-- Include Chart.js library --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize the Revenue Overview Chart (Line Chart)
        var ctx = document.getElementById("myAreaChart");
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                // Labels for the x-axis (months) are dynamically passed from the controller
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
                    // Data for the chart is dynamically passed from the controller
                    data: @json($revenueData),
                }],
            },
            options: {
                maintainAspectRatio: false, // Allows chart to resize freely
                scales: {
                    y: { beginAtZero: true } // Y-axis starts from zero
                },
                plugins: { legend: { display: false } } // Hide legend for this chart
            }
        });

        // Initialize the Appointment Status Pie Chart (Doughnut Chart)
        var ctxPie = document.getElementById("myPieChart");
        var myPieChart = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ["Completed", "Confirmed", "Cancelled"], // Labels for segments
                datasets: [{
                    // Data for segments is dynamically passed from the controller
                    data: @json($pieData),
                    backgroundColor: ['#1cc88a', '#4e73df', '#e74a3b'], // Colors for segments
                    hoverBackgroundColor: ['#17a673', '#2e59d9', '#e02d1b'], // Hover colors
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false, // Allows chart to resize freely
                cutout: '70%', // Creates a doughnut effect
            },
        });
    </script>
@endpush