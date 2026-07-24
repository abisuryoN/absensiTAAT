<x-app-layout>
    @section('title', 'Rekap Bulanan Kehadiran')

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
        /* Colored badges in table cells */
        .pct-badge { min-width: 52px; display: inline-block; text-align:center; }
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
                <a href="{{ route('parent.rekap_bulanan', ['student_id' => $child->id]) }}"
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
            <h4 class="fw-bold mb-0" style="color:#1e293b;">Rekap Bulanan</h4>
            <p class="text-muted mb-0 fs-7">
                @if($activeStudent)
                    {{ $activeStudent->name }} &bull; {{ $activeStudent->schoolClass->name ?? '-' }}
                @endif
            </p>
        </div>
    </div>

    @if($activeStudent)

    {{-- Year Filter --}}
    <div class="card filter-card mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('parent.rekap_bulanan') }}">
                <input type="hidden" name="student_id" value="{{ $activeStudent->id }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-auto">
                        <label class="form-label small fw-semibold mb-1">Tahun</label>
                        <select name="year" class="form-select form-select-sm" style="min-width:110px;" onchange="this.form.submit()">
                            @for($y = now()->year; $y >= now()->year - 4; $y--)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i>Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Monthly Summary Table --}}
    <div class="card section-card shadow-sm">
        <div class="card-header">
            <h6 class="fw-bold mb-0" style="color:#1e293b;">
                <i class="bi bi-bar-chart me-2 text-primary"></i>Rekap Per Bulan — Tahun {{ $selectedYear }}
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0">
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
                                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $row['hadir'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $row['terlambat'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $row['izin'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $row['sakit'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $row['alpa'] }}</span>
                            </td>
                            <td class="text-center text-muted fw-semibold">{{ $row['total'] }}</td>
                            <td class="text-center">
                                @if($row['total'] > 0)
                                    <span class="badge pct-badge {{ $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $pct }}%
                                    </span>
                                @else
                                    <span class="text-muted">–</span>
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
            @if($monthlyData->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $monthlyData->appends(['year' => $selectedYear, 'student_id' => $activeStudent->id])->links() }}
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