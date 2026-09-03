<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function __construct(
        private readonly ShiftService $shiftService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Shift::class);
        
        $shifts = Shift::orderBy('start_time')->get();

        return Inertia::render('Shifts/Index', [
            'shifts' => $shifts,
        ]);
    }

    public function store(StoreShiftRequest $request): RedirectResponse
    {
        $this->shiftService->createShift($request->validated());

        return back()->with('success', 'Shift created successfully.');
    }
}
