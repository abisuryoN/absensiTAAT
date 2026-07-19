<x-app-layout>
    @section('title', 'Data Orang Tua')

    {{-- Desktop Header --}}
    <div class="row mb-4 align-items-center d-none d-md-flex">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Daftar Orang Tua / Wali</h3>
            <p class="text-muted mb-0">Kelola data wali siswa untuk integrasi notifikasi WhatsApp.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.parents.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Orang Tua
            </a>
        </div>
    </div>

    {{-- Mobile Header --}}
    <div class="d-block d-md-none mobile-page-content">
        <div class="mobile-section-header">
            <div>
                <h3 class="mobile-heading">Data Orang Tua</h3>
                <p class="mobile-subtitle">Kelola data wali siswa</p>
            </div>
            <a href="{{ route('admin.parents.create') }}" class="btn btn-primary mobile-btn" style="white-space:nowrap;">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4 d-none d-md-block">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.parents.index') }}" class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama orang tua atau nomor HP..." value="{{ request('search') }}">
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
                            <th>Nama Lengkap</th>
                            <th>Hubungan</th>
                            <th>No. HP Utama (WA)</th>
                            <th>No. HP Cadangan</th>
                            <th>Alamat</th>
                            <th>Akun Portal (Email)</th>
                            <th>Daftar Siswa Terkait</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parents as $parent)
                            <tr>
                                <td data-label="Nama" class="fw-semibold text-dark">{{ $parent->name }}</td>
                                <td data-label="Hubungan">
                                    @if($parent->relationship == 'Ayah')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-8">Ayah</span>
                                    @elseif($parent->relationship == 'Ibu')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-8">Ibu</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 fs-8">Wali</span>
                                    @endif
                                </td>
                                <td data-label="No. HP (WA)" class="fw-bold text-dark">{{ $parent->phone }}</td>
                                <td data-label="HP Cadangan">{{ $parent->phone_secondary ?: '-' }}</td>
                                <td data-label="Alamat">{{ Str::limit($parent->address, 40) ?: '-' }}</td>
                                <td data-label="Email Portal">
                                    @if($parent->user)
                                        <span class="text-dark fs-7">{{ $parent->user->email }}</span>
                                    @else
                                        <span class="text-muted fs-8">Tidak Ada Akun</span>
                                    @endif
                                </td>
                                <td data-label="Siswa Terkait">
                                    @forelse($parent->students as $student)
                                        <span class="badge bg-light text-dark border fs-8 mb-1">{{ $student->name }} ({{ $student->class->name }})</span>
                                    @empty
                                        <span class="text-muted fs-8">Belum memetakan siswa</span>
                                    @endforelse
                                </td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.parents.edit', $parent) }}" class="btn btn-light btn-sm border" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data orang tua ini? Akun login terkait juga akan ikut dihapus.')">
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
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data orang tua ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $parents->links() }}
            </div>
        </div>

        {{-- Mobile Body --}}
        <div class="d-block d-md-none mobile-card-body" style="padding:0;">
            <!-- Search -->
            <div class="mobile-search-card">
                <form method="GET" action="{{ route('admin.parents.index') }}" class="mobile-search-form">
                    <div class="mobile-search-row">
                        <div class="mobile-search-group">
                            <span class="mobile-search-icon"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="mobile-search-input" placeholder="Cari nama atau nomor HP..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="mobile-search-btn">Cari</button>
                    </div>
                </form>
            </div>

            <!-- Data Cards (2-column grid) -->
            <div class="mobile-parent-list">
                @forelse($parents as $parent)
                    <div class="mobile-parent-card">
                        {{-- Header: Nama + Badge + Actions --}}
                        <div class="parent-card-header">
                            <div class="parent-card-name-area">
                                <div class="parent-card-name">{{ $parent->name }}</div>
                                <div class="parent-card-badge">
                                    @if($parent->relationship == 'Ayah')
                                        <span class="badge bg-primary">Ayah</span>
                                    @elseif($parent->relationship == 'Ibu')
                                        <span class="badge bg-danger">Ibu</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Wali</span>
                                    @endif
                                </div>
                            </div>
                            <div class="parent-card-actions">
                                <a href="{{ route('admin.parents.edit', $parent) }}" class="btn-sm-icon btn-edit" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST" onsubmit="return confirm('Hapus data orang tua ini?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-sm-icon btn-delete" title="Hapus">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Compact Info --}}
                        <div class="parent-card-body">
                            <div class="parent-info-row">
                                <i class="bi bi-whatsapp info-icon"></i>
                                <span class="info-value">{{ $parent->phone }}</span>
                            </div>
                            <div class="parent-info-row">
                                <i class="bi bi-telephone info-icon"></i>
                                <span class="info-value">{{ $parent->phone_secondary ?: '-' }}</span>
                            </div>
                            <div class="parent-info-row">
                                <i class="bi bi-geo-alt info-icon"></i>
                                <span class="info-value">{{ Str::limit($parent->address, 25) ?: '-' }}</span>
                            </div>
                        </div>

                        {{-- Siswa Terkait --}}
                        <div class="parent-card-students">
                            @forelse($parent->students as $student)
                                <span class="student-badge">{{ $student->name }}</span>
                            @empty
                                <span class="student-badge" style="color:#94a3b8;">-</span>
                            @endforelse
                        </div>
                    </div>
                @empty
                    {{-- Modern Empty State --}}
                    <div class="mobile-empty-state">
                        <div class="empty-icon-wrap">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4 class="empty-title">Belum Ada Data Orang Tua</h4>
                        <p class="empty-desc">Data wali siswa akan tampil di sini setelah ditambahkan.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $parents->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
</x-app-layout>
