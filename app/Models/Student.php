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

    /**
     * Accessor: URL foto siswa atau fallback ke avatar inisial.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo)) {
            return \Illuminate\Support\Facades\Storage::url($this->photo);
        }

        // Fallback: UI Avatars API dengan inisial nama
        $name = urlencode($this->name ?? 'S');
        return "https://ui-avatars.com/api/?name={$name}&background=6366f1&color=fff&size=128&bold=true";
    }

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

    // Alias for class() to avoid PHP reserved word conflicts
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Nama kelas untuk ditampilkan ke orang tua.
     * Urutan: kelas saat ini (termasuk yang sudah di-soft-delete) -> kelas terakhir di riwayat -> null.
     */
    public function displayClass(): ?SchoolClass
    {
        if ($this->class_id) {
            $class = SchoolClass::withTrashed()->with('major')->find($this->class_id);
            if ($class) {
                return $class;
            }
        }

        $history = $this->classStudentHistories()->latest('id')->first();

        return $history
            ? SchoolClass::withTrashed()->with('major')->find($history->class_id)
            : null;
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
