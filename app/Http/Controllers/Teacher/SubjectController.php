<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

/**
 * "My Subjects" — every subject this teacher actually teaches (e.g.
 * Physics, English, Maths), each showing the class(es) it's taught to and
 * the average performance in it. Deliberately separate from "My Classes"
 * (see Teacher\DashboardController) — a teacher browses their world two
 * ways: by class ("how is Grade 9 Green doing overall") or by subject
 * ("how is my Physics teaching landing across every class I teach it to").
 */
class SubjectController extends Controller
{
    protected function teacherOrFail()
    {
        $teacher = Auth::user()->teacher;
        abort_if(! $teacher, 403, "No staff record is linked to your account yet.");

        return $teacher;
    }

    public function index()
    {
        $teacher = $this->teacherOrFail();

        $assignments = ClassSubjectTeacher::with(["subject", "schoolClass", "section"])
            ->where("teacher_id", $teacher->id)
            ->get()
            ->groupBy("subject_id");

        $subjects = $assignments->map(function ($rows) {
            $subject = $rows->first()->subject;
            $studentIds = $this->studentIdsFor($rows);
            $average = $this->averageFor($subject->id, $studentIds);

            return (object) [
                "subject" => $subject,
                "classes" => $rows->map(fn ($r) => $r->schoolClass->name.($r->section ? " {$r->section->name}" : ""))->unique()->values(),
                "student_count" => $studentIds->count(),
                "average" => $average,
            ];
        })->filter(fn ($s) => $s->subject)->sortBy(fn ($s) => $s->subject->name)->values();

        return view("teacher.subjects.index", compact("subjects"));
    }

    public function show(Subject $subject)
    {
        $teacher = $this->teacherOrFail();

        $assignments = ClassSubjectTeacher::with(["schoolClass", "section"])
            ->where("teacher_id", $teacher->id)
            ->where("subject_id", $subject->id)
            ->get();

        abort_if($assignments->isEmpty(), 403, "You don't teach this subject.");

        // Per-class breakdown, so a teacher can see e.g. "Physics is strong
        // in Grade 9 Green but weak in Grade 10 Blue" at a glance.
        $byClass = $assignments->map(function ($assignment) use ($subject) {
            $studentIds = $assignment->section_id
                ? Student::where("section_id", $assignment->section_id)->pluck("id")
                : Student::where("school_class_id", $assignment->school_class_id)->pluck("id");

            return (object) [
                "label" => $assignment->schoolClass->name.($assignment->section ? " {$assignment->section->name}" : ""),
                "student_count" => $studentIds->count(),
                "average" => $this->averageFor($subject->id, $studentIds),
            ];
        });

        $allStudentIds = $this->studentIdsFor($assignments);
        $overallAverage = $this->averageFor($subject->id, $allStudentIds);

        // Top and bottom performers across every class this subject is
        // taught to, by their average result in it specifically.
        $results = ExamResult::with(["student.user", "student.schoolClass"])
            ->where("subject_id", $subject->id)
            ->whereIn("student_id", $allStudentIds)
            ->get()
            ->groupBy("student_id")
            ->map(fn ($rows) => (object) [
                "student" => $rows->first()->student,
                "average" => round($rows->avg(fn ($r) => $r->percentage()), 1),
            ])
            ->sortByDesc("average")
            ->values();

        return view("teacher.subjects.show", compact("subject", "byClass", "overallAverage", "results"));
    }

    protected function studentIdsFor($assignments)
    {
        $ids = collect();
        foreach ($assignments as $assignment) {
            $ids = $ids->merge(
                $assignment->section_id
                    ? Student::where("section_id", $assignment->section_id)->pluck("id")
                    : Student::where("school_class_id", $assignment->school_class_id)->pluck("id")
            );
        }

        return $ids->unique()->values();
    }

    protected function averageFor(int $subjectId, $studentIds): ?float
    {
        $results = ExamResult::where("subject_id", $subjectId)->whereIn("student_id", $studentIds)->get();

        return $results->isEmpty() ? null : round($results->avg(fn ($r) => $r->percentage()), 1);
    }
}
