<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Attendance\CheckInAction;
use App\Actions\Attendance\CheckOutAction;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Http\Requests\Attendance\CheckOutRequest;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    public function checkIn(CheckInRequest $request, CheckInAction $action): RedirectResponse
    {
        $action->execute(
            $request->user(),
            (float) $request->input('latitude'),
            (float) $request->input('longitude'),
            (int) $request->input('accuracy')
        );

        return back()->with('success', 'Successfully checked in.');
    }

    public function checkOut(CheckOutRequest $request, CheckOutAction $action): RedirectResponse
    {
        $action->execute(
            $request->user(),
            (float) $request->input('latitude'),
            (float) $request->input('longitude'),
            (int) $request->input('accuracy')
        );

        return back()->with('success', 'Successfully checked out.');
    }
}
