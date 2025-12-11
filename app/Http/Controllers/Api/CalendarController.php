<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Appointment;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function getEvents(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $doctorId = $request->doctor_id;

        // Fetch schedules
        $query = Schedule::whereBetween('date', [$start, $end]);
        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }
        $schedules = $query->get();

        $events = [];

        foreach ($schedules as $sched) {
            // Count actual bookings to show "X Booked" on the calendar view
            $bookedCount = Appointment::whereDate('appointment_date', $sched->date)
                ->where('status', '!=', 'cancelled')
                ->where('doctor_id', $sched->doctor_id)
                ->count();

            $events[] = [
                'id' => $sched->id,
                'title' => $bookedCount . " Patient(s)", // Simple count
                'start' => $sched->date->format('Y-m-d'),
                'backgroundColor' => '#ffffff', // White background
                'borderColor' => '#4e73df', // Blue border
                'textColor' => '#4e73df',
                'extendedProps' => [
                    'doctor_id' => $sched->doctor_id
                ]
            ];
        }

        return response()->json($events);
    }

    public function getDayDetails(Request $request)
    {
        $date = $request->date;
        $doctorId = $request->doctor_id;

        // 1. Get Schedule
        $schedule = Schedule::whereDate('date', $date)
            ->where('doctor_id', $doctorId)
            ->first();

        if (!$schedule) {
            return response()->json(['status' => 'closed', 'message' => 'No schedule set.']);
        }

        // 2. Get Appointments
        $appointments = Appointment::with(['patient', 'service'])
            ->whereDate('appointment_date', $date)
            ->where('doctor_id', $doctorId)
            ->where('status', '!=', 'cancelled')
            ->get();

        // 3. Generate 30-Minute Slots
        $slots = [];
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);
        
        // Define Lunch (12:00 - 1:00)
        $lunchStart = Carbon::parse($date . ' 12:00:00');
        $lunchEnd = Carbon::parse($date . ' 13:00:00');

        while ($startTime < $endTime) {
            $slotStart = $startTime->copy();
            // CHANGED: Add 30 minutes instead of 1 hour
            $slotEnd = $startTime->copy()->addMinutes(30); 
            
            $displayTime = $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A');
            
            // A. Lunch Check (Strict 12:00 - 1:00)
            if ($slotStart >= $lunchStart && $slotStart < $lunchEnd) {
                $slots[] = [
                    'type' => 'lunch',
                    'time_label' => $displayTime,
                    'status_text' => 'LUNCH BREAK',
                    'color' => 'red',
                    'raw_time' => $slotStart->format('H:i')
                ];
                $startTime->addMinutes(30);
                continue;
            }

            // B. Booking Conflict Check
            // We check if this 30-min slot falls INSIDE any existing booking
            $booking = $appointments->filter(function($appt) use ($slotStart, $slotEnd) {
                $apptStart = Carbon::parse($appt->appointment_date->format('Y-m-d') . ' ' . $appt->appointment_time->format('H:i:s'));
                $apptEnd = $apptStart->copy()->addMinutes($appt->duration_minutes);

                // If slot overlaps with booking
                return $slotStart < $apptEnd && $slotEnd > $apptStart;
            })->first();

            if ($booking) {
                if ($booking->status === 'blocked') {
                    $slots[] = [
                        'type' => 'blocked',
                        'time_label' => $displayTime,
                        'status_text' => 'RESERVED (ADMIN)',
                        'color' => 'red',
                        'appt_id' => $booking->id
                    ];
                } else {
                    $patientName = $booking->patient ? $booking->patient->name : 'Guest';
                    $serviceName = $booking->service ? $booking->service->name : 'General';
                    
                    $slots[] = [
                        'type' => 'booked',
                        'time_label' => $displayTime,
                        'details' => strtoupper($serviceName . ' - ' . $patientName),
                        'color' => 'red',
                        'appt_id' => $booking->id
                    ];
                }
            } else {
                $slots[] = [
                    'type' => 'available',
                    'time_label' => $displayTime,
                    'status_text' => 'AVAILABLE',
                    'color' => 'green',
                    'raw_date' => $date,
                    'raw_time' => $slotStart->format('H:i')
                ];
            }

            // Increment loop by 30 mins
            $startTime->addMinutes(30);
        }

        return response()->json(['status' => 'open', 'slots' => $slots]);
    }
}