<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\AttendanceGate;
use App\Models\QrToken;
use App\Models\Setting;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceGateService
{
    protected HolidayService $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    /**
     * Get current datetime — uses mock time & date if testing mode is active.
     */
    private function getCurrentDateTime(): Carbon
    {
        $mockEnabled = Setting::getVal('attendance_mock_enabled', false);
        $mockTime = Setting::getVal('attendance_mock_time', '');
        $mockDate = Setting::getVal('attendance_mock_date', '');

        if (!$mockEnabled) {
            return Carbon::now();
        }

        $mockDateTime = Carbon::now();

        if (!empty($mockDate)) {
            $mockDateTime = Carbon::createFromFormat('Y-m-d H:i', $mockDate . ' ' . $mockDateTime->format('H:i'));
        }

        if (!empty($mockTime)) {
            $parts = explode(':', $mockTime);
            $mockDateTime->setHour((int)$parts[0])->setMinute((int)$parts[1])->setSecond(0);
        }

        return $mockDateTime;
    }

    /**
     * Get current time string (H:i:s) using mock if active.
     */
    private function getCurrentTimeString(): string
    {
        return $this->getCurrentDateTime()->format('H:i:s');
    }

    /**
     * Get current date string (Y-m-d) using mock if active.
     */
    private function getCurrentDateString(): string
    {
        return $this->getCurrentDateTime()->format('Y-m-d');
    }

    /**
     * Validate that the current time is within attendance operating hours.
     * Skips validation if attendance_time_enabled is false.
     */
    private function validateAttendanceTime(): void
    {
        if (!Setting::getVal('attendance_time_enabled', true)) {
            return;
        }

        $currentTime = $this->getCurrentDateTime();
        $gateOpen = Setting::getVal('gate_open_time', '05:30');
        $gateClose = Setting::getVal('gate_close_time', '08:00');

        $openTime = Carbon::createFromFormat('H:i', $gateOpen);
        $closeTime = Carbon::createFromFormat('H:i', $gateClose);

        if ($currentTime->lessThan($openTime)) {
            throw new \Exception("Belum waktunya absen. Gerbang buka pukul {$gateOpen}.");
        }

        if ($currentTime->greaterThan($closeTime)) {
            throw new \Exception("Waktu absen sudah ditutup pukul {$gateClose}.");
        }
    }

    /**
     * Process barcode scanning.
     */
    public function processBarcodeScan(string $barcodeValue, ?int $scannedBy = null, ?int $petugasPiketId = null): AttendanceGate
    {
        return DB::transaction(function () use ($barcodeValue, $scannedBy, $petugasPiketId) {
            $student = Student::where('barcode_id', $barcodeValue)->first();

            if (!$student) {
                throw new \Exception("Barcode ID '{$barcodeValue}' tidak terdaftar.");
            }

            if (!$student->is_active) {
                throw new \Exception("Siswa {$student->name} berstatus tidak aktif.");
            }

            $today = $this->getCurrentDateString();
            if ($this->checkAlreadyScanned($student->id, $today)) {
                throw new \Exception("Siswa {$student->name} sudah melakukan absensi hari ini.");
            }

            // Check if today is holiday
            if ($this->checkHoliday($today)) {
                throw new \Exception("Hari ini adalah hari libur sekolah. Absensi tidak dapat dilakukan.");
            }

            // Cek jam operasional
            $this->validateAttendanceTime();

            $academicYear = AcademicYear::active()->first();
            $semester = Semester::active()->first();

            if (!$academicYear || !$semester) {
                throw new \Exception("Tahun ajaran atau semester aktif tidak ditemukan.");
            }

            $timeIn = $this->getCurrentTimeString();
            $status = $this->getStatusByTime($timeIn);

            $attendance = AttendanceGate::create([
                'student_id'       => $student->id,
                'academic_year_id' => $academicYear->id,
                'semester_id'      => $semester->id,
                'date'             => $today,
                'time_in'          => $timeIn,
                'status'           => $status,
                'method'           => 'barcode',
                'scanned_by'       => $scannedBy,
                'petugas_piket_id' => $petugasPiketId,
            ]);

            // Dispatch notification to parents via queue job
            if (Setting::getVal('whatsapp_enabled', false)) {
                SendWhatsAppNotificationJob::dispatch($attendance);
            }

            ActivityLogService::log(
                'scan',
                "Absensi Gerbang (Barcode): {$student->name} status {$status}",
                $attendance
            );

            return $attendance;
        });
    }

    /**
     * Process QR Token scanning.
     */
    public function processQrScan(string $token, ?int $scannedBy = null, ?int $petugasPiketId = null): AttendanceGate
    {
        return DB::transaction(function () use ($token, $scannedBy, $petugasPiketId) {
            $qrToken = QrToken::where('token', $token)
                ->where('is_used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$qrToken) {
                throw new \Exception("QR Code tidak valid atau sudah kedaluwarsa.");
            }

            $student = $qrToken->student;

            if (!$student->is_active) {
                throw new \Exception("Siswa {$student->name} berstatus tidak aktif.");
            }

            $today = $this->getCurrentDateString();
            if ($this->checkAlreadyScanned($student->id, $today)) {
                throw new \Exception("Siswa {$student->name} sudah melakukan absensi hari ini.");
            }

            // Check if today is holiday
            if ($this->checkHoliday($today)) {
                throw new \Exception("Hari ini adalah hari libur sekolah. Absensi tidak dapat dilakukan.");
            }

            // Cek jam operasional
            $this->validateAttendanceTime();

            $academicYear = AcademicYear::active()->first();
            $semester = Semester::active()->first();

            if (!$academicYear || !$semester) {
                throw new \Exception("Tahun ajaran atau semester aktif tidak ditemukan.");
            }

            // Mark token as used (pakai waktu real, bukan mock — buat keamanan)
            $qrToken->update([
                'is_used' => true,
                'used_at' => Carbon::now(),
            ]);

            $timeIn = $this->getCurrentTimeString();
            $status = $this->getStatusByTime($timeIn);

            $attendance = AttendanceGate::create([
                'student_id'       => $student->id,
                'academic_year_id' => $academicYear->id,
                'semester_id'      => $semester->id,
                'date'             => $today,
                'time_in'          => $timeIn,
                'status'           => $status,
                'method'           => 'qr_code',
                'scanned_by'       => $scannedBy,
                'petugas_piket_id' => $petugasPiketId,
            ]);

            // Dispatch notification to parents
            if (Setting::getVal('whatsapp_enabled', false)) {
                SendWhatsAppNotificationJob::dispatch($attendance);
            }

            ActivityLogService::log(
                'scan',
                "Absensi Gerbang (QR): {$student->name} status {$status}",
                $attendance
            );

            return $attendance;
        });
    }

    /**
     * Record manual attendance (e.g. from office).
     */
    public function manualAttendance(int $studentId, string $status, ?string $note = null, ?int $scannedBy = null): AttendanceGate
    {
        return DB::transaction(function () use ($studentId, $status, $note, $scannedBy) {
            $student = Student::findOrFail($studentId);

            if (!$student->is_active) {
                throw new \Exception("Siswa {$student->name} berstatus tidak aktif.");
            }

            $today = Carbon::today()->format('Y-m-d');

            // Check if already scanned, update if exists, otherwise create
            $attendance = AttendanceGate::where('student_id', $studentId)
                ->where('date', $today)
                ->first();

            $academicYear = AcademicYear::active()->first();
            $semester = Semester::active()->first();

            if (!$academicYear || !$semester) {
                throw new \Exception("Tahun ajaran atau semester aktif tidak ditemukan.");
            }

            if ($attendance) {
                $original = $attendance->getAttributes();
                $attendance->update([
                    'status' => $status,
                    'method' => 'manual',
                    'note' => $note,
                    'scanned_by' => $scannedBy,
                ]);
                ActivityLogService::logUpdate($attendance, $original, "Pembaruan Manual Absensi Gerbang: {$student->name} ke {$status}");
            } else {
                $attendance = AttendanceGate::create([
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'date' => $today,
                    'time_in' => Carbon::now()->format('H:i:s'),
                    'status' => $status,
                    'method' => 'manual',
                    'note' => $note,
                    'scanned_by' => $scannedBy,
                ]);
                ActivityLogService::logCreate($attendance, "Absensi Gerbang Manual: {$student->name} status {$status}");
            }

            // Send notification to parents
            if (Setting::getVal('whatsapp_enabled', false)) {
                SendWhatsAppNotificationJob::dispatch($attendance);
            }

            return $attendance;
        });
    }

    /**
     * Determine status by scan time.
     */
    public function getStatusByTime(string $timeIn): string
    {
        $startTime = Setting::getVal('school_start_time', '06:30');
        $threshold = (int) Setting::getVal('late_threshold_minutes', 15);

        $limitTime = Carbon::createFromFormat('H:i', $startTime)->addMinutes($threshold);
        $scanTime = Carbon::createFromFormat('H:i:s', $timeIn);

        return $scanTime->greaterThan($limitTime) ? 'terlambat' : 'hadir';
    }

    /**
     * Check if student has already scanned today.
     */
    public function checkAlreadyScanned(int $studentId, string $date): bool
    {
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();
        return AttendanceGate::where('student_id', $studentId)
            ->whereBetween('date', [$start, $end])
            ->exists();
    }

    /**
     * Check if date is holiday.
     */
    public function checkHoliday(string $date): bool
    {
        $checkDate = $this->getCurrentDateTime();
        $dayOfWeek = $checkDate->dayOfWeek;

        // Saturday (6) and Sunday (0) are holidays
        if ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY) {
            return true;
        }

        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();
        return \App\Models\Holiday::whereBetween('date', [$start, $end])->exists();
    }

    /**
     * Mark active students who haven't scanned today as Absent (Alpha).
     */
    public function markAbsentStudents(?string $date = null): int
    {
        $targetDate = $date ?? Carbon::today()->format('Y-m-d');

        if ($this->checkHoliday($targetDate)) {
            return 0;
        }

        $academicYear = AcademicYear::active()->first();
        $semester = Semester::active()->first();

        if (!$academicYear || !$semester) {
            return 0;
        }

        return DB::transaction(function () use ($targetDate, $academicYear, $semester) {
            $studentsWithoutAttendance = Student::where('is_active', true)
                ->whereNotExists(function ($query) use ($targetDate) {
                    $query->select(DB::raw(1))
                        ->from('attendance_gates')
                        ->whereColumn('attendance_gates.student_id', 'students.id')
                        ->where('attendance_gates.date', $targetDate);
                })
                ->get();

            $count = 0;
            foreach ($studentsWithoutAttendance as $student) {
                AttendanceGate::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'date' => $targetDate,
                    'time_in' => '00:00:00',
                    'status' => 'alpha',
                    'method' => 'manual',
                ]);
                $count++;
            }

            if ($count > 0) {
                ActivityLogService::log(
                    'system',
                    "Auto-mark Alpha: {$count} siswa ditandai Alpha pada tanggal {$targetDate}",
                    null
                );
            }

            return $count;
        });
    }
}
