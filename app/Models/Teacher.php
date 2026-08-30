<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "user_id", "employee_id", "qualification", "address", "joining_date"];

    protected $casts = [
        "joining_date" => "date",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignments()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    /**
     * Extra-curricular activities this teacher patrons/oversees (e.g. swimming coach).
     */
    public function activitiesAsPatron()
    {
        return $this->hasMany(Activity::class, "patron_id");
    }

    public function documents()
    {
        return $this->hasMany(TeacherDocument::class);
    }

    /**
     * Every class/section this teacher actually has a reason to walk into —
     * either because they're the class teacher of that section, or because
     * they have a subject assignment there (whole-class assignments with a
     * null section_id expand to every section of that class). This is the
     * single source of truth for "my classes" everywhere: the dashboard,
     * attendance, results, and reports all restrict to this list so a
     * teacher can never mark register or enter marks for a class that isn't
     * theirs.
     */
    public function attachedSections()
    {
        $classTeacherSectionIds = Section::where("class_teacher_id", $this->id)->pluck("id");

        $assignedSectionIds = collect();
        foreach ($this->assignments()->with("schoolClass.sections")->get() as $assignment) {
            if ($assignment->section_id) {
                $assignedSectionIds->push($assignment->section_id);
            } elseif ($assignment->schoolClass) {
                $assignedSectionIds = $assignedSectionIds->merge($assignment->schoolClass->sections->pluck("id"));
            }
        }

        $sectionIds = $classTeacherSectionIds->merge($assignedSectionIds)->unique();

        return Section::with("schoolClass")->whereIn("id", $sectionIds)->get()->sortBy([
            fn ($s) => $s->schoolClass->name,
            fn ($s) => $s->name,
        ]);
    }

    /**
     * The subjects this teacher is actually assigned to teach in a given
     * class (optionally narrowed to one section) — used to keep results
     * entry restricted to subjects they teach, not every subject offered.
     */
    public function subjectsFor(int $schoolClassId, ?int $sectionId = null)
    {
        return $this->assignments()
            ->where("school_class_id", $schoolClassId)
            ->where(function ($q) use ($sectionId) {
                $q->whereNull("section_id")->when($sectionId, fn ($q2) => $q2->orWhere("section_id", $sectionId));
            })
            ->with("subject")
            ->get()
            ->pluck("subject")
            ->filter()
            ->unique("id")
            ->values();
    }
}
