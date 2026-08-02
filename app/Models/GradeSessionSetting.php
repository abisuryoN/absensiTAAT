<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeSessionSetting extends Model
{
    protected $fillable = [
        'academic_year_id',
        'grade_level',
        'school_session_id',
    ];

    protected $casts = [
        'grade_level' => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolSession(): BelongsTo
    {
        return $this->belongsTo(SchoolSession::class);
    }
}
