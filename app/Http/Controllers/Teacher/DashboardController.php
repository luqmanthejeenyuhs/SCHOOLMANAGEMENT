<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher()->with("assignments.schoolClass", "assignments.section", "assignments.subject")->first();

        return view("teacher.dashboard", compact("teacher"));
    }
}
