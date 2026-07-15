<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\AttendanceSubject;
use App\Services\AttendanceSubjectService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherPortalController extends Controller
{
    protected AttendanceSubjectService $attendanceService;

    public function __construct(AttendanceSubjectService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Helper to get current authenticated teacher.
     */
    protected function getTeacherOrRedirect()
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (!$teacher) {
            abort(403, 'Akses ditolak. Akun Anda tidak terdaftar sebagai Guru.');
        }

        if (!$teacher->is_active) {
            abort(403, 'Akun Guru Anda dinonaktifkan.');
        }

        return $teacher;
    }

    /**
     * Dashboard Guru: hari ini, jadwal mengajar, status absensi.
     */
    public function dashboard()
    {
        $teacher = $this->getTeacherOrRedirect();
        $todaySchedules = $this->attendanceService->getTodaySchedules($teacher);

        $today = Carbon::today()->format('Y-m-d');
        
        // Fetch existing attendance records for today to show status
        $todayAttendances = AttendanceSubject::whereIn('schedule_id', $todaySchedules->pluck('id'))
            ->where('date', $today)
            ->get()
            ->keyBy('schedule_id');

        $schedulesCount = $todaySchedules->count();
        $submittedCount = 0;
        $draftCount = 0;

        foreach ($todaySchedules as $schedule) {
            $att = $todayAttendances->get($schedule->id);
            if ($att) {
                if ($att->status === 'submitted') {
                    $submittedCount++;
                } else {
                    $draftCount++;
                }
            }
        }

        return view('teacher.dashboard', compact(
            'teacher',
            'todaySchedules',
            'todayAttendances',
            'schedulesCount',
            'submittedCount',
            'draftCount',
            'today'
        ));
    }

    /**
     * Jadwal mengajar mingguan.
     */
    public function schedules()
    {
        $teacher = $this->getTeacherOrRedirect();
        $schedules = $this->attendanceService->getWeeklySchedules($teacher)
            ->groupBy(fn($s) => strtolower($s->getRawOriginal('day')));

        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $today = strtolower(Carbon::now()->translatedFormat('l'));

        return view('teacher.schedules', compact('teacher', 'schedules', 'days', 'today'));
    }

    /**
     * Input / Edit Absensi Mapel.
     */
    public function attendanceInput(int $scheduleId, Request $request)
    {
        $teacher = $this->getTeacherOrRedirect();
        $schedule = Schedule::with(['class', 'subject'])->findOrFail($scheduleId);

        // Verify ownership
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'Akses ditolak. Jadwal ini bukan milik Anda.');
        }

        // Validate date, default today
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        try {
            Carbon::parse($date);
        } catch (\Exception $e) {
            $date = Carbon::today()->format('Y-m-d');
        }

        $attendance = $this->attendanceService->getOrCreateAttendance($scheduleId, $date);

        return view('teacher.attendance_input', compact('teacher', 'schedule', 'date', 'attendance'));
    }

    /**
     * Simpan / Kirim Absensi Mapel.
     */
    public function attendanceStore(Request $request)
    {
        $teacher = $this->getTeacherOrRedirect();
        
        $request->validate([
            'attendance_id' => 'required|exists:attendance_subjects,id',
            'status' => 'required|in:draft,submitted',
            'students' => 'required|array',
            'students.*.status' => 'required|in:hadir,izin,sakit,alpha,dispensasi',
            'students.*.note' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $attendance = AttendanceSubject::findOrFail($request->attendance_id);

        if ($attendance->teacher_id !== $teacher->id) {
            abort(403, 'Akses ditolak.');
        }

        try {
            $this->attendanceService->saveAttendance(
                $attendance->id,
                $request->students,
                $request->status,
                $request->note
            );

            $message = $request->status === 'submitted'
                ? 'Absensi mata pelajaran berhasil dikirim.'
                : 'Draf absensi berhasil disimpan.';

            return redirect()->route('teacher.dashboard')->with('success', $message);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }

    /**
     * Rekap mengajar dan riwayat absen mapel.
     */
    public function recap(Request $request)
    {
        $teacher = $this->getTeacherOrRedirect();
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));

        try {
            Carbon::createFromFormat('Y-m', $selectedMonth);
        } catch (\Exception $e) {
            $selectedMonth = Carbon::now()->format('Y-m');
        }

        $recapData = $this->attendanceService->getTeachingRecap($teacher, $selectedMonth);

        $attendances = $recapData['attendances'];
        $stats = $recapData['stats'];

        return view('teacher.recap', compact('teacher', 'attendances', 'stats', 'selectedMonth'));
    }
}
