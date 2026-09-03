<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\ReviewLeaveRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveReviewController extends Controller
{
    public function __construct(
        private readonly LeaveService $leaveService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LeaveRequest::class); // Assuming policy handles HR vs Manager viewing

        $query = LeaveRequest::with(['user:id,name,employee_code,manager_id', 'reviewer:id,name']);

        if ($request->user()->role->value === 'manager') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('manager_id', $request->user()->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', 'pending');
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Leaves/Index', [
            'leaves' => $leaves,
            'filters' => $request->only(['status'])
        ]);
    }

    public function update(ReviewLeaveRequest $request, LeaveRequest $leave): RedirectResponse
    {
        $this->leaveService->reviewRequest(
            $leave, 
            $request->input('status'), 
            $request->input('reviewer_reason'),
            $request->user()
        );

        return back()->with('success', 'Leave request ' . $request->input('status') . '.');
    }
}
