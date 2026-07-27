<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        $records = StaffAttendance::with('employee')
            ->whereDate('date', $date)
            ->get()
            ->sortBy(fn ($r) => $r->clock_in ?? '99:99:99');

        return view('admin.staff_attendance.index', compact('records', 'date'));
    }
}
