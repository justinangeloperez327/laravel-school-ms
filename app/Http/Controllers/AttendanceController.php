<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolClassId = $request->query('school_class_id');
        $date = $request->query('date') ?? now()->toDateString();

        $query = Attendance::with(['student.user', 'schoolClass', 'teacher.user']);

        if ($schoolClassId) {
            $query->where('school_class_id', $schoolClassId);
        }

        if ($date) {
            $query->whereDate('attendance_date', $date);
        }

        $attendances = $query->get();
        $schoolClasses = SchoolClass::orderBy('name')->get();

        return [
            'attendances' => $attendances,
            'schoolClasses' => $schoolClasses,
            'schoolClassId' => $schoolClassId,
            'date' => $date
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schoolClasses = SchoolClass::orderBy('name')->get();
        $students = Student::with('user')->get();
        $teachers = Teacher::with('user')->get();

        return [
            'schoolClasses' => $schoolClasses,
            'students' => $students,
            'teachers' => $teachers
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRequest $request)
    {
        $attendance = Attendance::create($request->validated());

        return $attendance;
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['student.user', 'schoolClass', 'teacher.user']);
        return $attendance;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        $schoolClasses = SchoolClass::orderBy('name')->get();
        $students = Student::with('user')->get();
        $teachers = Teacher::with('user')->get();

        return [
            'attendance' => $attendance,
            'schoolClasses' => $schoolClasses,
            'students' => $students,
            'teachers' => $teachers
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $attendance->update($request->validated());

        return $attendance;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return ['message' => 'Attendance record deleted successfully'];
    }

    /**
     * Record attendance for multiple students at once
     */
    public function recordBulk(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'attendance_date' => 'required|date',
            'teacher_id' => 'required|exists:teachers,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.remarks' => 'nullable|string',
        ]);

        $schoolClassId = $request->school_class_id;
        $attendanceDate = $request->attendance_date;
        $teacherId = $request->teacher_id;

        $updated = [];

        foreach ($request->attendances as $attendance) {
            $updated[] = Attendance::updateOrCreate(
                [
                    'student_id' => $attendance['student_id'],
                    'school_class_id' => $schoolClassId,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'status' => $attendance['status'],
                    'teacher_id' => $teacherId,
                    'remarks' => $attendance['remarks'] ?? null,
                ]
            );
        }

        return [
            'message' => 'Attendance records updated successfully',
            'updated' => $updated
        ];
    }

    /**
     * Show the form for recording bulk attendance
     */
    public function createBulk(Request $request)
    {
        $schoolClassId = $request->query('school_class_id');
        $date = $request->query('date') ?? now()->toDateString();

        $schoolClasses = SchoolClass::orderBy('name')->get();
        $schoolClass = $schoolClassId ? SchoolClass::find($schoolClassId) : null;

        $students = [];
        $existingAttendances = [];

        if ($schoolClass) {
            $students = $schoolClass->enrollments()
                ->with('student.user')
                ->where('status', 'active')
                ->get()
                ->pluck('student');

            // Get existing attendance records for this class and date
            $existingAttendances = Attendance::where('school_class_id', $schoolClassId)
                ->where('attendance_date', $date)
                ->get()
                ->keyBy('student_id');
        }

        $teacherId = Auth::user()->teacher->id ?? null;
        $teachers = Teacher::with('user')->get();

        return [
            'schoolClasses' => $schoolClasses,
            'schoolClass' => $schoolClass,
            'students' => $students,
            'date' => $date,
            'teacherId' => $teacherId,
            'teachers' => $teachers,
            'existingAttendances' => $existingAttendances
        ];
    }
}
