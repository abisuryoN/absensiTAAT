<x-app-layout>
    @section('title', 'Rekap Harian Kehadiran')

    @push('styles')
    <style>
        .section-card {
            border-radius: 14px; border: none;
        }
        .section-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            border-radius: 14px 14px 0 0;
            padding: 1rem 1.25rem;
        }
        .filter-card {
            border-radius: 14px; border: none;
            background: #f8fafc;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .child-switcher-card {
            border-radius: 14px; border: none;
            background: #f8fafc;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        @media (max-width: 575.98px) {
            .filter-row .col-md-3,
            .filter-row .col-md-2 { width: 100%; }
        }
    </style>
    @endpush

    {{-- Child Switcher --}}
    @if($children->count() > 1)
    <div class="card child-switcher-card mb-4">
        <div class="card-body p-3">
            <small class="text-muted fw-semibold d-block mb-2">
                <i class="bi bi-people me-1"></i>Pilih Anak:
            </small>
            <div class="d-flex flex-wrap gap-2">
                @foreach($children as $child)
                <a href="{{ route('parent.rekap_harian', ['student_id' => $child->id]) }}"
                   class="btn btn-sm {{ $activeStudent && $activeStudent->id === $child->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $child->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1e293b;">Rekap Harian</h4>
            <p class="text-muted mb-0 fs-7">
                @if($activeStudent)
                    {{ $activeStudent->name }} &bull; {{ $activeStudent->schoolClass->name ?? '-' }}
                @endif
            </p>
        </div>
    </div>

    @if($activeStudent)

    {{-- Filter Card --}}
    <div class="card filter-card mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('parent.rekap_harian') }}">
                <input type="hidden" name="student_id" value="{{ $activeStudent->id }}">
                <div class="row g-2 g-md-3 align-items-end filter-row">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach(['hadir','terlambat','izin','sakit','alpa'] as $s)
                                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <a href="{{ route('parent.rekap_harian', ['student_id' => $activeStudent->id]) }}"
                               class="btn btn-outline-secondary btn-sm flex-fill">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card section-card shadow-sm">
        <div class="card-header">
            <h6 class="fw-bold mb-0" style="color:#1e293b;">
                <i class="bi bi-list-ul me-2 text-primary"></i>Daftar Kehadiran
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                        <tr>
                            <td class="fw-semibold">{{ \Carbon\Carbon::parse($att->date)->format('d/m/Y') }}</td>
                            <td class="text-muted">{{ \Carbon\Carbon::parse($att->date)->isoFormat('dddd') }}</td>
                            <td>@include('parent._status_badge', ['status' => $att->status])</td>
                            <td>{{ $att->scan_time ? \Carbon\Carbon::parse($att->scan_time)->format('H:i') : '-' }}</td>
                            <td class="text-muted small">{{ $att->note ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada data absensi untuk filter yang dipilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attendances->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $attendances->appends(request()->all())->links() }}
            </div>
            @endif
        </div>
    </div>

    @else
    <div class="card section-card shadow-sm">
        <div class="card-body text-center py-5">
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);display:flex;align-items:center;justify-content:center;margin:0 auto 1.2rem;">
                <i class="bi bi-people fs-2" style="color:#6366f1;"></i>
            </div>
            <h5 class="fw-bold mb-2">Belum Ada Siswa Terhubung</h5>
            <p class="text-muted mb-0">Hubungi admin sekolah untuk menautkan akun Anda.</p>
        </div>
    </div>
    @endif

</x-app-layout>