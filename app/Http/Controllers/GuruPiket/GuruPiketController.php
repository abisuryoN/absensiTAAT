<?php

namespace App\Http\Controllers\GuruPiket;

use App\Http\Controllers\Controller;
use App\Models\AttendanceGate;
use App\Models\PetugasPiket;
use App\Services\AttendanceGateService;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruPiketController extends Controller
{
    protected AttendanceGateService $service;

    public function __construct(AttendanceGateService $service)
    {
        $this->service = $service;
    }

    /**
     * Halaman setup: isi Nama Lengkap Piket untuk sesi ini.
     * Setiap device/browser yang login akan mengisi form ini secara TERPISAH,
     * menghasilkan piket_petugas_id yang tersimpan di session masing-masing.
     */
    public function setup()
    {
        // Jika sesi ini sudah punya nama, langsung ke dashboard
        if (session()->has('piket_petugas_id')) {
            return redirect()->route('piket.dashboard');
        }

        return view('guru-piket.setup');
    }

    /**
     * Simpan Nama Lengkap Piket ke session sesi ini.
     * - Normalisasi nama (trim, hilangkan double-space, Title Case)
     * - Cek tabel petugas_piket: jika nama sudah ada (case-insensitive), pakai record lama
     * - Jika belum ada, buat record baru
     * - Simpan id ke session('piket_petugas_id') — BUKAN ke database user
     */
    public function setupStore(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|min:2|max:100',
        ], [
            'nama_lengkap.required' => 'Nama Lengkap tidak boleh kosong.',
            'nama_lengkap.min'      => 'Nama Lengkap minimal 2 karakter.',
            'nama_lengkap.max'      => 'Nama Lengkap maksimal 100 karakter.',
        ]);

        $petugasPiket = PetugasPiket::findOrCreateByName($request->input('nama_lengkap'));

        // Simpan ID ke session sesi ini saja — tidak mempengaruhi device/sesi lain
        session(['piket_petugas_id'   => $petugasPiket->id]);
        session(['piket_nama_lengkap' => $petugasPiket->nama_lengkap]);

        ActivityLogService::log(
            'login',
            "Guru Piket '{$petugasPiket->nama_lengkap}' memulai sesi piket",
            $petugasPiket
        );

        return redirect()->route('piket.dashboard')
            ->with('success', "Selamat datang, {$petugasPiket->nama_lengkap}! Sesi piket dimulai.");
    }

    /**
     * Dashboard guru piket: ringkasan scan hari ini oleh petugas sesi ini.
     */
    public function dashboard()
    {
        $today          = Carbon::today()->format('Y-m-d');
        $petugasPiketId = session('piket_petugas_id');
        $namaLengkap    = session('piket_nama_lengkap');

        // Rekap scan hari ini oleh petugas piket ini
        $recentScans = AttendanceGate::with(['student.class'])
            ->where('petugas_piket_id', $petugasPiketId)
            ->where('date', $today)
            ->orderByDesc('time_in')
            ->get();

        $stats = [
            'hadir'     => $recentScans->where('status', 'hadir')->count(),
            'terlambat' => $recentScans->where('status', 'terlambat')->count(),
            'total'     => $recentScans->count(),
        ];

        return view('guru-piket.dashboard', compact('recentScans', 'stats', 'namaLengkap', 'today'));
    }

    /**
     * Halaman scan QR/Barcode untuk guru piket.
     */
    public function scan()
    {
        $namaLengkap = session('piket_nama_lengkap');
        return view('guru-piket.scan', compact('namaLengkap'));
    }

    /**
     * Proses scan QR/Barcode dari guru piket (AJAX).
     * Mencatat petugas_piket_id dari session sesi ini ke record absensi.
     */
    public function scanPost(Request $request)
    {
        $request->validate([
            'scan_value' => 'required|string',
        ]);

        $value          = trim($request->input('scan_value'));
        $petugasPiketId = session('piket_petugas_id');
        $namaLengkap    = session('piket_nama_lengkap');

        if (!$petugasPiketId) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi piket tidak valid. Silakan setup ulang nama piket.',
            ], 422);
        }

        try {
            if (strlen($value) >= 32) {
                $attendance = $this->service->processQrScan($value, Auth::id(), $petugasPiketId);
            } else {
                $attendance = $this->service->processBarcodeScan($value, Auth::id(), $petugasPiketId);
            }

            $student = $attendance->student;

            ActivityLogService::log(
                'scan',
                "Absensi Gerbang oleh Piket '{$namaLengkap}': {$student->name} status {$attendance->status}",
                $attendance
            );

            return response()->json([
                'success' => true,
                'message' => "Absensi berhasil dicatat untuk {$student->name}.",
                'data'    => [
                    'name'    => $student->name,
                    'nis'     => $student->nis,
                    'class'   => $student->class->name ?? '-',
                    'time'    => substr($attendance->time_in, 0, 5),
                    'status'  => ucfirst($attendance->status),
                    'petugas' => $namaLengkap,
                    'photo'   => $student->photo ? asset('storage/' . $student->photo) : null,
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
     * Rekap aktivitas scan petugas piket ini (lintas sesi, berdasarkan petugas_piket_id).
     */
    public function rekap(Request $request)
    {
        $petugasPiketId = session('piket_petugas_id');
        $namaLengkap    = session('piket_nama_lengkap');

        $query = AttendanceGate::with(['student.class'])
            ->where('petugas_piket_id', $petugasPiketId);

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->where('date', $request->tanggal);
        } else {
            // Default: hari ini
            $query->where('date', Carbon::today()->format('Y-m-d'));
        }

        $records = $query->orderByDesc('date')->orderByDesc('time_in')->paginate(25);

        $tanggal = $request->filled('tanggal') ? $request->tanggal : Carbon::today()->format('Y-m-d');

        return view('guru-piket.rekap', compact('records', 'namaLengkap', 'tanggal'));
    }

    /**
     * Akhiri sesi piket: hapus data sesi dari session, redirect ke login.
     * PENTING: hanya menghapus data session piket, bukan logout dari akun.
     * Guru piket bisa langsung mulai sesi baru dengan nama berbeda jika perlu.
     */
    public function endSession(Request $request)
    {
        $namaLengkap = session('piket_nama_lengkap', 'Petugas Piket');

        session()->forget('piket_petugas_id');
        session()->forget('piket_nama_lengkap');

        return redirect()->route('piket.setup')
            ->with('success', "Sesi piket {$namaLengkap} telah diakhiri. Silakan isi nama untuk memulai sesi baru.");
    }
}