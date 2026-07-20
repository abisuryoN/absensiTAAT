<x-app-layout>
    @section('title', 'Data Kelas')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-building me-2 text-primary"></i>Daftar Kelas
            </h3>
            <p class="text-muted mb-0">Kelola rombongan belajar (kelas), wali kelas, serta daya tampungnya.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kelas
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.classes.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama kelas..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-select-wrapper" data-placeholder="Semua Tahun Ajaran">
                    <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="custom-select-wrapper" data-placeholder="Semua Jurusan">
                    <select name="major_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Jurusan</option>
                        @foreach($majors as $major)
                            <option value="{{ $major->id }}" {{ request('major_id') == $major->id ? 'selected' : '' }}>{{ $major->code }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="custom-select-wrapper" data-placeholder="Semua Tingkat">
                    <select name="grade_level" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Tingkat</option>
                        <option value="10" {{ request('grade_level') == '10' ? 'selected' : '' }}>Kelas 10</option>
                        <option value="11" {{ request('grade_level') == '11' ? 'selected' : '' }}>Kelas 11</option>
                        <option value="12" {{ request('grade_level') == '12' ? 'selected' : '' }}>Kelas 12</option>
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
                            <th>Tingkat</th>
                            <th>Nama Kelas</th>
                            <th>Jurusan</th>
                            <th>Tahun Ajaran</th>
                            <th>Wali Kelas</th>
                            <th>Daya Tampung / Siswa</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $class)
                            <tr>
                                <td data-label="Tingkat"><span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold">Kelas {{ $class->grade_level }}</span></td>
                                <td data-label="Nama Kelas" class="fw-semibold text-dark">{{ $class->name }}</td>
                                <td data-label="Jurusan">{{ $class->major->code }}</td>
                                <td data-label="Tahun Ajaran">{{ $class->academicYear->name }}</td>
                                <td data-label="Wali Kelas">{{ $class->homeroomTeacher ? $class->homeroomTeacher->name : '-' }}</td>
                                <td data-label="Daya Tampung">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                            @php
                                                $percentage = $class->capacity > 0 ? ($class->students_count / $class->capacity) * 100 : 0;
                                                $barColor = $percentage > 90 ? 'bg-danger' : ($percentage > 70 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress-bar {{ $barColor }}" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <span class="fs-8 fw-semibold text-muted">{{ $class->students_count }}/{{ $class->capacity }}</span>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    @if($class->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-light btn-sm border" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">
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
                                    Tidak ada data kelas ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $classes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
