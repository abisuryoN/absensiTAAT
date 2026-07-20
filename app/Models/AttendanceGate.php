<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceGate extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'date',
        'time_in',
        'status',
        'method',
        'note',
        'scanned_by',
        'petugas_piket_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Siswa yang diabsen.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * User (admin/guru) yang melakukan scan — FK scanned_by.
     * Nullable: scan oleh petugas piket mungkin tidak punya user login terpisah.
     */
    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    /**
     * Petugas piket yang melakukan scan — FK petugas_piket_id.
     * Diisi oleh role guru_piket, null jika scan dilakukan langsung oleh admin.
     */
    public function petugasPiket(): BelongsTo
    {
        return $this->belongsTo(PetugasPiket::class, 'petugas_piket_id');
    }

    /**
     * Tahun ajaran terkait.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Semester terkait.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}