<?php

namespace App\Observers;

use App\Models\GradingScale;
use App\Models\School;

class SchoolObserver
{
    /**
     * Every school needs a starting grading scale for the exam module to
     * work immediately. This used to be inserted once in a migration back
     * when the app was single-tenant; now it's per-school, so it's seeded
     * here instead, every time a new school signs up.
     */
    public function created(School $school): void
    {
        $defaults = [
            ["grade" => "A", "min_score" => 80, "max_score" => 100, "points" => 12, "remark" => "Excellent"],
            ["grade" => "B", "min_score" => 70, "max_score" => 79.99, "points" => 9, "remark" => "Good"],
            ["grade" => "C", "min_score" => 60, "max_score" => 69.99, "points" => 6, "remark" => "Average"],
            ["grade" => "D", "min_score" => 50, "max_score" => 59.99, "points" => 3, "remark" => "Below Average"],
            ["grade" => "E", "min_score" => 0, "max_score" => 49.99, "points" => 1, "remark" => "Needs Improvement"],
        ];

        foreach ($defaults as $row) {
            GradingScale::create($row + ["school_id" => $school->id]);
        }
    }
}
