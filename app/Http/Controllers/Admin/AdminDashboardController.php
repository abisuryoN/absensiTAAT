<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\AttendanceGate;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Show real-time statistics and weekly trend on the Admin Dashboard.
     */
    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');

        // 1. Current Stats (New Logic)
        $totalSiswa = Student::where('is_active', true)->count();
        
        // Hadir = attendance hari ini status 'hadir' ATAU 'terlambat' (tetap dianggap hadir walau telat)
        $hadir = AttendanceGate::where('date', $today)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();
        
        // Terlambat = subset dari hadir, hanya yang 'terlambat'
        $terlambat = AttendanceGate::where('date', $today)
            ->where('status', 'terlambat')
            ->count();
        
        // Total siswa yang SUDAH punya record attendance hari ini (apapun statusnya)
        $totalCheckedIn = AttendanceGate::where('date', $today)
            ->whereIn('status', ['hadir', 'terlambat', 'izin', 'sakit', 'tidak_hadir'])
            ->count();
        
        // Tidak Hadir = Total Siswa - (yang sudah punya record hari ini)
        $tidakHadir = max(0, $totalSiswa - $totalCheckedIn);

        // Stat tambahan untuk breakdown
        $izin = AttendanceGate::where('date', $today)
            ->where('status', 'izin')
            ->count();
        $sakit = AttendanceGate::where('date', $today)
            ->where('status', 'sakit')
            ->count();
        $tidakHadirCount = AttendanceGate::where('date', $today)
            ->where('status', 'tidak_hadir')
            ->count();

        // 2. Query 7 Days Trend
        $startWeek = Carbon::today()->subDays(6)->format('Y-m-d');
        $endWeek = Carbon::today()->format('Y-m-d');

        $dailyStats = AttendanceGate::whereBetween('date', [$startWeek, $endWeek])
            ->select('date', 'status', DB::raw('count(*) as count'))
            ->groupBy('date', 'status')
            ->get()
            ->groupBy(fn($row) => Carbon::parse($row->date)->format('Y-m-d'));

        $chartLabels = [];
        $chartHadir = [];
        $chartTerlambat = [];
        $chartSakit = [];
        $chartIzin = [];
        $chartTidakHadir = [];

        for ($i = 6; $i >= 0; $i--) {
            $dateObj = Carbon::today()->subDays($i);
            $dateStr = $dateObj->format('Y-m-d');

            $chartLabels[] = $dateObj->translatedFormat('D, d M');

            $dayStats = $dailyStats->get($dateStr) ?? collect();

            $chartHadir[] = $dayStats->where('status', 'hadir')->sum('count');
            $chartTerlambat[] = $dayStats->where('status', 'terlambat')->sum('count');
            $chartSakit[] = $dayStats->where('status', 'sakit')->sum('count');
            $chartIzin[] = $dayStats->where('status', 'izin')->sum('count');
            $chartTidakHadir[] = $dayStats->where('status', 'tidak_hadir')->sum('count');
        }

        // 3. System activity log (last 10 entries)
        $activities = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalSiswa',
            'hadir',
            'terlambat',
            'tidakHadir',
            'izin',
            'sakit',
            'tidakHadirCount',
            'chartLabels',
            'chartHadir',
            'chartTerlambat',
            'chartSakit',
            'chartIzin',
            'chartTidakHadir',
            'activities'
        ));
    }


    public function switchAcademicYear(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|integer|exists:academic_years,id',
        ]);

        session(['selected_academic_year_id' => $request->integer('academic_year_id')]);

        return back();
    }
}