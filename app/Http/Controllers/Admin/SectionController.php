<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\ExamResult;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TimetableSlot;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::with(["schoolClass", "classTeacher.user"])->latest()->get();
        $classes = SchoolClass::all();

        return view("admin.sections.index", compact("sections", "classes"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "school_class_id" => ["required", Rule::exists("school_classes", "id")->where("school_id", Tenant::id())],
            "name" => "required|string|max:255",
            "class_teacher_id" => ["nullable", Rule::exists("teachers", "id")->where("school_id", Tenant::id())],
        ]);
        Section::create($data);

        return back()->with("success", "Stream created.");
    }

    /**
     * Assign or change the class teacher for a stream.
     */
    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            "class_teacher_id" => ["nullable", Rule::exists("teachers", "id")->where("school_id", Tenant::id())],
        ]);
        $section->update($data);

        return back()->with("success", "Class teacher updated.");
    }

    public function destroy(Section $section)
    {
        $section->delete();

        return back()->with("success", "Stream deleted.");
    }

    /**
     * Drill into a stream: three tabs — Information (class teacher, each
     * subject's teacher and mean grade, class average), the student
     * roster, and the stream's own timetable.
     */
    public function show(Section $section)
    {
        $section->load(["schoolClass", "classTeacher.user"]);

        $students = $section->students()
            ->with(["user", "feeInvoices.payments", "examResults", "latestExamResult", "attendances"])
            ->orderBy("admission_no")
            ->get()
            ->map(function ($student) {
                $student->fee_balance = $student->feeInvoices->sum(fn ($inv) => $inv->balance());
                $student->exam_average = $student->examResults->count()
                    ? round($student->examResults->avg(fn ($r) => $r->percentage()), 1)
                    : null;

                $totalMarked = $student->attendances->count();
                $student->attendance_rate = $totalMarked
                    ? round($student->attendances->whereIn("status", ["present", "late"])->count() / $totalMarked * 100, 1)
                    : null;

                return $student;
            });

        // Every subject taught to this stream, with its teacher and the
        // mean grade its own students got in it — the subject may be
        // assigned to the whole class (section_id null) or specifically to
        // this stream, so both are matched.
        $studentIds = $students->pluck("id");
        $subjectTeachers = ClassSubjectTeacher::with(["subject", "teacher.user"])
            ->where("school_class_id", $section->school_class_id)
            ->where(function ($q) use ($section) {
                $q->whereNull("section_id")->orWhere("section_id", $section->id);
            })
            ->get()
            ->sortBy(fn ($ct) => $ct->subject->name ?? "")
            ->map(function ($ct) use ($studentIds) {
                $results = ExamResult::where("subject_id", $ct->subject_id)
                    ->whereIn("student_id", $studentIds)
                    ->get();

                return (object) [
                    "subject" => $ct->subject,
                    "teacher" => $ct->teacher,
                    "mean_grade" => $results->isNotEmpty() ? round($results->avg(fn ($r) => $r->percentage()), 1) : null,
                    "result_count" => $results->count(),
                ];
            })
            ->values();

        $classAverage = $students->filter(fn ($s) => $s->exam_average !== null)->avg("exam_average");
        $classAverage = $classAverage !== null ? round($classAverage, 1) : null;

        // Timetable — lessons for this specific stream, plus any school-wide
        // break/lunch block (see TimetableSlot::SCHOOL_WIDE_TYPES).
        $timetableSlots = TimetableSlot::with(["subject", "teacher.user"])
            ->where(function ($q) use ($section) {
                $q->where("section_id", $section->id)
                    ->orWhereIn("slot_type", TimetableSlot::SCHOOL_WIDE_TYPES);
            })
            ->get();

        $timetableRanges = $timetableSlots
            ->map(fn ($slot) => $slot->start_time.'|'.$slot->end_time)
            ->unique()
            ->sort()
            ->values();

        return view("admin.sections.show", compact(
            "section", "students", "subjectTeachers", "classAverage",
            "timetableSlots", "timetableRanges"
        ));
    }
}
