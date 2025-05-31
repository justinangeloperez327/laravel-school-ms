<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SchoolClass extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolClassFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'teacher_id',
        'academic_year_id',
        'section',
    ];

    /**
     * Get the teacher who is in charge of the class.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the academic year that the class belongs to.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the enrollments for this class.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the students enrolled in this class.
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, Enrollment::class, 'school_class_id', 'id', 'id', 'student_id');
    }

    /**
     * Get the class subjects for this class.
     */
    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    /**
     * Get the subjects for this class through class subjects.
     */
    public function subjects(): HasManyThrough
    {
        return $this->hasManyThrough(Subject::class, ClassSubject::class, 'school_class_id', 'id', 'id', 'subject_id');
    }

    /**
     * Get the time tables for this class.
     */
    public function timeTables(): HasMany
    {
        return $this->hasMany(TimeTable::class);
    }
}
