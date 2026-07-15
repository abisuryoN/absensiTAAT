<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AttendanceGate;
use App\Models\SchoolClass;
use App\Services\AttendanceGateService;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            // Assume 32-byte (64 characters) hex or typical length is Qr Code token
            if (strlen($value) >= 32) {
                $attendance = $this->service->processQrScan($value, auth()->id());
            } else {
                $attendance = $this->service->processBarcodeScan($value, auth()->id());
            }

            $student = $attendance->student;

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
     * Rekap absensi hari ini.
     */
    public function today(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $query = AttendanceGate::with(['student.class', 'student.parent'])
            ->where('date', $today);

        if ($request->filled('search')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderByDesc('time_in')->paginate(20);
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('admin.attendance.today', compact('attendances', 'classes'));
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
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'note' => 'nullable|string',
        ]);

        try {
            $this->service->manualAttendance(
                $request->student_id,
                $request->status,
                $request->note,
                auth()->id()
            );

            return redirect()->route('admin.attendance.today')->with('success', 'Absensi manual berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
