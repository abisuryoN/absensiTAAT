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
use setasign\Fpdi\Fpdi;

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
        // Keep the larger resource budget scoped to PDF exports instead of changing global PHP settings.
        set_time_limit(300);
        ini_set('memory_limit', '512M');

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
            $data = $this->getGatePdfQuery(
                $request,
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
                $statusFilter
            )->get();
            $className = $request->filled('class_id')
                ? (SchoolClass::whereKey($request->input('class_id'))->value('name') ?? 'Semua Kelas')
                : 'Semua Kelas';
            ActivityLogService::logExport('Absensi Gerbang', $data->count(), 'PDF');

            $dataChunks = $data->isEmpty() ? collect([collect()]) : $data->chunk(300)->values();
            $temporaryFiles = [];

            try {
                foreach ($dataChunks as $chunkIndex => $chunkData) {
                    $chunkPath = tempnam(sys_get_temp_dir(), 'absensi-gate-pdf-');
                    if ($chunkPath === false) {
                        throw new \RuntimeException('Tidak dapat membuat file sementara untuk PDF absensi.');
                    }
                    $temporaryFiles[] = $chunkPath;

                    $pdf = app('dompdf.wrapper')->loadView('admin.reports.pdf_gate', [
                        'data' => $chunkData,
                        'schoolProfile' => $schoolProfile,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'statusFilter' => $statusFilter,
                        'className' => $className,
                        'includeReportHeader' => $chunkIndex === 0,
                        'includeSignature' => $chunkIndex === $dataChunks->count() - 1,
                        'rowNumberOffset' => $chunkIndex * 300,
                    ]);
                    $pdf->setOptions([
                        'chroot' => public_path(),
                        'isRemoteEnabled' => false,
                        'isPhpEnabled' => false,
                        'defaultFont' => 'Helvetica',
                    ])->setPaper('a4', 'portrait')->save($chunkPath);

                    unset($pdf);
                    gc_collect_cycles();
                }

                $mergedPdf = new Fpdi('P', 'mm', 'A4');
                foreach ($temporaryFiles as $temporaryFile) {
                    $pageCount = $mergedPdf->setSourceFile($temporaryFile);
                    for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                        $templateId = $mergedPdf->importPage($pageNumber);
                        $pageSize = $mergedPdf->getTemplateSize($templateId);
                        $mergedPdf->AddPage($pageSize['orientation'], [$pageSize['width'], $pageSize['height']]);
                        $mergedPdf->useTemplate($templateId);
                    }
                }

                $content = $mergedPdf->Output('S');
                return response($content, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                    'Content-Length' => (string) strlen($content),
                ]);
            } finally {
                foreach ($temporaryFiles as $temporaryFile) {
                    if (is_file($temporaryFile)) {
                        @unlink($temporaryFile);
                    }
                }
            }
        } else {
            $data = $this->getSubjectQuery($request)->get();
            $className = $request->filled('class_id')
                ? (SchoolClass::whereKey($request->input('class_id'))->value('name') ?? 'Semua Kelas')
                : 'Semua Kelas';
            $subjectName = $request->filled('subject_id')
                ? (Subject::whereKey($request->input('subject_id'))->value('name') ?? 'Semua Mapel')
                : 'Semua Mapel';
            ActivityLogService::logExport('Absensi Mapel', $data->count(), 'PDF');
            $pdf = Pdf::loadView('admin.reports.pdf_subject', compact(
                'data',
                'schoolProfile',
                'startDate',
                'endDate',
                'className',
                'subjectName'
            ));
            $pdf->setOptions([
                'chroot' => public_path(),
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'defaultFont' => 'Helvetica',
            ]);
            return $pdf->setPaper('a4', 'landscape')->download($fileName);
        }
    }

    /**
     * Query only the fields displayed in the gate PDF and eager load its relations.
     */
    private function getGatePdfQuery(Request $request, string $startDate, string $endDate, ?string $status)
    {
        $query = AttendanceGate::query()
            ->select(['id', 'student_id', 'date', 'time_in', 'status', 'method'])
            ->with([
                'student:id,class_id,nis,name',
                'student.class:id,name',
            ])
            ->orderByDesc('date')
            ->orderByDesc('time_in')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate);

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($studentQuery) use ($request) {
                $studentQuery->where('class_id', $request->input('class_id'));
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Helper to query Attendance Gate data.
     */
    protected function getGateQuery(Request $request, string $startDate, string $endDate, ?string $status)
    {
        $query = AttendanceGate::with([
            'student.user',
            'student.class.major',
            'scanner',
        ])
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
            'attendanceSubject.schedule.class',
            'student.user',
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
