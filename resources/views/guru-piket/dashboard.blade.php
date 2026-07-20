<x-app-layout>
    @section('title', 'Dashboard Piket')

    @push('styles')
    <style>
        .stat-card { border: none; border-radius: 12px; }
        .status-badge { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
        .status-hadir     { background: #d4edda; color: #155724; }
        .status-terlambat { background: #fff3cd; color: #856404; }
    </style>
    @endpush

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="small text-muted fw-semibold text-uppercase">GURU PIKET</div>
            <h4 class="fw-bold mb-0">{{ $namaLengkap }}</h4>
            <div class="small text-muted">{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</div>
        </div>
        <a href="{{ route('piket.scan') }}" class="btn btn-primary fw-semibold">
            <i class="bi bi-qr-code-scan me-1"></i>Scan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card stat-card shadow-sm text-center p-3">
                <div class="h3 fw-bold text-primary mb-0">{{ $stats['total'] }}</div>
                <div class="small text-muted">Total Scan</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card stat-card shadow-sm text-center p-3">
                <div class="h3 fw-bold text-success mb-0">{{ $stats['hadir'] }}</div>
                <div class="small text-muted">Hadir</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card stat-card shadow-sm text-center p-3">
                <div class="h3 fw-bold text-warning mb-0">{{ $stats['terlambat'] }}</div>
                <div class="small text-muted">Terlambat</div>
            </div>
        </div>
    </div>

    {{-- Riwayat Scan Hari Ini --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-clock-history me-2 text-primary"></i>Scan Hari Ini
            </h6>
            <a href="{{ route('piket.rekap') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-list-ul me-1"></i>Rekap
            </a>
        </div>
        <div class="card-body p-0">
            @if($recentScans->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox d-block mb-1 fs-4"></i>
                    Belum ada scan hari ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Siswa</th>
                                <th>Waktu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentScans as $scan)
                            <tr>
                                <td>
                                    <div class="fw-semibold small">{{ $scan->student->name ?? '-' }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $scan->student->nis ?? '' }}</div>
                                </td>
                                <td class="small text-muted">{{ substr($scan->time_in, 0, 5) }}</td>
                                <td>
                                    <span class="status-badge status-{{ $scan->status }}">
                                        {{ ucfirst($scan->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer aksi --}}
    <div class="d-flex gap-2 justify-content-center">
        <form method="POST" action="{{ route('piket.end-session') }}"
              onsubmit="return confirm('Akhiri sesi piket Anda sekarang?')">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-stop-circle me-1"></i>Akhiri Sesi
            </button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </button>
        </form>
    </div>

</x-app-layout>
