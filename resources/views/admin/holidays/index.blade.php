<x-app-layout>
    @section('title', 'Hari Libur')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Hari Libur</h3>
            <p class="text-muted mb-0">Kelola daftar hari libur nasional, sekolah, dan khusus per tahun ajaran.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Hari Libur
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
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
                            <th>Tanggal</th>
                            <th>Nama Hari Libur</th>
                            <th>Tipe</th>
                            <th>Tahun Ajaran</th>
                            <th>Keterangan</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $holiday)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $holiday->date->format('d M Y') }}</td>
                                <td class="fw-semibold">{{ $holiday->name }}</td>
                                <td>
                                    @if($holiday->type == 'Nasional')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-8">Nasional</span>
                                    @elseif($holiday->type == 'Sekolah')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-8">Sekolah</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 fs-8">Khusus</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-8">{{ $holiday->academicYear->name }}</td>
                                <td>{{ Str::limit($holiday->description, 40) ?: '-' }}</td>
                                <td class="text-center">
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
    </div>
</x-app-layout>
