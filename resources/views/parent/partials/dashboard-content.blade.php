{{--
    parent/partials/dashboard-content.blade.php
    ─────────────────────────────────────────────
    Partial yang di-render ulang via AJAX saat orang tua memilih anak lain.
    Variabel yang dibutuhkan:
      $activeStudent  – instance Student (with schoolClass.major)
      $summary        – array [hadir, terlambat, izin, sakit, tidak_hadir, total]
      $todayRecord    – AttendanceGate|null
      $recentAttendances – Collection AttendanceGate (max 10)
--}}

{{-- ── Penjelasan siswa nonaktif ─────────────────────────────────── --}}
@include('parent._inactive_notice', ['student' => $activeStudent])

{{-- ── Student Profile Card with Navigation ──────────────────────── --}}
<div class="card student-profile-card shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        {{-- Navigation Controls (Removed as requested) --}}
        
        <div class="d-flex align-items-center gap-3">
            <div class="student-avatar">
                <img src="{{ $activeStudent->photo_url }}"
                     alt="{{ $activeStudent->name }}"
                     class="w-100 h-100 object-fit-cover"
                     style="border-radius:14px;"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($activeStudent->name) }}&background=6366f1&color=fff&size=96&bold=true'">
            </div>
            <div class="flex-grow-1 min-width-0">
                @unless(isset($children) && $children->count() > 1)
                    <div class="fw-bold fs-5 mb-1" style="color:#fff;">{{ $activeStudent->name }}</div>
                @endunless
                <div style="color:rgba(255,255,255,.82); font-size:.85rem;">
                    NIS: {{ $activeStudent->nis ?? '-' }}
                    &bull; @php $studentClass = $activeStudent->displayClass(); @endphp
                    {{ $studentClass?->name ?? 'Kelas tidak tercatat' }}
                    @if($studentClass?->major)
                        &bull; {{ $studentClass->major->name }}
                    @endif
                    @unless($activeStudent->is_active)
                        &bull; Nonaktif
                    @endunless
                </div>
            </div>
            <div class="d-none d-md-flex align-items-center gap-2">
                @if($todayRecord)
                    <span class="badge" style="background:rgba(255,255,255,.2); color:#fff; font-size:.78rem; padding:.45rem .85rem; border-radius:8px;">
                        <i class="bi bi-door-open me-1"></i>
                        Hari ini: {{ ucfirst(str_replace('_', ' ', $todayRecord->status)) }}
                        @if($todayRecord->time_in && $todayRecord->status !== 'tidak_hadir')
                            · {{ \Carbon\Carbon::parse($todayRecord->time_in)->format('H:i') }}
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
                <i class="bi bi-door-open me-1"></i>Hari ini: {{ ucfirst(str_replace('_', ' ', $todayRecord->status)) }}
                @if($todayRecord->time_in && $todayRecord->status !== 'tidak_hadir')
                    · {{ \Carbon\Carbon::parse($todayRecord->time_in)->format('H:i') }}
                @endif
            </span>
        </div>
        @endif
    </div>
</div>

{{-- Hidden data for navigation script --}}
@if(isset($childrenNavData))
<script type="application/json" id="childrenData">
@json($childrenNavData)
</script>
@endif

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
                <div class="stat-value">{{ $summary['tidak_hadir'] ?? 0 }}</div>
                <div class="stat-label">Tidak Hadir</div>
            </div>
        </div>
    </div>
    <div class="stat-card-col">
        <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
            <div class="card-body">
                <div class="stat-icon mb-2">
                    <i class="bi bi-percent text-white"></i>
                </div>
                <div class="stat-value">{{ $summary['percentage'] ?? 0 }}%</div>
                <div class="stat-label">Persentase Hadir</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Monthly Comparison Card ────────────────────────────────────── --}}
