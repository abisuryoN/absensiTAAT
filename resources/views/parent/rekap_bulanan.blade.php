<x-app-layout>
    @section('title', 'Rekap Bulanan Kehadiran')

    {{-- Child Switcher --}}
    @if($children->count() > 1)
    <div class="card glass-card border-0 mb-4">
        <div class="card-body p-3">
            <small class="text-muted fw-semibold d-block mb-2">Pilih Anak:</small>
            <div class="d-flex flex-wrap gap-2">
                @foreach($children as $child)
                <a href="{{ route('parent.rekap_bulanan', ['student_id' => $child->id]) }}"
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
            <h3 class="fw-bold tracking-tight text-dark mb-1">Rekap Bulanan</h3>
            <p class="text-muted mb-0">
                @if($activeStudent)
                    {{ $activeStudent->name }} &bull; {{ $activeStudent->schoolClass->name ?? '-' }}
                @endif
            </p>
        </div>
    </div>

    @if($activeStudent)
    {{-- Year filter --}}
    <div class="card glass-card border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('parent.rekap_bulanan') }}" class="row g-2 align-items-end">
                <input type="hidden" name="student_id" value="{{ $activeStudent->id }}">
                <div class="col-auto">
                    <label class="form-label small fw-semibold mb-1">Tahun</label>
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        @for($y = now()->year; $y >= now()->year - 4; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Monthly Summary Table --}}
    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Terlambat</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Alpa</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">% Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyData as $row)
                        @php
                            $pct = $row['total'] > 0
                                ? round(($row['hadir'] + $row['terlambat']) / $row['total'] * 100)
                                : 0;
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $row['month_label'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success">{{ $row['hadir'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning">{{ $row['terlambat'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info">{{ $row['izin'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary">{{ $row['sakit'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger-subtle text-danger">{{ $row['alpa'] }}</span>
                            </td>
                            <td class="text-center text-muted">{{ $row['total'] }}</td>
                            <td class="text-center">
                                @if($row['total'] > 0)
                                    <span class="badge {{ $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $pct }}%
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada data absensi untuk tahun {{ $selectedYear }}.
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
            <p class="text-muted">Hubungi admin sekolah untuk menautkan akun Anda.</p>
        </div>
    </div>
    @endif
</x-app-layout>