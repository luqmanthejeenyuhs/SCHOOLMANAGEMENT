<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSubjectTeacher;
use App\Models\Employee;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input("search");

        $teachers = Teacher::with("user")
            ->when($search, function ($query, $search) {
                $query->where("employee_id", "like", "%{$search}%")
                    ->orWhereHas("user", fn ($u) => $u->where("name", "like", "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view("admin.teachers.index", compact("teachers", "search"));
    }

    public function show(Teacher $teacher)
    {
        $teacher->load([
            "user",
            "assignments.subject",
            "assignments.schoolClass",
            "assignments.section",
            "documents",
        ]);

        $subjectIds = $teacher->assignments->pluck("subject_id")->unique();

        $attendanceMarked = Attendance::where("marked_by", $teacher->user_id)
            ->with("student.user")
            ->latest("date")
            ->limit(15)
            ->get();

        $employee = Employee::where("teacher_id", $teacher->id)
            ->with(["payslips" => fn ($q) => $q->latest()->limit(12)])
            ->first();

        $performanceBySubject = ExamResult::whereIn("subject_id", $subjectIds)
            ->selectRaw("subject_id, AVG(marks_obtained/max_marks*100) as avg_pct, COUNT(*) as total")
            ->groupBy("subject_id")
            ->with("subject")
            ->get();

        $recentResults = ExamResult::whereIn("subject_id", $subjectIds)
            ->with(["exam", "subject", "student.user"])
            ->latest("id")
            ->limit(10)
            ->get();

        $classes = SchoolClass::orderBy("name")->get();
        $subjectsAll = Subject::orderBy("name")->get();
        $sectionsAll = Section::orderBy("name")->get();

        return view("admin.teachers.show", compact(
            "teacher", "attendanceMarked", "employee", "performanceBySubject", "recentResults",
            "classes", "subjectsAll", "sectionsAll"
        ));
    }

    public function create()
    {
        return view("admin.teachers.create");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:6",
            "phone" => "nullable|string",
            "qualification" => "nullable|string",
            "address" => "nullable|string",
            "joining_date" => "nullable|date",
            "employment_type" => "required|in:full_time,part_time,contract,intern,volunteer",
        ]);

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
            "phone" => $data["phone"] ?? null,
            "role" => "teacher",
        ]);

        // employee_id is never entered by hand — insert with a unique
        // placeholder first (so the unique constraint can't collide),
        // then correct it to EMPL<id> once the row's real id is known.
        $teacher = Teacher::create([
            "user_id" => $user->id,
            "employee_id" => "PENDING-".uniqid(),
            "qualification" => $data["qualification"] ?? null,
            "address" => $data["address"] ?? null,
            "joining_date" => $data["joining_date"] ?? null,
        ]);

        $teacher->update(["employee_id" => "EMPL".str_pad((string) $teacher->id, 3, "0", STR_PAD_LEFT)]);

        // Every teacher is also staff — link a payroll/HR record now so
        // Clock In/Out and payroll work immediately, instead of leaving
        // admins to remember a separate manual step in Payroll > Employees.
        // basic_salary defaults to 0 and is set properly later from there;
        // employment_type is asked up front so interns/volunteers are
        // never silently mixed in with paid staff.
        Employee::create([
            "user_id" => $user->id,
            "teacher_id" => $teacher->id,
            "name" => $data["name"],
            "job_title" => "Teacher",
            "employment_type" => $data["employment_type"],
            "is_teaching_staff" => true,
            "phone" => $data["phone"] ?? null,
            "employment_date" => $data["joining_date"] ?? null,
            "basic_salary" => 0,
        ]);

        return redirect()->route("admin.teachers.index")->with("success", "Teacher added successfully.");
    }

    public function edit(Teacher $teacher)
    {
        return view("admin.teachers.edit", compact("teacher"));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email,".$teacher->user_id,
            "phone" => "nullable|string",
            "qualification" => "nullable|string",
            "address" => "nullable|string",
            "joining_date" => "nullable|date",
        ]);

        $teacher->user->update([
            "name" => $data["name"],
            "email" => $data["email"],
            "phone" => $data["phone"] ?? null,
        ]);

        $teacher->update([
            "qualification" => $data["qualification"] ?? null,
            "address" => $data["address"] ?? null,
            "joining_date" => $data["joining_date"] ?? null,
        ]);

        return redirect()->route("admin.teachers.index")->with("success", "Teacher updated successfully.");
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->user()->delete();
        $teacher->delete();

        return back()->with("success", "Teacher removed.");
    }

    public function storeAssignment(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            "school_class_id" => "required|exists:school_classes,id",
            "subject_id" => "required|exists:subjects,id",
            "section_id" => "nullable|exists:sections,id",
        ]);

        $exists = ClassSubjectTeacher::where("teacher_id", $teacher->id)
            ->where("school_class_id", $data["school_class_id"])
            ->where("subject_id", $data["subject_id"])
            ->where("section_id", $data["section_id"] ?? null)
            ->exists();

        if ($exists) {
            return back()->with("error", "This teacher is already assigned to that class/subject/section.");
        }

        ClassSubjectTeacher::create([
            "teacher_id" => $teacher->id,
            "school_class_id" => $data["school_class_id"],
            "subject_id" => $data["subject_id"],
            "section_id" => $data["section_id"] ?? null,
        ]);

        return back()->with("success", "Subject assigned to teacher.");
    }

    public function destroyAssignment(Teacher $teacher, ClassSubjectTeacher $assignment)
    {
        // ClassSubjectTeacher isn't itself tenant-scoped (no school_id column) —
        // it's only ever reached through an already tenant-scoped Teacher, so
        // this check is what actually stops one school's admin from deleting
        // another school's assignment row by guessing/reusing an id in the URL.
        abort_unless($assignment->teacher_id === $teacher->id, 404);

        $assignment->delete();

        return back()->with("success", "Assignment removed.");
    }
}
