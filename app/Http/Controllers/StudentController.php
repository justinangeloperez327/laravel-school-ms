<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Student;
use App\Models\Guardian;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::with(['user', 'guardian'])->get();

        return Inertia::render('students/index', [
            'students' => $students,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $guardians = Guardian::all();

        return Inertia::render('students/create', [
            'guardians' => $guardians,
            'student' => new Student(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();

        // Create or update the user
        $user = User::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'password' => bcrypt($validated['password']),
            ]
        );

        // Create the student
        $student = Student::create([
            'user_id' => $user->id,
            'guardian_id' => $validated['guardian_id'],
            'date_of_birth' => $validated['date_of_birth'],
        ]);

        return redirect()->route('students.show', $student)->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $student->load(['user', 'guardian', 'enrollments.schoolClass', 'attendances', 'studentFees']);
        return Inertia::render('students/show', [
            'student' => $student,
            'enrollments' => $student->enrollments,
            'attendances' => $student->attendances,
            'fees' => $student->studentFees,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        $guardians = Guardian::all();
        $student->load(['user', 'guardian']);
        return Inertia::render('students/edit', [
            'student' => $student,
            'guardians' => $guardians,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        // Update the user
        $student->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // Update the student
        $student->update([
            'guardian_id' => $validated['guardian_id'],
            'date_of_birth' => $validated['date_of_birth'],
        ]);

        return redirect()->route('students.show', $student)->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        // Check if the student has any enrollments
        if ($student->enrollments()->exists()) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete student with active enrollments.']);
        }

        // Delete the student and associated user
        $student->user->delete();
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }
}
