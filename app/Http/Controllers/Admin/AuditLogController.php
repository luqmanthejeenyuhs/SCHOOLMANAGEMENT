<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;

/**
 * Read-only view of App\Models\AuditLog for the current school. Scoped
 * explicitly to Tenant::id() (rather than relying on a global scope) since
 * AuditLog intentionally isn't tenant-scoped by default — some rows (a
 * failed login on the generic /login page) have no school_id at all, and
 * a school admin should never see another school's rows or the platform's
 * super_admin activity.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $event = $request->query("event");
        $search = trim((string) $request->query("q", ""));

        $logs = AuditLog::query()
            ->where("school_id", Tenant::id())
            ->with("user")
            ->when($event, fn ($q) => $q->where("event", $event))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where("username_attempted", "like", "%{$search}%")
                        ->orWhere("ip_address", "like", "%{$search}%")
                        ->orWhereHas("user", fn ($u) => $u->where("name", "like", "%{$search}%"));
                });
            })
            ->latest("created_at")
            ->paginate(25)
            ->withQueryString();

        $events = AuditLog::where("school_id", Tenant::id())->distinct()->pluck("event");

        return view("admin.audit.index", compact("logs", "event", "search", "events"));
    }
}
