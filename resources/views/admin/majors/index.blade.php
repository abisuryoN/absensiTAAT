<x-app-layout>
    @section('title', 'Jurusan')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Jurusan</h3>
            <p class="text-muted mb-0">Kelola kompetensi keahlian/jurusan sekolah.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.majors.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Jurusan
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.majors.index') }}" class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama atau kode jurusan..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-select-wrapper" data-placeholder="Semua Status">
                    <select name="is_active" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
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
                            <th>Kode Jurusan</th>
                            <th>Nama Jurusan</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Kelas</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($majors as $major)
                            <tr>
                                <td data-label="Kode Jurusan" class="fw-bold text-dark">{{ $major->code }}</td>
                                <td data-label="Nama Jurusan" class="fw-semibold">{{ $major->name }}</td>
                                <td data-label="Deskripsi">{{ Str::limit($major->description, 50) ?: '-' }}</td>
                                <td data-label="Jumlah Kelas">{{ $major->classes_count }} Kelas</td>
                                <td data-label="Status">
                                    @if($major->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.majors.edit', $major) }}" class="btn btn-light btn-sm border" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <form action="{{ route('admin.majors.destroy', $major) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurusan ini?')">
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
                                    Tidak ada data jurusan ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $majors->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
