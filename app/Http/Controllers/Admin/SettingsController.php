<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLoggerService;
use App\Services\SettingsRegistryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsRegistryService $registry,
        private readonly AuditLoggerService $auditLogger
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()->role->value === 'super_admin', 403);

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $this->registry->getAllDefaults(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role->value === 'super_admin', 403);

        $validated = $request->validate([
            'timezone' => ['required', 'string', 'timezone'],
            'gps_accuracy_threshold_meters' => ['required', 'integer', 'min:10', 'max:2000'],
            'default_geofence_radius_meters' => ['required', 'integer', 'min:10', 'max:1000'],
            'late_arrival_buffer_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'auto_checkout_limit_hours' => ['required', 'integer', 'min:8', 'max:24'],
        ]);

        $oldSettings = $this->registry->getAllDefaults();

        foreach ($validated as $key => $value) {
            $this->registry->set($key, $value);
        }

        $this->auditLogger->log(
            'update_global_settings',
            'SystemSetting',
            0,
            $oldSettings,
            $validated
        );

        return back()->with('success', 'Global application settings updated and registry cache cleared.');
    }
}
