<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Exports\Parent\ParentDailyAttendanceExport;
use App\Exports\Parent\ParentMonthlyAttendanceExport;
use App\Models\AttendanceGate;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ParentAttendanceExportController extends Controller
{
    private function getStudent(Request $request): Student
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $student = Student::with('schoolClass')
            ->findOrFail($validated['student_id']);

        Gate::authorize('viewDashboard', $student);

        return $student;
    }

    public function dailyExcel(Request $request)
    {
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'in:hadir,terlambat,izin,sakit,alpa'],
        ]);

        $student = $this->getStudent($request);

        $query = AttendanceGate::with('student.schoolClass')
            ->where('student_id', $student->id)
            ->whereBetween('date', [$validated['from'], $validated['to']]);

        if (!empty($validated['status'])) {
            $status = $validated['status'] === 'alpa'
                ? 'tidak_hadir'
                : $validated['status'];

            $query->where('status', $status);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        return Excel::download(
            new ParentDailyAttendanceExport($attendances),
            'rekap-harian-' . $student->name . '.xlsx'
        );
    }

    public function dailyPdf(Request $request)
    {
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'in:hadir,terlambat,izin,sakit,alpa'],
        ]);

        $student = $this->getStudent($request);

        $query = AttendanceGate::with('student.schoolClass')
            ->where('student_id', $student->id)
            ->whereBetween('date', [$validated['from'], $validated['to']]);

        if (!empty($validated['status'])) {
            $status = $validated['status'] === 'alpa'
                ? 'tidak_hadir'
                : $validated['status'];

            $query->where('status', $status);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $pdf = Pdf::loadView('parent.exports.daily-pdf', [
            'student' => $student,
            'attendances' => $attendances,
            'from' => $validated['from'],
            'to' => $validated['to'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekap-harian-' . $student->name . '.pdf');
    }

    public function monthlyExcel(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:' . (now()->year + 1)],
        ]);

        $student = $this->getStudent($request);

        $months = $this->buildMonthlyData(
            $student->id,
            (int) $validated['year']
        );

        return Excel::download(
            new ParentMonthlyAttendanceExport(collect($months)),
            'rekap-bulanan-' . $student->name . '-' . $validated['year'] . '.xlsx'
        );
    }

    public function monthlyPdf(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:' . (now()->year + 1)],
        ]);

        $student = $this->getStudent($request);

        $months = $this->buildMonthlyData(
            $student->id,
            (int) $validated['year']
        );

        $pdf = Pdf::loadView('parent.exports.monthly-pdf', [
            'student' => $student,
            'months' => $months,
            'year' => $validated['year'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekap-bulanan-' . $student->name . '-' . $validated['year'] . '.pdf');
    }

    private function buildMonthlyData(int $studentId, int $year): array
    {
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $result = [];

        foreach ($monthNames as $month => $label) {
            $records = AttendanceGate::where('student_id', $studentId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            $hadir = $records->where('status', 'hadir')->count();
            $terlambat = $records->where('status', 'terlambat')->count();
            $izin = $records->where('status', 'izin')->count();
            $sakit = $records->where('status', 'sakit')->count();
            $tidak_hadir = $records->where('status', 'tidak_hadir')->count();
            $total = $records->count();

            $result[] = [
                'month_label' => $label,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'tidak_hadir' => $tidak_hadir,
                'total' => $total,
            ];
        }

        return $result;
    }
}
