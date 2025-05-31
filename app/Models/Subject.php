<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Subject extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Get the class subjects for this subject.
     */
    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    /**
     * Get the school classes that teach this subject.
     */
    public function schoolClasses(): HasManyThrough
    {
        return $this->hasManyThrough(SchoolClass::class, ClassSubject::class, 'subject_id', 'id', 'id', 'school_class_id');
    }

    /**
     * Get the grades for this subject.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Get the time tables for this subject.
     */
    public function timeTables(): HasMany
    {
        return $this->hasMany(TimeTable::class);
    }
}
