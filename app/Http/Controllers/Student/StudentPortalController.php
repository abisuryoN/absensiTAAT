<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AttendanceGate;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Services\QrTokenService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentPortalController extends Controller
{
    protected QrTokenService $qrService;

    public function __construct(QrTokenService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Dashboard siswa: info profil, statistik kehadiran, QR placeholder.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->with(['class', 'parent'])->first();

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan untuk akun ini.');
        }

        $semester = Semester::active()->first();

        // Attendance stats for current month
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->format('Y-m-d');
        $monthEnd = $now->copy()->endOfMonth()->format('Y-m-d');

        $monthlyAttendance = AttendanceGate::where('student_id', $student->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $totalDays = $monthlyAttendance->count();
        $hadirCount = $monthlyAttendance->where('status', 'hadir')->count();
        $terlambatCount = $monthlyAttendance->where('status', 'terlambat')->count();
        $izinCount = $monthlyAttendance->where('status', 'izin')->count();
        $sakitCount = $monthlyAttendance->where('status', 'sakit')->count();
        $alphaCount = $monthlyAttendance->where('status', 'alpha')->count();

        $attendancePercent = $totalDays > 0
            ? round((($hadirCount + $terlambatCount) / $totalDays) * 100, 1)
            : 0;

        // Today's schedule
        $dayName = strtolower($now->translatedFormat('l'));
        $todaySchedules = collect();

        if ($student->class_id && $semester) {
            $todaySchedules = Schedule::with(['subject', 'teacher'])
                ->where('class_id', $student->class_id)
                ->where('semester_id', $semester->id)
                ->where('is_active', true)
                ->whereRaw('LOWER(day) = ?', [$dayName])
                ->orderBy('start_time')
                ->get();
        }

        $qrTtl = (int) config('absensi.qr_token.ttl_seconds', 30);

        return view('student.dashboard', compact(
            'student',
            'attendancePercent',
            'hadirCount',
            'terlambatCount',
            'izinCount',
            'sakitCount',
            'alphaCount',
            'totalDays',
            'todaySchedules',
            'qrTtl',
        ));
    }

    /**
     * Generate QR token (AJAX endpoint).
     */
    public function generateQr(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        if (!$student->is_active) {
            return response()->json(['success' => false, 'message' => 'Akun siswa tidak aktif.'], 403);
        }

        $qrToken = $this->qrService->generateToken($student);

        // Generate SVG QR Code
        $qrSvg = QrCode::format('svg')
            ->size(200)
            ->errorCorrection('H')
            ->generate($qrToken->token);

        $ttl = (int) config('absensi.qr_token.ttl_seconds', 30);

        return response()->json([
            'success' => true,
            'data' => [
                'qr_svg' => base64_encode($qrSvg),
                'token' => substr($qrToken->token, 0, 8) . '****',
                'expires_at' => $qrToken->expires_at->toIso8601String(),
                'ttl_seconds' => $ttl,
            ],
        ]);
    }

    /**
     * Jadwal pelajaran mingguan siswa.
     */
    public function schedule()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->with('class')->first();

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $semester = Semester::active()->first();
        $schedules = collect();

        if ($student->class_id && $semester) {
            $schedules = Schedule::with(['subject', 'teacher'])
                ->where('class_id', $student->class_id)
                ->where('semester_id', $semester->id)
                ->where('is_active', true)
                ->orderByRaw("FIELD(LOWER(day), 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
                ->orderBy('start_time')
                ->get()
                ->groupBy(fn($s) => strtolower($s->getRawOriginal('day')));
        }

        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $today = strtolower(Carbon::now()->translatedFormat('l'));

        return view('student.schedule', compact('student', 'schedules', 'days', 'today'));
    }

    /**
     * Riwayat kehadiran siswa.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->with('class')->first();

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $query = AttendanceGate::where('student_id', $student->id)
            ->orderByDesc('date');

        // Filter by month
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        if ($selectedMonth) {
            $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $query->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')]);
        }

        // Filter by status
        $selectedStatus = $request->input('status');
        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        $attendances = $query->paginate(20)->appends($request->query());

        // Summary stats for selected month
        $summaryQuery = AttendanceGate::where('student_id', $student->id);
        if ($selectedMonth) {
            $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $summaryQuery->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')]);
        }
        $summary = $summaryQuery->get();

        $stats = [
            'total' => $summary->count(),
            'hadir' => $summary->where('status', 'hadir')->count(),
            'terlambat' => $summary->where('status', 'terlambat')->count(),
            'izin' => $summary->where('status', 'izin')->count(),
            'sakit' => $summary->where('status', 'sakit')->count(),
            'alpha' => $summary->where('status', 'alpha')->count(),
        ];

        return view('student.history', compact('student', 'attendances', 'stats', 'selectedMonth', 'selectedStatus'));
    }
}
