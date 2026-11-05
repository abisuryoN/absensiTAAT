<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SchoolProfile;
use App\Models\AttendanceGate;
use App\Models\AttendanceSubjectDetail;
use App\Exports\AttendanceGateExport;
use App\Exports\AttendanceSubjectExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;
use App\Services\DateTimeService;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected DateTimeService $dateTimeService;

    public function __construct(\App\Services\DateTimeService $dateTimeService)
    {
        $this->dateTimeService = $dateTimeService;
    }

    /**
     * Display reporting dashboard with preview list.
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $reportType = $request->input('report_type', 'gate');
        if ($reportType === 'gate') {
            $gateFilters = $this->resolveGateFilters($request);
            $startDate = $gateFilters['start_date'];
            $endDate = $gateFilters['end_date'];
            $selectedStatus = $gateFilters['status'];
        } else {
            $startDate = $request->input('start_date', $this->dateTimeService->now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', $this->dateTimeService->now()->format('Y-m-d'));
            $selectedStatus = $request->input('status');
        }

        $previewData = collect();

        if ($request->has('filter')) {
            if ($reportType === 'gate') {
                $previewData = $this->getGateQuery(
                    $request,
                    $startDate,
                    $endDate,
                    $selectedStatus
                )->paginate(20)->appends($request->query());
            } else {
                $previewData = $this->getSubjectQuery($request)->paginate(20)->appends($request->query());
            }
        }

        return view('admin.reports.index', compact(
            'classes',
            'subjects',
            'reportType',
            'startDate',
            'endDate',
            'selectedStatus',
            'previewData'
        ));
    }

    /**
     * Export report to Excel.
     */
    public function exportExcel(Request $request)
    {
        $reportType = $request->input('report_type', 'gate');
        $fileName = 'Laporan_Absensi_' . ($reportType === 'gate' ? 'Gerbang' : 'Mapel') . '_' . date('Ymd_His') . '.xlsx';

        if ($reportType === 'gate') {
            $gateFilters = $this->resolveGateFilters($request);
            $data = $this->getGateQuery(
                $request,
                $gateFilters['start_date'],
                $gateFilters['end_date'],
                $gateFilters['status']
            )->get();
            ActivityLogService::logExport('Absensi Gerbang', $data->count(), 'Excel');
            return Excel::download(new AttendanceGateExport($data), $fileName);
        } else {
            $data = $this->getSubjectQuery($request)->get();
            ActivityLogService::logExport('Absensi Mapel', $data->count(), 'Excel');
            return Excel::download(new AttendanceSubjectExport($data), $fileName);
        }
    }

    /**
     * Export report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $reportType = $request->input('report_type', 'gate');
        $schoolProfile = SchoolProfile::first();
        if ($reportType === 'gate') {
            $gateFilters = $this->resolveGateFilters($request);
            $startDate = Carbon::parse($gateFilters['start_date']);
            $endDate = Carbon::parse($gateFilters['end_date']);
            $statusFilter = $gateFilters['status'];
        } else {
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;
        }

        $fileName = 'Laporan_Absensi_' . ($reportType === 'gate' ? 'Gerbang' : 'Mapel') . '_' . date('Ymd_His') . '.pdf';

        if ($reportType === 'gate') {
            $data = $this->getGateQuery(
                $request,
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
                $statusFilter
            )->get();
            ActivityLogService::logExport('Absensi Gerbang', $data->count(), 'PDF');
            $pdf = Pdf::loadView('admin.reports.pdf_gate', compact('data', 'schoolProfile', 'startDate', 'endDate', 'statusFilter'));
            $pdf->setOption('chroot', public_path());
            return $pdf->setPaper('a4', 'portrait')->download($fileName);
        } else {
            $data = $this->getSubjectQuery($request)->get();
            ActivityLogService::logExport('Absensi Mapel', $data->count(), 'PDF');
            $pdf = Pdf::loadView('admin.reports.pdf_subject', compact('data', 'schoolProfile', 'startDate', 'endDate'));
            return $pdf->setPaper('a4', 'landscape')->download($fileName);
        }
    }

    /**
     * Helper to query Attendance Gate data.
     */
    protected function getGateQuery(Request $request, string $startDate, string $endDate, ?string $status)
    {
        $query = AttendanceGate::with(['student.class'])
            ->orderByDesc('date')
            ->orderByDesc('time_in')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate);

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Resolve gate report defaults without changing subject report behavior.
     */
    private function resolveGateFilters(Request $request): array
    {
        $latestAttendanceDate = AttendanceGate::max('date');
        $defaultEndDate = $latestAttendanceDate
            ? Carbon::parse($latestAttendanceDate)
            : $this->dateTimeService->now();
        $defaultStartDate = $defaultEndDate->copy()->startOfMonth();

        $status = $request->input('status');
        if ($status === 'alpha') {
            $status = 'tidak_hadir';
        }

        return [
            'start_date' => $request->filled('start_date')
                ? $request->input('start_date')
                : $defaultStartDate->format('Y-m-d'),
            'end_date' => $request->filled('end_date')
                ? $request->input('end_date')
                : $defaultEndDate->format('Y-m-d'),
            'status' => $status,
        ];
    }

    /**
     * Helper to query Attendance Subject details.
     */
    protected function getSubjectQuery(Request $request)
    {
        $query = AttendanceSubjectDetail::with([
            'attendanceSubject.schedule.subject',
            'attendanceSubject.schedule.teacher',
            'student.class'
        ]);

        $query->whereHas('attendanceSubject', function ($q) use ($request) {
            if ($request->filled('start_date')) {
                $q->where('date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $q->where('date', '<=', $request->end_date);
            }
            if ($request->filled('subject_id')) {
                $q->whereHas('schedule', function ($sq) use ($request) {
                    $sq->where('subject_id', $request->subject_id);
                });
            }
        });

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('attendance_subject_details.status', $request->status);
        }

        // Join to order by date desc
        $query->join('attendance_subjects', 'attendance_subject_details.attendance_subject_id', '=', 'attendance_subjects.id')
            ->orderByDesc('attendance_subjects.date')
            ->select('attendance_subject_details.*');

        return $query;
    }
}
