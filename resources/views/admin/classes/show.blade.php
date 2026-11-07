<x-app-layout>
    @section('title', 'Detail Kelas - ' . $class->name)

    {{-- Simulation mode banner --}}
    <x-simulation-banner />

    <div class="row mb-4 align-items-center">
        <div class="col">
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ route('admin.classes.index') }}" class="btn btn-light btn-sm border">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-building me-2 text-primary"></i>Detail Kelas {{ $class->name }}
            </h3>
            <p class="text-muted mb-0">Informasi lengkap kelas, wali kelas, dan daftar siswa.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-pencil-square me-1"></i> Edit Kelas
            </a>
        </div>
    </div>

    {{-- Informasi Kelas --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-dark">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Kelas
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-1">
                                <span class="text-muted fs-8">Nama Kelas</span>
                                <span class="fw-semibold text-dark">{{ $class->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-1">
                                <span class="text-muted fs-8">Tingkat Kelas</span>
                                <span class="fw-semibold text-dark">Kelas {{ $class->grade_level }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-1">
                                <span class="text-muted fs-8">Jurusan</span>
                                <span class="fw-semibold text-dark">{{ $class->major ? $class->major->name . ' (' . $class->major->code . ')' : '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-1">
                                <span class="text-muted fs-8">Tahun Ajaran</span>
                                <span class="fw-semibold text-dark">{{ $class->academicYear ? $class->academicYear->name : '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-1">
                                <span class="text-muted fs-8">Sesi Sekolah</span>
                                @if($class->schoolSession)
                                    <span class="badge bg-warning-subtle text-warning border px-2 py-1 fw-bold fs-8 align-self-start" title="Override Sesi Kelas">
                                        ⚡ {{ $class->schoolSession->name }} (Override)
                                    </span>
                                @else
                                    @php
                                        $resolvedSess = app(\App\Services\SchoolSessionResolverService::class)->resolveForGrade($class->grade_level);
                                    @endphp
                                    @if($resolvedSess)
                                        <span class="badge bg-primary-subtle text-primary border px-2 py-1 fw-bold fs-8 align-self-start">
                                            {{ $resolvedSess->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-1">
                                <span class="text-muted fs-8">Status</span>
                                @if($class->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8 align-self-start">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-8 align-self-start">Tidak Aktif</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-column gap-1">
                                <span class="text-muted fs-8">Daya Tampung / Jumlah Siswa</span>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="progress flex-grow-1" style="height: 10px; max-width: 300px;">
                                        @php
                                            $percentage = $class->capacity > 0 ? ($totalStudents / $class->capacity) * 100 : 0;
                                            $barColor = $percentage > 90 ? 'bg-danger' : ($percentage > 70 ? 'bg-warning' : 'bg-success');
                                        @endphp
                                        <div class="progress-bar {{ $barColor }}" style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                    <span class="fw-bold text-dark">{{ $totalStudents }}/{{ $class->capacity }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informasi Wali Kelas --}}
        <div class="col-lg-4">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-dark">
                        <i class="bi bi-person-badge me-2 text-primary"></i>Wali Kelas
                    </h5>

                    @if($class->homeroomTeacher)
                        <div class="text-center">
                            <img src="{{ $class->homeroomTeacher->photo_url }}" 
                                 alt="{{ $class->homeroomTeacher->name }}" 
                                 class="rounded-circle mb-3"
                                 style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #e5e7eb;"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($class->homeroomTeacher->name) }}&background=6366f1&color=fff&size=128&bold=true'">
                            <h6 class="fw-bold text-dark mb-1">{{ $class->homeroomTeacher->name }}</h6>
                            @if($class->homeroomTeacher->nip)
                                <p class="text-muted fs-8 mb-0">NIP: {{ $class->homeroomTeacher->nip }}</p>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted mb-0">Belum ada wali kelas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Siswa --}}
    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-people-fill me-2 text-primary"></i>Daftar Siswa
                    </h5>
                    <p class="text-muted fs-8 mb-0">Total: {{ $totalStudents }} siswa</p>
                </div>
            </div>

            {{-- Search Form --}}
            <form method="GET" action="{{ route('admin.classes.show', $class) }}" class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, NIS, atau NISN..." value="{{ request('search') }}">
                    @if(request('search'))
                        <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-light border">Reset</a>
                    @endif
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>

            {{-- Table Siswa (Desktop) --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>NIS</th>
                            <th>NISN</th>
                            <th>Nama Lengkap</th>
                            <th>Jenis Kelamin</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                            <tr>
                                <td>{{ $students->firstItem() + $index }}</td>
                                <td class="fw-semibold">{{ $student->nis }}</td>
                                <td>{{ $student->nisn ?? '-' }}</td>
                                <td class="fw-semibold text-dark">{{ $student->name }}</td>
                                <td>
                                    @if($student->gender == 'L')
                                        <span class="badge bg-info-subtle text-info border px-2 py-1">Laki-laki</span>
                                    @else
                                        <span class="badge bg-pink-subtle text-pink border px-2 py-1">Perempuan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->is_active)
                                        <span class="badge bg-success-subtle text-success border px-2 py-1">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border px-2 py-1">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    @if(request('search'))
                                        Tidak ada siswa ditemukan dengan kata kunci "{{ request('search') }}".
                                    @else
                                        Belum ada siswa terdaftar di kelas ini.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Card List Siswa (Mobile) --}}
            <div class="d-md-none">
                @forelse($students as $index => $student)
                    <div class="card border mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="fw-bold text-dark">{{ $student->name }}</div>
                                <span class="badge {{ $student->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border px-2 py-1 fs-8">
                                    {{ $student->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                            <div class="text-muted fs-8">
                                <div class="mb-1"><strong>NIS:</strong> {{ $student->nis }}</div>
                                <div class="mb-1"><strong>NISN:</strong> {{ $student->nisn ?? '-' }}</div>
                                <div>
                                    <strong>Jenis Kelamin:</strong> 
                                    <span class="badge {{ $student->gender == 'L' ? 'bg-info-subtle text-info' : 'bg-pink-subtle text-pink' }} border px-2 py-1">
                                        {{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        @if(request('search'))
                            Tidak ada siswa ditemukan dengan kata kunci "{{ request('search') }}".
                        @else
                            Belum ada siswa terdaftar di kelas ini.
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
                <div class="mt-3">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
