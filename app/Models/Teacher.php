<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nip',
        'nuptk',
        'name',
        'phone',
        'gender',
        'address',
        'photo',
        'is_active',
        'tahun_masuk_kerja',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Accessor: URL foto guru atau fallback ke avatar inisial.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo)) {
            return \Illuminate\Support\Facades\Storage::url($this->photo);
        }

        // Fallback: UI Avatars API dengan inisial nama
        $name = urlencode($this->name ?? 'G');
        return "https://ui-avatars.com/api/?name={$name}&background=4f46e5&color=fff&size=128&bold=true";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classes(): HasMany
    {
        // homeroom classes (kelas wali kelas)
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject')
                    ->withPivot('academic_year_id')
                    ->withTimestamps();
    }
}
