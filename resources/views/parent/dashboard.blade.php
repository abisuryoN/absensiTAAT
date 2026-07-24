<x-app-layout>
    @section('title', 'Dashboard Orang Tua')

    @push('styles')
    <style>
        /* ── Stat Cards ───────────────────────────────── */
        .stat-card {
            border: none;
            border-radius: 14px;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0,0,0,.15) !important;
        }
        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem;
            background: rgba(255,255,255,.22);
        }
        .stat-card .stat-value {
            font-size: 2rem; font-weight: 700; line-height: 1.1;
            color: #fff;
        }
        .stat-card .stat-label {
            font-size: .75rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .5px;
            color: rgba(255,255,255,.8);
            margin-top: 3px;
        }

        /* ── Student Profile Card ─────────────────────── */
        .student-profile-card {
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            border: none;
        }
        .student-avatar {
            width: 58px; height: 58px; border-radius: 14px;
            background: rgba(255,255,255,.22);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }

        /* ── Table Section ────────────────────────────── */
        .section-card {
            border-radius: 14px; border: none;
        }
        .section-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            border-radius: 14px 14px 0 0;
            padding: 1rem 1.25rem;
        }

        /* ── Child Switcher ───────────────────────────── */
        .child-switcher-card {
            border-radius: 14px; border: none;
            background: #f8fafc;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }

        /* Mobile tweaks */
        @media (max-width: 575.98px) {
            .stat-card .stat-value { font-size: 1.7rem; }
            .stat-card .card-body  { padding: .85rem !important; }
            .student-avatar        { width: 48px; height: 48px; font-size: 1.2rem; }
        }
    </style>
    @endpush

    {{-- ── Child Switcher (multiple children) ─────────────────────────────── --}}
    @if($children->count() > 1)
    <div class="card child-switcher-card mb-4">
        <div class="card-body p-3">
            <small class="text-muted fw-semibold d-block mb-2">
                <i class="bi bi-people me-1"></i>Pilih Anak:
            </small>
            <div class="d-flex flex-wrap gap-2">
                @foreach($children as $child)
                <a href="?student_id={{ $child->id }}"
                   class="btn btn-sm {{ $activeStudent && $activeStudent->id === $child->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $child->name }}
                    <span class="badge {{ $activeStudent && $activeStudent->id === $child->id ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' }} ms-1">
                        {{ $child->schoolClass->name ?? '-' }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($activeStudent)

    {{-- ── Student Profile Card ─────────────────────────────────────────────── --}}
    <div class="card student-profile-card shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-center gap-3">
                <div class="student-avatar">
                    {{ strtoupper(substr($activeStudent->name, 0, 1)) }}
                </div>
                <div class="flex-grow-1 min-width-0">
                    <div class="fw-bold fs-5 mb-1" style="color:#fff;">{{ $activeStudent->name }}</div>
                    <div style="color:rgba(255,255,255,.82); font-size:.85rem;">
                        NIS: {{ $activeStudent->nis ?? '-' }}
                        &bull; {{ $activeStudent->schoolClass->name ?? 'Kelas tidak diketahui' }}
                        @if($activeStudent->schoolClass?->major)
                            &bull; {{ $activeStudent->schoolClass->major->name }}
                        @endif
                    </div>
                </div>
                <div class="d-none d-md-block">
                    <span class="badge" style="background:rgba(255,255,255,.2); color:#fff; font-size:.78rem; padding:.45rem .85rem; border-radius:8px;">
                        <i class="bi bi-clock me-1"></i>{{ now()->translatedFormat('d M Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Stat Cards ───────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                <div class="card-body p-3">
                    <div class="stat-icon mb-2">
                        <i class="bi bi-check-circle-fill text-white"></i>
                    </div>
                    <div class="stat-value">{{ $summary['hadir'] ?? 0 }}</div>
                    <div class="stat-label">Hadir</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <div class="card-body p-3">
                    <div class="stat-icon mb-2">
                        <i class="bi bi-clock-history text-white"></i>
                    </div>
                    <div class="stat-value">{{ $summary['terlambat'] ?? 0 }}</div>
                    <div class="stat-label">Terlambat</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#3b82f6,#2563eb);">
                <div class="card-body p-3">
                    <div class="stat-icon mb-2">
                        <i class="bi bi-bandaid-fill text-white"></i>
                    </div>
                    <div class="stat-value">{{ ($summary['izin'] ?? 0) + ($summary['sakit'] ?? 0) }}</div>
                    <div class="stat-label">Izin / Sakit</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
                <div class="card-body p-3">
                    <div class="stat-icon mb-2">
                        <i class="bi bi-x-circle-fill text-white"></i>
                    </div>
                    <div class="stat-value">{{ $summary['alpa'] ?? 0 }}</div>
                    <div class="stat-label">Alpa</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Attendance Table ───────────────────────────────────────────── --}}
    <div class="card section-card shadow-sm">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="fw-bold mb-0" style="color:#1e293b;">
                    <i class="bi bi-calendar-week me-2 text-primary"></i>Rekap Kehadiran Terbaru (Bulan Ini)
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('parent.rekap_harian', ['student_id' => $activeStudent->id]) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-list-ul me-1"></i>Rekap Harian
                    </a>
                    <a href="{{ route('parent.rekap_bulanan', ['student_id' => $activeStudent->id]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-bar-chart me-1"></i>Rekap Bulanan
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendances as $att)
                        <tr>
                            <td class="fw-semibold">{{ \Carbon\Carbon::parse($att->date)->isoFormat('ddd, D MMM Y') }}</td>
                            <td>@include('parent._status_badge', ['status' => $att->status])</td>
                            <td class="text-muted small">{{ $att->note ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada data absensi bulan ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentAttendances->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $recentAttendances->appends(request()->only('student_id'))->links() }}
            </div>
            @endif
        </div>
    </div>

    @else
    {{-- No student connected --}}
    <div class="card section-card shadow-sm">
        <div class="card-body text-center py-5">
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);display:flex;align-items:center;justify-content:center;margin:0 auto 1.2rem;">
                <i class="bi bi-people fs-2" style="color:#6366f1;"></i>
            </div>
            <h5 class="fw-bold mb-2">Belum Ada Siswa Terhubung</h5>
            <p class="text-muted mb-0">Akun Anda belum ditautkan ke data siswa manapun.<br>
            Hubungi admin sekolah untuk menautkan akun Anda ke data anak Anda.</p>
        </div>
    </div>
    @endif

</x-app-layout>