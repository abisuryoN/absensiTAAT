<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AttendanceGate;
use App\Models\SchoolClass;
use App\Models\Major;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\AttendanceGateService;
use App\Services\ActivityLogService;
use App\Exports\AttendanceGateExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceGateController extends Controller
{
    protected AttendanceGateService $service;

    public function __construct(AttendanceGateService $service)
    {
        $this->service = $service;
    }

    /**
     * Halaman scan barcode / QR.
     */
    public function index()
    {
        return view('admin.attendance.scan');
    }

    /**
     * Process barcode / QR token via AJAX.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'scan_value' => 'required|string',
        ]);

        $value = trim($request->input('scan_value'));

        try {
            if (strlen($value) >= 32) {
                $attendance = $this->service->processQrScan($value, Auth::id());
            } else {
                $attendance = $this->service->processBarcodeScan($value, Auth::id());
            }

            $student = $attendance->student;

            ActivityLogService::logAttendanceScan($student->name, $attendance->status);

            return response()->json([
                'success' => true,
                'message' => "Absensi berhasil dicatat untuk {$student->name}.",
                'data' => [
                    'name' => $student->name,
                    'nis' => $student->nis,
                    'class' => $student->class->name ?? '-',
                    'time' => substr($attendance->time_in, 0, 5),
                    'status' => ucfirst($attendance->status),
                    'photo' => $student->photo ? asset('storage/' . $student->photo) : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Rekap absensi hari ini — student-centric so "Tidak Hadir" rows appear.
     */
    public function today(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');

        // Build a student-centric query so we can show "Tidak Hadir" rows too
        $studentQuery = Student::with([
            'class.major',
            'attendanceGates' => function ($q) use ($today) {
                $q->where('date', $today)->with('scanner');
            },
        ])->where('is_active', true);

        if ($request->filled('search')) {
            $studentQuery->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('class_id')) {
            $studentQuery->where('class_id', $request->class_id);
        }

        if ($request->filled('major_id')) {
            $studentQuery->whereHas('class', function ($q) use ($request) {
                $q->where('major_id', $request->major_id);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'tidak_hadir') {
                $studentQuery->whereDoesntHave('attendanceGates', function ($q) use ($today) {
                    $q->where('date', $today);
                });
            } else {
                $studentQuery->whereHas('attendanceGates', function ($q) use ($today, $request) {
                    $q->where('date', $today)->where('status', $request->status);
                });
            }
        }

        // "Tidak Hadir" rows first so they're easy to find and mark
        $students = $studentQuery
            ->orderByRaw('(SELECT COUNT(*) FROM attendance_gates ag WHERE ag.student_id = students.id AND ag.date = ?) DESC', [$today])
            ->orderBy('name')
            ->paginate(25);

        $classes  = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $majors   = Major::orderBy('name')->get();

        // Stats (unchanged — direct count on attendance_gates)
        $totalSiswa     = Student::where('is_active', true)->count();
        $hadirCount     = AttendanceGate::where('date', $today)->whereIn('status', ['hadir', 'terlambat'])->count();
        $terlambatCount = AttendanceGate::where('date', $today)->where('status', 'terlambat')->count();
        $izinCount      = AttendanceGate::where('date', $today)->where('status', 'izin')->count();
        $sakitCount     = AttendanceGate::where('date', $today)->where('status', 'sakit')->count();
        $alphaCount     = AttendanceGate::where('date', $today)->where('status', 'alpha')->count();
        $totalCheckedIn = AttendanceGate::where('date', $today)
            ->whereIn('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpha'])
            ->count();
        $tidakHadir = max(0, $totalSiswa - $totalCheckedIn);

        return view('admin.attendance.today', compact(
            'students', 'classes', 'majors',
            'totalSiswa', 'hadirCount', 'terlambatCount',
            'izinCount', 'sakitCount', 'alphaCount', 'tidakHadir'
        ));
    }

    /**
     * AJAX bulk mark siswa "Tidak Hadir" sebagai Izin/Sakit.
     */
    public function bulkMark(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:students,id',
            'status'        => 'required|in:izin,sakit',
            'note'          => 'nullable|string|max:500',
        ]);

        $today      = Carbon::today()->format('Y-m-d');
        $status     = $request->status;
        $note       = $request->note;
        $studentIds = $request->student_ids;
        $count      = 0;

        DB::transaction(function () use ($studentIds, $today, $status, $note, &$count) {
            $academicYear = AcademicYear::active()->first();
            $semester     = Semester::active()->first();

            foreach ($studentIds as $studentId) {
                $student = Student::find($studentId);
                if (!$student || !$student->is_active) {
                    continue;
                }

                $attendance = AttendanceGate::where('student_id', $studentId)
                    ->where('date', $today)
                    ->first();

                if ($attendance) {
                    // Only update if currently not marked (no check-in)
                    // but allow overwrite if it's still alpha/tidak_hadir
                    if (in_array($attendance->status, ['hadir', 'terlambat'])) {
                        continue; // skip siswa yang sudah hadir / terlambat
                    }
                    $attendance->update([
                        'status'     => $status,
                        'method'     => 'manual',
                        'note'       => $note,
                        'scanned_by' => Auth::id(),
                    ]);
                } else {
                    if (!$academicYear || !$semester) {
                        continue;
                    }
                    AttendanceGate::create([
                        'student_id'       => $studentId,
                        'academic_year_id' => $academicYear->id,
                        'semester_id'      => $semester->id,
                        'date'             => $today,
                        'time_in'          => Carbon::now()->format('H:i:s'),
                        'status'           => $status,
                        'method'           => 'manual',
                        'note'             => $note,
                        'scanned_by'       => Auth::id(),
                    ]);
                }

                $count++;
            }
        });

        // Refresh stats
        $hadirCount     = AttendanceGate::where('date', $today)->whereIn('status', ['hadir', 'terlambat'])->count();
        $terlambatCount = AttendanceGate::where('date', $today)->where('status', 'terlambat')->count();
        $izinCount      = AttendanceGate::where('date', $today)->where('status', 'izin')->count();
        $sakitCount     = AttendanceGate::where('date', $today)->where('status', 'sakit')->count();
        $alphaCount     = AttendanceGate::where('date', $today)->where('status', 'alpha')->count();
        $totalSiswa     = Student::where('is_active', true)->count();
        $totalCheckedIn = AttendanceGate::where('date', $today)
            ->whereIn('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpha'])
            ->count();
        $tidakHadir = max(0, $totalSiswa - $totalCheckedIn);

        $statusLabel = $status === 'izin' ? 'Izin' : 'Sakit';

        return response()->json([
            'success' => true,
            'message' => "{$count} siswa berhasil ditandai {$statusLabel}.",
            'stats'   => [
                'hadir'      => $hadirCount,
                'terlambat'  => $terlambatCount,
                'izin'       => $izinCount,
                'sakit'      => $sakitCount,
                'alpha'      => $alphaCount,
                'tidakHadir' => $tidakHadir,
            ],
        ]);
    }

    /**
     * Export rekap absensi ke Excel atau PDF.
     */
    public function exportAttendance(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'format'    => 'required|in:excel,pdf',
            'class_id'  => 'nullable|integer|exists:classes,id',
            'major_id'  => 'nullable|integer|exists:majors,id',
            'status'    => 'nullable|string',
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo   = Carbon::parse($request->date_to)->endOfDay();

        // ──────────────────────────────────────────────────────
        // Build the query — student-centric for "tidak_hadir"
        // ──────────────────────────────────────────────────────
        if ($request->status === 'tidak_hadir') {
            // Students in the date range who have NO records on EVERY day in the range
            // Simplification: for a single date, students with no record.
            // For multi-day export with "tidak_hadir" filter we do per-day rows.
            $rows = $this->buildAbsentRows($request, $dateFrom, $dateTo);
        } else {
            $query = AttendanceGate::with(['student.class.major', 'scanner'])
                ->whereBetween('date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);

            if ($request->filled('class_id')) {
                $query->whereHas('student', fn ($q) => $q->where('class_id', $request->class_id));
            }

            if ($request->filled('major_id')) {
                $query->whereHas('student.class', fn ($q) => $q->where('major_id', $request->major_id));
            }

            if ($request->filled('status') && $request->status !== 'semua') {
                $query->where('status', $request->status);
            }

            $rows = $query->orderBy('date')->orderBy('time_in')->get();
        }

        // ── Build filter labels for header ─────────────────────
        $filterLabels = $this->buildFilterLabels($request, $dateFrom, $dateTo);
        $school       = \App\Models\SchoolProfile::first();
        $schoolName   = $school->name ?? config('app.name', 'Sekolah');

        $filename = 'rekap-absensi-' . $dateFrom->format('Y-m-d');
        if ($dateFrom->format('Y-m-d') !== $dateTo->format('Y-m-d')) {
            $filename .= '_sd_' . $dateTo->format('Y-m-d');
        }

        // Log the export
        $exportCount = is_countable($rows) ? count($rows) : $rows->count();
        ActivityLogService::logExport('Absensi', $exportCount, strtoupper($request->format));

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.attendance.export-pdf', [
                'rows'         => $rows,
                'filterLabels' => $filterLabels,
                'schoolName'   => $schoolName,
                'dateFrom'     => $dateFrom,
                'dateTo'       => $dateTo,
                'isTidakHadir' => $request->status === 'tidak_hadir',
            ])->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        // Excel
        return Excel::download(
            new AttendanceGateExport($rows, $request->status === 'tidak_hadir'),
            $filename . '.xlsx'
        );
    }

    /**
     * Build rows for "tidak_hadir" export: iterate days × students without records.
     */
    private function buildAbsentRows(Request $request, Carbon $dateFrom, Carbon $dateTo): \Illuminate\Support\Collection
    {
        $rows    = collect();
        $current = $dateFrom->copy();

        while ($current->lte($dateTo)) {
            $dateStr = $current->format('Y-m-d');

            $q = Student::with('class.major')
                ->where('is_active', true)
                ->whereDoesntHave('attendanceGates', fn ($q) => $q->where('date', $dateStr));

            if ($request->filled('class_id')) {
                $q->where('class_id', $request->class_id);
            }

            if ($request->filled('major_id')) {
                $q->whereHas('class', fn ($c) => $c->where('major_id', $request->major_id));
            }

            $q->orderBy('name')->each(function ($student) use ($rows, $dateStr) {
                // Create a pseudo-object matching AttendanceGate structure
                $rows->push((object) [
                    'date'    => Carbon::parse($dateStr),
                    'time_in' => '-',
                    'status'  => 'tidak_hadir',
                    'method'  => '-',
                    'note'    => '-',
                    'student' => $student,
                    'scanner' => null,
                ]);
            });

            $current->addDay();
        }

        return $rows;
    }

    /**
     * Build human-readable filter labels for PDF header.
     */
    private function buildFilterLabels(Request $request, Carbon $dateFrom, Carbon $dateTo): array
    {
        $labels = [];

        if ($dateFrom->isSameDay($dateTo)) {
            $labels[] = 'Tanggal: ' . $dateFrom->translatedFormat('d F Y');
        } else {
            $labels[] = 'Periode: ' . $dateFrom->translatedFormat('d F Y') . ' – ' . $dateTo->translatedFormat('d F Y');
        }

        if ($request->filled('class_id')) {
            $class = SchoolClass::find($request->class_id);
            if ($class) {
                $labels[] = 'Kelas: ' . $class->name;
            }
        }

        if ($request->filled('major_id')) {
            $major = Major::find($request->major_id);
            if ($major) {
                $labels[] = 'Jurusan: ' . $major->name;
            }
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $statusMap = [
                'hadir'       => 'Hadir',
                'terlambat'   => 'Terlambat',
                'izin'        => 'Izin',
                'sakit'       => 'Sakit',
                'alpha'       => 'Alpha',
                'tidak_hadir' => 'Tidak Hadir',
            ];
            $labels[] = 'Status: ' . ($statusMap[$request->status] ?? ucfirst($request->status));
        }

        return $labels;
    }

    /**
     * Halaman input absensi manual.
     */
    public function manualIndex()
    {
        $students = Student::where('is_active', true)->orderBy('name')->get();
        return view('admin.attendance.manual', compact('students'));
    }

    /**
     * Simpan absensi manual.
     */
    public function manualStore(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'status'     => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'note'       => 'nullable|string',
        ]);

        try {
            $this->service->manualAttendance(
                $request->student_id,
                $request->status,
                $request->note,
                Auth::id()
            );

            $student = \App\Models\Student::find($request->student_id);
            if ($student) {
                ActivityLogService::logAttendanceManual($student->name, $request->status);
            }

            return redirect()->route('admin.attendance.today')
                ->with('success', 'Absensi manual berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}