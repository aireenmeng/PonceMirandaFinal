<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $doctors = \App\Models\User::where('role', 'doctor')->get();
        return view('admin.schedules.index', compact('doctors'));
    }

    public function create(Request $request)
    {
        $prefilledDate = $request->get('date'); 
        $prefilledDoctorId = $request->get('doctor_id');
        $doctors = \App\Models\User::where('role', 'doctor')->get();

        return view('admin.schedules.create', compact('doctors', 'prefilledDate', 'prefilledDoctorId'));
    }

    public function store(Request $request)
    {
        // --- 1. REMOVE 'max_appointments' FROM THIS LIST ---
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            // 'max_appointments' line IS DELETED
        ]);

        // 2. CHECK DUPLICATES
        $exists = Schedule::where('doctor_id', $request->doctor_id)
                          ->where('date', $request->date)
                          ->exists();

        if ($exists) {
            if($request->wantsJson()) {
                return response()->json(['message' => 'Schedule already exists for this date.'], 422);
            }
            return back()->withErrors(['date' => 'Schedule already exists.']);
        }

        // --- 3. ADD DUMMY VALUE HERE ---
        Schedule::create([
            'doctor_id' => $request->doctor_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'max_appointments' => 999, // <--- We hardcode this so DB is happy
        ]);

        // 4. RETURN
        if($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Availability initialized!']);
        }

        return redirect()->route('admin.schedules.index')->with('success', 'Availability set.');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();
        return redirect()->route('admin.schedules.index')->with('success', 'Availability removed.');
    }
}