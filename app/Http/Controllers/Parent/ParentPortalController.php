<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\AttendanceGate;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ParentPortalController extends Controller
{
    /**
     * Dashboard: summary for the selected child.
     */
    public function dashboard(Request $request)
    {
        $parent = Auth::user()->parent;

        if (!$parent) {
            return view('parent.dashboard', [
                'children'         => collect(),
                'activeStudent'    => null,
                'todayRecord'      => null,
                'summary'          => null,
                'recentAttendances'=> collect(),
            ]);
        }

        $children = $parent->students()->with('class')->where('is_active', true)->get();

        if ($children->isEmpty()) {
            return view('parent.dashboard', [
                'children'         => $children,
                'activeStudent'    => null,
                'todayRecord'      => null,
                'summary'          => null,
                'recentAttendances'=> collect(),
            ]);
        }

        // Switch child via query param
        $studentId     = $request->get('student_id', $children->first()->id);
        $activeStudent = $children->firstWhere('id', $studentId) ?? $children->first();

        // Today's attendance
        $today       = Carbon::today()->toDateString();
        $todayRecord = AttendanceGate::where('student_id', $activeStudent->id)
            ->whereDate('date', $today)
            ->first();

        // Current month summary
        $summary = $this->buildMonthStats($activeStudent->id, now()->year, now()->month);

        // Recent attendances this month – paginated
        $monthStart        = Carbon::now()->startOfMonth()->toDateString();
        $recentAttendances = AttendanceGate::where('student_id', $activeStudent->id)
            ->whereBetween('date', [$monthStart, $today])
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('parent.dashboard', compact(
            'children', 'activeStudent', 'todayRecord', 'summary', 'recentAttendances'
        ));
    }

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

        $children = $parent->students()->with('class')->where('is_active', true)->get();

        $studentId     = $request->get('student_id', $children->first()?->id);
        $activeStudent = $children->firstWhere('id', $studentId) ?? $children->first();

        $from = $request->get('from', Carbon::now()->startOfMonth()->toDateString());
        $to   = $request->get('to', Carbon::now()->toDateString());
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

        $children = $parent->students()->with('class')->where('is_active', true)->get();

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