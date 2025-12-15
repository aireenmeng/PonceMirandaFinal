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
                <div class="d-flex align-items-center">
                    <label class="mr-2 font-weight-bold text-gray-700">Date Range:</label>
                    <input type="date" name="start_date" class="form-control form-control-sm mr-2" value="{{ $start->format('Y-m-d') }}">
                    <span class="mr-2 text-gray-500">to</span>
                    <input type="date" name="end_date" class="form-control form-control-sm mr-3" value="{{ $end->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
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
                    <a class="nav-link active" id="tab-ledger" data-toggle="tab" href="#ledger" role="tab">
                        <i class="fas fa-list mr-1"></i> Transaction Ledger
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-services" data-toggle="tab" href="#services" role="tab">
                        <i class="fas fa-tooth mr-1"></i> Top Services
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-doctors" data-toggle="tab" href="#doctors" role="tab">
                        <i class="fas fa-user-md mr-1"></i> Doctor Productivity
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" id="tab-audit" data-toggle="tab" href="#audit" role="tab">
                        <i class="fas fa-shield-alt mr-1"></i> Audit Log
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            
            <div class="tab-pane fade show active" id="ledger" role="tabpanel">
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

            <div class="tab-pane fade" id="services" role="tabpanel">
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

            <div class="tab-pane fade" id="doctors" role="tabpanel">
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

            <div class="tab-pane fade" id="audit" role="tabpanel">
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
            // 1. Expand all tabs so they are visible in print
            $('.tab-pane').addClass('show active');
            $('.nav-tabs').hide(); // Hide the clickable tabs
            
            window.print();

            // 2. Reset after print (reload to be safe or just revert classes)
            // Timeout allows the print dialog to open before we reset
            setTimeout(() => {
                $('.nav-tabs').show();
                // Optional: Reload to restore exact state
                // location.reload(); 
            }, 1000);
        }

        function exportToExcel() {
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

            /* Save file */
            XLSX.writeFile(wb, "PonceMiranda_Report_{{ now()->format('Y-m-d') }}.xlsx");
        }
    </script>

    <style>
        @media print {
            /* Hide everything by default */
            body * { visibility: hidden; }
            
            /* Show only the report container */
            #reportContainer, #reportContainer * { visibility: visible; }
            
            /* Positioning */
            #reportContainer {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Clean up for Paper */
            .card { border: none !important; box-shadow: none !important; margin-bottom: 20px; }
            .card-header { border-bottom: 2px solid #000 !important; background: none !important; padding-left: 0; }
            .table { width: 100% !important; border-collapse: collapse; }
            .badge { border: 1px solid #000; color: #000 !important; background: none !important; }
            
            /* Hiding buttons/tabs explicitly */
            .btn, .nav-tabs, .d-print-none { display: none !important; }
        }
    </style>
@endpush