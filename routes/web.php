<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TermController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\StudentFeeController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ClassSubjectController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('students', StudentController::class);
    Route::resource('teachers', TeacherController::class);
    Route::resource('school-classes', SchoolClassController::class);
    Route::resource('enrollments', EnrollmentController::class);
    Route::resource('attendances', AttendanceController::class);
    Route::resource('grades', GradeController::class);
    Route::resource('class-subjects', ClassSubjectController::class);
    Route::resource('terms', TermController::class);
    Route::resource('guardians', GuardianController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('fee-categories', FeeCategoryController::class);
    Route::resource('student-fees', StudentFeeController::class);
    Route::resource('subjects', SubjectController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
