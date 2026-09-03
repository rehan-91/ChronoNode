<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\OfficeLocation;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceConstraintsTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private OfficeLocation $hq;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employee = User::factory()->create(['role' => 'employee']);
        
        $this->hq = OfficeLocation::create([
            'name' => 'HQ',
            'latitude' => 40.0000,
            'longitude' => -74.0000,
            'radius' => 100,
        ]);
    }

    public function test_blocks_duplicate_check_ins_for_same_day(): void
    {
        // Simulate existing check-in
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'check_in' => Carbon::now(),
            'status' => 'present'
        ]);

        // Attempt another check in
        $response = $this->actingAs($this->employee)
            ->postJson('/api/attendance/check-in', [
                'latitude' => 40.0000,
                'longitude' => -74.0000,
            ]);

        $response->assertStatus(400); // Or whatever constraint exception is mapped (422/400)
    }

    public function test_enforces_geofence_boundary_rejection(): void
    {
        // Ping from far outside the 100m radius
        $response = $this->actingAs($this->employee)
            ->postJson('/api/attendance/check-in', [
                'latitude' => 41.0000, // Way off
                'longitude' => -75.0000,
            ]);

        $response->assertStatus(403); // Forbidden due to boundary constraint
    }
    
    public function test_authentication_required_for_tracking(): void
    {
        $response = $this->postJson('/api/attendance/check-in', [
            'latitude' => 40.0000,
            'longitude' => -74.0000,
        ]);

        $response->assertStatus(401);
    }
}
