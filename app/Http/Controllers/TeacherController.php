<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;

use Inertia\Inertia;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = Teacher::with('user')->get();

        return Inertia::render('teachers/index', [
            'teachers' => $teachers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('teachers/create', [
            'users' => User::whereDoesntHave('teacher')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $validated = $request->validated();

        $teacher = Teacher::create([
            'user_id' => $validated['user_id'],
            'subject' => $validated['subject'],
            'qualification' => $validated['qualification'],
            'experience' => $validated['experience'],
        ]);

        return redirect()->route('teachers.show', $teacher)->with('success', 'Teacher created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'schoolClasses', 'timeTables']);

        return Inertia::render('teachers/show', [
            'teacher' => $teacher,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        $teacher->load('user');
        return Inertia::render('teachers/edit', [
            'teacher' => $teacher,
            'users' => User::whereDoesntHave('teacher')->orWhere('id', $teacher->user_id)->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $validated = $request->validated();

        $teacher->update([
            'subject' => $validated['subject'],
            'qualification' => $validated['qualification'],
            'experience' => $validated['experience'],
        ]);

        return redirect()->route('teachers.show', $teacher)->with('success', 'Teacher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
    }
}
