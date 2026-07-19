<x-app-layout>
    @section('title', 'Dashboard Orang Tua')

    {{-- Child Switcher --}}
    @if($children->count() > 1)
    <div class="card glass-card border-0 mb-4">
        <div class="card-body p-3">
            <small class="text-muted fw-semibold d-block mb-2">Pilih Anak:</small>
            <div class="d-flex flex-wrap gap-2">
                @foreach($children as $child)
                <a href="?student_id={{ $child->id }}"
                   class="btn btn-sm {{ $activeStudent && $activeStudent->id === $child->id ? 'btn-primary' : 'btn-light border' }}">
                    {{ $child->name }}
                    <span class="badge {{ $activeStudent && $activeStudent->id === $child->id ? 'bg-white text-primary' : 'bg-secondary' }} ms-1">
                        {{ $child->schoolClass->name ?? '-' }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($activeStudent)
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">{{ $activeStudent->name }}</h3>
            <p class="text-muted mb-0">
                NIS: {{ $activeStudent->nis ?? '-' }} &bull;
                {{ $activeStudent->schoolClass->name ?? 'Kelas tidak diketahui' }}
                @if($activeStudent->schoolClass?->major)
                    &bull; {{ $activeStudent->schoolClass->major->name }}
                @endif
            </p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-2 fw-bold text-success">{{ $summary['hadir'] ?? 0 }}</div>
                <small class="text-muted">Hadir</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-2 fw-bold text-warning">{{ $summary['terlambat'] ?? 0 }}</div>
                <small class="text-muted">Terlambat</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-2 fw-bold text-info">{{ ($summary['izin'] ?? 0) + ($summary['sakit'] ?? 0) }}</div>
                <small class="text-muted">Izin / Sakit</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-2 fw-bold text-danger">{{ $summary['alpa'] ?? 0 }}</div>
                <small class="text-muted">Alpa</small>
            </div>
        </div>
    </div>

    {{-- Recent Attendance --}}
    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Rekap Kehadiran Terbaru (Bulan Ini)</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('parent.rekap_harian', ['student_id' => $activeStudent->id]) }}"
                       class="btn btn-sm btn-outline-primary">Rekap Harian</a>
                    <a href="{{ route('parent.rekap_bulanan', ['student_id' => $activeStudent->id]) }}"
                       class="btn btn-sm btn-outline-secondary">Rekap Bulanan</a>
                </div>
            </div>
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
                            <td>{{ \Carbon\Carbon::parse($att->date)->isoFormat('ddd, D MMM Y') }}</td>
                            <td>@include('parent._status_badge', ['status' => $att->status])</td>
                            <td class="text-muted small">{{ $att->note ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada data absensi bulan ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @else
    <div class="card glass-card border-0">
        <div class="card-body text-center py-5">
            <i class="bi bi-people fs-1 text-muted d-block mb-3"></i>
            <h5>Belum Ada Siswa Terhubung</h5>
            <p class="text-muted">Akun Anda belum ditautkan ke data siswa manapun.<br>
            Hubungi admin sekolah untuk menautkan akun Anda ke data anak Anda.</p>
        </div>
    </div>
    @endif
</x-app-layout>