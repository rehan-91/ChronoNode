<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\OfficeLocation;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a realistic testing corporation.
     */
    public function run(): void
    {
        // 1. Create Core Infrastructure (Offices & Departments)
        $hq = OfficeLocation::create([
            'name' => 'Global Headquarters',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'radius' => 100, // Generous radius for HQ
        ]);
        
        $branch = OfficeLocation::create([
            'name' => 'West Coast Branch',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'radius' => 50,
        ]);

        $deptEngineering = Department::create(['name' => 'Engineering']);
        $deptSales = Department::create(['name' => 'Sales']);
        $deptHR = Department::create(['name' => 'Human Resources']);

        // 2. Create Core Administrative Users
        $superAdmin = User::create([
            'name' => 'System Super Admin',
            'email' => 'admin@company.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'employee_code' => 'SA-0001',
            'department_id' => $deptEngineering->id,
            'email_verified_at' => now(),
        ]);

        $hrAdmin = User::create([
            'name' => 'HR Director',
            'email' => 'hr@company.com',
            'password' => Hash::make('password'),
            'role' => 'hr_admin',
            'employee_code' => 'HR-0001',
            'department_id' => $deptHR->id,
            'email_verified_at' => now(),
        ]);

        // 3. Create Managers
        $engineeringManager = User::create([
            'name' => 'Engineering Lead',
            'email' => 'eng.manager@company.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'employee_code' => 'MGR-001',
            'department_id' => $deptEngineering->id,
            'email_verified_at' => now(),
        ]);

        $salesManager = User::create([
            'name' => 'Sales Director',
            'email' => 'sales.manager@company.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'employee_code' => 'MGR-002',
            'department_id' => $deptSales->id,
            'email_verified_at' => now(),
        ]);

        // 4. Create ~150 Active Employees
        $employees = [];
        for ($i = 1; $i <= 145; $i++) {
            $isEngineering = $i % 2 === 0;
            $employees[] = User::create([
                'name' => "Employee $i",
                'email' => "employee{$i}@company.com",
                'password' => Hash::make('password'),
                'role' => 'employee',
                'employee_code' => 'EMP-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                'department_id' => $isEngineering ? $deptEngineering->id : $deptSales->id,
                'manager_id' => $isEngineering ? $engineeringManager->id : $salesManager->id,
                'email_verified_at' => now(),
            ]);
        }

        // 5. Generate Dense Monthly Historical Dataset
        // Let's seed the last 30 days of attendance
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        // Sample a subset of employees to speed up seeding (e.g., 20 employees)
        $subset = array_slice($employees, 0, 20);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekend()) {
                continue; // Skip weekends for standard shifts
            }

            foreach ($subset as $employee) {
                // Randomize attendance states
                $rand = rand(1, 100);
                
                $checkIn = $date->copy()->setTime(9, rand(0, 15)); // Standard 9:00 - 9:15 AM
                $checkOut = $date->copy()->setTime(17, rand(0, 30)); // Standard 5:00 - 5:30 PM
                $status = 'present';
                $lateMinutes = 0;

                if ($rand <= 5) {
                    // 5% chance absent
                    continue; 
                } elseif ($rand > 5 && $rand <= 15) {
                    // 10% chance late
                    $checkIn = $date->copy()->setTime(9, rand(30, 59));
                    $status = 'late';
                    $lateMinutes = $checkIn->diffInMinutes($date->copy()->setTime(9, 0));
                }

                Attendance::create([
                    'user_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => $status,
                    'check_in_location_id' => $hq->id,
                    'check_out_location_id' => $hq->id,
                    'working_minutes' => $checkIn->diffInMinutes($checkOut),
                    'late_minutes' => $lateMinutes,
                ]);
            }
        }
    }
}
