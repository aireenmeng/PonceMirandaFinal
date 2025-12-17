<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduleService
{
    /**
     * Get the doctor's schedule for a specific date, including virtual schedule fallback.
     *
     * @param string $date
     * @param int $doctorId
     * @return \App\Models\Schedule|null
     */
    public function getDoctorSchedule(string $date, int $doctorId): ?Schedule
    {
        $schedule = Schedule::whereDate('date', $date)->where('doctor_id', $doctorId)->first();

        // Implement virtual schedule fallback for Mon-Sat 09:00-17:00
        if (!$schedule) {
            $carbonDate = Carbon::parse($date);
            if (!$carbonDate->isSunday()) {
                // Create a transient Schedule object (not saved to DB)
                $schedule = new Schedule([
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'max_appointments' => 20 // Default capacity
                ]);
            }
        }
        return $schedule;
    }

    /**
     * Generates a list of time slots for a doctor on a given date.
     *
     * @param string $date
     * @param int $doctorId
     * @param int $durationMinutes - Duration of the slot needed
     * @param int|null $excludeAppointmentId - Appointment ID to exclude from conflict checks (for editing)
     * @return array
     */
    public function generateTimeSlots(string $date, int $doctorId, int $durationMinutes, ?int $excludeAppointmentId = null): array
    {
        $schedule = $this->getDoctorSchedule($date, $doctorId);

        $rawAppointments = collect(); // Initialize an empty collection

        // Get Bookings (eager load patient if necessary)
        $bookingsQuery = Appointment::whereDate('appointment_date', $date)
            ->where('doctor_id', $doctorId)
            ->where('status', '!=', 'cancelled');
        
        if ($excludeAppointmentId) {
            $bookingsQuery->where('id', '!=', $excludeAppointmentId);
        }

        $bookings = $bookingsQuery->get(); // Get the bookings first

        // Prepare raw appointments list for the frontend (for display purposes)
        // EXCLUDE 'blocked' slots from the patient list
        $isDoctorOrAdmin = Auth::check() && (Auth::user()->role === 'doctor' || Auth::user()->role === 'admin');
        $rawAppointments = $bookings->filter(function($appt) {
            return $appt->status !== 'blocked';
        })->map(function($appt) use ($isDoctorOrAdmin) {
            return [
                'id' => $appt->id,
                'time' => Carbon::parse($appt->appointment_time)->format('h:i A'),
                'patient_name' => $isDoctorOrAdmin ? ($appt->patient->name ?? 'Unknown') : 'Booked',
                'service' => $appt->service->name ?? '-'
            ];
        })->values(); // Reset keys after filter

        // Handle cases where no schedule or day off
        if (!$schedule || (Carbon::parse($schedule->start_time)->format('H:i') === '00:00' && Carbon::parse($schedule->end_time)->format('H:i') === '00:00')) {
            return [
                'status' => 'closed',
                'message' => 'Doctor not available',
                'slots' => [],
                'appointments' => $rawAppointments->toArray(),
                'max_appointments_reached' => false
            ];
        }

        // --- Capacity Check ---
        $maxAppointments = $schedule->max_appointments ?? 20;
        $confirmedBookingsCount = $bookings->filter(function($appt) {
            return $appt->status === 'confirmed' || $appt->status === 'pending';
        })->count();
        $capacityReached = $confirmedBookingsCount >= $maxAppointments;


        // Generate Slots
        $slots = [];
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);
        
        $lunchStart = Carbon::parse($date . ' 12:00:00');
        $lunchEnd = Carbon::parse($date . ' 13:00:00');

        while ($startTime < $endTime) {
            $slotStart = $startTime->copy();
            $slotEnd = $startTime->copy()->addMinutes(30);
            $label = $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A');

            $slotStatus = 'available';
            $slotDetails = 'Available';
            $apptId = null;

            // CHECK LUNCH
            if ($slotStart->betweenIncluded($lunchStart, $lunchEnd->copy()->subMinute())) {
                $slotStatus = 'lunch';
                $slotDetails = 'Lunch Break';
            }
            
            // CHECK EXISTING BOOKINGS
            foreach ($bookings as $appt) {
                $apptStart = Carbon::parse($date . ' ' . Carbon::parse($appt->appointment_time)->format('H:i:s'));
                $apptEnd = $apptStart->copy()->addMinutes((int)$appt->duration_minutes); // Explicitly cast to int

                if ($slotStart >= $apptStart && $slotStart < $apptEnd) {
                    // Check specifically for BLOCKED status
                    if ($appt->status === 'blocked') {
                        $slotStatus = 'blocked';
                        $slotDetails = 'Blocked';
                    } else {
                        $slotStatus = 'booked';
                        $slotDetails = $isDoctorOrAdmin ? ($appt->patient->name ?? 'Unknown Patient') : 'Booked';
                    }
                    $apptId = $appt->id; 
                    break; 
                }
            }

            // CHECK CAPACITY (Only if slot is still available after other checks)
            if ($slotStatus === 'available' && $capacityReached) {
                $slotStatus = 'full';
                $slotDetails = 'Fully Booked';
            }

            $slots[] = [
                'time_label' => $label,
                'raw_time' => $slotStart->format('H:i'),
                'raw_date' => $date,
                'type' => $slotStatus,
                'details' => $slotDetails,
                'appt_id' => $apptId
            ];

            $startTime->addMinutes(30);
        }

        // Filter out past slots if the date is today
        if (Carbon::parse($date)->isToday()) {
            $now = Carbon::now();
            $filteredSlots = [];
            foreach ($slots as $slot) {
                $slotDateTime = Carbon::parse($date . ' ' . $slot['raw_time']);
                if ($slotDateTime->gte($now)) {
                    $filteredSlots[] = $slot;
                }
            }
            $slots = $filteredSlots;
        }

        return [
            'status' => 'open',
            'slots' => $slots,
            'appointments' => $rawAppointments->toArray(),
            'max_appointments_reached' => $capacityReached
        ];
    }
}