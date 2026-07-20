<x-app-layout>
    @section('title', 'Dashboard Siswa')

    @push('styles')
    <style>
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,.12);
        }
        .stat-card .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.1;
        }
        .stat-card .stat-label {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-top: 2px;
        }
        .status-badge-today {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
        }
        .qr-code-placeholder {
            width: 180px;
            height: 180px;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #94a3b8;
        }
        .qr-refresh-btn {
            font-size: 0.82rem;
        }
    </style>
    @endpush

    <div class="main-content-inner">

        {{-- ── Page Header ─────────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0" style="color:#1e293b;">Dashboard Siswa</h4>
                <p class="text-muted mb-0 fs-7">Selamat datang, {{ auth()->user()->name }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border fs-8">
                    <i class="bi bi-clock me-1"></i>{{ now()->format('d M Y') }}
                </span>
                <a href="{{ route('student.qrcode') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-qr-code me-1"></i>QR Absensi
                </a>
            </div>
        </div>

        {{-- ── Info Profil & Status Hari Ini ───────────────────────── --}}
        <div class="row g-3 mb-4">
            {{-- Profile Card --}}
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#4361ee,#3a0ca3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;font-weight:700;flex-shrink:0;">
                                {{ strtoupper(substr($student->name ?? auth()->user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="color:#1e293b;font-size:1rem;">{{ $student->name }}</div>
                                <div class="text-muted fs-8">NIS: {{ $student->student_id_number ?? $student->nis ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-2 rounded" style="background:#f1f5f9;">
                                    <div class="fs-8 text-muted">Kelas</div>
                                    <div class="fw-semibold" style="font-size:0.85rem;color:#1e293b;">{{ $student->class->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded" style="background:#f1f5f9;">
                                    <div class="fs-8 text-muted">Status</div>
                                    @if($student->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.75rem;">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:0.75rem;">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Kehadiran Bulan Ini --}}
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="fw-bold" style="color:#1e293b;">Kehadiran Bulan Ini</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-8">
                                {{ now()->format('F Y') }}
                            </span>
                        </div>
                        {{-- Progress bar --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between fs-8 text-muted mb-1">
                                <span>Persentase Kehadiran</span>
                                <span class="fw-bold text-primary">{{ $attendancePercent }}%</span>
                            </div>
                            <div class="progress" style="height:8px;border-radius:50px;">
                                <div class="progress-bar bg-primary" style="width:{{ $attendancePercent }}%;border-radius:50px;"></div>
                            </div>
                        </div>
                        {{-- Mini stats --}}
                        <div class="row g-2">
                            <div class="col">
                                <div class="text-center p-2 rounded" style="background:#dcfce7;">
                                    <div class="fw-bold" style="color:#16a34a;font-size:1.1rem;">{{ $hadirCount }}</div>
                                    <div class="fs-8" style="color:#15803d;">Hadir</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="text-center p-2 rounded" style="background:#fef9c3;">
                                    <div class="fw-bold" style="color:#ca8a04;font-size:1.1rem;">{{ $terlambatCount }}</div>
                                    <div class="fs-8" style="color:#a16207;">Terlambat</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="text-center p-2 rounded" style="background:#dbeafe;">
                                    <div class="fw-bold" style="color:#2563eb;font-size:1.1rem;">{{ $izinCount }}</div>
                                    <div class="fs-8" style="color:#1d4ed8;">Izin</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="text-center p-2 rounded" style="background:#fce7f3;">
                                    <div class="fw-bold" style="color:#db2777;font-size:1.1rem;">{{ $sakitCount }}</div>
                                    <div class="fs-8" style="color:#be185d;">Sakit</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="text-center p-2 rounded" style="background:#fee2e2;">
                                    <div class="fw-bold" style="color:#dc2626;font-size:1.1rem;">{{ $alphaCount }}</div>
                                    <div class="fs-8" style="color:#b91c1c;">Alpha</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards ───────────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#4361ee,#3a0ca3);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);">
                                <i class="bi bi-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="stat-value text-white">{{ $hadirCount }}</div>
                        <div class="stat-label text-white opacity-75">Hadir</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);">
                                <i class="bi bi-clock text-white"></i>
                            </div>
                        </div>
                        <div class="stat-value text-white">{{ $terlambatCount }}</div>
                        <div class="stat-label text-white opacity-75">Terlambat</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#10b981,#059669);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);">
                                <i class="bi bi-file-text text-white"></i>
                            </div>
                        </div>
                        <div class="stat-value text-white">{{ $izinCount }}</div>
                        <div class="stat-label text-white opacity-75">Izin</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#ec4899,#be185d);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);">
                                <i class="bi bi-heart-pulse text-white"></i>
                            </div>
                        </div>
                        <div class="stat-value text-white">{{ $sakitCount }}</div>
                        <div class="stat-label text-white opacity-75">Sakit</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);">
                                <i class="bi bi-x-circle text-white"></i>
                            </div>
                        </div>
                        <div class="stat-value text-white">{{ $alphaCount }}</div>
                        <div class="stat-label text-white opacity-75">Alpha</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card shadow-sm h-100" style="background:#eef4ff;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);">
                                <i class="bi bi-calendar3 text-white"></i>
                            </div>
                        </div>
                        <div class="stat-value text-white">{{ $totalDays }}</div>
                        <div class="stat-label text-white opacity-75">Total Hari</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Jadwal Hari Ini ──────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:12px 12px 0 0;">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-calendar-week me-2 text-primary"></i>Jadwal Hari Ini
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 fs-8">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                    </h6>
                    <a href="{{ route('student.schedule') }}" class="btn btn-sm btn-outline-primary fs-8">
                        Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($todaySchedules->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x fs-1 text-muted opacity-50 d-block mb-2"></i>
                        <p class="text-muted mb-0">Tidak ada jadwal pelajaran hari ini</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr style="background:#f8fafc;">
                                    <th class="px-4 py-3 border-0" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Waktu</th>
                                    <th class="px-3 py-3 border-0" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Mata Pelajaran</th>
                                    <th class="px-3 py-3 border-0 d-none d-md-table-cell" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Guru</th>
                                    <th class="px-3 py-3 border-0 d-none d-md-table-cell" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Ruang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todaySchedules as $schedule)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.82rem;">
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 fw-semibold" style="color:#1e293b;">{{ $schedule->subject->name ?? '-' }}</td>
                                        <td class="px-3 py-3 text-muted d-none d-md-table-cell">{{ $schedule->teacher->name ?? '-' }}</td>
                                        <td class="px-3 py-3 text-muted d-none d-md-table-cell">{{ $schedule->room ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
    @push('scripts')
    <script>
        // Nothing special needed — QR generation is on the qrcode page
    </script>
    @endpush
</x-app-layout>