<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Holiday\StoreHolidayRequest;
use App\Models\Holiday;
use App\Services\HolidayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HolidayController extends Controller
{
    public function __construct(
        private readonly HolidayService $holidayService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Holiday::class);

        $year = $request->input('year', date('Y'));
        
        $holidays = Holiday::whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        return Inertia::render('Holidays/Index', [
            'holidays' => $holidays,
            'filters' => ['year' => $year]
        ]);
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        $this->holidayService->createHoliday($request->validated());

        return back()->with('success', 'Holiday added successfully.');
    }
}
