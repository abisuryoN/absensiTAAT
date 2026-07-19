<x-app-layout>
    @section('title', 'Data Siswa')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Daftar Siswa</h3>
            <p class="text-muted mb-0">Kelola data peserta didik, kelas penempatan, dan orang tua terkait.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Siswa
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.students.index') }}" class="mb-4">
                <div class="d-flex flex-wrap gap-3 align-items-center">

                    <!-- Cari -->
                    <div class="flex-grow-1" style="min-width: 200px; max-width: 380px;">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                   placeholder="Cari nama, NIS, atau NISN..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Filter Kelas -->
                    <div class="custom-select-wrapper" data-placeholder="Semua Kelas" style="min-width: 160px; flex: 1;">
                        <select name="class_id" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Jurusan -->
                    <div class="custom-select-wrapper" data-placeholder="Semua Jurusan" style="min-width: 140px; flex: 1;">
                        <select name="major_id" onchange="this.form.submit()">
                            <option value="">Semua Jurusan</option>
                            @foreach($majors as $major)
                                <option value="{{ $major->id }}" {{ request('major_id') == $major->id ? 'selected' : '' }}>{{ $major->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div class="custom-select-wrapper" data-placeholder="Semua Status" style="min-width: 140px; flex: 1;">
                        <select name="is_active" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <!-- Tombol Filter -->
                    <div>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>

                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th>NIS / NISN</th>
                            <th>Nama Lengkap</th>
                            <th>Gender</th>
                            <th>Kelas</th>
                            <th>Orang Tua</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td data-label="NIS / NISN">
                                    <span class="d-block fw-semibold text-dark fs-7">NIS: {{ $student->nis }}</span>
                                    <span class="text-muted fs-8">NISN: {{ $student->nisn ?: '-' }}</span>
                                </td>
                                <td data-label="Nama" class="fw-semibold text-dark">{{ $student->name }}</td>
                                <td data-label="Gender">
                                    @if($student->gender == 'L')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-8">L</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-8">P</span>
                                    @endif
                                </td>
                                <td data-label="Kelas">
                                    <span class="badge bg-light text-dark border px-2 py-1 fw-semibold fs-8">{{ $student->class->name ?? '-' }}</span>
                                </td>
                                <td data-label="Orang Tua">
                                    @if($student->parent)
                                        <span class="fw-semibold fs-7">{{ $student->parent->name }}</span>
                                        <span class="text-muted fs-8 d-block">{{ $student->parent->phone }}</span>
                                    @else
                                        <span class="text-muted fs-8">Belum diisi</span>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    @if($student->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-light btn-sm border" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini? Akun login terkait juga akan dihapus.')">
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
                                    Tidak ada data siswa ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</x-app-layout>