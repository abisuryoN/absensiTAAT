<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function getAll(array $filters = [])
    {
        $query = Schedule::with(['academicYear', 'semester', 'teacher', 'subject', 'class']);

        if (!empty($filters['search'])) {
            $query->whereHas('teacher', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            })->orWhereHas('subject', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            })->orWhereHas('class', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (!empty($filters['day'])) {
            $query->where('day', $filters['day']);
        }

        return $query->orderBy('day')->orderBy('start_time')->paginate(15);
    }

    public function store(array $data): Schedule
    {
        return DB::transaction(function () use ($data) {
            $this->checkConflicts($data);

            $schedule = Schedule::create($data);
            ActivityLogService::logCreate($schedule, "Menambahkan Jadwal Pelajaran ID: {$schedule->id}");

            return $schedule;
        });
    }

    public function update(Schedule $schedule, array $data): Schedule
    {
        return DB::transaction(function () use ($schedule, $data) {
            $original = $schedule->getAttributes();
            $this->checkConflicts($data, $schedule->id);

            $schedule->update($data);
            ActivityLogService::logUpdate($schedule, $original, "Mengubah Jadwal Pelajaran ID: {$schedule->id}");

            return $schedule;
        });
    }

    public function delete(Schedule $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            if ($schedule->attendanceSubjects()->count() > 0) {
                throw new \Exception('Jadwal tidak dapat dihapus karena sudah memiliki rekaman absensi mata pelajaran.');
            }

            ActivityLogService::logDelete($schedule, "Menghapus Jadwal Pelajaran ID: {$schedule->id}");
            $schedule->delete();
        });
    }

    /**
     * Check schedule conflicts.
     */
    protected function checkConflicts(array $data, ?int $ignoreId = null): void
    {
        $day = strtolower($data['day']);
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];
        $teacherId = $data['teacher_id'];
        $classId = $data['class_id'];
        $academicYearId = $data['academic_year_id'] ?? \App\Models\AcademicYear::active()->first()?->id;
        $semesterId = $data['semester_id'] ?? \App\Models\Semester::active()->first()?->id;

        // 1. Check teacher conflict: Guru tidak boleh mengajar di kelas lain pada waktu yang sama
        $teacherConflict = Schedule::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('teacher_id', $teacherId)
            ->where('day', $day)
            ->where('is_active', true)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($ignoreId) {
            $teacherConflict->where('id', '!=', $ignoreId);
        }

        if ($teacherConflict->exists()) {
            $conflicting = $teacherConflict->first();
            throw new \Exception("Konflik Jadwal: Guru yang bersangkutan sudah mengajar kelas {$conflicting->class->name} pada {$day} jam {$conflicting->start_time} - {$conflicting->end_time}.");
        }

        // 2. Check class conflict: Kelas tidak boleh menerima pelajaran lain pada waktu yang sama
        $classConflict = Schedule::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('class_id', $classId)
            ->where('day', $day)
            ->where('is_active', true)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($ignoreId) {
            $classConflict->where('id', '!=', $ignoreId);
        }

        if ($classConflict->exists()) {
            $conflicting = $classConflict->first();
            throw new \Exception("Konflik Jadwal: Kelas sudah menerima pelajaran {$conflicting->subject->name} oleh {$conflicting->teacher->name} pada {$day} jam {$conflicting->start_time} - {$conflicting->end_time}.");
        }
    }
}
