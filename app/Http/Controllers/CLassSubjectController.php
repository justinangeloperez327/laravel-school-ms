<?php

namespace App\Http\Controllers;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Http\Requests\StoreClassSubjectRequest;
use App\Http\Requests\UpdateClassSubjectRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassSubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolClassId = $request->query('school_class_id');

        $query = ClassSubject::with(['schoolClass', 'subject']);

        if ($schoolClassId) {
            $query->where('school_class_id', $schoolClassId);
        }

        $classSubjects = $query->get();
        $schoolClasses = SchoolClass::orderBy('name')->get();

        return Inertia::render('class-subjects/index', [
            'classSubjects' => $classSubjects,
            'schoolClasses' => $schoolClasses,
            'schoolClassId' => $schoolClassId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schoolClasses = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return Inertia::render('class-subjects/create', [
            'schoolClasses' => $schoolClasses,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassSubjectRequest $request)
    {
        // Check if the combination already exists
        $exists = ClassSubject::where('school_class_id', $request->school_class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['error' => 'This subject is already assigned to this class.'])->withInput();
        }

        $classSubject = ClassSubject::create($request->validated());

        return redirect()->route('class-subjects.index')
            ->with('success', 'Subject assigned to class successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassSubject $classSubject)
    {
        $classSubject->load(['schoolClass', 'subject']);
        return Inertia::render('class-subjects/show', [
            'classSubject' => $classSubject,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassSubject $classSubject)
    {
        $schoolClasses = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return Inertia::render('class-subjects/edit', [
            'classSubject' => $classSubject,
            'schoolClasses' => $schoolClasses,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassSubjectRequest $request, ClassSubject $classSubject)
    {
        // Check if the new combination already exists (exclude current record)
        $exists = ClassSubject::where('school_class_id', $request->school_class_id)
            ->where('subject_id', $request->subject_id)
            ->where('id', '!=', $classSubject->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['error' => 'This subject is already assigned to this class.'])->withInput();
        }

        $classSubject->update($request->validated());

        return redirect()->route('class-subjects.index')
            ->with('success', 'Class subject updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassSubject $classSubject)
    {
        $classSubject->delete();

        return redirect()->route('class-subjects.index')
            ->with('success', 'Subject removed from class successfully');
    }

    /**
     * Assign multiple subjects to a class
     */
    public function assignMultiple(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $schoolClassId = $request->school_class_id;
        $subjectIds = $request->subject_ids;

        foreach ($subjectIds as $subjectId) {
            // Check if record already exists
            $exists = ClassSubject::where('school_class_id', $schoolClassId)
                ->where('subject_id', $subjectId)
                ->exists();

            if (!$exists) {
                ClassSubject::create([
                    'school_class_id' => $schoolClassId,
                    'subject_id' => $subjectId,
                ]);
            }
        }

        return redirect()->route('class-subjects.index', ['school_class_id' => $schoolClassId])
            ->with('success', 'Subjects assigned to class successfully.');
    }
}
