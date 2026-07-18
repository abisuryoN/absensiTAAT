<x-app-layout>
    @section('title', 'Jadwal Pelajaran')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Jadwal Pelajaran</h3>
            <p class="text-muted mb-0">Kelola jadwal mengajar guru per kelas, hari, dan jam pelajaran.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Jadwal
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.schedules.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari guru, mapel, kelas..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="custom-select-wrapper" data-placeholder="Semua Kelas">
                    <select name="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="custom-select-wrapper" data-placeholder="Semua Guru">
                    <select name="teacher_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="custom-select-wrapper" data-placeholder="Semua Hari">
                    <select name="day" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Hari</option>
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $day)
                            <option value="{{ $day }}" {{ request('day') == $day ? 'selected' : '' }}>{{ $day }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-light border fw-semibold">Filter</button>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru</th>
                            <th>Ruangan</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td data-label="Hari">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-bold">{{ $schedule->day }}</span>
                                </td>
                                <td data-label="Jam" class="fw-semibold text-dark">
                                    {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                </td>
                                <td data-label="Kelas">
                                    <span class="badge bg-light text-dark border px-2 py-1 fw-semibold fs-8">{{ $schedule->class->name }}</span>
                                </td>
                                <td data-label="Mapel" class="fw-semibold">{{ $schedule->subject->name }}</td>
                                <td data-label="Guru">{{ $schedule->teacher->name }}</td>
                                <td data-label="Ruangan">{{ $schedule->room ?: '-' }}</td>
                                <td data-label="Tahun Ajaran" class="text-muted fs-8">{{ $schedule->academicYear->name }} / {{ $schedule->semester->name }}</td>
                                <td data-label="Status">
                                    @if($schedule->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-light btn-sm border" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal pelajaran ini?')">
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
                                    Tidak ada data jadwal pelajaran ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $schedules->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
