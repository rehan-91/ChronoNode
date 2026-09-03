<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Attendance\ReviewCorrectionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\ReviewCorrectionRequest;
use App\Models\AttendanceCorrection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceCorrectionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AttendanceCorrection::class);

        $query = AttendanceCorrection::with([
            'user:id,name,employee_code,manager_id',
            'attendance:id,date,check_in,check_out'
        ]);

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

        $corrections = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Corrections/Index', [
            'corrections' => $corrections,
            'filters' => $request->only(['status'])
        ]);
    }

    public function review(ReviewCorrectionRequest $request, AttendanceCorrection $correction, ReviewCorrectionAction $action): RedirectResponse
    {
        $action->execute(
            $correction,
            $request->input('status'),
            $request->input('reason'),
            $request->input('edited_check_in'),
            $request->input('edited_check_out')
        );

        return back()->with('success', 'Correction request processed successfully.');
    }
}
