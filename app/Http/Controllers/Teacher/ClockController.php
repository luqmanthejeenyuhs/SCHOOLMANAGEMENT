<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;

class ClockController extends Controller
{
    public function index()
    {
        $employee = Employee::where('user_id', auth()->id())->first();

        $today = $employee
            ? StaffAttendance::where('employee_id', $employee->id)
                ->whereDate('date', today())
                ->first()
            : null;

        return view('teacher.clock.index', compact('employee', 'today'));
    }

    public function store(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->first();

        abort_unless($employee, 404, 'No staff record is linked to your account yet. Ask an admin to link it.');

        $record = StaffAttendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => today()->toDateString(),
        ]);

        if (! $record->exists) {
            $record->clock_in = now()->format('H:i:s');
            $record->status = 'present';
            $record->save();
            $message = 'Clocked in at ' . now()->format('g:i A') . '.';
        } elseif (! $record->clock_out) {
            $record->clock_out = now()->format('H:i:s');
            $record->save();
            $message = 'Clocked out at ' . now()->format('g:i A') . '.';
        } else {
            $message = 'You have already clocked in and out today.';
        }

        return back()->with('success', $message);
    }
}
