<x-app-layout>
    @section('title', 'Rekap Absensi Hari Ini')

    <div class="row mb-4 align-items-center">
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

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.attendance.today') }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama siswa atau NIS..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status Kehadiran</option>
                        <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="izin" {{ request('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="alpha" {{ request('status') === 'alpha' ? 'selected' : '' }}>Alpha (Tidak Masuk)</option>
                    </select>
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
                                <td class="fw-semibold text-dark">{{ substr($attendance->time_in, 0, 5) }} WIB</td>
                                <td class="text-muted fs-8">{{ $attendance->student->nis }}</td>
                                <td class="fw-semibold text-dark">{{ $attendance->student->name }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 fs-8">{{ $attendance->student->class->name ?? '-' }}</span>
                                </td>
                                <td>
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
                                <td>
                                    @if($attendance->method === 'barcode')
                                        <span class="fs-8 text-muted"><i class="bi bi-barcode me-1"></i> Barcode</span>
                                    @elseif($attendance->method === 'qr_code')
                                        <span class="fs-8 text-muted"><i class="bi bi-qr-code me-1"></i> QR Code</span>
                                    @else
                                        <span class="fs-8 text-muted"><i class="bi bi-pencil-square me-1"></i> Manual</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-8">{{ $attendance->note ?: '-' }}</td>
                                <td class="fs-8">{{ $attendance->scanner->name ?? 'System' }}</td>
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
    </div>
</x-app-layout>
