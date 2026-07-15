<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AttendanceGate;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Show real-time statistics and weekly trend on the Admin Dashboard.
     */
    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');

        // 1. Current Stats
        $totalSiswa = Student::where('is_active', true)->count();
        $hadir = AttendanceGate::where('date', $today)->where('status', 'hadir')->count();
        $terlambat = AttendanceGate::where('date', $today)->where('status', 'terlambat')->count();
        
        // Sick/Permission/Alpha explicitly marked
        $sakitIzinAlpha = AttendanceGate::where('date', $today)
            ->whereIn('status', ['sakit', 'izin', 'alpha'])
            ->count();
        
        $totalCheckedIn = AttendanceGate::where('date', $today)->count();
        $belumAbsen = max(0, $totalSiswa - $totalCheckedIn);

        // 2. Query 7 Days Trend
        $startWeek = Carbon::today()->subDays(6)->format('Y-m-d');
        $endWeek = Carbon::today()->format('Y-m-d');

        $dailyStats = AttendanceGate::whereBetween('date', [$startWeek, $endWeek])
            ->select('date', 'status', DB::raw('count(*) as count'))
            ->groupBy('date', 'status')
            ->get()
            ->groupBy(fn($row) => $row->date->format('Y-m-d'));

        $chartLabels = [];
        $chartHadir = [];
        $chartTerlambat = [];
        $chartAlpha = [];

        for ($i = 6; $i >= 0; $i--) {
            $dateObj = Carbon::today()->subDays($i);
            $dateStr = $dateObj->format('Y-m-d');

            $chartLabels[] = $dateObj->translatedFormat('D, d M');

            $dayStats = $dailyStats->get($dateStr) ?? collect();

            $chartHadir[] = $dayStats->where('status', 'hadir')->first()?->count ?? 0;
            $chartTerlambat[] = $dayStats->where('status', 'terlambat')->first()?->count ?? 0;
            $chartAlpha[] = $dayStats->where('status', 'alpha')->first()?->count ?? 0;
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
            'sakitIzinAlpha',
            'belumAbsen',
            'chartLabels',
            'chartHadir',
            'chartTerlambat',
            'chartAlpha',
            'activities'
        ));
    }
}
