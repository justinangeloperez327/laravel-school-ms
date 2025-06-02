<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Term;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['class_id', 'subject_id', 'term_id']);
        $query = Grade::with(['enrollment.student', 'subject', 'term']);

        if (isset($filters['class_id'])) {
            $query->whereHas('enrollment', function($q) use ($filters) {
                $q->where('school_class_id', $filters['class_id']);
            });
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        $grades = $query->paginate(15);
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $terms = Term::all();

        return view('grades.index', compact('grades', 'classes', 'subjects', 'terms', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $enrollments = Enrollment::with('student', 'schoolClass')->get();
        $subjects = Subject::all();
        $terms = Term::all();

        return view('grades.create', compact('enrollments', 'subjects', 'terms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGradeRequest $request): RedirectResponse
    {
        Grade::create($request->validated());
        return redirect()->route('grades.index')->with('success', 'Grade record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Grade $grade): View
    {
        $grade->load(['enrollment.student', 'subject', 'term']);

        return view('grades.show', compact('grade'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Grade $grade): View
    {
        $enrollments = Enrollment::with('student', 'schoolClass')->get();
        $subjects = Subject::all();
        $terms = Term::all();

        return view('grades.edit', compact('grade', 'enrollments', 'subjects', 'terms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGradeRequest $request, Grade $grade): RedirectResponse
    {
        $grade->update($request->validated());

        return redirect()->route('grades.index')
            ->with('success', 'Grade record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grade $grade): RedirectResponse
    {
        $grade->delete();

        return redirect()->route('grades.index')
            ->with('success', 'Grade record deleted successfully.');
    }

    /**
     * Import grades in bulk for a specific class and subject.
     */
    public function import(Request $request): View
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $termId = $request->input('term_id');

        $enrollments = Enrollment::with('student')
            ->where('school_class_id', $classId)
            ->get();

        $subject = Subject::findOrFail($subjectId);
        $term = Term::findOrFail($termId);

        return view('grades.import', compact('enrollments', 'subject', 'term'));
    }

    /**
     * Store bulk imported grades.
     */
    public function storeBulk(Request $request): RedirectResponse
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.enrollment_id' => 'required|exists:enrollments,id',
            'grades.*.grade' => 'required|numeric|min:0|max:100',
            'grades.*.remarks' => 'nullable|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $subjectId = $request->input('subject_id');
        $termId = $request->input('term_id');
        $gradesData = $request->input('grades');

        foreach ($gradesData as $gradeData) {
            Grade::updateOrCreate(
                [
                    'enrollment_id' => $gradeData['enrollment_id'],
                    'subject_id' => $subjectId,
                    'term_id' => $termId,
                ],
                [
                    'grade' => $gradeData['grade'],
                    'remarks' => $gradeData['remarks'] ?? null,
                ]
            );
        }

        return redirect()->route('grades.index')
            ->with('success', 'Grades imported successfully.');
    }
}
