<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Leave\StoreLeaveRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly LeaveService $leaveService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $leaves = LeaveRequest::where('user_id', $request->user()->id)
            ->with('reviewer:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Leaves/Index', [
            'leaves' => $leaves,
        ]);
    }

    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $this->leaveService->submitRequest($request->user(), $request->validated());

        return back()->with('success', 'Leave request submitted successfully.');
    }
}
