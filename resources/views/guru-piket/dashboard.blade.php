<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Piket - Absensi Sekolah</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body { background: #f4f6fc; }
        .piket-header {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white;
            padding: 1.25rem 1.5rem;
        }
        .stat-card { border: none; border-radius: 12px; }
        .status-badge {
            font-size: 0.75rem; font-weight: 600;
            padding: 3px 10px; border-radius: 20px;
        }
        .status-hadir { background: #d4edda; color: #155724; }
        .status-terlambat { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="piket-header d-flex align-items-center justify-content-between">
        <div>
            <div class="small opacity-75">GURU PIKET</div>
            <h5 class="mb-0 fw-bold">{{ $namaLengkap }}</h5>
            <div class="small opacity-75">{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('piket.scan') }}" class="btn btn-light btn-sm fw-semibold">
                <i class="bi bi-qr-code-scan me-1"></i>Scan
            </a>
        </div>
    </div>

    <div class="container-fluid p-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
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

        {{-- Aksi Cepat --}}
        <div class="d-grid gap-2 mb-4">
            <a href="{{ route('piket.scan') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-qr-code-scan me-2"></i>Mulai Scan Absensi
            </a>
            <a href="{{ route('piket.rekap') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-2"></i>Rekap Aktivitas Saya
            </a>
        </div>

        {{-- Daftar scan hari ini --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-check me-2 text-primary"></i>Scan Hari Ini ({{ $stats['total'] }})
                </h6>
            </div>
            <div class="card-body p-0">
                @if($scanHariIni->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                        Belum ada scan hari ini.<br>
                        <a href="{{ route('piket.scan') }}" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-qr-code-scan me-1"></i>Mulai Scan
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Siswa</th>
                                    <th>Kelas</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scanHariIni as $record)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold small">{{ $record->student->name ?? '-' }}</div>
                                            <div class="text-muted" style="font-size:0.75rem">{{ $record->student->nis ?? '-' }}</div>
                                        </td>
                                        <td class="small text-muted">{{ $record->student->class->name ?? '-' }}</td>
                                        <td class="small">{{ substr($record->time_in, 0, 5) }}</td>
                                        <td>
                                            @if($record->status === 'hadir')
                                                <span class="status-badge status-hadir">Hadir</span>
                                            @elseif($record->status === 'terlambat')
                                                <span class="status-badge status-terlambat">Terlambat</span>
                                            @else
                                                <span class="status-badge bg-light text-secondary">{{ ucfirst($record->status) }}</span>
                                            @endif
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
        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('piket.end-session') }}" class="d-inline"
                  onsubmit="return confirm('Akhiri sesi piket Anda sekarang?')">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-stop-circle me-1"></i>Akhiri Sesi Piket
                </button>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="d-inline ms-2">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </button>
            </form>
        </div>
    </div>
</body>
</html>