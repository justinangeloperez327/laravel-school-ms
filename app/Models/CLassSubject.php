<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSubject extends Model
{
    /** @use HasFactory<\Database\Factories\CLassSubjectFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_class_id',
        'subject_id',
    ];

    /**
     * Get the school class that owns the class subject.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * Get the subject that belongs to the class subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the time tables associated with this class subject.
     */
    public function timeTables(): HasMany
    {
        return $this->hasMany(TimeTable::class);
    }

    /**
     * Get the grades associated with this class subject.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
