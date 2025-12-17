<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function index(Request $request)
    {
        // Set default date range to the current month if not provided
        $start = $request->get('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $end = $request->get('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        // Get filters
        $doctorId = $request->get('doctor_id');
        $serviceId = $request->get('service_id');

        // Fetch Data for Dropdowns
        $doctors = User::where('role', 'doctor')->orderBy('name')->get();
        $services = Service::orderBy('name')->get();

        // Retrieve completed appointments with details for the ledger
        $completedAppts = Appointment::with(['service', 'doctor', 'patient' => function($query) { $query->withTrashed(); }])
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->when($doctorId, function($q) use ($doctorId) {
                return $q->where('doctor_id', $doctorId);
            })
            ->when($serviceId, function($q) use ($serviceId) {
                return $q->where('service_id', $serviceId);
            })
            ->orderBy('appointment_date')
            ->get();

        // Calculate service performance statistics (count and revenue)
        $serviceStats = Appointment::query()
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->when($doctorId, function($q) use ($doctorId) {
                return $q->where('appointments.doctor_id', $doctorId);
            })
            ->when($serviceId, function($q) use ($serviceId) {
                return $q->where('appointments.service_id', $serviceId);
            })
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as count'), DB::raw('sum(appointments.price) as revenue'))
            ->groupBy('services.name')
            ->orderByDesc('revenue')
            ->get();

        // Calculate doctor productivity statistics
        $doctorStats = Appointment::query()
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->when($doctorId, function($q) use ($doctorId) {
                return $q->where('appointments.doctor_id', $doctorId);
            })
            ->when($serviceId, function($q) use ($serviceId) {
                return $q->where('appointments.service_id', $serviceId);
            })
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select('users.name', DB::raw('count(*) as count'), DB::raw('sum(appointments.price) as revenue'))
            ->groupBy('users.name')
            ->orderByDesc('count')
            ->get();

        // Retrieve cancelled appointments for audit
        // Note: Cancellations are usually relevant regardless of current filters (security log), 
        // but we can apply them if desired. For now, we'll keep audit log broad or maybe apply doctor if pertinent.
        // Let's apply filters to audit log too so "Doctor's Report" includes their specific cancellations.
        $cancelledAppts = Appointment::with(['patient' => function($query) { $query->withTrashed(); }, 'canceller'])
            ->where('appointments.status', 'cancelled')
            ->whereBetween('updated_at', [$start, $end])
            ->when($doctorId, function($q) use ($doctorId) {
                return $q->where('doctor_id', $doctorId);
            })
            ->when($serviceId, function($q) use ($serviceId) {
                return $q->where('service_id', $serviceId);
            })
            ->get();

        // Calculate summary statistics
        $stats = [
            'total_completed' => $completedAppts->count(),
            'total_cancelled' => $cancelledAppts->count(),
            'revenue' => $completedAppts->sum('price')
        ];

        return view('admin.reports.index', compact(
            'completedAppts', 'cancelledAppts', 'serviceStats', 'doctorStats', 'stats', 'start', 'end',
            'doctors', 'services'
        ));
    }
}
