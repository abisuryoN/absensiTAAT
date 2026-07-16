<x-app-layout>
    @section('title', 'Data Guru')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Daftar Guru</h3>
            <p class="text-muted mb-0">Kelola tenaga pendidik, data kontak, dan mata pelajaran yang diampu.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Data Guru
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.teachers.index') }}" class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, NIP, atau no HP..." value="{{ request('search') }}">
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
                            <th>Foto</th>
                            <th>NIP / NUPTK</th>
                            <th>Nama Lengkap</th>
                            <th>Gender</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Mata Pelajaran Diampu</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $teacher)
                            <tr>
                                <td>
                                    @if($teacher->photo)
                                        <img src="{{ Storage::url($teacher->photo) }}" alt="" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px;">
                                    @else
                                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                            {{ substr($teacher->name, 0, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="d-block fw-semibold text-dark fs-7">NIP: {{ $teacher->nip ?: '-' }}</span>
                                    <span class="text-muted fs-8">NUPTK: {{ $teacher->nuptk ?: '-' }}</span>
                                </td>
                                <td class="fw-semibold text-dark">{{ $teacher->name }}</td>
                                <td>
                                    @if($teacher->gender == 'L')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-8">Laki-laki</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-8">Perempuan</span>
                                    @endif
                                </td>
                                <td>{{ $teacher->user->email }}</td>
                                <td>{{ $teacher->phone ?: '-' }}</td>
                                <td>
                                    @forelse($teacher->subjects as $subject)
                                        <span class="badge bg-light text-dark border fs-8 mb-1">{{ $subject->name }}</span>
                                    @empty
                                        <span class="text-muted fs-8">-</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($teacher->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-light btn-sm border" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini? Akun login terkait juga akan ikut dihapus.')">
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
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data guru ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $teachers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
