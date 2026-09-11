<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\School;
use Illuminate\Http\Request;

/**
 * The platform-wide equivalent of Admin\AuditLogController — that one is
 * deliberately scoped to a single school (Tenant::id()); this one
 * deliberately isn't, since a super_admin's whole job here is seeing
 * activity across every school at once.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $event = $request->query("event");
        $schoolId = $request->query("school_id");
        $search = trim((string) $request->query("q", ""));

        $logs = AuditLog::query()
            ->with(["user", "school"])
            ->when($event, fn ($q) => $q->where("event", $event))
            ->when($schoolId, fn ($q) => $q->where("school_id", $schoolId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where("username_attempted", "like", "%{$search}%")
                        ->orWhere("ip_address", "like", "%{$search}%")
                        ->orWhereHas("user", fn ($u) => $u->where("name", "like", "%{$search}%"));
                });
            })
            ->latest("created_at")
            ->paginate(30)
            ->withQueryString();

        $events = AuditLog::distinct()->pluck("event");
        $schools = School::orderBy("name")->get(["id", "name"]);

        return view("superadmin.audit.index", compact("logs", "event", "schoolId", "search", "events", "schools"));
    }
}
