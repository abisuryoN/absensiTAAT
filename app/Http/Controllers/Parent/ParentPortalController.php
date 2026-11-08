<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\AttendanceGate;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\Setting;
use App\Services\DateTimeService;
use App\Services\SchoolSessionResolverService;

class ParentPortalController extends Controller
{
    // ──────────────────────────────────────────────
    // Dashboard
    // ──────────────────────────────────────────────

    /**
     * Main parent dashboard page.
     */
    public function dashboard(Request $request)
    {
        $parent = Auth::user()->parent;

        if (!$parent) {
            return view('parent.dashboard', [
                'children'      => collect(),
                'activeStudent' => null,
            ]);
        }

        // Eager-load class & major to avoid N+1
        // Note: show ALL children (active or not) so parents can always see their child's data
        $children = $parent->students()->get();

        if ($children->isEmpty()) {
            return view('parent.dashboard', [
                'children'      => $children,
                'activeStudent' => null,
            ]);
        }

        // Determine active student:
        // 1) query param  2) session  3) first child
        $sessionStudentId = session('parent_selected_student');
        $requestStudentId = $request->get('student_id');

        $preferredId   = $requestStudentId ?? $sessionStudentId ?? $children->first()->id;
        $activeStudent = $children->firstWhere('id', $preferredId) ?? $children->first();

        // Persist selection to session
        session(['parent_selected_student' => $activeStudent->id]);

        $dashboardData = $this->getDashboardData($activeStudent);

        return view('parent.dashboard', array_merge(
            ['children' => $children, 'activeStudent' => $activeStudent],
            $dashboardData
        ));
    }

    /**
     * AJAX endpoint: returns rendered HTML partial for a specific student.
     * Secured by StudentParentPolicy@viewDashboard.
     *
     * GET /parent/dashboard/student/{student}
     */
    public function studentData(Request $request, Student $student)
    {
        // Policy check: student must belong to the authenticated parent
        Gate::authorize('viewDashboard', $student);

        // Persist to session
        session(['parent_selected_student' => $student->id]);

        $dashboardData = $this->getDashboardData($student);

        return view('parent.partials.dashboard-content', array_merge(
            ['activeStudent' => $student],
            $dashboardData
        ));
    }

    // ──────────────────────────────────────────────
    // Rekap Harian
    // ──────────────────────────────────────────────

    /**
     * Daily attendance recap for a specific date range.
     */
    public function rekapHarian(Request $request)
    {
        $parent = Auth::user()->parent;

        if (!$parent) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Data orang tua tidak ditemukan untuk akun ini.');
        }

        $children = $parent->students()->get();

        $studentId     = $request->get('student_id', $children->first()?->id);
        $activeStudent = $children->firstWhere('id', $studentId) ?? $children->first();

        $from   = $request->get('from', Carbon::now()->startOfMonth()->toDateString());
        $to     = $request->get('to', Carbon::now()->toDateString());
        $status = $request->get('status');

