<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Generate and display the comprehensive clinic report.
     * 
     * This method aggregates data from various sources to provide a holistic view
     * of the clinic's performance within a specified date range. It covers:
     * - Financial Ledger: Detailed list of all completed transactions.
     * - Service Analytics: Breakdown of revenue and popularity by service type.
     * - Staff Performance: Productivity metrics for doctors (volume and revenue).
     * - Operational Audit: Analysis of cancellations to identify retention issues.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // -------------------------------------------------------------------------
        // 1. Date Range Configuration
        // -------------------------------------------------------------------------
        // Determine the reporting period. If the user provides a custom range via
        // 'start_date' and 'end_date', we use that. Otherwise, we default to the
        // current month to show the most relevant recent data.
        $start = $request->get('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $end = $request->get('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        // -------------------------------------------------------------------------
        // 2. Main Financial Ledger
        // -------------------------------------------------------------------------
        // Retrieve the master list of all completed appointments.
        // We use eager loading ('with') to fetch related Service, Doctor, and Patient
        // data in a single query to optimize performance (N+1 problem prevention).
        // Note: We include 'withTrashed' for patients to ensure records remain accurate
        // even if a patient's account has been deleted from the system.
        $completedAppts = Appointment::with(['service', 'doctor', 'patient' => function($query) { $query->withTrashed(); }])
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->orderBy('appointment_date')
            ->get();

        // -------------------------------------------------------------------------
        // 3. Service Performance Analytics
        // -------------------------------------------------------------------------
        // Analyze which treatments are driving the most value.
        // We group appointments by service name and aggregate:
        // - Volume: How many times was this service performed? (count)
        // - Revenue: How much money did this service generate? (sum of price)
        $serviceStats = Appointment::query()
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as count'), DB::raw('sum(appointments.price) as revenue'))
            ->groupBy('services.name')
            ->orderByDesc('revenue') // Prioritize high-revenue services in the report
            ->get();

        // -------------------------------------------------------------------------
        // 4. Doctor Productivity Metrics
        // -------------------------------------------------------------------------
        // Evaluate the workload and financial contribution of each doctor.
        // This helps identify top performers and potential scheduling imbalances.
        // Similar to service stats, we aggregate count and revenue, but grouped by Doctor.
        $doctorStats = Appointment::query()
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select('users.name', DB::raw('count(*) as count'), DB::raw('sum(appointments.price) as revenue'))
            ->groupBy('users.name')
            ->orderByDesc('count') // Order by volume of patients seen
            ->get();

        // -------------------------------------------------------------------------
        // 5. Cancellation Audit
        // -------------------------------------------------------------------------
        // Track lost opportunities. Monitoring cancellations is crucial for
        // understanding patient retention and operational efficiency.
        // We track WHO cancelled the appointment ('canceller') to distinguish
        // between patient cancellations and clinic-initiated cancellations.
        $cancelledAppts = Appointment::with(['patient' => function($query) { $query->withTrashed(); }, 'canceller'])
            ->where('appointments.status', 'cancelled')
            ->whereBetween('updated_at', [$start, $end]) // Use updated_at to catch when the cancellation happened
            ->get();

        // -------------------------------------------------------------------------
        // 6. Summary Totals
        // -------------------------------------------------------------------------
        // Calculate high-level KPIs for the dashboard header cards.
        $stats = [
            'total_completed' => $completedAppts->count(),
            'total_cancelled' => $cancelledAppts->count(),
            'revenue' => $completedAppts->sum('price')
        ];

        return view('admin.reports.index', compact(
            'completedAppts', 'cancelledAppts', 'serviceStats', 'doctorStats', 'stats', 'start', 'end'
        ));
    }
}