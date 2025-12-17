<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\ScheduleService; 

/**
 * Handles JSON data requests for the FullCalendar implementation.
 * 
 * Provides endpoints for fetching events (appointments/availability) and 
 * detailed slot information for specific dates.
 */
class CalendarController extends Controller
{
    protected $scheduleService; 

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Retrieve calendar events for a specific date range.
     * 
     * Handles two distinct modes:
     * 1. Aggregate View (All Doctors): Returns a summary of appointment counts per day.
     * 2. Single Doctor View: Returns specific availability blocks, day-offs, and appointment counts.
     *    Uses the ScheduleService to generate "virtual" schedules if explicit ones don't exist.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        $doctorId = $request->doctor_id;

        $events = [];

        // Mode 1: Aggregate View for Admins (Summary of all activity)
        if ($doctorId === 'all') {
            $counts = Appointment::whereBetween('appointment_date', [$start, $end])
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'blocked')
                ->selectRaw('appointment_date, count(*) as count')
                ->groupBy('appointment_date')
                ->get();

            foreach ($counts as $row) {
                $events[] = [
                    'title' => $row->count . " Patient(s)",
                    'start' => $row->appointment_date, 
                    'backgroundColor' => '#4e73df',
                    'borderColor' => '#4e73df',
                    'textColor' => '#ffffff',
                ];
            }
            return response()->json($events);
        }

        // Mode 2: Single Doctor View
        // Retrieve explicit schedule overrides for the requested period
        $savedSchedules = Schedule::whereBetween('date', [$start, $end])
            ->where('doctor_id', $doctorId)
            ->get()
            ->keyBy(function($item) { return $item->date->format('Y-m-d'); });

        $isPatient = Auth::check() && Auth::user()->role === 'patient';

        // Iterate through each day to build the calendar events
        $curr = $start->copy();
        while ($curr <= $end) {
            $dateStr = $curr->format('Y-m-d');
            
            // Retrieve the effective schedule (saved or default virtual)
            $actualSchedule = $this->scheduleService->getDoctorSchedule($dateStr, $doctorId);

            if (!$actualSchedule) { 
                // No schedule available (e.g., Sunday with no override)
                $curr->addDay();
                continue; 
            }

            $startT = Carbon::parse($actualSchedule->start_time);
            $endT = Carbon::parse($actualSchedule->end_time);
            $isDayOff = $startT->format('H:i') === '00:00' && $endT->format('H:i') === '00:00';

            $title = '';
            $color = '';
            $textColor = '';
            $id = $savedSchedules->get($dateStr)->id ?? 'virtual-' . $dateStr; 

            if ($isDayOff) {
                $title = "DAY OFF";
                $color = '#e74a3b';
                $textColor = '#ffffff';
            } elseif ($isPatient) {
                $title = "Open";
                $color = '#1cc88a';
                $textColor = '#ffffff';
            } else {
                $count = Appointment::whereDate('appointment_date', $dateStr)
                    ->where('doctor_id', $doctorId)
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'blocked')
                    ->count();
                
                // For admin/doctor view, only show the event if there are patients or it's a specific status
                if ($count == 0 && !$isDayOff) {
                    $curr->addDay();
                    continue;
                }

                $title = "$count Patient(s)";
                $color = '#ffffff';
                $textColor = '#4e73df';
            }

            $events[] = [
                'id' => $id,
                'title' => $title,
                'start' => $dateStr,
                'backgroundColor' => $color,
                'borderColor' => $isPatient ? $color : '#4e73df',
                'textColor' => $textColor,
                'extendedProps' => [
                    'start_time' => $actualSchedule->start_time,
                    'end_time' => $actualSchedule->end_time,
                ]
            ];
            $curr->addDay();
        }

        return response()->json($events);
    }

    /**
     * Retrieve detailed information for a specific day.
     * 
     * Returns a list of appointments (for admins) or a list of available
     * time slots (for booking).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDayDetails(Request $request)
    {
        $date = $request->date;
        $doctorId = $request->doctor_id;

        // Detail View for "All Doctors": List all appointments for the day
        if ($doctorId === 'all') {
            $appointments = Appointment::with(['patient', 'doctor', 'service'])
                ->whereDate('appointment_date', $date)
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'blocked')
                ->orderBy('appointment_time')
                ->get()
                ->map(function($appt) {
                    return [
                        'id' => $appt->id,
                        'time' => Carbon::parse($appt->appointment_time)->format('h:i A'),
                        'doctor_name' => $appt->doctor->name,
                        'patient_name' => $appt->patient->name ?? 'Unknown',
                        'service_name' => $appt->service->name,
                        'status' => $appt->status
                    ];
                });

            return response()->json([
                'type' => 'aggregate',
                'appointments' => $appointments
            ]);
        }

        // Detail View for Single Doctor: Generate Time Slots
        $durationMinutes = $request->duration_minutes ?? 30; 
        $excludeAppointmentId = $request->exclude_appointment_id ?? null;

        $slotsData = $this->scheduleService->generateTimeSlots($date, $doctorId, (int)$durationMinutes, (int)$excludeAppointmentId);

        return response()->json($slotsData);
    }
}