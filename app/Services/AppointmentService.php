<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Checks for conflicts with doctor's schedule, max appointments, and overlapping appointments.
     *
     * @param array $appointmentData Must contain:
     *   'doctor_id', 'appointment_date', 'appointment_time', 'duration_minutes'.
     *   Optional: 'user_id' (to check patient conflicts).
     * @param int|null $excludeAppointmentId Optional ID of an appointment to exclude from overlap checks.
     * @return array An array of error messages, or an empty array if no conflicts.
     */
    public function checkConflicts(array $appointmentData, ?int $excludeAppointmentId = null): array
    {
        $errors = [];

        $doctorId = $appointmentData['doctor_id'];
        $requestedDate = Carbon::parse($appointmentData['appointment_date']);
        $requestedStartTime = Carbon::parse($appointmentData['appointment_time']);
        $durationMinutes = (int) $appointmentData['duration_minutes'];
        $requestedEndTime = $requestedStartTime->copy()->addMinutes($durationMinutes);

        // --- 1. Check Doctor's Schedule ---
        $schedule = Schedule::where('doctor_id', $doctorId)
            ->where('date', $requestedDate->toDateString())
            ->first();

        // Implement virtual schedule for Mon-Sat 09:00-17:00 if no explicit schedule exists
        if (!$schedule) {
            if ($requestedDate->isSunday()) {
                $errors[] = 'Doctor is not available on Sundays.';
                return $errors;
            }
            // Use virtual schedule: 09:00-17:00, max_appointments: 20
            $schedule = new Schedule([
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'max_appointments' => 20
            ]);
        }

        $scheduleStartTime = Carbon::parse($schedule->start_time);
        $scheduleEndTime = Carbon::parse($schedule->end_time);

        // Check if doctor is on day off (00:00 - 00:00)
        if ($scheduleStartTime->format('H:i') === '00:00' && $scheduleEndTime->format('H:i') === '00:00') {
            $errors[] = 'Doctor is on a day off.';
            return $errors; // No need to check further if day off
        }

        // Check if appointment falls within doctor's working hours
        if ($requestedStartTime->lt($scheduleStartTime) || $requestedEndTime->gt($scheduleEndTime)) {
            $errors[] = 'Appointment is outside doctor\'s working hours (' . $scheduleStartTime->format('h:i A') . ' - ' . $scheduleEndTime->format('h:i A') . ').';
        }

        // --- 2. Check Max Appointments ---
        // Exclude the appointment being updated from the count
        $query = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $requestedDate->toDateString())
            ->where('status', '!=', 'cancelled');

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }
        $currentAppointmentsCount = $query->count();

        if ($currentAppointmentsCount >= $schedule->max_appointments) {
            $errors[] = 'Doctor\'s schedule is full for this day.';
        }

        // --- 3. Check for Overlapping Appointments (Doctor) ---
        $overlapQuery = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $requestedDate->toDateString())
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($requestedStartTime, $requestedEndTime) {
                $query->where(function($q) use ($requestedStartTime, $requestedEndTime) {
                    $q->where('appointment_time', '<', $requestedEndTime->format('H:i:s'))
                      ->whereRaw('ADDTIME(appointment_time, SEC_TO_TIME(duration_minutes * 60)) > ?', [$requestedStartTime->format('H:i:s')]);
                });
            });

        if ($excludeAppointmentId) {
            $overlapQuery->where('id', '!=', $excludeAppointmentId);
        }
        $overlap = $overlapQuery->count();

        if ($overlap > 0) {
            $errors[] = 'Appointment time overlaps with an existing appointment for this doctor.';
        }

        // --- 4. Check for Overlapping Appointments (Patient) ---
        if (isset($appointmentData['user_id']) && $appointmentData['user_id']) {
            $patientOverlapQuery = Appointment::where('user_id', $appointmentData['user_id'])
                ->where('appointment_date', $requestedDate->toDateString())
                ->where('status', '!=', 'cancelled')
                ->where(function($query) use ($requestedStartTime, $requestedEndTime) {
                    $query->where(function($q) use ($requestedStartTime, $requestedEndTime) {
                        $q->where('appointment_time', '<', $requestedEndTime->format('H:i:s'))
                          ->whereRaw('ADDTIME(appointment_time, SEC_TO_TIME(duration_minutes * 60)) > ?', [$requestedStartTime->format('H:i:s')]);
                    });
                });

            if ($excludeAppointmentId) {
                $patientOverlapQuery->where('id', '!=', $excludeAppointmentId);
            }
            
            // The patient might be booking with a DIFFERENT doctor, so we don't filter by doctor_id here.
            // We just want to know if the PATIENT is busy.
            
            $patientOverlap = $patientOverlapQuery->count();

            if ($patientOverlap > 0) {
                $errors[] = 'Patient already has an appointment scheduled during this time.';
            }
        }

        return $errors;
    }
}
