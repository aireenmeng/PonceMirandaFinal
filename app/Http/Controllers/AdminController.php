<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. KPI CARDS
        $pendingCount = Appointment::where('status', 'pending')->count();
        
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())
                            ->whereIn('status', ['confirmed', 'pending', 'completed'])
                            ->count();

        $totalPatients = User::where('role', 'patient')->count();

        // Total Earnings (Completed Appointments only)
        $earnings = Appointment::where('appointments.status', 'completed')
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->sum('services.price');

        // 2. CHART DATA: Monthly Revenue (Optimized)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        
        $monthlyStats = Appointment::where('appointments.status', 'completed')
            ->where('appointment_date', '>=', $sixMonthsAgo)
            ->join('services', 'appointments.service_id', '=', 'services.id')
            // MySQL compatible date format
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as month_key, SUM(services.price) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $revenueData = [];
        $months = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $monthKey = $dt->format('Y-m');
            $months[] = $dt->format('M Y');
            $revenueData[] = $monthlyStats[$monthKey] ?? 0;
        }

        // 3. PIE CHART: Appointment Status (Optimized)
        $statusStats = Appointment::selectRaw('status, count(*) as count')
            ->whereIn('status', ['completed', 'confirmed', 'cancelled'])
            ->groupBy('status')
            ->pluck('count', 'status');

        $pieData = [
            $statusStats['completed'] ?? 0,
            $statusStats['confirmed'] ?? 0,
            $statusStats['cancelled'] ?? 0,
        ];

        // 4. "UP NEXT" for Admin (Earliest confirmed appointment across all doctors)
        $nextPatient = Appointment::with(['patient', 'doctor', 'service'])
            ->where('status', 'confirmed')
            ->where(function($query) {
                $query->whereDate('appointment_date', '>', Carbon::today())
                      ->orWhere(function($q) {
                          $q->whereDate('appointment_date', Carbon::today())
                            ->whereTime('appointment_time', '>=', Carbon::now()->format('H:i:s'));
                      });
            })
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first();

        return view('admin.dashboard', compact(
            'pendingCount', 'todayAppointments', 'totalPatients', 'earnings',
            'months', 'revenueData', 'pieData', 'nextPatient' // Pass nextPatient
        ));
    }
}