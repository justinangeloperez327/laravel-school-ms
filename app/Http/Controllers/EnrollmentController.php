<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolClassId = $request->query('school_class_id');
        $studentId = $request->query('student_id');

        $query = Enrollment::with(['student.user', 'schoolClass']);

        if ($schoolClassId) {
            $query->where('school_class_id', $schoolClassId);
        }

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $enrollments = $query->get();
        $schoolClasses = SchoolClass::orderBy('name')->get();
        $students = Student::with('user')->get();

        return Inertia::render('enrollments/index', [
            'enrollments' => $enrollments,
            'schoolClasses' => $schoolClasses,
            'students' => $students,
            'schoolClassId' => $schoolClassId,
            'studentId' => $studentId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::with('user')->get();
        $schoolClasses = SchoolClass::orderBy('name')->get();

        return Inertia::render('enrollments/create', [
            'students' => $students,
            'schoolClasses' => $schoolClasses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEnrollmentRequest $request)
    {
        // Check if student is already enrolled in the same class
        $exists = Enrollment::where('student_id', $request->student_id)
            ->where('school_class_id', $request->school_class_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['error' => 'Student is already enrolled in this class.'])->withInput();
        }

        $enrollment = Enrollment::create($request->validated());

        return redirect()->route('enrollments.index')
            ->with('success', 'Student enrolled successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student.user', 'schoolClass']);

        return Inertia::render('enrollments/show', [
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enrollment $enrollment)
    {
        $students = Student::with('user')->get();
        $schoolClasses = SchoolClass::orderBy('name')->get();

        return Inertia::render('enrollments/edit', [
            'enrollment' => $enrollment,
            'students' => $students,
            'schoolClasses' => $schoolClasses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment)
    {
        // Check if student is already enrolled in the same class (exclude current record)
        if ($enrollment->student_id != $request->student_id || $enrollment->school_class_id != $request->school_class_id) {
            $exists = Enrollment::where('student_id', $request->student_id)
                ->where('school_class_id', $request->school_class_id)
                ->where('id', '!=', $enrollment->id)
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors(['error' => 'Student is already enrolled in this class.'])->withInput();
            }
        }

        $enrollment->update($request->validated());

        return redirect()->route('enrollments.index')
            ->with('success', 'Enrollment updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()->route('enrollments.index')
            ->with('success', 'Enrollment deleted successfully');
    }

    /**
     * Enroll multiple students to a class
     */
    public function enrollMultiple(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'enrolled_at' => 'required|date',
            'status' => 'required|string',
        ]);

        $schoolClassId = $request->school_class_id;
        $studentIds = $request->student_ids;

        foreach ($studentIds as $studentId) {
            // Check if enrollment already exists
            $exists = Enrollment::where('student_id', $studentId)
                ->where('school_class_id', $schoolClassId)
                ->exists();

            if (!$exists) {
                Enrollment::create([
                    'student_id' => $studentId,
                    'school_class_id' => $schoolClassId,
                    'enrolled_at' => $request->enrolled_at,
                    'status' => $request->status,
                ]);
            }
        }

        return redirect()->route('enrollments.index', ['school_class_id' => $schoolClassId])
            ->with('success', 'Students enrolled successfully.');
    }
}