<div class="card section-card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="fw-bold mb-0" style="color:#1e293b;">
            <i class="bi bi-arrow-left-right me-2 text-primary"></i>Perbandingan Bulanan
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 rounded" style="background:#f8fafc;">
                    <div class="small text-muted mb-1">{{ $monthlyComparison['current_label'] }}</div>
                    <div class="fs-3 fw-bold text-primary">{{ $monthlyComparison['current']['percentage'] }}%</div>
                    <div class="small text-muted">
                        {{ $monthlyComparison['current']['total'] }} hari tercatat
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded" style="background:#f8fafc;">
                    <div class="small text-muted mb-1">{{ $monthlyComparison['previous_label'] }}</div>
                    <div class="fs-3 fw-bold text-secondary">{{ $monthlyComparison['previous']['percentage'] }}%</div>
                    <div class="small text-muted">
                        {{ $monthlyComparison['previous']['total'] }} hari tercatat
                    </div>
                </div>
            </div>
        </div>
        @if($monthlyComparison['can_compare'])
            @if($monthlyComparison['change'] != 0)
            <div class="mt-3 text-center">
                <span class="badge {{ $monthlyComparison['is_improvement'] ? 'bg-success' : 'bg-warning' }} px-3 py-2">
                    <i class="bi bi-{{ $monthlyComparison['is_improvement'] ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                    {{ $monthlyComparison['is_improvement'] ? 'Meningkat' : 'Menurun' }} {{ number_format($monthlyComparison['change_abs'], 1) }}% dari bulan lalu
                </span>
            </div>
            @else
            <div class="mt-3 text-center">
                <span class="badge bg-secondary px-3 py-2">
                    <i class="bi bi-dash-lg me-1"></i>Tidak ada perubahan dari bulan lalu
                </span>
            </div>
            @endif
        @elseif(!$monthlyComparison['has_previous_data'] && $monthlyComparison['has_current_data'])
        <div class="mt-3 text-center">
            <span class="badge bg-info px-3 py-2">
                <i class="bi bi-info-circle me-1"></i>Data bulan sebelumnya belum tersedia
            </span>
        </div>
        @elseif(!$monthlyComparison['has_current_data'] && $monthlyComparison['has_previous_data'])
        <div class="mt-3 text-center">
            <span class="badge bg-info px-3 py-2">
                <i class="bi bi-info-circle me-1"></i>Data bulan ini belum tersedia
            </span>
        </div>
        @else
        <div class="mt-3 text-center">
            <span class="badge bg-light text-dark px-3 py-2">
                <i class="bi bi-calendar-x me-1"></i>Belum ada data untuk kedua bulan
            </span>
        </div>
        @endif
    </div>
</div>

{{-- ── Six-Month Trend Chart ──────────────────────────────────────── --}}
<div class="card section-card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="fw-bold mb-0" style="color:#1e293b;">
            <i class="bi bi-graph-up me-2 text-primary"></i>Tren Kehadiran 6 Bulan Terakhir
        </h6>
    </div>
    <div class="card-body" style="min-height: 300px !important; position: relative; padding: 20px;">
        <div style="position: relative !important; width: 100% !important; height: 280px !important; display: block !important;">
            <canvas id="attendanceTrendChart" style="display: block !important; box-sizing: border-box; width: 100% !important; height: 100% !important;"></canvas>
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
                   class="btn btn-sm btn-primary">
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
                        <th>Jam Masuk</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances as $att)
                    <tr>
                        <td class="fw-semibold">{{ \Carbon\Carbon::parse($att->date)->isoFormat('ddd, D MMM Y') }}</td>
                        <td>@include('parent._status_badge', ['status' => $att->status])</td>
                        <td>{{ ($att->time_in && $att->status !== 'tidak_hadir') ? \Carbon\Carbon::parse($att->time_in)->format('H:i') : '-' }}</td>
                        <td class="small">@include('parent._keterangan', ['att' => $att])</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
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

{{-- Chart data embedded for re-initialization --}}
<script type="application/json" id="chartTrendData">
@json($sixMonthTrend)
</script>
