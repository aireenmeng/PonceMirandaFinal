<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with key metrics and charts.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Retrieve key performance indicators
        $pendingCount = Appointment::where('status', 'pending')->count();
        
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())
                            ->whereIn('status', ['confirmed', 'pending', 'completed'])
                            ->count();

        $totalPatients = User::where('role', 'patient')->count();

        // Calculate total earnings from completed appointments
        $earnings = Appointment::where('appointments.status', 'completed')
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->sum('services.price');

        // Prepare monthly revenue data for the chart (last 6 months)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        
        $monthlyStats = Appointment::where('appointments.status', 'completed')
            ->where('appointment_date', '>=', $sixMonthsAgo)
            ->join('services', 'appointments.service_id', '=', 'services.id')
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

        // Prepare appointment status distribution for the pie chart
        $statusStats = Appointment::selectRaw('status, count(*) as count')
            ->whereIn('status', ['completed', 'confirmed', 'cancelled'])
            ->groupBy('status')
            ->pluck('count', 'status');

        $pieData = [
            $statusStats['completed'] ?? 0,
            $statusStats['confirmed'] ?? 0,
            $statusStats['cancelled'] ?? 0,
        ];

        // Retrieve upcoming appointments for the next available slot
        $now = Carbon::now();
        
        $nextSlot = Appointment::where('status', 'confirmed')
            ->where(function($query) use ($now) {
                $query->whereDate('appointment_date', '>', $now->toDateString())
                      ->orWhere(function($q) use ($now) {
                          $q->whereDate('appointment_date', $now->toDateString())
                            ->whereTime('appointment_time', '>=', $now->toTimeString());
                      });
            })
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first(['appointment_date', 'appointment_time']);

        $nextPatients = collect([]);

        if ($nextSlot) {
            $nextPatients = Appointment::with(['patient', 'doctor', 'service'])
                ->where('status', 'confirmed')
                ->where('appointment_date', $nextSlot->appointment_date)
                ->where('appointment_time', $nextSlot->appointment_time)
                ->get();
        }

        return view('admin.dashboard', compact(
            'pendingCount', 'todayAppointments', 'totalPatients', 'earnings',
            'months', 'revenueData', 'pieData', 'nextPatients'
        ));
    }
}