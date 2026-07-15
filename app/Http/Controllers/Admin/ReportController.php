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
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display reporting dashboard with preview list.
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $reportType = $request->input('report_type', 'gate');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $previewData = collect();

        if ($request->has('filter')) {
            if ($reportType === 'gate') {
                $previewData = $this->getGateQuery($request)->paginate(20)->appends($request->query());
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
            $data = $this->getGateQuery($request)->get();
            return Excel::download(new AttendanceGateExport($data), $fileName);
        } else {
            $data = $this->getSubjectQuery($request)->get();
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
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $fileName = 'Laporan_Absensi_' . ($reportType === 'gate' ? 'Gerbang' : 'Mapel') . '_' . date('Ymd_His') . '.pdf';

        if ($reportType === 'gate') {
            $data = $this->getGateQuery($request)->get();
            $pdf = Pdf::loadView('admin.reports.pdf_gate', compact('data', 'schoolProfile', 'startDate', 'endDate'));
            return $pdf->setPaper('a4', 'portrait')->download($fileName);
        } else {
            $data = $this->getSubjectQuery($request)->get();
            $pdf = Pdf::loadView('admin.reports.pdf_subject', compact('data', 'schoolProfile', 'startDate', 'endDate'));
            return $pdf->setPaper('a4', 'landscape')->download($fileName);
        }
    }

    /**
     * Helper to query Attendance Gate data.
     */
    protected function getGateQuery(Request $request)
    {
        $query = AttendanceGate::with(['student.class'])
            ->orderByDesc('date')
            ->orderByDesc('time_in');

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
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
            $query->where('status', $request->status);
        }

        // Join to order by date desc
        $query->join('attendance_subjects', 'attendance_subject_details.attendance_subject_id', '=', 'attendance_subjects.id')
            ->orderByDesc('attendance_subjects.date')
            ->select('attendance_subject_details.*');

        return $query;
    }
}
