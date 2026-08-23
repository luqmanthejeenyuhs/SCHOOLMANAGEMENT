<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\StaffAttendance;
use App\Services\StaffAttendanceService;
use Illuminate\Http\Request;

class ClockController extends Controller
{
    public function __construct(protected StaffAttendanceService $attendance)
    {
    }

    public function index()
    {
        $employee = Employee::where('user_id', auth()->id())->first();

        $today = $employee
            ? StaffAttendance::where('employee_id', $employee->id)
                ->whereDate('date', today())
                ->first()
            : null;

        $school = $employee?->school;

        return view('teacher.clock.index', compact('employee', 'today', 'school'));
    }

    public function store(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->first();

        abort_unless($employee, 404, 'No staff record is linked to your account yet. Ask an admin to link it.');

        $data = $request->validate([
            'action' => 'required|in:clock_in,clock_out',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        try {
            $record = $data['action'] === 'clock_in'
                ? $this->attendance->clockIn($employee, $data['lat'] ?? null, $data['lng'] ?? null)
                : $this->attendance->clockOut($employee, $data['lat'] ?? null, $data['lng'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $action = $data['action'] === 'clock_in' ? 'Clocked in' : 'Clocked out';
        $time = $data['action'] === 'clock_in' ? $record->clock_in : $record->clock_out;
        $suffix = $record->status === 'late' ? ' (marked late)' : '';

        return back()->with('success', "{$action} at {$time->format('g:i A')}{$suffix}.");
    }
}
