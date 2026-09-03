<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\GenerateReportRequest;
use App\Jobs\CompileReportJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        // Enforce basic clearance
        abort_unless(in_array($request->user()->role->value, ['super_admin', 'hr_admin']), 403);

        $directory = 'reports';
        $files = [];

        if (Storage::disk('local')->exists($directory)) {
            $rawFiles = Storage::disk('local')->files($directory);
            
            foreach ($rawFiles as $file) {
                // Prevent returning hidden files
                if (basename($file)[0] === '.') continue;

                $files[] = [
                    'name' => basename($file),
                    'size' => round(Storage::disk('local')->size($file) / 1024, 2) . ' KB',
                    'last_modified' => date('Y-m-d H:i:s', Storage::disk('local')->lastModified($file)),
                ];
            }
        }

        // Sort descending by modified date
        usort($files, fn($a, $b) => $b['last_modified'] <=> $a['last_modified']);

        return Inertia::render('Admin/Reports/Index', [
            'files' => $files,
        ]);
    }

    public function generate(GenerateReportRequest $request): RedirectResponse
    {
        // Pushes to Laravel queue to prevent UI blocking
        CompileReportJob::dispatch($request->user(), $request->validated());

        return back()->with('success', 'Report generation queued. It will appear in the downloads list shortly once the background job finishes.');
    }

    public function download(Request $request, string $filename): StreamedResponse|RedirectResponse
    {
        abort_unless(in_array($request->user()->role->value, ['super_admin', 'hr_admin']), 403);

        $path = 'reports/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'File not found or expired.');
        }

        return Storage::disk('local')->download($path);
    }
    
    public function destroy(Request $request, string $filename): RedirectResponse
    {
        abort_unless(in_array($request->user()->role->value, ['super_admin', 'hr_admin']), 403);

        $path = 'reports/' . $filename;
        
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
        
        return back()->with('success', 'Report file removed securely.');
    }
}
