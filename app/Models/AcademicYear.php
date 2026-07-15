<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'academic_year_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function attendanceRules(): HasMany
    {
        return $this->hasMany(AttendanceRule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
