<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    // 1. LIST APPOINTMENTS (Tabs Logic)
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending'); // Default to pending

        $appointments = Appointment::with(['patient', 'doctor', 'service'])
            ->where('status', $status)
            ->orderBy('appointment_date', 'asc')
            ->get();

        return view('admin.appointments.index', compact('appointments', 'status'));
    }

    // 2. SHOW WALK-IN FORM
    public function create()
    {
        $patients = User::where('role', 'patient')->get();
        $doctors = User::where('role', 'doctor')->get();
        $services = Service::all();

        return view('admin.appointments.create', compact('patients', 'doctors', 'services'));
    }

    // 3. STORE WALK-IN (Instant Confirm)
    // Update the store method (or storeWalkIn)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            // NEW: Expect minutes (30, 60, 90)
            'duration_minutes' => 'required|integer|min:30', 
        ]);

        // 1. Calculate New Appointment Range
        $newStart = \Carbon\Carbon::parse($request->appointment_date . ' ' . $request->appointment_time);
        $newEnd = $newStart->copy()->addMinutes($request->duration_minutes);

        // 2. Precise Conflict Check (Overlaps)
        $conflict = Appointment::where('doctor_id', $request->doctor_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->filter(function($appt) use ($newStart, $newEnd) {
                $existingStart = \Carbon\Carbon::parse($appt->appointment_date->format('Y-m-d') . ' ' . $appt->appointment_time->format('H:i:s'));
                $existingEnd = $existingStart->copy()->addMinutes($appt->duration_minutes);

                // Standard Overlap Formula: (StartA < EndB) and (EndA > StartB)
                return $newStart < $existingEnd && $newEnd > $existingStart;
            });

        if ($conflict->count() > 0) {
            return back()->withErrors(['time' => 'This time slot overlaps with another appointment. Please adjust the duration.']);
        }

        // 3. Save
        Appointment::create([
            'user_id' => $request->user_id,
            'doctor_id' => $request->doctor_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration_minutes' => $request->duration_minutes, // Save Minutes
            'status' => 'confirmed'
        ]);

        return redirect()->route('admin.schedules.index') // Redirect back to calendar
            ->with('success', 'Appointment booked successfully.');
    }

    // 4. CONFIRM (Pending -> Confirmed)
    public function confirm($id)
    {
        $appt = Appointment::findOrFail($id);
        $appt->update(['status' => 'confirmed']);

        return back()->with('success', 'Appointment confirmed.');
    }

    // 5. CANCEL / VOID (With Reason)
    public function cancel(Request $request, $id)
    {
        $request->validate(['cancellation_reason' => 'required|string|max:255']);

        $appt = Appointment::findOrFail($id);
        
        $appt->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Appointment cancelled.');
    }

    // 6. COMPLETE (Confirmed -> Completed)
    public function complete($id)
    {
        $appt = Appointment::findOrFail($id);
        $appt->update(['status' => 'completed']);

        return back()->with('success', 'Appointment marked as completed.');
    }

    // 7. STORE FROM CALENDAR MODAL (Walk-In)
   public function storeWalkIn(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'duration' => 'required|integer|min:1|max:4', // Duration is 1-4 hours
        ]);

        $patientId = null;
        // --- (Patient Creation/Selection Logic remains the same) ---
        if ($request->patient_type === 'new') {
            $request->validate([
                'new_name' => 'required|string',
                'new_phone' => 'required|string',
                'new_email' => 'nullable|email|unique:users,email', 
            ]);

            // Create Guest User
            $newUser = User::create([
                'name' => $request->new_name,
                'email' => $request->new_email ?? 'guest_'.time().'@clinic.com',
                'phone' => $request->new_phone,
                'password' => bcrypt('password'),
                'role' => 'patient',
                'email_verified_at' => now(),
            ]);
            $patientId = $newUser->id;
        } else {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $patientId = $request->user_id;
        }
        // --- (End Patient Logic) ---

        // 2. CHECK FOR EXTENDED SLOT CONFLICTS
        $startTime = \Carbon\Carbon::parse($request->appointment_time);
        $duration = (int)$request->duration;

        // Loop through all hours in the required duration (e.g., 1 PM, 2 PM if duration=2)
        for ($i = 0; $i < $duration; $i++) {
            $checkTime = $startTime->copy()->addHours($i)->format('H:i:s');
            
            // Check if ANY appointment (confirmed or pending) already occupies this specific hour slot
            $conflict = Appointment::where('doctor_id', $request->doctor_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->whereTime('appointment_time', $checkTime)
                ->whereIn('status', ['confirmed', 'pending'])
                ->exists();
            
            if ($conflict) {
                // Return an error if any of the required slots are taken.
                $conflictingHour = $startTime->copy()->addHours($i)->format('h:i A');
                return redirect()->back()->withErrors(['booking' => "The slot starting at {$conflictingHour} is already reserved for a 1-hour service. Please choose a shorter duration or a different time."]);
            }
        }
        
        // 3. Create Appointment (Booking the START time only, the conflict check protects the rest)
        Appointment::create([
            'user_id' => $patientId,
            'doctor_id' => $request->doctor_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration_hours' => $duration, // IMPORTANT: We need to store this new field
            'status' => 'confirmed'
        ]);
        
        return redirect()->back()->with('success', 'Appointment booked successfully!');
    }

    // ... existing methods ...

    // 8. SHOW APPOINTMENT DETAILS (The Page)
    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'service', 'canceller'])->findOrFail($id);
        
        // If it's just a block, maybe redirect back or show simple info
        if($appointment->status === 'blocked') {
            return back()->with('info', 'This slot is manually blocked by admin.');
        }

        return view('admin.appointments.show', compact('appointment'));
    }

    // 9. BLOCK/UNBLOCK SLOT (Adjust Availability)
    public function blockSlot(Request $request)
    {
        // If "Reserved" is chosen
        if ($request->status === 'reserved') {
            Appointment::create([
                'doctor_id' => $request->doctor_id,
                'appointment_date' => $request->date,
                'appointment_time' => $request->time,
                'status' => 'blocked', // Special status
                'service_id' => null,  // No service
                'user_id' => null      // No patient
            ]);
        } 
        // If "Available" is chosen (Delete the block)
        else {
            Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->date)
                ->where('appointment_time', $request->time)
                ->where('status', 'blocked')
                ->delete(); // Remove the block
        }

        return response()->json(['success' => true]);
    }
}