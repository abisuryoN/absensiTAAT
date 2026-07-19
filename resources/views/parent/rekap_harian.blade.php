<x-app-layout>
    @section('title', 'Rekap Harian Kehadiran')

    {{-- Child Switcher --}}
    @if($children->count() > 1)
    <div class="card glass-card border-0 mb-4">
        <div class="card-body p-3">
            <small class="text-muted fw-semibold d-block mb-2">Pilih Anak:</small>
            <div class="d-flex flex-wrap gap-2">
                @foreach($children as $child)
                <a href="{{ route('parent.rekap_harian', ['student_id' => $child->id]) }}"
                   class="btn btn-sm {{ $activeStudent && $activeStudent->id === $child->id ? 'btn-primary' : 'btn-light border' }}">
                    {{ $child->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Rekap Harian</h3>
            <p class="text-muted mb-0">
                @if($activeStudent)
                    {{ $activeStudent->name }} &bull; {{ $activeStudent->schoolClass->name ?? '-' }}
                @endif
            </p>
        </div>
    </div>

    @if($activeStudent)
    {{-- Filter --}}
    <div class="card glass-card border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('parent.rekap_harian') }}" class="row g-2 align-items-end">
                <input type="hidden" name="student_id" value="{{ $activeStudent->id }}">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control form-control-sm"
                           value="{{ $from }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control form-control-sm"
                           value="{{ $to }}">
                </div>
                <div class="col-md-2">
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
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('parent.rekap_harian', ['student_id' => $activeStudent->id]) }}"
                       class="btn btn-light border btn-sm ms-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-premium align-middle">
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
            <div class="mt-3">{{ $attendances->appends(request()->all())->links() }}</div>
        </div>
    </div>
    @else
    <div class="card glass-card border-0">
        <div class="card-body text-center py-5">
            <i class="bi bi-people fs-1 text-muted d-block mb-3"></i>
            <h5>Belum Ada Siswa Terhubung</h5>
            <p class="text-muted">Hubungi admin sekolah untuk menautkan akun Anda.</p>
        </div>
    </div>
    @endif
</x-app-layout>