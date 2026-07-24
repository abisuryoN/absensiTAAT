<x-app-layout>
    @section('title', 'Dashboard Guru')

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
    </style>
    @endpush

    <div class="main-content-inner">

        {{-- ── Page Header ─────────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0" style="color:#1e293b;">Dashboard Guru</h4>
                <p class="text-muted mb-0 fs-7">Selamat datang, {{ auth()->user()->name }}</p>
            </div>
            <span class="badge bg-light text-secondary border fs-8">
                <i class="bi bi-clock me-1"></i>{{ now()->format('d M Y') }}
            </span>
        </div>

        {{-- ── Profil Guru ──────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-body p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;font-weight:700;">
                            {{ strtoupper(substr($teacher->name ?? auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="col">
                        <div class="fw-bold fs-5" style="color:#1e293b;">{{ $teacher->name ?? '-' }}</div>
                        <div class="text-muted fs-8">
                            NIP: {{ $teacher->nip ?? '-' }}
                            @if($teacher->phone)
                                &nbsp;·&nbsp; {{ $teacher->phone }}
                            @endif
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil me-1"></i>Edit Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards ───────────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#3b82f6,#2563eb);">
                    <div class="card-body p-3">
                        <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                            <i class="bi bi-calendar3 text-white"></i>
                        </div>
                        <div class="stat-value text-white">{{ $schedulesCount ?? 0 }}</div>
                        <div class="stat-label text-white opacity-75">Jadwal Hari Ini</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#10b981,#059669);">
                    <div class="card-body p-3">
                        <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                            <i class="bi bi-check2-square text-white"></i>
                        </div>
                        <div class="stat-value text-white">{{ $submittedCount ?? 0 }}</div>
                        <div class="stat-label text-white opacity-75">Sudah Submit</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                    <div class="card-body p-3">
                        <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                            <i class="bi bi-pencil-square text-white"></i>
                        </div>
                        <div class="stat-value text-white">{{ $draftCount ?? 0 }}</div>
                        <div class="stat-label text-white opacity-75">Draft/Belum Submit</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm h-100" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
                    <div class="card-body p-3">
                        <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                            <i class="bi bi-dash-circle text-white"></i>
                        </div>
                        <div class="stat-value text-white">{{ ($schedulesCount ?? 0) - ($submittedCount ?? 0) - ($draftCount ?? 0) }}</div>
                        <div class="stat-label text-white opacity-75">Belum Diisi</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Jadwal Mengajar Hari Ini ─────────────────────────────── --}}
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:12px 12px 0 0;">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0" style="color:#1e293b;">
                        <i class="bi bi-calendar-week me-2 text-primary"></i>Jadwal Mengajar Hari Ini
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 fs-8">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                    </h6>
                    <a href="{{ route('teacher.schedules') }}" class="btn btn-sm btn-outline-primary fs-8">
                        Semua Jadwal <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @php $schedules = $todaySchedules ?? collect(); @endphp
                @if(empty($schedules) || (is_object($schedules) && $schedules->isEmpty()) || (is_array($schedules) && count($schedules) === 0))
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x fs-1 text-muted opacity-50 d-block mb-2"></i>
                        <p class="text-muted mb-0">Tidak ada jadwal mengajar hari ini</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr style="background:#f8fafc;">
                                    <th class="px-4 py-3 border-0" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Waktu</th>
                                    <th class="px-3 py-3 border-0" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Kelas</th>
                                    <th class="px-3 py-3 border-0" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Mata Pelajaran</th>
                                    <th class="px-3 py-3 border-0 d-none d-md-table-cell" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Ruang</th>
                                    <th class="px-3 py-3 border-0" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedules as $schedule)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.82rem;">
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 fw-semibold" style="color:#1e293b;">{{ $schedule->class->name ?? '-' }}</td>
                                        <td class="px-3 py-3 text-muted">{{ $schedule->subject->name ?? '-' }}</td>
                                        <td class="px-3 py-3 text-muted d-none d-md-table-cell">{{ $schedule->room ?? '-' }}</td>
                                        <td class="px-3 py-3">
                                            <a href="{{ route('teacher.attendance.input', $schedule->id) }}" class="btn btn-sm btn-primary fs-8">
                                                <i class="bi bi-check2-square me-1"></i>Absen
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>