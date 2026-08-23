<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\StaffAttendance;
use App\Services\StaffAttendanceService;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    public function __construct(protected StaffAttendanceService $attendance)
    {
    }

    public function index(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        $records = StaffAttendance::with(['employee', 'markedBy'])
            ->whereDate('date', $date)
            ->get()
            ->sortBy(fn ($r) => $r->clock_in?->timestamp ?? PHP_INT_MAX);

        // Staff with no record at all for the day, so the "mark attendance"
        // form can also cover people who never attempted to clock in
        // (support staff without logins, or someone who forgot entirely).
        $unrecorded = Employee::where('is_active', true)
            ->whereNotIn('id', $records->pluck('employee_id'))
            ->orderBy('name')
            ->get();

        $allEmployees = Employee::where('is_active', true)->orderBy('name')->get();

        return view('admin.staff_attendance.index', compact('records', 'date', 'unrecorded', 'allEmployees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,late,absent,on_leave,half_day',
            'remarks' => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        $this->attendance->markManually(
            $employee,
            $data['date'],
            $data['clock_in'] ?? null,
            $data['clock_out'] ?? null,
            $data['status'],
            $request->user(),
            $data['remarks'] ?? null,
        );

        return back()->with('success', "Attendance recorded for {$employee->name}.");
    }

    public function update(Request $request, StaffAttendance $staffAttendance)
    {
        $data = $request->validate([
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,late,absent,on_leave,half_day',
            'remarks' => 'nullable|string|max:255',
        ]);

        $date = $staffAttendance->date->toDateString();

        $staffAttendance->update([
            'clock_in' => $data['clock_in'] ? "{$date} {$data['clock_in']}" : null,
            'clock_out' => $data['clock_out'] ? "{$date} {$data['clock_out']}" : null,
            'status' => $data['status'],
            'method' => 'manual',
            'marked_by' => $request->user()->id,
            'remarks' => $data['remarks'] ?? $staffAttendance->remarks,
        ]);

        return back()->with('success', 'Attendance record updated.');
    }
}
