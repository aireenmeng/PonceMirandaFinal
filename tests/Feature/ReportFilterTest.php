<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_filter_respects_date_range_for_stats()
    {
        $this->withoutMiddleware();

        // 1. Setup Data
        $admin = User::factory()->create(['role' => 'admin']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        $patient = User::factory()->create(['role' => 'patient']);
        $service = Service::create(['name' => 'Test Service', 'price' => 100, 'duration_minutes' => 60, 'status' => 'active']);

        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2025, 1, 31);

        // Appointment INSIDE range
        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2025-01-15',
            'appointment_time' => '10:00:00',
            'status' => 'completed',
            'price' => 100,
            'duration_minutes' => 60
        ]);

        // Appointment OUTSIDE range (Next Month)
        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2025-02-15',
            'appointment_time' => '10:00:00',
            'status' => 'completed',
            'price' => 100,
             'duration_minutes' => 60
        ]);

        // 2. Act
        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31'
        ]));

        // 3. Assert
        $response->assertStatus(200);

        // Check Ledger (completedAppts) - Should have 1
        $completedAppts = $response->viewData('completedAppts');
        $this->assertEquals(1, $completedAppts->count(), 'Ledger should have 1 record');

        // Check Service Stats - Should have count 1, revenue 100
        $serviceStats = $response->viewData('serviceStats');
        $this->assertTrue($serviceStats->contains('name', 'Test Service'));
        $stat = $serviceStats->firstWhere('name', 'Test Service');
        $this->assertEquals(1, $stat->count, 'Service stats count should be 1');
        $this->assertEquals(100, $stat->revenue, 'Service stats revenue should be 100');

        // Check Doctor Stats
        $doctorStats = $response->viewData('doctorStats');
        $docStat = $doctorStats->firstWhere('name', $doctor->name);
        $this->assertEquals(1, $docStat->count, 'Doctor stats count should be 1');
    }

    public function test_reports_filter_includes_end_date_fully()
    {
        $this->withoutMiddleware();
        
        $doctor = User::factory()->create(['role' => 'doctor']);
        $patient = User::factory()->create(['role' => 'patient']);
        $service = Service::first();

        // Appointment on the VERY END of the filtering day
        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2025-01-31',
            'appointment_time' => '23:59:00',
            'status' => 'completed',
            'price' => 200,
            'duration_minutes' => 60
        ]);

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.reports.index', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31'
        ]));

        $completedAppts = $response->viewData('completedAppts');
        $this->assertEquals(1, $completedAppts->count(), 'Should include appointment on the last day.');
    }
}
