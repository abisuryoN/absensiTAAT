<x-app-layout>
    @section('title', 'Detail Orang Tua / Wali')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <a href="{{ route('admin.parents.index') }}" class="text-muted text-decoration-none me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <span class="fw-bold fs-5">Detail Orang Tua / Wali</span>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.parents.edit', $parent) }}" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-pencil-square me-1"></i> Edit
            </a>
            <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST"
                  onsubmit="return confirm('Hapus data orang tua ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm fw-semibold">
                    <i class="bi bi-trash3 me-1"></i> Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- Info Card --}}
        <div class="col-lg-5">
            <div class="card glass-card border-0 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.1em">
                        Informasi Pribadi
                    </h6>
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted fw-normal small">Nama Lengkap</dt>
                        <dd class="col-7 fw-semibold">{{ $parent->name }}</dd>

                        <dt class="col-5 text-muted fw-normal small">Hubungan</dt>
                        <dd class="col-7">{{ $parent->relationship ? ucfirst($parent->relationship) : '-' }}</dd>

                        <dt class="col-5 text-muted fw-normal small">NIK</dt>
                        <dd class="col-7 font-monospace">{{ $parent->nik ?? '-' }}</dd>

                        <dt class="col-5 text-muted fw-normal small">No. HP</dt>
                        <dd class="col-7">{{ $parent->phone ?? '-' }}</dd>

                        <dt class="col-5 text-muted fw-normal small">No. HP Cadangan</dt>
                        <dd class="col-7">{{ $parent->phone_secondary ?? '-' }}</dd>

                        <dt class="col-5 text-muted fw-normal small">Alamat</dt>
                        <dd class="col-7">{{ $parent->address ?? '-' }}</dd>

                        <dt class="col-5 text-muted fw-normal small">Status</dt>
                        <dd class="col-7">
                            @if($parent->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Nonaktif</span>
                            @endif
                        </dd>
                    </dl>

                    <hr class="my-3">
                    <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.1em">
                        Akun Portal
                    </h6>
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted fw-normal small">Email Login</dt>
                        <dd class="col-7">
                            @if($parent->email)
                                <i class="bi bi-check-circle-fill text-success me-1"></i>{{ $parent->email }}
                            @else
                                <span class="text-muted">Belum ada akun</span>
                            @endif
                        </dd>
                        <dt class="col-5 text-muted fw-normal small">Dibuat</dt>
                        <dd class="col-7 small">{{ $parent->created_at->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Students Card --}}
        <div class="col-lg-7">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.1em">
                        Siswa yang Ditautkan ({{ $parent->students->count() }})
                    </h6>
                    @forelse($parent->students as $student)
                    <div class="d-flex align-items-center gap-3 p-3 rounded mb-2"
                         style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                             style="width:40px;height:40px;font-size:.9rem;">
                            {{ strtoupper(substr($student->name, 0, 2)) }}
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold text-dark">{{ $student->name }}</div>
                            <small class="text-muted">
                                NIS: {{ $student->nis ?? '-' }} &bull;
                                {{ $student->schoolClass->name ?? 'Kelas tidak diketahui' }}
                            </small>
                        </div>
                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-light btn-sm border flex-shrink-0">
                            <i class="bi bi-arrow-right text-primary"></i>
                        </a>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-people fs-3 d-block mb-2 opacity-50"></i>
                        Belum ada siswa yang ditautkan ke orang tua ini.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>