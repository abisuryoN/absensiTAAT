<x-app-layout>
    @section('title', 'Riwayat Kehadiran')

    <div class="row g-4">
        <!-- Header -->
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-clock-history me-2 text-success"></i>Riwayat Kehadiran
                    </h4>
                    <p class="text-muted mb-0 fs-7">
                        {{ $student->name }} &bull; Kelas {{ $student->class->name ?? '-' }}
                    </p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="col-12">
            <div class="row g-3">
                <div class="col-6 col-md-2">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-3 fw-bold text-dark">{{ $stats['total'] }}</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Total Hari</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-3 fw-bold text-success">{{ $stats['hadir'] }}</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Hadir</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-3 fw-bold text-warning">{{ $stats['terlambat'] }}</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Terlambat</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-3 fw-bold text-info">{{ $stats['izin'] }}</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Izin</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-3 fw-bold text-primary">{{ $stats['sakit'] }}</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Sakit</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-3 fw-bold text-danger">{{ $stats['alpha'] }}</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Alpha</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="col-12">
            <div class="card glass-card border-0 shadow-sm p-3">
                <form method="GET" action="{{ route('student.history') }}" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Bulan</label>
                        <input type="month"
                               name="month"
                               value="{{ $selectedMonth }}"
                               class="form-control form-control-sm"
                               style="max-width: 180px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Status</label>
                        <div class="custom-select-wrapper" data-placeholder="Semua Status">
                        <select name="status" class="form-select form-select-sm" style="max-width: 160px;">
                            <option value="">Semua Status</option>
                            <option value="hadir" {{ $selectedStatus === 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="terlambat" {{ $selectedStatus === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                            <option value="izin" {{ $selectedStatus === 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ $selectedStatus === 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="alpha" {{ $selectedStatus === 'alpha' ? 'selected' : '' }}>Alpha</option>
                        </select>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('student.history') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="col-12">
            <div class="card glass-card border-0 shadow-sm">
                <div class="card-body p-0">
                    @if($attendances->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted mb-0">Tidak ada data kehadiran untuk filter yang dipilih.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase px-4">Tanggal</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase">Hari</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase">Jam Masuk</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase">Status</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase">Metode</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances as $att)
                                        <tr>
                                            <td data-label="Tanggal" class="px-4">
                                                <span class="fw-semibold">{{ $att->date->format('d M Y') }}</span>
                                            </td>
                                            <td data-label="Hari">
                                                <span class="fs-7 text-muted">{{ $att->date->translatedFormat('l') }}</span>
                                            </td>
                                            <td data-label="Jam Masuk">
                                                @if($att->time_in && $att->time_in !== '00:00:00')
                                                    <span class="badge bg-dark bg-opacity-10 text-dark fw-semibold px-2 py-1">
                                                        {{ substr($att->time_in, 0, 5) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted fs-8">-</span>
                                                @endif
                                            </td>
                                            <td data-label="Status">
                                                @php
                                                    $statusConfig = [
                                                        'hadir' => ['bg' => 'bg-success', 'icon' => 'bi-check-circle-fill'],
                                                        'terlambat' => ['bg' => 'bg-warning text-dark', 'icon' => 'bi-clock-fill'],
                                                        'izin' => ['bg' => 'bg-info', 'icon' => 'bi-envelope-fill'],
                                                        'sakit' => ['bg' => 'bg-primary', 'icon' => 'bi-heart-pulse-fill'],
                                                        'alpha' => ['bg' => 'bg-danger', 'icon' => 'bi-x-circle-fill'],
                                                    ];
                                                    $cfg = $statusConfig[$att->status] ?? ['bg' => 'bg-secondary', 'icon' => 'bi-question-circle'];
                                                @endphp
                                                <span class="badge {{ $cfg['bg'] }} px-2 py-1">
                                                    <i class="bi {{ $cfg['icon'] }} me-1"></i>{{ ucfirst($att->status) }}
                                                </span>
                                            </td>
                                            <td data-label="Metode">
                                                @php
                                                    $methodLabels = [
                                                        'barcode' => 'Barcode',
                                                        'qr_code' => 'QR Code',
                                                        'manual' => 'Manual',
                                                    ];
                                                @endphp
                                                <span class="fs-8 text-muted">{{ $methodLabels[$att->method] ?? $att->method }}</span>
                                            </td>
                                            <td data-label="Catatan">
                                                <span class="fs-8 text-muted">{{ $att->note ?? '-' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-4 py-3 border-top">
                            {{ $attendances->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
