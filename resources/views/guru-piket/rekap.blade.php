<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Aktivitas Piket</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body { background: #f4f6fc; }
        .piket-header {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white; padding: 1rem 1.5rem;
        }
        .status-badge { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
        .status-hadir { background: #d4edda; color: #155724; }
        .status-terlambat { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="piket-header d-flex align-items-center justify-content-between">
        <div>
            <a href="{{ route('piket.dashboard') }}" class="text-white text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
            <h5 class="mb-0 fw-bold mt-1">Rekap Aktivitas</h5>
        </div>
        <div class="small opacity-75">{{ $namaLengkap }}</div>
    </div>

    <div class="container-fluid p-3">

        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('piket.rekap') }}" class="d-flex gap-2 align-items-end flex-wrap">
                    <div class="flex-fill" style="min-width:180px">
                        <label class="form-label small fw-semibold mb-1">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $tanggal }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <a href="{{ route('piket.rekap') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-clock-history me-2 text-primary"></i>
                    Riwayat Scan — {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $records->total() }} record</span>
                </h6>
            </div>
            <div class="card-body p-0">
                @if($records->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                        Tidak ada data untuk tanggal ini.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $i => $record)
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ $records->firstItem() + $i }}</td>
                                        <td>
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
                    @if($records->hasPages())
                        <div class="p-3">
                            {{ $records->appends(request()->query())->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="mt-3 text-center">
            <a href="{{ route('piket.scan') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-qr-code-scan me-1"></i>Kembali ke Scan
            </a>
        </div>
    </div>
</body>
</html>