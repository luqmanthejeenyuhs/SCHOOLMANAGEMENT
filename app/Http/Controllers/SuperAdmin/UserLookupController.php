<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * "Find any user, on any school, for support purposes" — e.g. a teacher
 * emails support saying they can't log in; rather than asking which
 * school and impersonating blind, a super_admin can search by name,
 * email, or username directly here and jump straight to the right place.
 */
class UserLookupController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query("q", ""));

        $users = User::query()
            ->with("school")
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where("name", "like", "%{$search}%")
                        ->orWhere("email", "like", "%{$search}%")
                        ->orWhere("username", "like", "%{$search}%");
                });
            })
            ->orderBy("name")
            ->paginate(30)
            ->withQueryString();

        return view("superadmin.users.index", compact("users", "search"));
    }
}
