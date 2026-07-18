<x-app-layout>
    @section('title', 'Rekap Absensi Hari Ini')

    {{-- Desktop Header --}}
    <div class="row mb-4 align-items-center d-none d-md-flex">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Rekap Absensi Hari Ini</h3>
            <p class="text-muted mb-0">Daftar lengkap kehadiran siswa melalui pintu gerbang pada tanggal {{ now()->format('d M Y') }}.</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.attendance.manual') }}" class="btn btn-outline-primary fw-semibold">
                <i class="bi bi-pencil-square me-1"></i> Absensi Manual
            </a>
            <a href="{{ route('admin.attendance.scan') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-qr-code-scan me-1"></i> Buka Layar Scan
            </a>
        </div>
    </div>

    {{-- Mobile Header --}}
    <div class="d-block d-md-none mobile-page-content">
        <div class="mobile-section-header">
            <div>
                <h3 class="mobile-heading">Rekap Absensi Hari Ini</h3>
                <p class="mobile-subtitle">Daftar kehadiran siswa via gerbang pada {{ now()->format('d M Y') }}</p>
            </div>
        </div>
        <div class="mobile-btn-row">
            <a href="{{ route('admin.attendance.manual') }}" class="btn btn-outline-primary mobile-btn flex-fill">
                <i class="bi bi-pencil-square me-1"></i> Manual
            </a>
            <a href="{{ route('admin.attendance.scan') }}" class="btn btn-primary mobile-btn flex-fill">
                <i class="bi bi-qr-code-scan me-1"></i> Buka Scanner
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        {{-- Desktop card body --}}
        <div class="card-body p-4 d-none d-md-block">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.attendance.today') }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama siswa atau NIS..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-select-wrapper" data-placeholder="Semua Kelas">
                    <select name="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-select-wrapper" data-placeholder="Semua Status Kehadiran">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status Kehadiran</option>
                        <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="izin" {{ request('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="alpha" {{ request('status') === 'alpha' ? 'selected' : '' }}>Alpha (Tidak Masuk)</option>
                    </select>
                    </div>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-light border fw-semibold">Filter</button>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th>Waktu Scan</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Metode</th>
                            <th>Keterangan / Catatan</th>
                            <th>Petugas / Scanner</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td data-label="Waktu Scan" class="fw-semibold text-dark">{{ substr($attendance->time_in, 0, 5) }} WIB</td>
                                <td data-label="NIS" class="text-muted fs-8">{{ $attendance->student->nis }}</td>
                                <td data-label="Nama Siswa" class="fw-semibold text-dark">{{ $attendance->student->name }}</td>
                                <td data-label="Kelas">
                                    <span class="badge bg-light text-dark border px-2 py-1 fs-8">{{ $attendance->student->class->name ?? '-' }}</span>
                                </td>
                                <td data-label="Status">
                                    @if($attendance->status === 'hadir')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Hadir</span>
                                    @elseif($attendance->status === 'terlambat')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-8">Terlambat</span>
                                    @elseif($attendance->status === 'izin')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 fs-8">Izin</span>
                                    @elseif($attendance->status === 'sakit')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 fs-8">Sakit</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Alpha</span>
                                    @endif
                                </td>
                                <td data-label="Metode">
                                    @if($attendance->method === 'barcode')
                                        <span class="fs-8 text-muted"><i class="bi bi-barcode me-1"></i> Barcode</span>
                                    @elseif($attendance->method === 'qr_code')
                                        <span class="fs-8 text-muted"><i class="bi bi-qr-code me-1"></i> QR Code</span>
                                    @else
                                        <span class="fs-8 text-muted"><i class="bi bi-pencil-square me-1"></i> Manual</span>
                                    @endif
                                </td>
                                <td data-label="Keterangan" class="text-muted fs-8">{{ $attendance->note ?: '-' }}</td>
                                <td data-label="Petugas" class="fs-8">{{ $attendance->scanner->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Belum ada data absensi gerbang yang masuk hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $attendances->appends(request()->all())->links() }}
            </div>
        </div>

        {{-- Mobile card body --}}
        <div class="d-block d-md-none mobile-card-body">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.attendance.today') }}" class="mobile-filter-form">
                <div class="mobile-filter-row">
                    <div class="mobile-search-group">
                        <span class="mobile-search-icon"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="mobile-search-input" placeholder="Cari nama atau NIS..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="mobile-filter-row">
                    <div class="mobile-select-wrapper flex-fill">
                        <select name="class_id" class="mobile-select" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mobile-select-wrapper flex-fill">
                        <select name="status" class="mobile-select" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                            <option value="izin" {{ request('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="alpha" {{ request('status') === 'alpha' ? 'selected' : '' }}>Alpha</option>
                        </select>
                    </div>
                    <button type="submit" class="mobile-filter-btn">Filter</button>
                </div>
            </form>

            <!-- Cards -->
            <div class="mobile-attendance-list">
                @forelse($attendances as $attendance)
                    <div class="mobile-attendance-card">
                        <div class="mobile-att-card-top">
                            <div class="mobile-att-name">{{ $attendance->student->name }}</div>
                            <div class="mobile-att-status">
                                @if($attendance->status === 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($attendance->status === 'terlambat')
                                    <span class="badge bg-danger">Terlambat</span>
                                @elseif($attendance->status === 'izin')
                                    <span class="badge bg-info">Izin</span>
                                @elseif($attendance->status === 'sakit')
                                    <span class="badge bg-warning">Sakit</span>
                                @else
                                    <span class="badge bg-secondary">Alpha</span>
                                @endif
                            </div>
                        </div>
                        <div class="mobile-att-card-bottom">
                            <span>NIS: {{ $attendance->student->nis }}</span>
                            <span>{{ $attendance->student->class->name ?? '-' }}</span>
                            <span>{{ substr($attendance->time_in, 0, 5) }} WIB</span>
                        </div>
                    </div>
                @empty
                    <div class="mobile-empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada data absensi hari ini.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $attendances->appends(request()->all())->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
</x-app-layout>
