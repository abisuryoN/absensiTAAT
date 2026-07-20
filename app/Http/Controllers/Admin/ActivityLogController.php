<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Return modules list filtered by role — used by AJAX in the Log Aktivitas filter.
     */
    public function modulesByRole(Request $request): \Illuminate\Http\JsonResponse
    {
        $role = $request->query('role', '');

        $map = [
            'super_admin' => [
                'Absensi Gerbang',
                'Rekap Hari Ini',
                'Data Siswa',
                'Data Orang Tua',
                'Data Guru',
                'Data Kelas',
                'Tahun Ajaran',
                'Semester',
                'Jurusan',
                'Mata Pelajaran',
                'Manajemen Akun',
                'Manajemen Super Admin',
                'Import Data',
                'Export/Download Data',
                'Laporan Absensi',
                'Pengaturan Sistem',
            ],
            'guru' => [
                'Rekap Absensi',
                'Absensi Kelas',
            ],
            'siswa' => [
                'Login',
            ],
            'parent' => [
                'Rekap Anak',
                'Login',
            ],
            'guru_piket' => [
                'Absensi Gerbang',
                'Login',
            ],
        ];

        if ($role === '') {
            // Union of all roles, sorted
            $modules = collect($map)->flatten()->unique()->sort()->values()->toArray();
        } else {
            $modules = $map[$role] ?? [];
        }

        return response()->json($modules);
    }

    public function index(Request $request)
    {
        // Default: today
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today()->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->endOfDay();

        $query = ActivityLog::with('user')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc');

        // Filter by role
        if ($request->filled('role')) {
            $query->where('causer_role', $request->role);
        }

        // Filter by module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Search by description or user name (via join)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Distinct module values for filter dropdown
        $modules = ActivityLog::select('module')
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        // Role options
        $roles = ['super_admin', 'guru', 'guru_piket', 'siswa', 'parent'];

        return view('admin.activity-logs.index', compact(
            'logs', 'modules', 'roles',
            'dateFrom', 'dateTo'
        ));
    }
}