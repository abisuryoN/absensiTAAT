<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\AttendanceGate;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

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
        $children = $parent->students()
            ->with(['schoolClass.major'])
            ->get();

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

        $children = $parent->students()->with('schoolClass')->get();

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

        $children = $parent->students()->with('schoolClass')->get();

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
            ->get();

        return compact('todayRecord', 'summary', 'recentAttendances');
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

        return [
            'hadir'     => $records->where('status', 'hadir')->count(),
            'terlambat' => $records->where('status', 'terlambat')->count(),
            'izin'      => $records->where('status', 'izin')->count(),
            'sakit'     => $records->where('status', 'sakit')->count(),
            'alpa'      => $records->where('status', 'alpa')->count(),
            'total'     => $records->count(),
        ];
    }

    private function emptyMonthStats(): array
    {
        return ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => 0];
    }
}