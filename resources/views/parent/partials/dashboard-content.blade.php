{{--
    parent/partials/dashboard-content.blade.php
    ─────────────────────────────────────────────
    Partial yang di-render ulang via AJAX saat orang tua memilih anak lain.
    Variabel yang dibutuhkan:
      $activeStudent  – instance Student (with schoolClass.major)
      $summary        – array [hadir, terlambat, izin, sakit, alpa, total]
      $todayRecord    – AttendanceGate|null
      $recentAttendances – Collection AttendanceGate (max 10)
--}}

{{-- ── Student Profile Card ──────────────────────────────────────── --}}
<div class="card student-profile-card shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex align-items-center gap-3">
            <div class="student-avatar">
                @if($activeStudent->photo)
                    <img src="{{ Storage::url($activeStudent->photo) }}" alt="" class="w-100 h-100 object-fit-cover" style="border-radius:14px;">
                @else
                    {{ strtoupper(substr($activeStudent->name, 0, 1)) }}
                @endif
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
            <div class="d-none d-md-flex align-items-center gap-2">
                @if($todayRecord)
                    <span class="badge" style="background:rgba(255,255,255,.2); color:#fff; font-size:.78rem; padding:.45rem .85rem; border-radius:8px;">
                        <i class="bi bi-door-open me-1"></i>
                        Hari ini: {{ ucfirst($todayRecord->status) }}
                        @if($todayRecord->scan_time)
                            · {{ \Carbon\Carbon::parse($todayRecord->scan_time)->format('H:i') }}
                        @endif
                    </span>
                @else
                    <span class="badge" style="background:rgba(255,255,255,.15); color:rgba(255,255,255,.7); font-size:.78rem; padding:.45rem .85rem; border-radius:8px;">
                        <i class="bi bi-clock me-1"></i>{{ now()->translatedFormat('d M Y') }}
                    </span>
                @endif
            </div>
        </div>
        {{-- Today's status badge – visible on mobile --}}
        @if($todayRecord)
        <div class="d-md-none mt-2">
            <span class="badge" style="background:rgba(255,255,255,.2); color:#fff; font-size:.8rem; padding:.4rem .75rem; border-radius:8px;">
                <i class="bi bi-door-open me-1"></i>Hari ini: {{ ucfirst($todayRecord->status) }}
                @if($todayRecord->scan_time)
                    · {{ \Carbon\Carbon::parse($todayRecord->scan_time)->format('H:i') }}
                @endif
            </span>
        </div>
        @endif
    </div>
</div>

{{-- ── Stat Cards ────────────────────────────────────────────────── --}}
<div class="stat-cards-grid">
    <div class="stat-card-col">
        <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
            <div class="card-body">
                <div class="stat-icon mb-2">
                    <i class="bi bi-check-circle-fill text-white"></i>
                </div>
                <div class="stat-value">{{ $summary['hadir'] ?? 0 }}</div>
                <div class="stat-label">Hadir</div>
            </div>
        </div>
    </div>
    <div class="stat-card-col">
        <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
            <div class="card-body">
                <div class="stat-icon mb-2">
                    <i class="bi bi-clock-history text-white"></i>
                </div>
                <div class="stat-value">{{ $summary['terlambat'] ?? 0 }}</div>
                <div class="stat-label">Terlambat</div>
            </div>
        </div>
    </div>
    <div class="stat-card-col">
        <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#3b82f6,#2563eb);">
            <div class="card-body">
                <div class="stat-icon mb-2">
                    <i class="bi bi-bandaid-fill text-white"></i>
                </div>
                <div class="stat-value">{{ ($summary['izin'] ?? 0) + ($summary['sakit'] ?? 0) }}</div>
                <div class="stat-label">Izin / Sakit</div>
            </div>
        </div>
    </div>
    <div class="stat-card-col">
        <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
            <div class="card-body">
                <div class="stat-icon mb-2">
                    <i class="bi bi-x-circle-fill text-white"></i>
                </div>
                <div class="stat-value">{{ $summary['alpa'] ?? 0 }}</div>
                <div class="stat-label">Alpa</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Recent Attendance Table ────────────────────────────────────── --}}
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
        @if($recentAttendances->count() >= 10)
        <div class="px-4 py-3 border-top text-center">
            <a href="{{ route('parent.rekap_harian', ['student_id' => $activeStudent->id]) }}"
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-right me-1"></i>Lihat Semua Kehadiran
            </a>
        </div>
        @endif
    </div>
</div>
