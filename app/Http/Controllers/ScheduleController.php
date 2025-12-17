<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

/**
 * Manages doctor availability schedules.
 * 
 * Allows admins to define working hours for doctors on specific dates.
 * These schedules are then used by the appointment booking system.
 */
class ScheduleController extends Controller
{
    /**
     * Display the schedule management interface.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $doctors = \App\Models\User::where('role', 'doctor')->get();
        return view('admin.schedules.index', compact('doctors'));
    }

    /**
     * Show the form for creating a new schedule.
     * 
     * Allows pre-filling the doctor and date if navigated from the calendar.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $prefilledDate = $request->get('date'); 
        $prefilledDoctorId = $request->get('doctor_id');
        $doctors = \App\Models\User::where('role', 'doctor')->get();

        return view('admin.schedules.create', compact('doctors', 'prefilledDate', 'prefilledDoctorId'));
    }

    /**
     * Store a new schedule in storage.
     * 
     * Checks for existing schedules on the same day to prevent duplicates.
     * Supports both standard form submission and AJAX requests (for calendar interactions).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'max_appointments' => 'nullable|integer|min:1', 
        ]);

        // Prevent defining multiple schedules for the same doctor on the same day
        $exists = Schedule::where('doctor_id', $request->doctor_id)
                          ->where('date', $request->date)
                          ->exists();

        if ($exists) {
            // Return JSON error for AJAX requests (e.g., drag-and-drop on calendar)
            if($request->wantsJson()) {
                return response()->json(['message' => 'Schedule already exists for this date.'], 422);
            }
            return back()->withErrors(['date' => 'Schedule already exists.']);
        }

        Schedule::create([
            'doctor_id' => $request->doctor_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'max_appointments' => $request->max_appointments ?? 20, 
        ]);

        if($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Availability initialized!']);
        }

        return redirect()->route('admin.schedules.index')->with('success', 'Availability set.');
    }

    /**
     * Remove the specified schedule from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();
        return redirect()->route('admin.schedules.index')->with('success', 'Availability removed.');
    }
}