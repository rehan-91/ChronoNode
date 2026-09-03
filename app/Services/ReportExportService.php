<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportExportService
{
    /**
     * Compiles attendance data in chunks to prevent memory exhaustion,
     * writing directly to a local CSV file.
     */
    public function compileCsv(string $type, string $startDate, string $endDate, ?int $departmentId = null): string
    {
        $filename = sprintf('%s_%s_to_%s_%s.csv', $type, $startDate, $endDate, Str::random(8));
        $directory = 'reports';
        
        if (!Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }
        
        $path = Storage::disk('local')->path($directory . '/' . $filename);
        $handle = fopen($path, 'w');
        
        // Payroll-ready CSV Headers
        fputcsv($handle, [
            'Date', 
            'Employee Code', 
            'Employee Name', 
            'Department', 
            'Check In', 
            'Check Out', 
            'Status', 
            'Working (Mins)', 
            'Late (Mins)', 
            'Early Departure (Mins)', 
            'Overtime (Mins)'
        ]);

        $query = Attendance::with(['user.department'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->whereHas('user', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        // Apply specialized aggregate patterns based on requested report type
        if ($type === 'late_patterns') {
            $query->where('late_minutes', '>', 0);
        } elseif ($type === 'overtime_aggregates') {
            $query->where('overtime_minutes', '>', 0);
        }

        // Process in highly efficient chunks to protect main thread memory
        $query->chunk(500, function ($attendances) use ($handle) {
            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->date,
                    $attendance->user->employee_code ?? 'N/A',
                    $attendance->user->name ?? 'N/A',
                    $attendance->user->department->name ?? 'N/A',
                    $attendance->check_in ?? 'N/A',
                    $attendance->check_out ?? 'N/A',
                    $attendance->status,
                    $attendance->working_minutes,
                    $attendance->late_minutes,
                    $attendance->early_departure_minutes,
                    $attendance->overtime_minutes,
                ]);
            }
        });

        fclose($handle);
        return $filename;
    }
}
