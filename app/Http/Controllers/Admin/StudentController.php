<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(["user", "schoolClass", "section"])->latest()->paginate(10);

        return view("admin.students.index", compact("students"));
    }

    public function create()
    {
        $classes = SchoolClass::with("sections")->get();

        return view("admin.students.create", compact("classes"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:6",
            "admission_no" => "required|string|unique:students,admission_no",
            "school_class_id" => "required|exists:school_classes,id",
            "section_id" => "nullable|exists:sections,id",
            "guardian_name" => "nullable|string",
            "guardian_phone" => "nullable|string",
            "dob" => "nullable|date",
            "address" => "nullable|string",
        ]);

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
            "role" => "student",
        ]);

        Student::create([
            "user_id" => $user->id,
            "admission_no" => $data["admission_no"],
            "school_class_id" => $data["school_class_id"],
            "section_id" => $data["section_id"] ?? null,
            "guardian_name" => $data["guardian_name"] ?? null,
            "guardian_phone" => $data["guardian_phone"] ?? null,
            "dob" => $data["dob"] ?? null,
            "address" => $data["address"] ?? null,
        ]);

        return redirect()->route("admin.students.index")->with("success", "Student admitted successfully.");
    }

    /**
     * Full student profile: bio + guardian info, class/stream/subject-teachers,
     * fee ledger and running balance, exam history, attendance, and a CBC
     * assessment summary — rendered as tabs on the profile page.
     */
    public function show(Student $student)
    {
        $student->load(["user", "schoolClass", "section"]);

        // Fees
        $invoices = $student->feeInvoices()->with("payments")->latest()->get();
        $totalBilled = $invoices->sum("amount");
        $totalPaid = $invoices->sum(fn ($inv) => $inv->totalPaid());
        $feeBalance = $invoices->sum(fn ($inv) => $inv->balance());
        $payments = Payment::whereIn("fee_invoice_id", $invoices->pluck("id"))
            ->with("invoice")
            ->latest("payment_date")
            ->get();

        // Exams
        $examResults = $student->examResults()
            ->with(["exam", "subject"])
            ->get()
            ->groupBy(fn ($r) => $r->exam->name ?? "Exam #".$r->exam_id);

        // Attendance
        $attendance = $student->attendances()
            ->where("date", ">=", now()->subDays(60))
            ->orderByDesc("date")
            ->get();
        $recentAttendance = $attendance->where("date", ">=", now()->subDays(30));
        $attendanceTotal = $recentAttendance->count();
        $attendancePresent = $recentAttendance->whereIn("status", ["present", "late"])->count();
        $attendanceRate = $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 1) : null;

        // Class & stream context
        $classmateCount = $student->section
            ? Student::where("section_id", $student->section_id)->where("id", "!=", $student->id)->count()
            : null;
        $subjectTeachers = $student->schoolClass
            ? ClassSubjectTeacher::where("school_class_id", $student->school_class_id)
                ->where(fn ($q) => $q->where("section_id", $student->section_id)->orWhereNull("section_id"))
                ->with(["subject", "teacher.user"])
                ->get()
            : collect();

        // CBC summary
        $cbcRecords = $student->cbcRecords()->with("subStrand")->latest()->get();

        return view("admin.students.show", compact(
            "student", "invoices", "totalBilled", "totalPaid", "feeBalance", "payments",
            "examResults", "attendance", "attendanceRate", "classmateCount", "subjectTeachers", "cbcRecords"
        ));
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::with("sections")->get();

        return view("admin.students.edit", compact("student", "classes"));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email,".$student->user_id,
            "admission_no" => "required|string|unique:students,admission_no,".$student->id,
            "school_class_id" => "required|exists:school_classes,id",
            "section_id" => "nullable|exists:sections,id",
            "guardian_name" => "nullable|string",
            "guardian_phone" => "nullable|string",
            "dob" => "nullable|date",
            "address" => "nullable|string",
        ]);

        $student->user->update([
            "name" => $data["name"],
            "email" => $data["email"],
        ]);

        $student->update([
            "admission_no" => $data["admission_no"],
            "school_class_id" => $data["school_class_id"],
            "section_id" => $data["section_id"] ?? null,
            "guardian_name" => $data["guardian_name"] ?? null,
            "guardian_phone" => $data["guardian_phone"] ?? null,
            "dob" => $data["dob"] ?? null,
            "address" => $data["address"] ?? null,
        ]);

        return redirect()->route("admin.students.index")->with("success", "Student updated successfully.");
    }

    public function destroy(Student $student)
    {
        $student->user()->delete();
        $student->delete();

        return back()->with("success", "Student removed.");
    }
}
