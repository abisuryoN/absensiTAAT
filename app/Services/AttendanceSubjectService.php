<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\AttendanceSubject;
use App\Models\AttendanceSubjectDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceSubjectService
{
    /**
     * Get active academic year and semester.
     */
    protected function getActiveAcademicContext(): array
    {
        $academicYear = AcademicYear::active()->first();
        $semester = Semester::active()->first();

        if (!$academicYear || !$semester) {
            throw new \Exception("Tahun ajaran atau semester aktif tidak ditemukan.");
        }

        return [$academicYear, $semester];
    }

    /**
     * Get teaching schedules for today.
     */
    public function getTodaySchedules(Teacher $teacher): Collection
    {
        try {
            list($academicYear, $semester) = $this->getActiveAcademicContext();
        } catch (\Exception $e) {
            return collect();
        }

        $dayName = strtolower(Carbon::now()->translatedFormat('l'));

        return Schedule::with(['subject', 'class'])
            ->where('teacher_id', $teacher->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->where('is_active', true)
            ->whereRaw('LOWER(day) = ?', [$dayName])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get weekly teaching schedules grouped by day.
     */
    public function getWeeklySchedules(Teacher $teacher): Collection
    {
        try {
            list($academicYear, $semester) = $this->getActiveAcademicContext();
        } catch (\Exception $e) {
            return collect();
        }

        return Schedule::with(['subject', 'class'])
            ->where('teacher_id', $teacher->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->where('is_active', true)
            ->orderByRaw("FIELD(LOWER(day), 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get or create subject attendance for a specific date and schedule.
     */
    public function getOrCreateAttendance(int $scheduleId, string $date): AttendanceSubject
    {
        $schedule = Schedule::findOrFail($scheduleId);
        list($academicYear, $semester) = $this->getActiveAcademicContext();

        return DB::transaction(function () use ($schedule, $date, $academicYear, $semester) {
            // Find existing attendance
            $attendance = AttendanceSubject::where('schedule_id', $schedule->id)
                ->where('date', $date)
                ->first();

            if (!$attendance) {
                // Create draft subject attendance header
                $attendance = AttendanceSubject::create([
                    'schedule_id' => $schedule->id,
                    'teacher_id' => $schedule->teacher_id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'date' => $date,
                    'status' => 'draft',
                    'method' => 'realtime',
                ]);

                // Get all active students in the class
                $students = Student::where('class_id', $schedule->class_id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

                // Initialize all student details with 'hadir' status by default
                foreach ($students as $student) {
                    AttendanceSubjectDetail::create([
                        'attendance_subject_id' => $attendance->id,
                        'student_id' => $student->id,
                        'status' => 'hadir',
                        'note' => null,
                    ]);
                }

                ActivityLogService::logCreate($attendance, "Inisiasi draf absensi mapel untuk kelas {$schedule->class->name} - {$schedule->subject->name} tanggal {$date}");
            }

            // Load details and student relationship
            $attendance->load(['details.student', 'schedule.class', 'schedule.subject']);

            return $attendance;
        });
    }

    /**
     * Save subject attendance details and header state.
     */
    public function saveAttendance(int $attendanceId, array $studentData, string $status, ?string $note = null): AttendanceSubject
    {
        return DB::transaction(function () use ($attendanceId, $studentData, $status, $note) {
            $attendance = AttendanceSubject::with('schedule.class', 'schedule.subject')->findOrFail($attendanceId);
            $original = $attendance->getAttributes();

            $updateData = [
                'status' => $status,
                'note' => $note,
            ];

            if ($status === 'submitted') {
                $updateData['submitted_at'] = Carbon::now();
            }

            $attendance->update($updateData);

            // Save details
            foreach ($studentData as $studentId => $detail) {
                AttendanceSubjectDetail::updateOrCreate(
                    [
                        'attendance_subject_id' => $attendance->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => $detail['status'] ?? 'hadir',
                        'note' => $detail['note'] ?? null,
                    ]
                );
            }

            // Fire activity logs
            if ($status === 'submitted' && $original['status'] === 'draft') {
                ActivityLogService::logUpdate($attendance, $original, "Kirim Absensi Mapel (Final): {$attendance->schedule->subject->name} di {$attendance->schedule->class->name} tanggal {$attendance->date->format('Y-m-d')}");
            } else {
                ActivityLogService::logUpdate($attendance, $original, "Simpan Draf Absensi Mapel: {$attendance->schedule->subject->name} di {$attendance->schedule->class->name} tanggal {$attendance->date->format('Y-m-d')}");
            }

            return $attendance;
        });
    }

    /**
     * Get teaching history and statistics for recap.
     */
    public function getTeachingRecap(Teacher $teacher, string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->format('Y-m-d');
        $end = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->format('Y-m-d');

        $attendances = AttendanceSubject::with(['schedule.class', 'schedule.subject', 'details'])
            ->where('teacher_id', $teacher->id)
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('date')
            ->get();

        $totalClasses = $attendances->count();
        $submittedClasses = $attendances->where('status', 'submitted')->count();
        $draftClasses = $attendances->where('status', 'draft')->count();

        // Calculate student attendance statistics
        $totalPresent = 0;
        $totalStudents = 0;

        foreach ($attendances as $att) {
            if ($att->status === 'submitted') {
                $totalStudents += $att->details->count();
                $totalPresent += $att->details->whereIn('status', ['hadir', 'dispensasi'])->count();
            }
        }

        $averagePresenceRate = $totalStudents > 0
            ? round(($totalPresent / $totalStudents) * 100, 1)
            : 0;

        return [
            'attendances' => $attendances,
            'stats' => [
                'total' => $totalClasses,
                'submitted' => $submittedClasses,
                'draft' => $draftClasses,
                'presence_rate' => $averagePresenceRate,
            ]
        ];
    }
}
