<x-app-layout>
    @section('title', 'Semester')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Semester</h3>
            <p class="text-muted mb-0">Kelola semester pembelajaran yang terkait dengan tahun ajaran.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.semesters.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Semester
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.semesters.index') }}" class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama semester..." value="{{ request('search') }}">
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
                            <th>Tahun Ajaran</th>
                            <th>Nama Semester</th>
                            <th>No. Semester</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($semesters as $semester)
                            <tr>
                                <td data-label="Tahun Ajaran" class="fw-semibold text-dark">{{ $semester->academicYear->name }}</td>
                                <td data-label="Nama Semester">{{ $semester->name }}</td>
                                <td data-label="No. Semester">Semester {{ $semester->semester_number }}</td>
                                <td data-label="Tanggal Mulai">{{ $semester->start_date->format('d M Y') }}</td>
                                <td data-label="Tanggal Selesai">{{ $semester->end_date->format('d M Y') }}</td>
                                <td data-label="Status">
                                    @if($semester->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.semesters.edit', $semester) }}" class="btn btn-light btn-sm border" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <form action="{{ route('admin.semesters.destroy', $semester) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semester ini? Data yang terikat akan ikut terpengaruh.')">
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
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data semester ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $semesters->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
