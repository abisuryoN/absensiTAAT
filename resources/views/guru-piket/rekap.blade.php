<x-app-layout>
    @section('title', 'Rekap Aktivitas Piket')

    @push('styles')
    <style>
        .status-badge     { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
        .status-hadir     { background: #d4edda; color: #155724; }
        .status-terlambat { background: #fff3cd; color: #856404; }
    </style>
    @endpush

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Rekap Aktivitas</h4>
            <div class="small text-muted">{{ $namaLengkap }}</div>
        </div>
        <a href="{{ route('piket.dashboard') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    {{-- Filter --}}
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
                <a href="{{ route('piket.rekap') }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </form>
        </div>
    </div>

    {{-- Records --}}
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
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Siswa</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th class="d-none d-md-table-cell">Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $scan)
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
                                <td class="small text-muted d-none d-md-table-cell">
                                    {{ $scan->student->class->name ?? '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="px-3 py-2">
                    {{ $records->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

</x-app-layout>
