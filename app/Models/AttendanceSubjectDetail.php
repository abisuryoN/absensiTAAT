<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSubjectDetail extends Model
{
    // No soft deletes per requirement

    protected $table = 'attendance_subject_details';

    protected $fillable = [
        'attendance_subject_id',
        'student_id',
        'status',
        'note',
    ];

    public function attendanceSubject(): BelongsTo
    {
        return $this->belongsTo(AttendanceSubject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
