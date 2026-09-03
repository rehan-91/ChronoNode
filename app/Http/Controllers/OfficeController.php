<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Office\StoreOfficeRequest;
use App\Http\Requests\Office\UpdateOfficeRequest;
use App\Models\Office;
use App\Services\OfficeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfficeController extends Controller
{
    public function __construct(
        private readonly OfficeService $officeService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Office::class);

        $query = Office::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
        }

        $query->withCount(['users' => function ($q) {
            $q->where('status', 'active');
        }]);

        $offices = $query->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Offices/Index', [
            'offices' => $offices,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreOfficeRequest $request): RedirectResponse
    {
        $this->officeService->createOffice($request->validated());

        return redirect()->route('offices.index')->with('success', 'Office created successfully.');
    }

    public function update(UpdateOfficeRequest $request, Office $office): RedirectResponse
    {
        $this->officeService->updateOffice($office, $request->validated());

        return redirect()->route('offices.index')->with('success', 'Office updated successfully.');
    }

    public function toggleStatus(Office $office): RedirectResponse
    {
        $this->authorize('update', $office);
        
        $this->officeService->toggleStatus($office);

        return back()->with('success', 'Office status updated successfully.');
    }
}
