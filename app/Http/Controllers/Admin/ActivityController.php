<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * The "Activities" tab: every extra-curricular activity (e.g. Swimming)
     * with its patron and participant count, searchable by name or patron.
     */
    public function index(Request $request)
    {
        $teachers = Teacher::with("user")->get();
        $search = $request->get("q");

        $activities = Activity::with("patron.user")
            ->withCount("students")
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where("name", "like", "%{$search}%")
                        ->orWhereHas("patron.user", fn ($q2) => $q2->where("name", "like", "%{$search}%"));
                });
            })
            ->orderBy("name")
            ->get();

        return view("admin.activities.index", compact("activities", "teachers", "search"));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Activity::create($data);

        return back()->with("success", "Activity created.");
    }

    /**
     * Drill into an activity: who runs it, when it happens, and who has
     * signed up.
     */
    public function show(Activity $activity)
    {
        $activity->load("patron.user");

        $students = $activity->students()->with("user")->orderBy("admission_no")->get();

        $availableStudents = Student::with("user")
            ->whereNotIn("id", $students->pluck("id"))
            ->orderBy("admission_no")
            ->get();

        return view("admin.activities.show", compact("activity", "students", "availableStudents"));
    }

    public function update(Request $request, Activity $activity)
    {
        $data = $this->validated($request);
        $activity->update($data);

        return back()->with("success", "Activity updated.");
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route("admin.activities.index")->with("success", "Activity deleted.");
    }

    /**
     * Sign a student up for the activity.
     */
    public function enroll(Request $request, Activity $activity)
    {
        $data = $request->validate([
            "student_id" => "required|exists:students,id",
        ]);

        $activity->students()->syncWithoutDetaching([
            $data["student_id"] => ["signed_up_at" => now()],
        ]);

        return back()->with("success", "Student signed up.");
    }

    /**
     * Remove a student from the activity's roster.
     */
    public function unenroll(Activity $activity, Student $student)
    {
        $activity->students()->detach($student->id);

        return back()->with("success", "Student removed from activity.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            "name" => "required|string|max:255",
            "patron_id" => "nullable|exists:teachers,id",
            "day_of_week" => "nullable|string|max:20",
            "start_time" => "nullable|date_format:H:i",
            "end_time" => "nullable|date_format:H:i|after:start_time",
            "venue" => "nullable|string|max:255",
            "description" => "nullable|string",
        ]);
    }
}
