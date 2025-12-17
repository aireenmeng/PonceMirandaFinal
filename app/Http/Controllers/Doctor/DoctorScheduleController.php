<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Manages the doctor's personal schedule view.
 * 
 * Allows doctors to view their daily timeline, see booked slots, and 
 * manually block/unblock time slots for breaks or other unavailability.
 */
class DoctorScheduleController extends Controller
{
    /**
     * Show the daily schedule timeline.
     * 
     * Generates a list of 30-minute time slots between 9 AM and 5 PM.
     * Checks each slot against the database to determine its status
     * (available, booked, or blocked).
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $doctor_id = Auth::id();

        // Define standard working hours (Hardcoded 9-5 for this view)
        $startTime = Carbon::parse($date . ' 09:00:00');
        $endTime   = Carbon::parse($date . ' 17:00:00');
        
        // Retrieve all appointments and blocks for the selected date
        $existingBookings = Appointment::where('doctor_id', $doctor_id)
            ->whereDate('appointment_date', $date)
            ->get();

        $slots = [];

        // Generate time slots in 30-minute increments
        while ($startTime < $endTime) {
            $timeStr = $startTime->format('H:i:s');
            
            // Check if this specific time matches any existing booking record
            $booking = $existingBookings->first(function($item) use ($timeStr) {
                return $item->appointment_time == $timeStr;
            });

            $status = 'available';
            if ($booking) {
                if ($booking->status == 'blocked') {
                    $status = 'blocked';
                } elseif (in_array($booking->status, ['confirmed', 'completed'])) {
                    $status = 'booked';
                }
            }

            $slots[] = [
                'time' => $timeStr,
                'display' => $startTime->format('h:i A'),
                'status' => $status,
                'booking_id' => $booking ? $booking->id : null // Needed for unblocking actions
            ];

            $startTime->addMinutes(30);
        }

        return view('doctor.schedule.index', compact('date', 'slots'));
    }

    /**
     * Initialize or update the day's schedule configuration.
     * 
     * Allows setting custom start/end times or marking the entire day as off.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDateSchedule(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'is_day_off' => 'nullable' 
        ]);

        $isDayOff = filter_var($request->is_day_off, FILTER_VALIDATE_BOOLEAN);

        if ($isDayOff) {
            $start = '00:00:00';
            $end   = '00:00:00';

            // Cancel existing appointments for this day
            Appointment::where('doctor_id', Auth::id())
                ->whereDate('appointment_date', $request->date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Doctor marked day off',
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now()
                ]);
        } else {
            $start = $request->start_time ?: '09:00:00';
            $end   = $request->end_time   ?: '17:00:00';
        }

        Schedule::updateOrCreate(
            [
                'doctor_id' => Auth::id(), 
                'date' => $request->date
            ],
            [
                'start_time' => $start,
                'end_time' => $end,
                'max_appointments' => 20 
            ]
        );

        return response()->json(['message' => 'Schedule updated successfully!']);
    }

    /**
     * Block or unblock a specific time slot.
     * 
     * To "block" a slot, we create a dummy appointment record with status 'blocked'.
     * To "unblock", we delete that record. This integrates seamlessly with the 
     * existing appointment conflict checking logic.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleSlot(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'action' => 'required|in:block,unblock' 
        ]);

        $doctorId = auth()->id();

        if ($request->action === 'block') {
            // Prevent creating duplicate blocks
            $exists = \App\Models\Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $request->date)
                ->where('appointment_time', $request->time)
                ->exists();

            if (!$exists) {
                \App\Models\Appointment::create([
                    'doctor_id' => $doctorId,
                    'user_id' => $doctorId, // The doctor "owns" their own block
                    'service_id' => 1,      // Uses a placeholder service ID
                    'appointment_date' => $request->date,
                    'appointment_time' => $request->time,
                    'duration_minutes' => 30, 
                    'status' => 'blocked',    
                    'price' => 0
                ]);
            }
            $msg = 'Slot has been blocked.';
        } else {
            // Find and remove the block
            \App\Models\Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $request->date)
                ->where('appointment_time', $request->time)
                ->where('status', 'blocked')
                ->delete();
                
            $msg = 'Slot is now open.';
        }

        return back()->with('success', $msg);
    }
}