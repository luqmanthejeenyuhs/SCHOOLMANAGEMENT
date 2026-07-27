<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CbcLearningArea;
use App\Models\CbcCompetencyRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CbcAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::all();
        $learningAreas = CbcLearningArea::with('strands.subStrands')->get();

        $classId = $request->get('school_class_id');
        $learningAreaId = $request->get('cbc_learning_area_id');
        $term = $request->get('term', 'Term 1 '.now()->year);

        $students = collect();
        $subStrands = collect();

        if ($classId && $learningAreaId) {
            $learningArea = CbcLearningArea::with('strands.subStrands')->find($learningAreaId);
            $subStrands = $learningArea ? $learningArea->strands->flatMap->subStrands : collect();

            $students = Student::with(['user', 'cbcRecords' => function ($q) use ($term, $subStrands) {
                $q->where('term', $term)->whereIn('cbc_sub_strand_id', $subStrands->pluck('id'));
            }])->where('school_class_id', $classId)->get();
        }

        return view('teacher.cbc_assessment', compact(
            'classes', 'learningAreas', 'classId', 'learningAreaId', 'term', 'students', 'subStrands'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'term' => 'required|string|max:100',
            'levels' => 'required|array', // levels[student_id][sub_strand_id] = EE|ME|AE|BE
        ]);

        foreach ($data['levels'] as $studentId => $subStrandLevels) {
            foreach ($subStrandLevels as $subStrandId => $level) {
                if (! $level) {
                    continue;
                }

                CbcCompetencyRecord::updateOrCreate(
                    ['student_id' => $studentId, 'cbc_sub_strand_id' => $subStrandId, 'term' => $data['term']],
                    ['performance_level' => $level, 'recorded_by' => Auth::id()]
                );
            }
        }

        return back()->with('success', 'CBC competency ratings saved.');
    }
}
