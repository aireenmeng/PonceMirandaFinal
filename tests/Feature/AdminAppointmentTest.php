<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_book_overlapping_appointments_for_patient()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Setup
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor1 = User::factory()->create(['role' => 'doctor']);
        $doctor2 = User::factory()->create(['role' => 'doctor']);
        
        $service = Service::create([
            'name' => 'General Checkup',
            'description' => 'Routine checkup',
            'price' => 50.00,
            'duration_minutes' => 60,
            'status' => 'active'
        ]);

        $appointmentDate = Carbon::tomorrow()->toDateString();
        $appointmentTime = '10:00:00';

        // 2. Create first confirmed appointment
        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor1->id,
            'service_id' => $service->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'duration_minutes' => 60,
            'status' => 'confirmed',
        ]);

        // 3. Attempt to book second appointment for same patient, same time, different doctor
        $response = $this->actingAs($admin)->post(route('admin.appointments.store'), [
            'patient_type' => 'existing',
            'user_id' => $patient->id,
            'doctor_id' => $doctor2->id,
            'service_id' => $service->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'duration_minutes' => 60,
        ]);

        // 4. Assert
        // Expecting a validation error or a redirect with error message
        // Currently it likely redirects with success (status 302 to index)
        // If it fails (as expected with current bug), it has no errors.
        
        // We assert that session has errors to prove the bug exists (the test will fail if bug exists)
        $response->assertSessionHasErrors();
    }

    public function test_admin_cannot_restore_past_appointment()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Setup
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        $service = Service::first() ?? Service::create(['name' => 'Test', 'price' => 10, 'duration_minutes' => 30, 'status' => 'active']);

        $appointment = Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => Carbon::yesterday()->toDateString(),
            'appointment_time' => '10:00:00',
            'duration_minutes' => 30,
            'status' => 'cancelled',
            'cancellation_reason' => 'Test',
            'cancelled_by' => $admin->id
        ]);

        // 2. Act
        $response = $this->actingAs($admin)->post(route('admin.appointments.restore', $appointment->id));

        // 3. Assert
        $response->assertSessionHas('error'); // Check for flash error
        $this->assertEquals('cancelled', $appointment->fresh()->status); // Status should NOT change
    }

    public function test_admin_appointments_default_sort_order()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $admin = User::factory()->create(['role' => 'admin']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        $service = Service::first() ?? Service::create(['name' => 'Test', 'price' => 10, 'duration_minutes' => 30, 'status' => 'active']);
        $patient = User::factory()->create(['role' => 'patient']);

        // A: Cancelled Old (Yesterday)
        $apptA = Appointment::create([
            'user_id' => $patient->id, 'doctor_id' => $doctor->id, 'service_id' => $service->id,
            'appointment_date' => Carbon::tomorrow()->toDateString(), 'appointment_time' => '10:00:00',
            'duration_minutes' => 30, 'status' => 'cancelled', 'cancellation_reason' => 'Test', 'cancelled_by' => $admin->id,
            'cancelled_at' => Carbon::yesterday()
        ]);

        // B: Cancelled New (Today)
        $apptB = Appointment::create([
            'user_id' => $patient->id, 'doctor_id' => $doctor->id, 'service_id' => $service->id,
            'appointment_date' => Carbon::yesterday()->toDateString(), 'appointment_time' => '10:00:00',
            'duration_minutes' => 30, 'status' => 'cancelled', 'cancellation_reason' => 'Test', 'cancelled_by' => $admin->id,
            'cancelled_at' => Carbon::now()
        ]);

        // Act
        $response = $this->actingAs($admin)->get(route('admin.appointments.index', ['status' => 'cancelled']));

        // Assert
        $data = $response->viewData('appointments');
        // B should be first (Newer cancellation)
        $this->assertEquals($apptB->id, $data->first()->id); 
        $this->assertEquals($apptA->id, $data->last()->id);
    }
}
