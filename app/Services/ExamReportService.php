<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\GradingScale;
use Illuminate\Support\Collection;

class ExamReportService
{
    /**
     * Build a full results table for an exam: every student who sat it, their
     * marks per subject, total/mean/grade, and their rank in the class, their
     * stream, and in each individual subject.
     *
     * Returns ['subjects' => Collection<Subject>, 'students' => Collection<array>]
     * where each student row looks like:
     * [
     *   'student' => Student,
     *   'results' => [subject_id => ExamResult],
     *   'total' => float, 'possible' => float, 'mean' => float,
     *   'grade' => ?string, 'points' => ?float,
     *   'class_position' => int, 'stream_position' => ?int,
     *   'subject_positions' => [subject_id => int],
     * ]
     */
    public function classResults(Exam $exam): array
    {
        $exam->loadMissing(["results.student.user", "results.student.section", "results.subject"]);

        $subjects = $exam->results->pluck("subject")->unique("id")->sortBy("name")->values();

        $byStudent = $exam->results->groupBy("student_id");

        $rows = $byStudent->map(function ($results, $studentId) {
            $total = $results->sum("marks_obtained");
            $possible = $results->sum("max_marks");
            $mean = $possible > 0 ? round(($total / $possible) * 100, 1) : 0.0;
            $band = GradingScale::forPercentage($mean);

            return [
                "student" => $results->first()->student,
                "results" => $results->keyBy("subject_id"),
                "total" => $total,
                "possible" => $possible,
                "mean" => $mean,
                "grade" => $band?->grade,
                "points" => $band?->points,
            ];
        })->values();

        // Class-wide rank by mean (standard competition ranking: ties share a
        // rank, and the next distinct score skips ahead by the tie count).
        $this->assignRanks($rows, "mean", "class_position");

        // Stream (section) rank — only meaningful where the school actually
        // uses streams; students with no section just get a null rank.
        $rows->groupBy(fn ($row) => $row["student"]->section_id)
            ->each(function ($group, $sectionId) use ($rows) {
                if ($sectionId === null || $sectionId === "") {
                    $group->each(function ($row) use ($rows) {
                        $index = $rows->search(fn ($r) => $r["student"]->id === $row["student"]->id);
                        $rows[$index]["stream_position"] = null;
                    });

                    return;
                }
                $this->assignRanks($group, "mean", "stream_position");
                $group->each(function ($row) use ($rows) {
                    $index = $rows->search(fn ($r) => $r["student"]->id === $row["student"]->id);
                    $rows[$index]["stream_position"] = $row["stream_position"];
                });
            });

        // Per-subject position: rank students by marks_obtained within each subject.
        $subjectPositions = [];
        foreach ($subjects as $subject) {
            $subjectRows = $exam->results->where("subject_id", $subject->id)
                ->sortByDesc("marks_obtained")
                ->values();

            $rank = 0;
            $seen = 0;
            $prevMarks = null;
            foreach ($subjectRows as $result) {
                $seen++;
                if ($prevMarks === null || $result->marks_obtained < $prevMarks) {
                    $rank = $seen;
                    $prevMarks = $result->marks_obtained;
                }
                $subjectPositions[$subject->id][$result->student_id] = $rank;
            }
        }

        $rows = $rows->map(function ($row) use ($subjectPositions) {
            $row["subject_positions"] = [];
            foreach ($row["results"] as $subjectId => $result) {
                $row["subject_positions"][$subjectId] = $subjectPositions[$subjectId][$row["student"]->id] ?? null;
            }

            return $row;
        });

        return ["subjects" => $subjects, "students" => $rows->sortBy("class_position")->values()];
    }

    /**
     * Standard competition ranking (1, 2, 2, 4...) on a numeric field, mutating
     * each row in place under $rankKey.
     */
    protected function assignRanks(Collection $rows, string $field, string $rankKey): void
    {
        $sorted = $rows->sortByDesc($field)->values();

        $rank = 0;
        $seen = 0;
        $prevValue = null;
        foreach ($sorted as $row) {
            $seen++;
            if ($prevValue === null || $row[$field] < $prevValue) {
                $rank = $seen;
                $prevValue = $row[$field];
            }
            $index = $rows->search(fn ($r) => $r["student"]->id === $row["student"]->id);
            $rows[$index][$rankKey] = $rank;
        }
    }
}
