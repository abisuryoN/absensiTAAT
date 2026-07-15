<?php

namespace App\Services;

use App\Models\QrToken;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Str;

class QrTokenService
{
    /**
     * Generate a new one-time QR token for a student.
     * Invalidates any existing unused tokens for the same student.
     */
    public function generateToken(Student $student): QrToken
    {
        // Invalidate all existing unused tokens for this student
        QrToken::where('student_id', $student->id)
            ->where('is_used', false)
            ->update(['is_used' => true, 'used_at' => now()]);

        $ttl = (int) config('absensi.qr_token.ttl_seconds', 30);
        $length = (int) config('absensi.qr_token.length', 32);

        return QrToken::create([
            'student_id' => $student->id,
            'token' => Str::random($length * 2), // hex-like token
            'expires_at' => Carbon::now()->addSeconds($ttl),
            'is_used' => false,
        ]);
    }

    /**
     * Clean up expired and used QR tokens.
     * Returns the number of deleted records.
     */
    public function cleanupExpired(): int
    {
        return QrToken::where(function ($query) {
            $query->where('expires_at', '<', Carbon::now())
                  ->orWhere('is_used', true);
        })->delete();
    }
}
