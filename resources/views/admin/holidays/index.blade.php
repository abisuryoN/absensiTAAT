<x-app-layout>
    @section('title', 'Hari Libur')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Desktop Header --}}
    <div class="row mb-4 align-items-center d-none d-md-flex">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Hari Libur</h3>
            <p class="text-muted mb-0">Kelola daftar hari libur nasional, sekolah, dan khusus per tahun ajaran.</p>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-success fw-semibold me-2" data-bs-toggle="modal" data-bs-target="#syncModal">
                <i class="bi bi-arrow-repeat me-1"></i> Sinkronisasi dari API
            </button>
            <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Hari Libur
            </a>
        </div>
    </div>

    {{-- Mobile Header --}}
    <div class="d-block d-md-none mobile-page-content">
        <div class="mobile-section-header">
            <div>
                <h3 class="mobile-heading">Hari Libur</h3>
                <p class="mobile-subtitle">Kelola daftar hari libur</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success mobile-btn" data-bs-toggle="modal" data-bs-target="#syncModal" style="padding:8px 12px; font-size:13px;">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
                <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary mobile-btn" style="padding:8px 12px; font-size:13px;">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card glass-card border-0">
        {{-- Desktop card body --}}
        <div class="card-body p-4 d-none d-md-block">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.holidays.index') }}" class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama hari libur..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-light border fw-semibold">Cari</button>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 120px;">TANGGAL</th>
                            <th>NAMA HARI LIBUR</th>
                            <th class="text-center" style="width: 120px;">TIPE</th>
                            <th class="text-center" style="width: 150px;">TAHUN AJARAN</th>
                            <th>KETERANGAN</th>
                            <th class="text-center" style="width: 120px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $holiday)
                            <tr>
                                <td data-label="Tanggal" class="fw-semibold text-dark text-center">{{ $holiday->date->format('d M Y') }}</td>
                                <td data-label="Nama Hari Libur" class="fw-semibold">{{ $holiday->name }}</td>
                                <td data-label="Tipe" class="text-center">
                                    @if($holiday->type == 'national')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-8">Nasional</span>
                                    @elseif($holiday->type == 'school')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-8">Khusus</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 fs-8">Lainnya</span>
                                    @endif
                                </td>
                                <td data-label="Tahun Ajaran" class="text-center text-muted">{{ $holiday->academicYear->name }}</td>
                                <td data-label="Keterangan">{{ Str::limit($holiday->description, 40) ?: '-' }}</td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-light btn-sm border" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm border" title="Hapus">
                                                <i class="bi bi-trash3 text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data hari libur ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $holidays->links() }}
            </div>
        </div>

        {{-- Mobile card body --}}
        <div class="d-block d-md-none mobile-card-body">
            <!-- Search -->
            <form method="GET" action="{{ route('admin.holidays.index') }}" class="mobile-search-form mb-3">
                <div class="mobile-search-row">
                    <div class="mobile-search-group">
                        <span class="mobile-search-icon"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="mobile-search-input" placeholder="Cari hari libur..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="mobile-cari-btn">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                </div>
            </form>

            <!-- Cards -->
            <div class="mobile-holiday-list">
                @forelse($holidays as $holiday)
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-top">
                            <div>
                                <div class="mobile-data-name">{{ $holiday->name }}</div>
                                <div class="mobile-date-label">{{ $holiday->date->format('d M Y') }}</div>
                            </div>
                            <div class="mobile-data-actions">
                                <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-light btn-sm border" title="Edit">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </a>
                                <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Hapus hari libur ini?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm border" title="Hapus">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="mobile-data-details">
                            <div class="mobile-data-detail">
                                <span>Tipe:</span>
                                @if($holiday->type == 'national')
                                    <span class="badge bg-danger">Nasional</span>
                                @elseif($holiday->type == 'school')
                                    <span class="badge bg-primary">Khusus</span>
                                @else
                                    <span class="badge bg-warning">Lainnya</span>
                                @endif
                            </div>
                            <div class="mobile-data-detail">
                                <span>Tahun Ajaran:</span>
                                <span>{{ $holiday->academicYear->name }}</span>
                            </div>
                            <div class="mobile-data-detail">
                                <span>Ket:</span>
                                <span>{{ Str::limit($holiday->description, 50) ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="mobile-empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Tidak ada data hari libur ditemukan.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mobile-pagination-wrapper mt-3">
                {{ $holidays->appends(request()->all())->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Sync Modal -->
    <div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="syncModalLabel">Sinkronisasi Hari Libur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.holidays.sync') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Informasi:</strong> Sistem akan mengambil data hari libur nasional dari API dan menambahkan hari Sabtu & Minggu sebagai hari libur otomatis untuk tahun ajaran yang dipilih.
                        </div>
                        <div class="mb-3">
                            <label for="academic_year_id" class="form-label fw-semibold">Pilih Tahun Ajaran <span class="text-danger">*</span></label>
                            <div class="custom-select-wrapper" data-placeholder="-- Pilih Tahun Ajaran --">
                                <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                        {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <small>Data hari libur yang sudah ada tidak akan diganti. Hanya data baru yang akan ditambahkan.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-arrow-repeat me-1"></i> Sinkronisasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