        $attendances = new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 15);
        if ($activeStudent) {
            $query = AttendanceGate::where('student_id', $activeStudent->id)
                ->whereBetween('date', [$from, $to]);

            if ($status) {
                $query->where('status', $status);
            }

            $attendances = $query->orderBy('date', 'desc')->paginate(15);
            $attendances->getCollection()->transform(fn ($a) => $this->withLateInfo($a, $activeStudent));
        }

        return view('parent.rekap_harian', compact('children', 'activeStudent', 'attendances', 'from', 'to'));
    }

    // ──────────────────────────────────────────────
    // Rekap Bulanan
    // ──────────────────────────────────────────────

    /**
     * Monthly attendance recap.
     */
    public function rekapBulanan(Request $request)
    {
        $parent = Auth::user()->parent;

        if (!$parent) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Data orang tua tidak ditemukan untuk akun ini.');
        }

        $children = $parent->students()->get();

        $studentId     = $request->get('student_id', $children->first()?->id);
        $activeStudent = $children->firstWhere('id', $studentId) ?? $children->first();

        $selectedYear = (int) $request->get('year', now()->year);

        $months = [
            1 => 'Januari', 2 => 'Februari',  3 => 'Maret',
            4 => 'April',   5 => 'Mei',        6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',    9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // Build all 12 months
        $allMonths = [];
        foreach ($months as $m => $label) {
            $stats = $activeStudent
                ? $this->buildMonthStats($activeStudent->id, $selectedYear, $m)
                : $this->emptyMonthStats();
            $stats['month_label'] = $label;
            $allMonths[] = $stats;
        }

        // Paginate the 12-month array manually (6 per page)
        $perPage     = 6;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $offset      = ($currentPage - 1) * $perPage;
        $pageItems   = array_slice($allMonths, $offset, $perPage);

        $monthlyData = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageItems,
            count($allMonths),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('parent.rekap_bulanan', compact('children', 'activeStudent', 'monthlyData', 'selectedYear'));
    }

    // ──────────────────────────────────────────────
    // Shared Data Builder
    // ──────────────────────────────────────────────

    /**
     * Build all dashboard data for a given student.
     * Used by both dashboard() and studentData() to avoid duplicate queries.
     */
    private function getDashboardData(Student $student): array
    {
        $today       = Carbon::today()->toDateString();
        $monthStart  = Carbon::now()->startOfMonth()->toDateString();

        // Today's attendance record
        $todayRecord = AttendanceGate::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        // Current-month statistics
        $summary = $this->buildMonthStats($student->id, now()->year, now()->month);

        // Recent 10 attendances this month (no pagination on dashboard)
        $recentAttendances = AttendanceGate::where('student_id', $student->id)
            ->whereBetween('date', [$monthStart, $today])
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($a) => $this->withLateInfo($a, $student));

        if ($todayRecord) {
            $this->withLateInfo($todayRecord, $student);
        }

        // Six-month trend data for chart
        $sixMonthTrend = $this->buildSixMonthTrend($student->id);

        // Monthly comparison (current vs previous month)
        $monthlyComparison = $this->buildMonthlyComparison($student->id);

        return compact('todayRecord', 'summary', 'recentAttendances', 'sixMonthTrend', 'monthlyComparison');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function buildMonthStats(int $studentId, int $year, int $month): array
    {
        $records = AttendanceGate::where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $hadir = $records->where('status', 'hadir')->count();
        $terlambat = $records->where('status', 'terlambat')->count();
        $izin = $records->where('status', 'izin')->count();
        $sakit = $records->where('status', 'sakit')->count();
        $tidakHadir = $records->where('status', 'tidak_hadir')->count();
        $total = $records->count();

        // Calculate attendance percentage (hadir + terlambat considered as "attended")
        $attended = $hadir + $terlambat;
        $percentage = $total > 0 ? round(($attended / $total) * 100, 1) : 0;

        return [
            'hadir'       => $hadir,
            'terlambat'   => $terlambat,
            'izin'        => $izin,
            'sakit'       => $sakit,
            'tidak_hadir' => $tidakHadir,
            'total'       => $total,
            'percentage'  => $percentage,
        ];
    }

    /**
     * Tambahkan atribut bantu ke baris absensi:
     *  - late_minutes : lama terlambat (menit) dihitung dari jam masuk sekolah, hanya untuk status terlambat.
     */
    private function withLateInfo(AttendanceGate $att, Student $student): AttendanceGate
    {
        $att->late_minutes = null;

        if ($att->status === 'terlambat' && $att->time_in) {
            try {
                $start = $this->schoolStartTime($student);
                $in    = Carbon::createFromFormat('H:i:s', substr($att->time_in, 0, 8));
                $att->late_minutes = max(0, (int) $start->diffInMinutes($in, false));
            } catch (\Throwable $e) {
                $att->late_minutes = null;
            }
        }

        return $att;
    }

    private function schoolStartTime(Student $student): Carbon
    {
        $start = null;

        if (app(DateTimeService::class)->isMultipleSessionsEnabled()) {
            $session = app(SchoolSessionResolverService::class)->resolve($student);
            $start   = $session?->school_start_time;
        }

        $start ??= Setting::getVal('school_start_time', '06:30');

        return Carbon::createFromFormat('H:i', substr($start, 0, 5));
    }

    private function emptyMonthStats(): array
    {
        return ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'tidak_hadir' => 0, 'total' => 0];
    }

    /**
     * Build six-month trend data for chart.
     * Returns array of 6 months (oldest to newest) with attendance stats.
     */
    private function buildSixMonthTrend(int $studentId): array
    {
        $trend = [];
        $now = Carbon::now()->startOfMonth(); // Normalize to first day of month

        // Get last 6 months (including current month)
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonthsNoOverflow($i);
            $stats = $this->buildMonthStats($studentId, $date->year, $date->month);
            $stats['month_label'] = $date->translatedFormat('M Y'); // e.g., "Okt 2026"
            $stats['month_short'] = $date->translatedFormat('M'); // e.g., "Okt"
            $trend[] = $stats;
        }

        return $trend;
    }

    /**
     * Build monthly comparison between current and previous month.
     */
    private function buildMonthlyComparison(int $studentId): array
    {
        $now = Carbon::now()->startOfMonth(); // Normalize to first day of month
        $currentMonth = $this->buildMonthStats($studentId, $now->year, $now->month);

        $prevDate = $now->copy()->subMonthNoOverflow();
        $previousMonth = $this->buildMonthStats($studentId, $prevDate->year, $prevDate->month);

        // Check if we have valid data for comparison
        $currentTotal = $currentMonth['total'] ?? 0;
        $previousTotal = $previousMonth['total'] ?? 0;

        // Calculate change in percentage
        $currentPct = $currentMonth['percentage'] ?? 0;
        $previousPct = $previousMonth['percentage'] ?? 0;
        $change = $currentPct - $previousPct;

        return [
            'current' => $currentMonth,
            'previous' => $previousMonth,
            'current_label' => $now->translatedFormat('F Y'),
            'previous_label' => $prevDate->translatedFormat('F Y'),
            'change' => $change,
            'change_abs' => abs($change),
            'is_improvement' => $change >= 0,
            'has_current_data' => $currentTotal > 0,
            'has_previous_data' => $previousTotal > 0,
            'can_compare' => $currentTotal > 0 && $previousTotal > 0,
        ];
    }
}