<x-app-layout>
    @section('title', 'Rekap Absensi Hari Ini')

    {{-- STAT CARDS --}}
    <div class="row g-3 mb-4 stat-cards-row">
        <div class="col-6 col-md-2">
            <div class="card card-stat text-white border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                <div class="card-body p-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0" id="stat-total">{{ number_format($totalSiswa) }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Total Siswa</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card card-stat bg-success text-white border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0" id="stat-hadir">{{ number_format($hadirCount) }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Hadir</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card card-stat bg-warning text-white border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0" id="stat-terlambat">{{ number_format($terlambatCount) }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Terlambat</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'izin']) }}" class="text-decoration-none">
                <div class="card card-stat text-white border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                            <i class="bi bi-envelope-check fs-4"></i>
                        </div>
                        <div class="stat-value display-6 fw-bold mb-0" id="stat-izin">{{ number_format($izinCount) }}</div>
                        <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Izin</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'sakit']) }}" class="text-decoration-none">
                <div class="card card-stat text-white border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                            <i class="bi bi-heart-pulse fs-4"></i>
                        </div>
                        <div class="stat-value display-6 fw-bold mb-0" id="stat-sakit">{{ number_format($sakitCount) }}</div>
                        <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Sakit</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'tidak_hadir']) }}" class="text-decoration-none">
                <div class="card card-stat bg-danger text-white border-0 rounded-4 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        </div>
                        <div class="stat-value display-6 fw-bold mb-0" id="stat-tidak-hadir">{{ number_format($tidakHadir) }}</div>
                        <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Tidak Hadir</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="row mb-4 align-items-center d-none d-md-flex">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Rekap Absensi Hari Ini</h3>
            <p class="text-muted mb-0">Daftar lengkap kehadiran siswa melalui pintu gerbang pada tanggal {{ now()->format('d M Y') }}.</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <button type="button" class="btn btn-outline-success fw-semibold" data-bs-toggle="modal" data-bs-target="#modalExport">
                <i class="bi bi-download me-1"></i> Download Rekap
            </button>
            <a href="{{ route('admin.attendance.manual') }}" class="btn btn-outline-primary fw-semibold">
                <i class="bi bi-pencil-square me-1"></i> Absensi Manual
            </a>
            <a href="{{ route('admin.attendance.scan') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-qr-code-scan me-1"></i> Buka Layar Scan
            </a>
        </div>
    </div>

    {{-- MOBILE HEADER --}}
    <div class="d-block d-md-none mobile-page-content">
        <div class="mobile-section-header">
            <div>
                <h3 class="mobile-heading">Rekap Absensi Hari Ini</h3>
                <p class="mobile-subtitle">{{ now()->format('d M Y') }}</p>
            </div>
        </div>
        <div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalExport" style="flex:1;">
                <i class="bi bi-download me-1"></i> Download
            </button>
            <a href="{{ route('admin.attendance.manual') }}" class="btn btn-outline-primary btn-sm" style="flex:1;">
                <i class="bi bi-pencil-square me-1"></i> Manual
            </a>
            <a href="{{ route('admin.attendance.scan') }}" class="btn btn-primary btn-sm" style="flex:1;">
                <i class="bi bi-qr-code-scan me-1"></i> Scanner
            </a>
        </div>
    </div>

    {{-- MAIN CARD --}}
    <div class="card glass-card border-0">

        {{-- DESKTOP --}}
        <div class="card-body p-4 d-none d-md-block">
            <form method="GET" action="{{ route('admin.attendance.today') }}" class="row g-3 mb-4" id="filterForm">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama siswa atau NIS..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="custom-select-wrapper" data-placeholder="Semua Kelas">
                        <select name="class_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="custom-select-wrapper" data-placeholder="Semua Jurusan">
                        <select name="major_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Jurusan</option>
                            @foreach($majors as $major)
                                <option value="{{ $major->id }}" {{ request('major_id') == $major->id ? 'selected' : '' }}>{{ $major->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-select-wrapper" data-placeholder="Semua Status Kehadiran">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Status Kehadiran</option>
                            <option value="hadir"       {{ request('status') === 'hadir'       ? 'selected' : '' }}>Hadir</option>
                            <option value="terlambat"   {{ request('status') === 'terlambat'   ? 'selected' : '' }}>Terlambat</option>
                            <option value="izin"        {{ request('status') === 'izin'        ? 'selected' : '' }}>Izin</option>
                            <option value="sakit"       {{ request('status') === 'sakit'       ? 'selected' : '' }}>Sakit</option>
                            <option value="alpha"       {{ request('status') === 'alpha'       ? 'selected' : '' }}>Alpha</option>
                            <option value="tidak_hadir" {{ request('status') === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-light border fw-semibold">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-premium align-middle" id="attendanceTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="checkAll" title="Pilih semua Tidak Hadir">
                                </div>
                            </th>
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
                        @forelse($students as $student)
                            @php
                                $att = $student->attendanceGates->first();
                                $isTidakHadir = $att === null;
                            @endphp
                            <tr class="{{ $isTidakHadir ? 'row-tidak-hadir' : '' }}" data-student-id="{{ $student->id }}">
                                <td>
                                    @if($isTidakHadir)
                                        <div class="form-check mb-0">
                                            <input class="form-check-input student-checkbox"
                                                   type="checkbox"
                                                   value="{{ $student->id }}"
                                                   data-name="{{ $student->name }}">
                                        </div>
                                    @endif
                                </td>
                                <td class="fw-semibold text-dark">
                                    @if($isTidakHadir)
                                        <span class="text-muted fs-8">—</span>
                                    @else
                                        {{ substr($att->time_in, 0, 5) }} WIB
                                    @endif
                                </td>
                                <td class="text-muted fs-8">{{ $student->nis }}</td>
                                <td class="fw-semibold text-dark">{{ $student->name }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 fs-8">{{ $student->class->name ?? '-' }}</span>
                                </td>
                                <td>
                                    @if($isTidakHadir)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-8">Tidak Hadir</span>
                                    @elseif($att->status === 'hadir')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Hadir</span>
                                    @elseif($att->status === 'terlambat')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 fs-8">Terlambat</span>
                                    @elseif($att->status === 'izin')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 fs-8">Izin</span>
                                    @elseif($att->status === 'sakit')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Sakit</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Alpha</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isTidakHadir)
                                        <span class="text-muted fs-8">—</span>
                                    @elseif($att->method === 'barcode')
                                        <span class="fs-8 text-muted"><i class="bi bi-barcode me-1"></i>Barcode</span>
                                    @elseif($att->method === 'qr_code')
                                        <span class="fs-8 text-muted"><i class="bi bi-qr-code me-1"></i>QR Code</span>
                                    @else
                                        <span class="fs-8 text-muted"><i class="bi bi-pencil-square me-1"></i>Manual</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-8">{{ $isTidakHadir ? '—' : ($att->note ?: '—') }}</td>
                                <td class="fs-8">{{ $isTidakHadir ? '—' : ($att->scanner->name ?? 'System') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $students->appends(request()->all())->links() }}</div>
        </div>

        {{-- MOBILE --}}
        <div class="d-block d-md-none mobile-card-body">
            <div class="mobile-search-card" style="margin-bottom:0;">
                <form method="GET" action="{{ route('admin.attendance.today') }}" class="mobile-search-form">
                    <div class="mobile-search-row">
                        <div class="mobile-search-group">
                            <span class="mobile-search-icon"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="mobile-search-input" placeholder="Cari nama atau NIS..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="mobile-cari-btn">Cari</button>
                    </div>
                    <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;">
                        <div class="custom-select-wrapper" data-placeholder="Kelas" style="flex:1; min-width:90px;">
                            <select name="class_id" onchange="this.form.submit()">
                                <option value="">Semua Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="custom-select-wrapper" data-placeholder="Jurusan" style="flex:1; min-width:90px;">
                            <select name="major_id" onchange="this.form.submit()">
                                <option value="">Semua Jurusan</option>
                                @foreach($majors as $major)
                                    <option value="{{ $major->id }}" {{ request('major_id') == $major->id ? 'selected' : '' }}>{{ $major->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="custom-select-wrapper" data-placeholder="Status" style="flex:1; min-width:90px;">
                            <select name="status" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="hadir"       {{ request('status') === 'hadir'       ? 'selected' : '' }}>Hadir</option>
                                <option value="terlambat"   {{ request('status') === 'terlambat'   ? 'selected' : '' }}>Terlambat</option>
                                <option value="izin"        {{ request('status') === 'izin'        ? 'selected' : '' }}>Izin</option>
                                <option value="sakit"       {{ request('status') === 'sakit'       ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha"       {{ request('status') === 'alpha'       ? 'selected' : '' }}>Alpha</option>
                                <option value="tidak_hadir" {{ request('status') === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mobile-attendance-list">
                @forelse($students as $student)
                    @php
                        $att = $student->attendanceGates->first();
                        $isTidakHadir = $att === null;
                    @endphp
                    <div class="mobile-attendance-card {{ $isTidakHadir ? 'mobile-card-absent' : '' }}">
                        <div class="mobile-att-card-top">
                            @if($isTidakHadir)
                            <div style="display:flex; align-items:center; margin-right:8px;">
                                <input class="form-check-input student-checkbox-mobile" type="checkbox"
                                       value="{{ $student->id }}" data-name="{{ $student->name }}"
                                       style="width:18px; height:18px; cursor:pointer;">
                            </div>
                            @endif
                            <div class="mobile-att-avatar">{{ strtoupper(substr($student->name, 0, 1)) }}</div>
                            <div class="mobile-att-info">
                                <div class="mobile-att-name">{{ $student->name }}</div>
                                <div class="mobile-att-meta">
                                    <span>NIS {{ $student->nis }}</span>
                                    <span class="dot-sep">•</span>
                                    <span>{{ $student->class->name ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="mobile-att-status">
                                @if($isTidakHadir)
                                    <span class="badge bg-danger">Tidak Hadir</span>
                                @elseif($att->status === 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($att->status === 'terlambat')
                                    <span class="badge bg-warning">Terlambat</span>
                                @elseif($att->status === 'izin')
                                    <span class="badge bg-info">Izin</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($att->status) }}</span>
                                @endif
                            </div>
                        </div>
                        @if(!$isTidakHadir)
                        <div class="mobile-att-card-bottom">
                            <div class="mobile-att-detail"><i class="bi bi-clock"></i><span>{{ substr($att->time_in, 0, 5) }} WIB</span></div>
                            <div class="mobile-att-detail">
                                @if($att->method === 'barcode')
                                    <i class="bi bi-barcode"></i><span>Barcode</span>
                                @elseif($att->method === 'qr_code')
                                    <i class="bi bi-qr-code"></i><span>QR Code</span>
                                @else
                                    <i class="bi bi-pencil-square"></i><span>Manual</span>
                                @endif
                            </div>
                            @if($att->note)
                            <div class="mobile-att-detail"><i class="bi bi-chat-dots"></i><span>{{ Str::limit($att->note, 30) }}</span></div>
                            @endif
                            <div class="mobile-att-detail"><i class="bi bi-person-badge"></i><span>{{ $att->scanner->name ?? 'System' }}</span></div>
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="mobile-empty-state" style="padding-top:32px;">
                        <div class="empty-icon-wrap"><i class="bi bi-calendar-check"></i></div>
                        <h4 class="empty-title">Belum Ada Data</h4>
                        <p class="empty-desc">Tidak ada data yang sesuai filter.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-3">{{ $students->appends(request()->all())->links('vendor.pagination.bootstrap-5') }}</div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         FLOATING ACTION BAR
    ═══════════════════════════════════════ --}}
    <div id="floatingActionBar" class="floating-action-bar d-none">
        <div class="fab-inner">
            <span class="fab-count">
                <i class="bi bi-check2-square me-1"></i>
                <strong id="fabCount">0</strong> siswa dipilih
            </span>
            <div class="fab-actions">
                <button type="button" class="btn btn-info btn-sm fw-semibold text-white" onclick="openMarkModal('izin')">
                    <i class="bi bi-envelope-check me-1"></i> Tandai Izin
                </button>
                <button type="button" class="btn btn-warning btn-sm fw-semibold text-dark" onclick="openMarkModal('sakit')">
                    <i class="bi bi-heart-pulse me-1"></i> Tandai Sakit
                </button>
                <button type="button" class="btn btn-outline-light btn-sm fw-semibold" onclick="cancelSelection()">
                    <i class="bi bi-x-lg me-1"></i> Batal
                </button>
            </div>
        </div>
    </div>

    @pushOnce('modals')
    {{-- ═══════════════════════════════════════
         MODAL: KONFIRMASI TANDAI IZIN/SAKIT
    ═══════════════════════════════════════ --}}
    <div class="modal fade" id="modalMarkStatus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-check2-circle me-2"></i>
                        <span id="markStatusTitle">Tandai Izin</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border mb-3 py-2">
                        <small class="text-muted">
                            <i class="bi bi-people me-1"></i>
                            Akan menandai <strong id="markCount">0</strong> siswa sebagai
                            <strong id="markStatusName">Izin</strong>:
                        </small>
                        <div id="markStudentList" class="mt-1" style="max-height:120px; overflow-y:auto;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="markNote" class="form-label fw-semibold">
                            Keterangan / Catatan <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea id="markNote" class="form-control" rows="3"
                                  placeholder="Misal: Demam, Acara keluarga, Izin dokter..."
                                  maxlength="500"></textarea>
                        <div class="form-text">Berlaku untuk semua siswa yang dipilih.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary fw-semibold" id="btnConfirmMark" onclick="submitBulkMark()">
                        <i class="bi bi-check-lg me-1"></i> Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         MODAL: DOWNLOAD REKAP
    ═══════════════════════════════════════ --}}
    <div class="modal fade" id="modalExport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-download me-2"></i> Download Rekap Absensi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm" method="GET" action="{{ route('admin.attendance.export') }}" target="_blank">

                        {{-- Rentang Tanggal --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Rentang Tanggal</label>
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm date-preset" data-preset="today">
                                    Hari Ini
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm date-preset" data-preset="this_month">
                                    Bulan Ini
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm date-preset" data-preset="custom">
                                    Pilih Tanggal
                                </button>
                            </div>
                            <div id="customDateRange" class="row g-2">
                                <div class="col-sm-6">
                                    <label class="form-label text-muted small">Dari Tanggal</label>
                                    <input type="date" name="date_from" id="exportDateFrom" class="form-control"
                                           value="{{ now()->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label text-muted small">Sampai Tanggal</label>
                                    <input type="date" name="date_to" id="exportDateTo" class="form-control"
                                           value="{{ now()->format('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            {{-- Filter Kelas --}}
                            <div class="col-sm-4">
                                <label class="form-label fw-semibold">Kelas</label>
                                <div class="custom-select-wrapper" data-placeholder="Semua Kelas">
                                    <select name="class_id">
                                        <option value="">Semua Kelas</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Filter Jurusan --}}
                            <div class="col-sm-4">
                                <label class="form-label fw-semibold">Jurusan</label>
                                <div class="custom-select-wrapper" data-placeholder="Semua Jurusan">
                                    <select name="major_id">
                                        <option value="">Semua Jurusan</option>
                                        @foreach($majors as $major)
                                            <option value="{{ $major->id }}">{{ $major->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Filter Status --}}
                            <div class="col-sm-4">
                                <label class="form-label fw-semibold">Status Kehadiran</label>
                                <div class="custom-select-wrapper" data-placeholder="Semua Status">
                                    <select name="status">
                                        <option value="semua">Semua Status</option>
                                        <option value="hadir">Hadir</option>
                                        <option value="terlambat">Terlambat</option>
                                        <option value="izin">Izin</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="alpha">Alpha</option>
                                        <option value="tidak_hadir">Tidak Hadir</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Format File --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Format File</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="formatExcel" value="excel" checked>
                                    <label class="form-check-label" for="formatExcel">
                                        <i class="bi bi-file-earmark-spreadsheet text-success me-1"></i> Excel (.xlsx)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="formatPdf" value="pdf">
                                    <label class="form-check-label" for="formatPdf">
                                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i> PDF (.pdf)
                                    </label>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success fw-semibold" onclick="submitExport()">
                        <i class="bi bi-download me-1"></i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endPushOnce

    {{-- ═══════════════════════════════════════
         STYLES
    ═══════════════════════════════════════ --}}
    @push('styles')
    <style>
        /* Card Statistik - matching dashboard design */
        .card-stat {
            position: relative;
            border-radius: 16px !important;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }
        .card-stat::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -30px;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            z-index: 0;
        }
        .card-stat .card-body {
            position: relative;
            z-index: 1;
            min-height: 110px;
        }
        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.18) !important;
        }
        .card-stat .stat-value {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            line-height: 1.1;
            color: white !important;
        }
        .card-stat .stat-label {
            font-size: clamp(0.7rem, 2vw, 0.85rem);
            color: rgba(255, 255, 255, 0.75) !important;
        }
        
        @media (max-width: 767.98px) {
            .card-stat .card-body {
                padding: 1rem !important;
                min-height: 100px;
            }
            .card-stat .stat-value {
                font-size: 1.6rem;
            }
            .card-stat .stat-label {
                font-size: 0.7rem;
            }
            .stat-card-body {
                padding: 16px 12px;
            }
            .stat-number {
                font-size: 1.5rem !important;
                line-height: 1.2;
            }
            .stat-label {
                font-size: 0.7rem !important;
                margin-top: 2px;
                display: block;
            }
            /* Prevent stat label overflow */
            .card.rounded-4 { border-radius: 12px !important; }
            /* Mobile action buttons spacing */
            .d-block.d-md-none .mobile-page-content > div[style*="gap:8px"] {
                margin-top: 4px;
            }
            /* Mobile search/filter area spacing */
            .mobile-search-card {
                margin-top: 12px !important;
            }
            /* Modal mobile fixes */
            #modalExport .modal-body {
                max-height: 70vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            #modalExport .modal-dialog {
                margin: 8px;
                max-width: calc(100vw - 16px);
            }
            #modalExport .modal-footer .btn {
                min-height: 44px;
                font-size: 1rem;
            }
            #modalMarkStatus .modal-dialog {
                margin: 8px;
                max-width: calc(100vw - 16px);
            }
            /* Ensure modal appears above bottom nav */
            .modal { z-index: 1060 !important; }
            .modal-backdrop { z-index: 1059 !important; }

            /* Stat cards: bypass Bootstrap gutter (reset by _mobile.scss) with CSS flexbox gap */
            .stat-cards-row {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 12px !important;
                margin: 0 0 16px 0 !important;
            }
            .stat-cards-row > [class*="col-"] {
                flex: 0 0 calc(50% - 6px) !important;
                width: calc(50% - 6px) !important;
                max-width: calc(50% - 6px) !important;
                padding: 0 !important;
                margin-top: 0 !important;
            }
        }

        /* Floating Action Bar */
        .floating-action-bar {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            min-width: 340px;
            max-width: 90vw;
        }
        .fab-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #1e293b;
            color: #f1f5f9;
            padding: 12px 20px;
            border-radius: 50px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.32);
        }
        .fab-count {
            white-space: nowrap;
            font-size: 0.875rem;
        }
        .fab-actions {
            display: flex;
            gap: 8px;
            flex-wrap: nowrap;
        }
        .row-tidak-hadir td:first-child {
            background: transparent;
        }
        .row-tidak-hadir {
            background-color: #fff7f7;
        }
        .row-tidak-hadir:hover {
            background-color: #fee2e2 !important;
        }
        .row-tidak-hadir.selected-row {
            background-color: #fde8e8 !important;
        }
        .mobile-card-absent {
            border-left: 3px solid #ef4444;
        }
        @media (max-width: 576px) {
            .floating-action-bar { bottom: 72px; }
            .fab-inner { flex-direction: column; align-items: flex-start; border-radius: 16px; padding: 12px 16px; }
            .fab-actions { flex-wrap: wrap; }
        }
    </style>
    @endpush

    {{-- ═══════════════════════════════════════
         JAVASCRIPT
    ═══════════════════════════════════════ --}}
    @push('scripts')
    <script>
    (function () {
        'use strict';

        const today = '{{ now()->format("Y-m-d") }}';
        const bulkMarkUrl = '{{ route("admin.attendance.bulk-mark") }}';
        const csrfToken   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let selectedStatus = 'izin';
        let selectedIds    = new Set();

        // ── Checkbox: desktop ────────────────────────────────────
        function bindDesktopCheckboxes() {
            const all  = document.getElementById('checkAll');
            const rows = document.querySelectorAll('.student-checkbox');

            if (all) {
                all.addEventListener('change', function () {
                    rows.forEach(cb => {
                        cb.checked = this.checked;
                        syncSelection(cb);
                    });
                    updateFab();
                });
            }

            rows.forEach(cb => {
                cb.addEventListener('change', function () {
                    syncSelection(this);
                    updateFab();
                    // Update "select all" indeterminate state
                    if (all) {
                        const total   = rows.length;
                        const checked = [...rows].filter(c => c.checked).length;
                        all.checked       = checked === total;
                        all.indeterminate = checked > 0 && checked < total;
                    }
                });
            });
        }

        // ── Checkbox: mobile ─────────────────────────────────────
        function bindMobileCheckboxes() {
            document.querySelectorAll('.student-checkbox-mobile').forEach(cb => {
                cb.addEventListener('change', function () {
                    syncSelection(this);
                    updateFab();
                });
            });
        }

        function syncSelection(cb) {
            const id   = parseInt(cb.value, 10);
            const name = cb.dataset.name;
            if (cb.checked) {
                selectedIds.add(JSON.stringify({ id, name }));
            } else {
                // Remove matching entry
                selectedIds.forEach(entry => {
                    if (JSON.parse(entry).id === id) selectedIds.delete(entry);
                });
            }
            // Highlight row
            const row = cb.closest('tr') || cb.closest('.mobile-attendance-card');
            if (row) row.classList.toggle('selected-row', cb.checked);
        }

        function getSelectedArray() {
            return [...selectedIds].map(e => JSON.parse(e));
        }

        // ── Floating action bar ──────────────────────────────────
        function updateFab() {
            const count = getSelectedArray().length;
            const bar   = document.getElementById('floatingActionBar');
            const label = document.getElementById('fabCount');
            if (!bar) return;
            if (count > 0) {
                bar.classList.remove('d-none');
                label.textContent = count;
            } else {
                bar.classList.add('d-none');
            }
        }

        // ── Cancel selection ─────────────────────────────────────
        window.cancelSelection = function () {
            selectedIds.clear();
            document.querySelectorAll('.student-checkbox, .student-checkbox-mobile').forEach(cb => {
                cb.checked = false;
                const row = cb.closest('tr') || cb.closest('.mobile-attendance-card');
                if (row) row.classList.remove('selected-row');
            });
            const all = document.getElementById('checkAll');
            if (all) { all.checked = false; all.indeterminate = false; }
            updateFab();
        };

        // ── Open mark modal ──────────────────────────────────────
        window.openMarkModal = function (status) {
            selectedStatus = status;
            const arr = getSelectedArray();
            if (arr.length === 0) return;

            const titleEl     = document.getElementById('markStatusTitle');
            const countEl     = document.getElementById('markCount');
            const statusEl    = document.getElementById('markStatusName');
            const listEl      = document.getElementById('markStudentList');
            const confirmBtn  = document.getElementById('btnConfirmMark');
            const noteEl      = document.getElementById('markNote');

            const label = status === 'izin' ? 'Izin' : 'Sakit';
            titleEl.textContent  = 'Tandai ' + label;
            countEl.textContent  = arr.length;
            statusEl.textContent = label;
            noteEl.value         = '';

            // Confirm button colour
            confirmBtn.className = 'btn fw-semibold ' + (status === 'izin' ? 'btn-info text-white' : 'btn-warning text-dark');

            // Student list
            listEl.innerHTML = arr.map(s =>
                '<span class="badge bg-light text-dark border me-1 mb-1">' + escapeHtml(s.name) + '</span>'
            ).join('');

            const modal = new bootstrap.Modal(document.getElementById('modalMarkStatus'));
            modal.show();
        };

        // ── Submit bulk mark via AJAX ─────────────────────────────
        window.submitBulkMark = function () {
            const arr  = getSelectedArray();
            const note = document.getElementById('markNote').value.trim();
            const btn  = document.getElementById('btnConfirmMark');

            if (arr.length === 0) return;

            btn.disabled    = true;
            btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

            const body = new URLSearchParams();
            arr.forEach(s => body.append('student_ids[]', s.id));
            body.append('status', selectedStatus);
            if (note) body.append('note', note);

            fetch(bulkMarkUrl, {
                method:  'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept':       'application/json',
                },
                body: body.toString(),
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Konfirmasi';

                bootstrap.Modal.getInstance(document.getElementById('modalMarkStatus')).hide();

                if (data.success) {
                    showToast('success', data.message);
                    updateStats(data.stats);
                    cancelSelection();
                    // Reload table rows after a short delay
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showToast('danger', data.message || 'Terjadi kesalahan.');
                }
            })
            .catch(() => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Konfirmasi';
                showToast('danger', 'Gagal terhubung ke server.');
            });
        };

        // ── Update stat cards ────────────────────────────────────
        function updateStats(stats) {
            const map = {
                'stat-hadir':       stats.hadir,
                'stat-terlambat':   stats.terlambat,
                'stat-izin':        stats.izin,
                'stat-sakit':       stats.sakit,
                'stat-tidak-hadir': stats.tidakHadir,
            };
            Object.keys(map).forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = map[id].toLocaleString('id-ID');
            });
        }

        // ── Toast notification ───────────────────────────────────
        function showToast(type, message) {
            const container = document.getElementById('toast-container') || createToastContainer();
            const id        = 'toast-' + Date.now();
            const bg        = type === 'success' ? 'bg-success' : 'bg-danger';

            const html = `
                <div id="${id}" class="toast align-items-center text-white ${bg} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body fw-semibold">${escapeHtml(message)}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
            const toastEl = document.getElementById(id);
            new bootstrap.Toast(toastEl, { delay: 4000 }).show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        function createToastContainer() {
            const div = document.createElement('div');
            div.id        = 'toast-container';
            div.className = 'toast-container position-fixed top-0 end-0 p-3';
            div.style.zIndex = '1100';
            document.body.appendChild(div);
            return div;
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&')
                .replace(/</g, '<')
                .replace(/>/g, '>')
                .replace(/"/g, '"');
        }

        // ── Export modal: date presets ───────────────────────────
        function bindDatePresets() {
            document.querySelectorAll('.date-preset').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.date-preset').forEach(b => b.classList.remove('active', 'btn-secondary'));
                    this.classList.add('active', 'btn-secondary');
                    this.classList.remove('btn-outline-secondary');

                    const preset   = this.dataset.preset;
                    const fromEl   = document.getElementById('exportDateFrom');
                    const toEl     = document.getElementById('exportDateTo');
                    const rangeDiv = document.getElementById('customDateRange');

                    if (preset === 'today') {
                        fromEl.value         = today;
                        toEl.value           = today;
                        rangeDiv.style.display = 'none';
                    } else if (preset === 'this_month') {
                        const now   = new Date();
                        const year  = now.getFullYear();
                        const month = String(now.getMonth() + 1).padStart(2, '0');
                        const last  = new Date(year, now.getMonth() + 1, 0).getDate();
                        fromEl.value         = `${year}-${month}-01`;
                        toEl.value           = `${year}-${month}-${String(last).padStart(2, '0')}`;
                        rangeDiv.style.display = 'none';
                    } else {
                        rangeDiv.style.display = '';
                    }
                });
            });

            // On modal open: re-init any un-initialized wrappers + activate preset
            document.getElementById('modalExport')?.addEventListener('show.bs.modal', () => {
                // Re-init dropdowns inside the modal that haven't been initialized yet
                document.querySelectorAll('#modalExport .custom-select-wrapper').forEach(wrapper => {
                    if (!wrapper.querySelector('.custom-select')) {
                        if (typeof CustomDropdown !== 'undefined') {
                            new CustomDropdown(wrapper, {
                                placeholder: wrapper.getAttribute('data-placeholder') || 'Pilih'
                            });
                        }
                    }
                });

                // Activate "Hari Ini" preset by default
                const todayBtn = document.querySelector('.date-preset[data-preset="today"]');
                if (todayBtn && !document.querySelector('.date-preset.active')) {
                    todayBtn.click();
                }
            });
        }

        // ── Submit export ────────────────────────────────────────
        window.submitExport = function () {
            document.getElementById('exportForm').submit();
        };

        // ── Init ─────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            bindDesktopCheckboxes();
            bindMobileCheckboxes();
            bindDatePresets();
        });

    }());
    </script>
    @endpush

</x-app-layout>
