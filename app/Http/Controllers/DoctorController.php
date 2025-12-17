<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon;

class DoctorDashboardController extends Controller
{
    /**
     * Display the doctor's dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $doctorId = Auth::id();
        $today = Carbon::today();

        // Get today's confirmed and completed appointments
        $todaysAppointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['confirmed', 'completed']) // Only confirmed/completed
            ->orderBy('appointment_time')
            ->get();

        // Count upcoming confirmed appointments for the next 7 days
        $upcomingCount = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', '>', $today)
            ->where('status', 'confirmed')
            ->count();

        return view('doctor.dashboard', compact('todaysAppointments', 'upcomingCount'));
    }

    /**
     * Update the diagnosis and prescription for a specific appointment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $appointmentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDiagnosis(Request $request, $appointmentId)
    {
        $request->validate([
            'diagnosis' => 'required|string|min:10',
            'prescription' => 'nullable|string'
        ]);

        $appt = Appointment::where('id', $appointmentId)
            ->where('doctor_id', Auth::id())
            ->firstOrFail();

        $appt->update([
            'diagnosis' => $request->diagnosis,
            'prescription' => $request->prescription,
            'status' => 'completed' // Auto-complete when notes are added
        ]);

        return back()->with('success', 'Medical notes saved successfully.');
    }
}