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

        // Recent attendances (last 10 records this month)
        $monthStart        = Carbon::now()->startOfMonth()->toDateString();
        $recentAttendances = AttendanceGate::where('student_id', $activeStudent->id)
            ->whereBetween('date', [$monthStart, $today])
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

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

        $studentId = $request->get('student_id', $children->first()?->id);
        $student   = $children->firstWhere('id', $studentId) ?? $children->first();

        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to', Carbon::now()->toDateString());

        $records = collect();
        if ($student) {
            $records = AttendanceGate::where('student_id', $student->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date', 'desc')
                ->get();
        }

        return view('parent.rekap_harian', compact('children', 'student', 'records', 'dateFrom', 'dateTo'));
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

        $studentId = $request->get('student_id', $children->first()?->id);
        $student   = $children->firstWhere('id', $studentId) ?? $children->first();

        $year = (int) $request->get('year', now()->year);

        // Build 12-month summary
        $monthlyStats = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyStats[$m] = $student
                ? $this->buildMonthStats($student->id, $year, $m)
                : $this->emptyMonthStats();
        }

        return view('parent.rekap_bulanan', compact('children', 'student', 'monthlyStats', 'year'));
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