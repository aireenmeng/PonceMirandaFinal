@extends('layouts.admin')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4 d-print-none">
        <h1 class="h3 mb-0 text-gray-800">Analytics & Reports</h1>
        <div class="d-flex">
            <button onclick="exportToExcel()" class="btn btn-sm btn-success shadow-sm mr-2">
                <i class="fas fa-file-excel fa-sm text-white-50"></i> Export Excel
            </button>
            <button onclick="printReport()" class="btn btn-sm btn-dark shadow-sm">
                <i class="fas fa-print fa-sm text-white-50"></i> Print Report
            </button>
        </div>
    </div>

    <div class="card shadow mb-4 d-print-none">
        <div class="card-body py-2">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="form-inline justify-content-between">
                <input type="hidden" name="active_tab" id="activeTabInput" value="{{ request('active_tab', 'ledger') }}">
                <div class="d-flex align-items-center flex-wrap">
                    <label class="mr-2 font-weight-bold text-gray-700">Filters:</label>
                    
                    {{-- Doctor Filter --}}
                    <select name="doctor_id" class="custom-select custom-select-sm mr-2" style="width: 180px;" onchange="this.form.submit()">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                Dr. {{ $doc->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Service Filter --}}
                    <select name="service_id" class="custom-select custom-select-sm mr-2" style="width: 180px;" onchange="this.form.submit()">
                        <option value="">All Services</option>
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}" {{ request('service_id') == $srv->id ? 'selected' : '' }}>
                                {{ $srv->name }}
                            </option>
                        @endforeach
                    </select>

                    <label class="mr-2 font-weight-bold text-gray-700">Date:</label>
                    <input type="date" name="start_date" class="form-control form-control-sm mr-2" value="{{ $start->format('Y-m-d') }}">
                    <span class="mr-2 text-gray-500">to</span>
                    <input type="date" name="end_date" class="form-control form-control-sm mr-3" value="{{ $end->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-secondary ml-1" title="Clear Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
                <div>
                    <span class="badge badge-light border text-dark p-2">
                        Total Revenue: <span class="text-success font-weight-bold">₱{{ number_format($stats['revenue'], 2) }}</span>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div id="reportContainer">
        
        <div class="d-none d-print-block text-center mb-4">
            <h2 class="font-weight-bold text-dark">Ponce Miranda Dental Clinic</h2>
            <p class="mb-0">Official Business Report</p>
            <p class="small text-muted">Period: {{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}</p>
            <hr>
        </div>

        <div class="d-print-none mb-3">
            <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ request('active_tab', 'ledger') == 'ledger' ? 'active' : '' }}" id="tab-ledger" data-toggle="tab" href="#ledger" role="tab" onclick="document.getElementById('activeTabInput').value='ledger'">
                        <i class="fas fa-list mr-1"></i> Transaction Ledger
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('active_tab') == 'services' ? 'active' : '' }}" id="tab-services" data-toggle="tab" href="#services" role="tab" onclick="document.getElementById('activeTabInput').value='services'">
                        <i class="fas fa-tooth mr-1"></i> Top Services
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('active_tab') == 'doctors' ? 'active' : '' }}" id="tab-doctors" data-toggle="tab" href="#doctors" role="tab" onclick="document.getElementById('activeTabInput').value='doctors'">
                        <i class="fas fa-user-md mr-1"></i> Doctor Productivity
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('active_tab') == 'audit' ? 'active' : '' }} text-danger" id="tab-audit" data-toggle="tab" href="#audit" role="tab" onclick="document.getElementById('activeTabInput').value='audit'">
                        <i class="fas fa-shield-alt mr-1"></i> Audit Log
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            
            <div class="tab-pane fade {{ request('active_tab', 'ledger') == 'ledger' ? 'show active' : '' }}" id="ledger" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-white d-flex justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Completed Transactions</h6>
                        <small class="text-muted">{{ $stats['total_completed'] }} Records</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="table-ledger" width="100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Service</th>
                                        <th>Doctor</th>
                                        <th>Diagnosis</th>
                                        <th>Prescription</th>
                                        <th class="text-right">Amount</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($completedAppts as $appt)
                                    <tr>
                                        <td>{{ $appt->appointment_date->format('M d, Y') }}</td>
                                        <td>{{ $appt->patient->name ?? 'N/A (Patient Deleted)' }}</td>
                                        <td>{{ $appt->service->name }}</td>
                                        <td>Dr. {{ $appt->doctor->name }}</td>
                                        <td>{{ $appt->diagnosis  }}</td>
                                        <td>{{ $appt->prescription }}</td>

                                        <td class="text-right font-weight-bold">₱{{ number_format($appt->price, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted">No records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ request('active_tab') == 'services' ? 'show active' : '' }}" id="services" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-info">Service Performance Analysis</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-sm" id="table-services">
                            <thead class="thead-light">
                                <tr>
                                    <th>Treatment Name</th>
                                    <th class="text-center">Qty Sold</th>
                                    <th class="text-right">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceStats as $stat)
                                <tr>
                                    <td class="font-weight-bold">{{ $stat->name }}</td>
                                    <td class="text-center">{{ $stat->count }}</td>
                                    <td class="text-right">₱{{ number_format($stat->revenue, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ request('active_tab') == 'doctors' ? 'show active' : '' }}" id="doctors" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-success">Doctor Productivity Report</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-sm" id="table-doctors">
                            <thead class="thead-light">
                                <tr>
                                    <th>Doctor Name</th>
                                    <th class="text-center">Patients Seen</th>
                                    <th class="text-right">Revenue Generated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($doctorStats as $stat)
                                <tr>
                                    <td class="font-weight-bold">Dr. {{ $stat->name }}</td>
                                    <td class="text-center">{{ $stat->count }}</td>
                                    <td class="text-right text-success font-weight-bold">₱{{ number_format($stat->revenue, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ request('active_tab') == 'audit' ? 'show active' : '' }}" id="audit" role="tabpanel">
                <div class="card shadow mb-4 border-left-danger">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-danger">Cancellation Security Log</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="table-audit">
                                <thead class="bg-light text-danger">
                                    <tr>
                                        <th>Date Cancelled</th>
                                        <th>Original Date</th>
                                        <th>Patient</th>
                                        <th>Reason</th>
                                        <th>Authorized By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cancelledAppts as $appt)
                                    <tr>
                                        <td>{{ $appt->updated_at->format('M d H:i') }}</td>
                                        <td>{{ $appt->appointment_date->format('M d') }}</td>
                                        <td>{{ $appt->patient->name }}</td>
                                        <td class="font-italic text-muted">"{{ $appt->cancellation_reason }}"</td>
                                        <td class="font-weight-bold">{{ $appt->canceller->name ?? 'System' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted">No cancellations found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-none d-print-block mt-5">
            <hr>
            <div class="d-flex justify-content-between text-muted small">
                <span>Generated By: <strong>{{ Auth::user()->name }}</strong></span>
                <span>Date: {{ now()->format('F d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

    <script>
        function printReport() {
            window.print();
        }

        function exportToExcel() {
            // Check if current inputs match the report dates
            const currentStart = "{{ $start->format('Y-m-d') }}";
            const currentEnd = "{{ $end->format('Y-m-d') }}";
            const inputStart = document.querySelector('input[name="start_date"]').value;
            const inputEnd = document.querySelector('input[name="end_date"]').value;

            if (currentStart !== inputStart || currentEnd !== inputEnd) {
                if(!confirm("The selected date range does not match the generated report.\n\nClick 'Cancel' to Update/Filter the report first.\nClick 'OK' to export the currently displayed data.")) {
                    return;
                }
            }

            /* Create a new workbook */
            var wb = XLSX.utils.book_new();
            
            /* Add Ledger Sheet */
            var ws1 = XLSX.utils.table_to_sheet(document.getElementById('table-ledger'));
            XLSX.utils.book_append_sheet(wb, ws1, "Transactions");

            /* Add Services Sheet */
            var ws2 = XLSX.utils.table_to_sheet(document.getElementById('table-services'));
            XLSX.utils.book_append_sheet(wb, ws2, "Services");

            /* Add Doctors Sheet */
            var ws3 = XLSX.utils.table_to_sheet(document.getElementById('table-doctors'));
            XLSX.utils.book_append_sheet(wb, ws3, "Doctors");

            /* Save file with date range */
            XLSX.writeFile(wb, "PonceMiranda_Report_{{ $start->format('Y-m-d') }}_to_{{ $end->format('Y-m-d') }}.xlsx");
        }
    </script>

    <style>
        @media print {
            /* Hide Sidebar, Topbar, Buttons, Inputs */
            .sidebar, .topbar, .btn, .d-print-none, form, footer, #accordionSidebar { 
                display: none !important; 
            }
            
            /* Reset Layout */
            #wrapper { 
                display: block !important; 
                width: 100% !important; 
                overflow: visible !important;
            }
            #content-wrapper { 
                margin-left: 0 !important; 
                width: 100% !important; 
                overflow: visible !important;
            }
            #content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .container-fluid { 
                padding: 0 !important; 
                max-width: 100% !important;
            }

            /* Report Container Visibility */
            #reportContainer { 
                display: block !important; 
                width: 100% !important;
            }

            /* Show all Tab Panes (stacked) */
            .tab-content > .tab-pane {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            .nav-tabs { display: none !important; }

            /* Styling for Print */
            .card { 
                border: none !important; 
                box-shadow: none !important; 
                margin-bottom: 20px !important;
            }
            .card-header {
                background-color: #fff !important;
                border-bottom: 2px solid #333 !important;
                color: #000 !important;
            }
            .table { 
                width: 100% !important; 
                border-collapse: collapse !important; 
            }
            .table th, .table td {
                color: #000 !important;
                border: 1px solid #ddd !important;
            }
            
            /* Typography */
            body { 
                font-size: 12pt; 
                color: #000 !important; 
                background: #fff !important;
            }
            h1, h2, h3, h4, h5, h6 { color: #000 !important; }
        }
    </style>
@endpush