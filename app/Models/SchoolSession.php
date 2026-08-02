<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolSession extends Model
{
    protected $fillable = [
        'name',
        'gate_open_time',
        'school_start_time',
        'late_threshold_minutes',
        'gate_close_time',
        'auto_alpha_time',
        'school_end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'late_threshold_minutes'  => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function gradeSessionSettings(): HasMany
    {
        return $this->hasMany(GradeSessionSetting::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Returns "🌅 Pagi" style label */
    public function getIconLabelAttribute(): string
    {
        $icon = str_contains(strtolower($this->name), 'pagi') ? '🌅' : '🌇';
        return "{$icon} {$this->name}";
    }

    /** e.g. "06:30" */
    public function getStartLabelAttribute(): string
    {
        return substr($this->school_start_time, 0, 5);
    }
}
