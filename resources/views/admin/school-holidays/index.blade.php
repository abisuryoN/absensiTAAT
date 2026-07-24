<x-app-layout>
    @section('title', 'Hari Libur Sekolah')

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
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-calendar-heart me-2 text-primary"></i>Hari Libur Sekolah
            </h3>
            <p class="text-muted mb-0">Kelola hari libur khusus sekolah di luar hari libur nasional.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.school-holidays.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Hari Libur
            </a>
        </div>
    </div>

    {{-- Mobile Header --}}
    <div class="d-block d-md-none mobile-page-content">
        <div class="mobile-section-header">
            <div>
                <h3 class="mobile-heading">Hari Libur Sekolah</h3>
                <p class="mobile-subtitle">Kelola hari libur khusus sekolah</p>
            </div>
            <a href="{{ route('admin.school-holidays.create') }}" class="btn btn-primary mobile-btn" style="padding:8px 12px; font-size:13px;">
                <i class="bi bi-plus-lg"></i>
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        {{-- Desktop card body --}}
        <div class="card-body p-4 d-none d-md-block">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.school-holidays.index') }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari judul hari libur..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="year" class="form-select">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ ($filters['year'] ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="month" class="form-select">
                        <option value="">Semua Bulan</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ ($filters['month'] ?? '') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="is_active" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1" {{ ($filters['is_active'] ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ ($filters['is_active'] ?? '') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-light border fw-semibold flex-grow-1">Cari</button>
                    @if(array_filter($filters))
                        <a href="{{ route('admin.school-holidays.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 130px;">TANGGAL</th>
                            <th>JUDUL HARI LIBUR</th>
                            <th>DESKRIPSI</th>
                            <th class="text-center" style="width: 100px;">STATUS</th>
                            <th class="text-center" style="width: 120px;">DITAMBAHKAN</th>
                            <th class="text-center" style="width: 120px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $holiday)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2">
                                        {{ $holiday->holiday_date->format('d M Y') }}
                                    </span>
                                    <div class="text-muted" style="font-size:0.72rem;">
                                        {{ $holiday->holiday_date->isoFormat('dddd') }}
                                    </div>
                                </td>
                                <td>
                                    <p class="fw-semibold mb-0 text-dark">{{ $holiday->title }}</p>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size:0.85rem;">
                                        {{ $holiday->description ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($holiday->is_active)
                                        <span class="badge bg-success-subtle text-success fw-semibold">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary fw-semibold">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center text-muted" style="font-size:0.82rem;">
                                    {{ $holiday->creator?->name ?? 'Sistem' }}
                                    <div>{{ $holiday->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.school-holidays.edit', $holiday) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.school-holidays.destroy', $holiday) }}" method="POST" class="delete-form-sh" onsubmit="return confirmDeleteSH(event)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                    Belum ada data hari libur sekolah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($holidays->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $holidays->appends($filters)->links() }}
                </div>
            @endif
        </div>

        {{-- Mobile card body --}}
        <div class="d-block d-md-none">
            <div class="p-3">
                <form method="GET" action="{{ route('admin.school-holidays.index') }}" class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari hari libur..." value="{{ $filters['search'] ?? '' }}">
                        <button type="submit" class="btn btn-light border">Cari</button>
                    </div>
                </form>

                @forelse($holidays as $holiday)
                    <div class="card border-0 shadow-sm rounded-3 mb-3 p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="fw-bold mb-1 text-dark">{{ $holiday->title }}</p>
                                <span class="text-muted" style="font-size:0.82rem;">
                                    {{ $holiday->holiday_date->isoFormat('dddd, D MMMM Y') }}
                                </span>
                                @if($holiday->description)
                                    <p class="text-muted mb-0 mt-1" style="font-size:0.8rem;">{{ $holiday->description }}</p>
                                @endif
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1">
                                @if($holiday->is_active)
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
                                @endif
                                <div class="d-flex gap-1 mt-1">
                                    <a href="{{ route('admin.school-holidays.edit', $holiday) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.school-holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirmDeleteSH(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                        Belum ada data hari libur sekolah.
                    </div>
                @endforelse

                @if($holidays->hasPages())
                    <div class="mt-3">{{ $holidays->appends($filters)->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    <script>
    function confirmDeleteSH(e) {
        e.preventDefault();
        const form = e.target.closest('form');
        if (confirm('Yakin ingin menghapus hari libur ini? Data tidak dapat dikembalikan.')) {
            form.submit();
        }
        return false;
    }
    </script>
</x-app-layout>
