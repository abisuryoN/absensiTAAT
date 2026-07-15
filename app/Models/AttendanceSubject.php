<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSubject extends Model
{
    // No soft deletes per requirement

    protected $table = 'attendance_subjects';

    protected $fillable = [
        'schedule_id',
        'teacher_id',
        'academic_year_id',
        'semester_id',
        'date',
        'status',
        'method',
        'note',
        'submitted_at',
    ];

    protected $casts = [
        'date' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(AttendanceSubjectDetail::class);
    }
}
