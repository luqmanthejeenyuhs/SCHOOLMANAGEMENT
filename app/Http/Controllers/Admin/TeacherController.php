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
use App\Models\TeacherDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            "documents",
            "assignments.subject",
            "assignments.schoolClass",
            "assignments.section",
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

    /**
     * Assign this teacher to teach a subject in a given class (and optionally
     * a specific stream). Duplicate assignments are blocked.
     */
    public function storeAssignment(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            "subject_id" => "required|exists:subjects,id",
            "school_class_id" => "required|exists:school_classes,id",
            "section_id" => "nullable|exists:sections,id",
        ]);

        $exists = ClassSubjectTeacher::where("teacher_id", $teacher->id)
            ->where("subject_id", $data["subject_id"])
            ->where("school_class_id", $data["school_class_id"])
            ->where("section_id", $data["section_id"] ?? null)
            ->exists();

        if ($exists) {
            return back()->with("error", "This teacher is already assigned to that subject/class/section.");
        }

        $teacher->assignments()->create($data);

        return back()->with("success", "Subject assigned to teacher.");
    }

    /**
     * Remove a teacher's subject assignment.
     */
    public function destroyAssignment(Teacher $teacher, ClassSubjectTeacher $assignment)
    {
        abort_unless($assignment->teacher_id === $teacher->id, 404);

        $assignment->delete();

        return back()->with("success", "Assignment removed.");
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
            "id_number" => "nullable|string|max:50",
            "tsc_number" => "nullable|string|max:50",
            "qualification" => "nullable|string",
            "address" => "nullable|string",
            "joining_date" => "nullable|date",
            "next_of_kin_name" => "nullable|string|max:255",
            "next_of_kin_phone" => "nullable|string|max:50",
            "next_of_kin_relationship" => "nullable|string|max:100",
            "passport_photo" => "nullable|file|image|max:5120",
            "national_id_document" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:10240",
            "police_clearance" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:10240",
            "other_documents.*" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:10240",
        ]);

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
            "phone" => $data["phone"] ?? null,
            "role" => "teacher",
        ]);

        $teacher = Teacher::create([
            "user_id" => $user->id,
            "id_number" => $data["id_number"] ?? null,
            "tsc_number" => $data["tsc_number"] ?? null,
            "qualification" => $data["qualification"] ?? null,
            "address" => $data["address"] ?? null,
            "joining_date" => $data["joining_date"] ?? null,
            "next_of_kin_name" => $data["next_of_kin_name"] ?? null,
            "next_of_kin_phone" => $data["next_of_kin_phone"] ?? null,
            "next_of_kin_relationship" => $data["next_of_kin_relationship"] ?? null,
        ]);

        // Employee/staff number is auto-assigned from the record's own ID
        // (e.g. EMPLOYEE-7) so it's always unique and never needs typing in.
        $teacher->update(["employee_id" => "EMPLOYEE-" . $teacher->id]);

        $this->storeDocuments($teacher, $request);

        return redirect()->route("admin.teachers.show", $teacher)->with("success", "Teacher added successfully.");
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load("documents");

        return view("admin.teachers.edit", compact("teacher"));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email,".$teacher->user_id,
            "phone" => "nullable|string",
            "id_number" => "nullable|string|max:50",
            "tsc_number" => "nullable|string|max:50",
            "qualification" => "nullable|string",
            "address" => "nullable|string",
            "joining_date" => "nullable|date",
            "next_of_kin_name" => "nullable|string|max:255",
            "next_of_kin_phone" => "nullable|string|max:50",
            "next_of_kin_relationship" => "nullable|string|max:100",
            "passport_photo" => "nullable|file|image|max:5120",
            "national_id_document" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:10240",
            "police_clearance" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:10240",
            "other_documents.*" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:10240",
        ]);

        $teacher->user->update([
            "name" => $data["name"],
            "email" => $data["email"],
            "phone" => $data["phone"] ?? null,
        ]);

        $teacher->update([
            "id_number" => $data["id_number"] ?? null,
            "tsc_number" => $data["tsc_number"] ?? null,
            "qualification" => $data["qualification"] ?? null,
            "address" => $data["address"] ?? null,
            "joining_date" => $data["joining_date"] ?? null,
            "next_of_kin_name" => $data["next_of_kin_name"] ?? null,
            "next_of_kin_phone" => $data["next_of_kin_phone"] ?? null,
            "next_of_kin_relationship" => $data["next_of_kin_relationship"] ?? null,
        ]);

        $this->storeDocuments($teacher, $request);

        return redirect()->route("admin.teachers.show", $teacher)->with("success", "Teacher updated successfully.");
    }

    public function destroy(Teacher $teacher)
    {
        foreach ($teacher->documents as $document) {
            Storage::delete($document->path);
        }

        $teacher->user()->delete();
        $teacher->delete();

        return back()->with("success", "Teacher removed.");
    }

    /**
     * Stream a teacher's uploaded document (ID, passport photo, police
     * clearance, etc.) back for download. Files are kept on the private
     * "local" disk, never a public URL, since some of these are sensitive.
     */
    public function downloadDocument(Teacher $teacher, TeacherDocument $document)
    {
        abort_unless($document->teacher_id === $teacher->id, 404);
        abort_unless(Storage::exists($document->path), 404);

        return Storage::download($document->path, $document->original_name);
    }

    public function destroyDocument(Teacher $teacher, TeacherDocument $document)
    {
        abort_unless($document->teacher_id === $teacher->id, 404);

        Storage::delete($document->path);
        $document->delete();

        return back()->with("success", "Document removed.");
    }

    /**
     * Handle the fixed single-file slots (passport photo, ID, police
     * clearance) plus a free "other documents" multi-upload, storing each
     * on the private disk and recording it against the teacher.
     */
    private function storeDocuments(Teacher $teacher, Request $request): void
    {
        $singleSlots = ["passport_photo", "national_id_document", "police_clearance"];

        foreach ($singleSlots as $field) {
            if ($request->hasFile($field)) {
                // Replace, don't duplicate: a re-upload of "Passport Photo" (say)
                // should retire the old file rather than leave both listed.
                $existing = $teacher->documents()->where("type", $field)->get();
                foreach ($existing as $old) {
                    Storage::delete($old->path);
                    $old->delete();
                }

                $file = $request->file($field);
                $teacher->documents()->create([
                    "type" => $field,
                    "original_name" => $file->getClientOriginalName(),
                    "path" => $file->store("teacher_documents/{$teacher->id}"),
                ]);
            }
        }

        if ($request->hasFile("other_documents")) {
            foreach ($request->file("other_documents") as $file) {
                $teacher->documents()->create([
                    "type" => "other",
                    "original_name" => $file->getClientOriginalName(),
                    "path" => $file->store("teacher_documents/{$teacher->id}"),
                ]);
            }
        }
    }
}
