<x-app-layout>
    @section('title', 'Data Orang Tua / Wali')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Desktop Header --}}
    <div class="row mb-4 align-items-center d-none d-md-flex">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Daftar Orang Tua / Wali</h3>
            <p class="text-muted mb-0">Master data orang tua dan wali siswa untuk portal dan notifikasi.</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.parents.export') }}" class="btn btn-success fw-semibold">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Referensi
            </a>
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
                <p class="mobile-subtitle">Master data wali siswa</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.parents.export') }}" class="btn btn-success mobile-btn" style="white-space:nowrap;">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
                <a href="{{ route('admin.parents.create') }}" class="btn btn-primary mobile-btn" style="white-space:nowrap;">
                    <i class="bi bi-plus-lg"></i> Tambah
                </a>
            </div>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4 d-none d-md-block">
            {{-- Search --}}
            <form method="GET" action="{{ route('admin.parents.index') }}" class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Cari nama, NIK, atau nomor HP..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-light border fw-semibold">Cari</button>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>NIK</th>
                            <th>No. HP</th>
                            <th>Email / Akun Portal</th>
                            <th>Siswa Terkait</th>
                            <th>Status</th>
                            <th class="text-center" style="width:160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parents as $parent)
                        <tr>
                            <td data-label="Nama" class="fw-semibold text-dark">
                                {{ $parent->name }}
                                @if($parent->relationship)
                                    <br><small class="text-muted fw-normal">{{ ucfirst($parent->relationship) }}</small>
                                @endif
                            </td>
                            <td data-label="NIK" class="font-monospace small">{{ $parent->nik ?? '-' }}</td>
                            <td data-label="No. HP">{{ $parent->phone ?? '-' }}</td>
                            <td data-label="Email">
                                @if($parent->email)
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    <small>{{ $parent->email }}</small>
                                @else
                                    <span class="text-muted fs-8">Belum ada akun</span>
                                @endif
                            </td>
                            <td data-label="Siswa">
                                @if($parent->students->count() > 0)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $parent->students->count() }} siswa
                                    </span>
                                @else
                                    <span class="text-muted fs-8">-</span>
                                @endif
                            </td>
                            <td data-label="Status">
                                @if($parent->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Nonaktif</span>
                                @endif
                            </td>
                            <td data-label="Aksi" class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.parents.show', $parent) }}"
                                       class="btn btn-light btn-sm border" title="Detail">
                                        <i class="bi bi-eye text-info"></i>
                                    </a>
                                    <a href="{{ route('admin.parents.edit', $parent) }}"
                                       class="btn btn-light btn-sm border" title="Edit">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST"
                                          onsubmit="return confirm('Hapus data orang tua ini? Siswa yang tertaut akan dilepas.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-light btn-sm border" title="Hapus">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                                Belum ada data orang tua/wali.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $parents->links() }}</div>
        </div>

        {{-- Mobile Body --}}
        <div class="d-block d-md-none mobile-card-body">
            <div class="mobile-search-card">
                <form method="GET" action="{{ route('admin.parents.index') }}" class="mobile-search-form">
                    <div class="mobile-search-row">
                        <div class="mobile-search-group">
                            <span class="mobile-search-icon"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="mobile-search-input"
                                   placeholder="Cari nama atau NIK..."
                                   value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="mobile-cari-btn">Cari</button>
                    </div>
                </form>
            </div>

            <div class="mobile-parent-list">
                @forelse($parents as $parent)
                <div class="mobile-parent-card">
                    <div class="parent-card-header">
                        <div class="parent-card-name-area">
                            <div class="parent-card-name">{{ $parent->name }}</div>
                            <div class="parent-card-badge">
                                @if($parent->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </div>
                        </div>
                        <div class="parent-card-actions">
                            <a href="{{ route('admin.parents.show', $parent) }}" class="btn-sm-icon" title="Detail">
                                <i class="bi bi-eye text-info"></i>
                            </a>
                            <a href="{{ route('admin.parents.edit', $parent) }}" class="btn-sm-icon btn-edit" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST"
                                  onsubmit="return confirm('Hapus data orang tua ini?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm-icon btn-delete" title="Hapus">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="parent-card-body">
                        <div class="parent-info-row">
                            <i class="bi bi-credit-card-2-front info-icon"></i>
                            <span class="info-value font-monospace">{{ $parent->nik ?? '-' }}</span>
                        </div>
                        <div class="parent-info-row">
                            <i class="bi bi-telephone info-icon"></i>
                            <span class="info-value">{{ $parent->phone ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="parent-card-students">
                        @forelse($parent->students as $student)
                            <span class="student-badge">{{ $student->name }}</span>
                        @empty
                            <span class="student-badge" style="color:#94a3b8;">Belum ada siswa terkait</span>
                        @endforelse
                    </div>
                </div>
                @empty
                <div class="mobile-empty-state">
                    <div class="empty-icon-wrap"><i class="bi bi-people"></i></div>
                    <h4 class="empty-title">Belum Ada Data Orang Tua</h4>
                    <p class="empty-desc">Tambahkan data orang tua/wali untuk mulai menggunakan fitur ini.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-3">{{ $parents->appends(request()->all())->links('vendor.pagination.bootstrap-5') }}</div>
        </div>
    </div>
</x-app-layout>