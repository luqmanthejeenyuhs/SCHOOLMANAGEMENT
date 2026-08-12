<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

/**
 * Lets a super_admin step into a school's context (e.g. for support) without
 * being a member of that school. Session-based, cleared on stop() or logout.
 */
class ImpersonationController extends Controller
{
    public function start(Request $request, School $school)
    {
        $request->session()->put("impersonating_school_id", $school->id);

        return redirect("/dashboard")->with("success", "Now viewing {$school->name} as an admin.");
    }

    public function stop(Request $request)
    {
        $request->session()->forget("impersonating_school_id");

        return redirect()->route("superadmin.schools.index")->with("success", "Stopped impersonating.");
    }
}
