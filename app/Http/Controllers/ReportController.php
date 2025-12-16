<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
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

        // Retrieve completed appointments with details for the ledger
        $completedAppts = Appointment::with(['service', 'doctor', 'patient' => function($query) { $query->withTrashed(); }])
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->orderBy('appointment_date')
            ->get();

        // Calculate service performance statistics (count and revenue)
        $serviceStats = Appointment::query()
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as count'), DB::raw('sum(appointments.price) as revenue'))
            ->groupBy('services.name')
            ->orderByDesc('revenue')
            ->get();

        // Calculate doctor productivity statistics
        $doctorStats = Appointment::query()
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select('users.name', DB::raw('count(*) as count'), DB::raw('sum(appointments.price) as revenue'))
            ->groupBy('users.name')
            ->orderByDesc('count')
            ->get();

        // Retrieve cancelled appointments for audit
        $cancelledAppts = Appointment::with(['patient' => function($query) { $query->withTrashed(); }, 'canceller'])
            ->where('appointments.status', 'cancelled')
            ->whereBetween('updated_at', [$start, $end])
            ->get();

        // Calculate summary statistics
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
