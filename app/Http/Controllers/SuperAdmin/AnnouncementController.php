<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with("createdBy")->latest()->paginate(15);

        return view("superadmin.announcements.index", compact("announcements"));
    }

    public function create()
    {
        $schools = School::orderBy("name")->get(["id", "name"]);

        return view("superadmin.announcements.create", compact("schools"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "title" => "required|string|max:255",
            "body" => "required|string|max:2000",
            "audience" => "required|in:all,specific",
            "school_ids" => "required_if:audience,specific|array",
            "school_ids.*" => "exists:schools,id",
            "level" => "required|in:info,warning,success",
        ]);

        $data["school_ids"] = $data["audience"] === "specific" ? ($data["school_ids"] ?? []) : null;
        $data["created_by"] = Auth::id();
        $data["is_active"] = true;

        Announcement::create($data);

        return redirect()->route("superadmin.announcements.index")->with("success", "Announcement published.");
    }

    public function toggleActive(Announcement $announcement)
    {
        $announcement->update(["is_active" => ! $announcement->is_active]);

        return back()->with("success", $announcement->is_active ? "Announcement re-activated." : "Announcement hidden.");
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with("success", "Announcement deleted.");
    }
}
