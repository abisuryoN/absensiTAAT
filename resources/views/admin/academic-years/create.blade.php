<x-app-layout>
    @section('title', 'Tambah Tahun Ajaran')

    {{-- Desktop Header --}}
    <div class="row mb-4 d-none d-md-flex">
        <div class="col">
            <a href="{{ route('admin.academic-years.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Tambah Tahun Ajaran</h3>
            <p class="text-muted mb-0">Buat tahun ajaran baru untuk sistem.</p>
        </div>
    </div>

    {{-- Mobile Header --}}
    <div class="d-block d-md-none mobile-page-content">
        <div class="mobile-section-header">
            <a href="{{ route('admin.academic-years.index') }}" class="btn btn-light border btn-sm mb-2" style="display:inline-flex;align-items:center;gap:6px;">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h3 class="mobile-heading">Tambah Tahun Ajaran</h3>
            <p class="mobile-subtitle">Buat tahun ajaran baru</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card glass-card border-0 mobile-form-card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.academic-years.store') }}">
                        @csrf

                        <div class="row mobile-form-row mb-3">
                            <div class="col-md-12 mobile-full-width">
                                <label for="name" class="form-label fw-semibold">Nama Tahun Ajaran <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: 2025/2026" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text fs-8">Gunakan format terstandar seperti "2025/2026".</div>
                            </div>
                        </div>

                        <div class="row mobile-form-row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mobile-full-width mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Aktifkan Tahun Ajaran Ini</label>
                            </div>
                            <div class="form-text fs-8">Mengaktifkan tahun ajaran ini akan otomatis menonaktifkan tahun ajaran aktif lainnya di sistem.</div>
                        </div>

                        <div class="mobile-form-submit">
                            <button type="submit" class="btn btn-primary fw-semibold px-4">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
