<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;

class SchoolClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $academicYearId = $request->query('academic_year_id');

        // Default to current academic year if none specified
        if (!$academicYearId) {
            $currentAcademicYear = AcademicYear::where('active', true)->first();
            $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;
        }

        $query = SchoolClass::with(['academicYear', 'teacher']);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $schoolClasses = $query->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year_start', 'desc')->get();

        return Inertia::render('school-classes/index', [
            'schoolClasses' => $schoolClasses,
            'academicYears' => $academicYears,
            'academicYearId' => $academicYearId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teachers = Teacher::whereHas('user')->with('user')->get();
        $academicYears = AcademicYear::orderBy('year_start', 'desc')->get();

        return Inertia::render('school-classes/create', [
            'teachers' => $teachers,
            'academicYears' => $academicYears,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchoolClassRequest $request)
    {
        $schoolClass = SchoolClass::create($request->validated());

        return redirect()->route('school-classes.index')
            ->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolClass $schoolClass)
    {
        $schoolClass->load(['teacher.user', 'academicYear', 'students', 'subjects']);

        return Inertia::render('school-classes/show', [
            'schoolClass' => $schoolClass,
            'students' => $schoolClass->students,
            'subjects' => $schoolClass->subjects,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolClass $schoolClass)
    {
        $teachers = Teacher::whereHas('user')->with('user')->get();
        $academicYears = AcademicYear::orderBy('year_start', 'desc')->get();

        return Inertia::render('school-classes/edit', [
            'schoolClass' => $schoolClass,
            'teachers' => $teachers,
            'academicYears' => $academicYears,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass)
    {
        $schoolClass->update($request->validated());

        return redirect()->route('school-classes.index')
            ->with('success', 'Class updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();

        return redirect()->route('school-classes.index')
            ->with('success', 'Class deleted successfully');
    }
}
