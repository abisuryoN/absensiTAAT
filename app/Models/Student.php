<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'parent_id',
        'class_id',
        'nis',
        'nisn',
        'name',
        'gender',
        'phone',
        'birth_date',
        'birth_place',
        'address',
        'photo',
        'barcode_id',
        'is_active',
        'tahun_masuk',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StudentParent::class, 'parent_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendanceGates(): HasMany
    {
        return $this->hasMany(AttendanceGate::class);
    }

    public function attendanceSubjectDetails(): HasMany
    {
        return $this->hasMany(AttendanceSubjectDetail::class);
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(QrToken::class);
    }

    public function classStudentHistories(): HasMany
    {
        return $this->hasMany(ClassStudentHistory::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
