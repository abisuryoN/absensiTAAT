<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AttendanceGate;
use App\Models\QrToken;
use App\Models\SchoolSession;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Student;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceGateService
{
    public function __construct(
        protected HolidayService              $holidayService,
        protected DateTimeService             $dateTimeService,
        protected SchoolSessionResolverService $sessionResolver,
    ) {}

    // =========================================================================
    // BARCODE SCAN
    // =========================================================================

    public function processBarcodeScan(
        string $barcodeValue,
        ?int   $scannedBy      = null,
        ?int   $petugasPiketId = null
    ): AttendanceGate {
        return DB::transaction(function () use ($barcodeValue, $scannedBy, $petugasPiketId) {
            $student = Student::where('barcode_id', $barcodeValue)->first();

            if (!$student) {
                throw new \Exception("Barcode ID '{$barcodeValue}' tidak terdaftar.");
            }

            return $this->createAttendance($student, 'barcode', $scannedBy, $petugasPiketId);
        });
    }

    // =========================================================================
    // QR SCAN
    // =========================================================================

    public function processQrScan(
        string $token,
        ?int   $scannedBy      = null,
        ?int   $petugasPiketId = null
    ): AttendanceGate {
        return DB::transaction(function () use ($token, $scannedBy, $petugasPiketId) {
            $qrToken = QrToken::where('token', $token)
                ->where('is_used', false)
                ->where('expires_at', '>', $this->dateTimeService->now())
                ->first();

            if (!$qrToken) {
                throw new \Exception("QR Code tidak valid atau sudah kedaluwarsa.");
            }

            $student = $qrToken->student;

            // Mark token as used
            $qrToken->update([
                'is_used' => true,
                'used_at' => $this->dateTimeService->now(),
            ]);

            return $this->createAttendance($student, 'qr_code', $scannedBy, $petugasPiketId);
        });
    }

    // =========================================================================
    // MANUAL ATTENDANCE
    // =========================================================================

    public function manualAttendance(
        int     $studentId,
        string  $status,
        ?string $note      = null,
        ?int    $scannedBy = null
    ): AttendanceGate {
        return DB::transaction(function () use ($studentId, $status, $note, $scannedBy) {
            $student = Student::findOrFail($studentId);

            if (!$student->is_active) {
                throw new \Exception("Siswa {$student->name} berstatus tidak aktif.");
            }

            $today = $this->dateTimeService->currentDate();

            [$academicYear, $semester] = $this->getActiveContext();

            $attendance = AttendanceGate::where('student_id', $studentId)
                ->where('date', $today)
                ->first();

            if ($attendance) {
                $original = $attendance->getAttributes();
                $attendance->update([
                    'status'     => $status,
                    'method'     => 'manual',
                    'note'       => $note,
                    'scanned_by' => $scannedBy,
                ]);
                ActivityLogService::logUpdate($attendance, $original, "Pembaruan Manual Absensi Gerbang: {$student->name} ke {$status}");
            } else {
                $attendance = AttendanceGate::create([
                    'student_id'       => $studentId,
                    'academic_year_id' => $academicYear->id,
                    'semester_id'      => $semester->id,
                    'date'             => $today,
                    'time_in'          => $this->dateTimeService->currentTime(),
                    'status'           => $status,
                    'method'           => 'manual',
                    'note'             => $note,
                    'scanned_by'       => $scannedBy,
                ]);
                ActivityLogService::logCreate($attendance, "Absensi Gerbang Manual: {$student->name} status {$status}");
            }

            if (Setting::getVal('whatsapp_enabled', false)) {
                SendWhatsAppNotificationJob::dispatch($attendance);
            }

            return $attendance;
        });
    }

    // =========================================================================
    // STATUS CALCULATION
    // =========================================================================

    /**
     * Determine attendance status by scan time and session config.
     * Session may be null when multiple_sessions is disabled (use global settings).
     */
    public function getStatusByTime(string $timeIn, ?SchoolSession $session = null): string
    {
        if ($session) {
            $startTime = $session->school_start_time;
            $threshold = $session->late_threshold_minutes;
        } else {
            $startTime = Setting::getVal('school_start_time', '06:30');
            $threshold = (int) Setting::getVal('late_threshold_minutes', 15);
        }

        $limitTime = Carbon::createFromFormat('H:i', substr($startTime, 0, 5))->addMinutes($threshold);
        $scanTime  = Carbon::createFromFormat('H:i:s', $timeIn);

        return $scanTime->greaterThan($limitTime) ? 'terlambat' : 'hadir';
    }

    // =========================================================================
    // GATE WINDOW CHECK
    // =========================================================================

    /**
     * Validate whether scanning is allowed at the current time for a session.
     * Returns ['allowed' => bool, 'message' => string|null].
     */
    public function validateGateWindow(?SchoolSession $session): array
    {
        if (!$session || !$this->dateTimeService->isMultipleSessionsEnabled()) {
            return ['allowed' => true, 'message' => null];
        }

        $now       = $this->dateTimeService->now();
        $gateOpen  = Carbon::createFromFormat('H:i', substr($session->gate_open_time, 0, 5));
        $schoolEnd = Carbon::createFromFormat('H:i', substr($session->school_end_time, 0, 5));

        if ($now->lt($gateOpen)) {
            return [
                'allowed' => false,
                'message' => "Gerbang sesi {$session->name} belum dibuka. Dibuka pukul " . substr($session->gate_open_time, 0, 5) . ".",
            ];
        }

        if ($now->gt($schoolEnd)) {
            return [
                'allowed' => false,
                'message' => "Sesi {$session->name} telah berakhir pukul " . substr($session->school_end_time, 0, 5) . ".",
            ];
        }

        return ['allowed' => true, 'message' => null];
    }

    // =========================================================================
    // AUTO ALPHA (TIDAK HADIR)
    // =========================================================================

    /**
     * Mark students without attendance today as tidak_hadir.
     * When multi-session is enabled, only marks students whose session's
     * auto_alpha_time has already passed.
     */
    public function markAbsentStudents(?string $date = null): int
    {
        $targetDate = $date ?? $this->dateTimeService->currentDate();

        // Skip holidays
        if ($this->holidayService->isHolidayDate($this->dateTimeService->today())) {
            return 0;
        }

        [$academicYear, $semester] = $this->getActiveContext();
        if (!$academicYear || !$semester) {
            return 0;
        }

        return DB::transaction(function () use ($targetDate, $academicYear, $semester) {
            $multiSession = $this->dateTimeService->isMultipleSessionsEnabled();
            $now          = $this->dateTimeService->now();

            $studentsWithoutAttendance = Student::where('is_active', true)
                ->with(['schoolClass.schoolSession'])
                ->whereNotExists(function ($query) use ($targetDate) {
                    $start = Carbon::parse($targetDate)->startOfDay();
                    $end   = Carbon::parse($targetDate)->endOfDay();
                    $query->select(DB::raw(1))
                        ->from('attendance_gates')
                        ->whereColumn('attendance_gates.student_id', 'students.id')
                        ->whereBetween('attendance_gates.date', [$start, $end]);
                })
                ->get();

            $count = 0;

            foreach ($studentsWithoutAttendance as $student) {
                // If multi-session: only mark alpha if session's auto_alpha_time has passed
                if ($multiSession) {
                    $session = $this->sessionResolver->resolve($student);
                    if ($session) {
                        $autoAlpha = Carbon::createFromFormat('H:i', substr($session->auto_alpha_time, 0, 5));
                        if ($now->lt($autoAlpha)) {
                            continue; // Session not yet past auto-alpha time
                        }
                    }
                } else {
                    $autoAlphaStr = Setting::getVal('auto_alpha_time', '23:00');
                    $autoAlpha    = Carbon::createFromFormat('H:i', substr($autoAlphaStr, 0, 5));
                    if ($now->lt($autoAlpha)) {
                        continue;
                    }
                }

                AttendanceGate::create([
                    'student_id'       => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id'      => $semester->id,
                    'date'             => $targetDate,
                    'time_in'          => '00:00:00',
                    'status'           => 'tidak_hadir',
                    'method'           => 'manual',
                ]);
                $count++;
            }

            if ($count > 0) {
                ActivityLogService::log(
                    'system',
                    "Auto-mark Tidak Hadir: {$count} siswa ditandai pada tanggal {$targetDate}",
                    null
                );
            }

            return $count;
        });
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function checkAlreadyScanned(int $studentId, string $date): bool
    {
        $start = Carbon::parse($date)->startOfDay();
        $end   = Carbon::parse($date)->endOfDay();
        return AttendanceGate::where('student_id', $studentId)
            ->whereBetween('date', [$start, $end])
            ->exists();
    }

    public function checkHoliday(string $date): bool
    {
        return $this->holidayService->isHolidayDate(Carbon::parse($date));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    protected function createAttendance(
        Student $student,
        string  $method,
        ?int    $scannedBy,
        ?int    $petugasPiketId
    ): AttendanceGate {
        if (!$student->is_active) {
            throw new \Exception("Siswa {$student->name} berstatus tidak aktif.");
        }

        $today = $this->dateTimeService->currentDate();

        if ($this->checkAlreadyScanned($student->id, $today)) {
            throw new \Exception("Siswa {$student->name} sudah melakukan absensi hari ini.");
        }

        // Holiday check uses simulated date via HolidayService
        $holidayCheck = $this->holidayService->isHoliday($this->dateTimeService->today());
        if ($holidayCheck['is_holiday']) {
            throw new \Exception("Hari ini adalah hari libur ({$holidayCheck['reason']}). Absensi tidak dapat dilakukan.");
        }

        [$academicYear, $semester] = $this->getActiveContext();

        // Resolve session (multi-session mode aware)
        $session = $this->dateTimeService->isMultipleSessionsEnabled()
            ? $this->sessionResolver->resolve($student)
            : null;

        // Gate window validation
        $gateCheck = $this->validateGateWindow($session);
        if (!$gateCheck['allowed']) {
            throw new \Exception($gateCheck['message']);
        }

        $timeIn = $this->dateTimeService->currentTime();
        $status = $this->getStatusByTime($timeIn, $session);

        $attendance = AttendanceGate::create([
            'student_id'       => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'date'             => $today,
            'time_in'          => $timeIn,
            'status'           => $status,
            'method'           => $method,
            'scanned_by'       => $scannedBy,
            'petugas_piket_id' => $petugasPiketId,
        ]);

        if (Setting::getVal('whatsapp_enabled', false)) {
            SendWhatsAppNotificationJob::dispatch($attendance);
        }

        ActivityLogService::log(
            'scan',
            "Absensi Gerbang (" . strtoupper($method) . "): {$student->name} status {$status}",
            $attendance
        );

        return $attendance;
    }

    protected function getActiveContext(): array
    {
        $academicYear = AcademicYear::active()->first();
        $semester     = Semester::active()->first();

        if (!$academicYear || !$semester) {
            throw new \Exception("Tahun ajaran atau semester aktif tidak ditemukan.");
        }

        return [$academicYear, $semester];
    }
}
