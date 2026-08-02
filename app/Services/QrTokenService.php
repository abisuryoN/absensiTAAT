<?php

namespace App\Services;

use App\Models\QrToken;
use App\Models\Student;
use Illuminate\Support\Str;

class QrTokenService
{
    public function __construct(protected DateTimeService $dateTimeService) {}

    /**
     * Generate a new one-time QR token for a student.
     * Invalidates any existing unused tokens for the same student.
     */
    public function generateToken(Student $student): QrToken
    {
        $now = $this->dateTimeService->now();

        // Invalidate all existing unused tokens for this student
        QrToken::where('student_id', $student->id)
            ->where('is_used', false)
            ->update(['is_used' => true, 'used_at' => $now]);

        $ttl    = (int) config('absensi.qr_token.ttl_seconds', 30);
        $length = (int) config('absensi.qr_token.length', 32);

        return QrToken::create([
            'student_id' => $student->id,
            'token'      => Str::random($length * 2),
            'expires_at' => $now->addSeconds($ttl),
            'is_used'    => false,
        ]);
    }

    /**
     * Clean up expired and used QR tokens.
     */
    public function cleanupExpired(): int
    {
        $now = $this->dateTimeService->now();

        return QrToken::where(function ($query) use ($now) {
            $query->where('expires_at', '<', $now)
                  ->orWhere('is_used', true);
        })->delete();
    }
}
