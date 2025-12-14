<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
//hi
class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. KPI CARDS
        $pendingCount = Appointment::where('status', 'pending')->count();
        
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())
                            ->where('status', 'confirmed')
                            ->count();

        $totalPatients = User::where('role', 'patient')->count();

        // Total Earnings (Completed Appointments only)
        $earnings = Appointment::where('appointments.status', 'completed')
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->sum('services.price');

        // 2. CHART DATA: Monthly Revenue (Last 6 Months)
        $revenueData = [];
        $months = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            $monthlyEarned = Appointment::where('appointments.status', 'completed')
                ->whereMonth('appointment_date', $month->month)
                ->whereYear('appointment_date', $month->year)
                ->join('services', 'appointments.service_id', '=', 'services.id')
                ->sum('services.price');
                
            $revenueData[] = $monthlyEarned;
        }

        // 3. PIE CHART: Appointment Status Distribution
        $pieData = [
            Appointment::where('status', 'completed')->count(),
            Appointment::where('status', 'confirmed')->count(),
            Appointment::where('status', 'cancelled')->count(),
        ];

        return view('admin.dashboard', compact(
            'pendingCount', 'todayAppointments', 'totalPatients', 'earnings',
            'months', 'revenueData', 'pieData'
        ));
    }
}