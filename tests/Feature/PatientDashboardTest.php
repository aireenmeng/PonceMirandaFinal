<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_correct_upcoming_appointment()
    {
        // 1. Setup Data
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        $service = Service::create([
            'name' => 'General Checkup',
            'description' => 'Routine checkup',
            'price' => 50.00,
            'duration_minutes' => 30,
            'status' => 'active'
        ]);

        // Freeze time to a specific known point: 2025-12-16 10:00:00
        $now = Carbon::create(2025, 12, 16, 10, 0, 0);
        Carbon::setTestNow($now);

        // Appointment 1: Past Date (Yesterday)
        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => $now->copy()->subDay()->toDateString(),
            'appointment_time' => '10:00:00',
            'status' => 'confirmed',
        ]);

        // Appointment 2: Today, Past Time (08:00)
        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => $now->toDateString(),
            'appointment_time' => '08:00:00',
            'status' => 'confirmed',
        ]);

        // Appointment 3: Today, Future Time (14:00) - TARGET
        $targetAppointment = Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => $now->toDateString(),
            'appointment_time' => '14:00:00',
            'status' => 'confirmed',
        ]);

        // Appointment 4: Tomorrow (08:00)
        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => $now->copy()->addDay()->toDateString(),
            'appointment_time' => '08:00:00',
            'status' => 'confirmed',
        ]);

        // 2. Act
        $response = $this->actingAs($patient)->get(route('dashboard'));

        // 3. Assert
        $response->assertStatus(200);
        
        // Assert that the view has the 'upcoming' variable
        $response->assertViewHas('upcoming');
        
        // Assert that 'upcoming' matches our target appointment
        $upcoming = $response->viewData('upcoming');
        $this->assertNotNull($upcoming, 'Upcoming appointment should not be null.');
        $this->assertEquals($targetAppointment->id, $upcoming->id, 'The dashboard should display Today 14:00 as up next.');
    }
}
