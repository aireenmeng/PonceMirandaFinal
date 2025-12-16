<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_cannot_book_overlapping_appointments()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Setup
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor1 = User::factory()->create(['role' => 'doctor']);
        $doctor2 = User::factory()->create(['role' => 'doctor']);
        
        $service = Service::create([
            'name' => 'General Checkup',
            'description' => 'Routine checkup',
            'price' => 50.00,
            'duration_minutes' => 60, // 1 hour duration
            'status' => 'active'
        ]);

        $appointmentDate = Carbon::tomorrow()->toDateString();
        $appointmentTime = '10:00:00';

        // 2. Create first appointment (Confirmed or Pending)
        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor1->id,
            'service_id' => $service->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'duration_minutes' => 60,
            'status' => 'confirmed',
        ]);

        // 3. Attempt to book second appointment at the same time with a different doctor
        $response = $this->actingAs($patient)->post(route('patient.booking.store'), [
            'service_id' => $service->id,
            'doctor_id' => $doctor2->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
        ]);

        // 4. Assert
        // Expecting a validation error, but currently it will succeed (redirect)
        // So we assert checking if we get session errors
        $response->assertSessionHasErrors();
    }

    public function test_patient_cannot_book_partial_overlap()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Setup
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        $service = Service::create([
            'name' => 'Cleaning',
            'price' => 50,
            'duration_minutes' => 60,
            'status' => 'active'
        ]);

        $date = Carbon::tomorrow()->toDateString();

        // Existing: 10:00 - 11:00
        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => $date,
            'appointment_time' => '10:00:00',
            'duration_minutes' => 60,
            'status' => 'confirmed',
        ]);

        // Try: 10:30 - 11:30
        $response = $this->actingAs($patient)->post(route('patient.booking.store'), [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'appointment_time' => '10:30',
        ]);

        $response->assertSessionHasErrors();
    }
}
