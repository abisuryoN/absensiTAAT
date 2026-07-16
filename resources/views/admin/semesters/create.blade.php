<x-app-layout>
    @section('title', 'Tambah Semester')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.semesters.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Tambah Semester</h3>
            <p class="text-muted mb-0">Hubungkan semester baru dengan tahun ajaran aktif.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.semesters.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="academic_year_id" class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                            <div class="custom-select-wrapper" data-placeholder="Pilih Tahun Ajaran">
                            <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                                <option value="">Pilih Tahun Ajaran</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            </div>
                            @error('academic_year_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nama Semester <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Ganjil atau Genap" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="semester_number" class="form-label fw-semibold">Nomor Semester <span class="text-danger">*</span></label>
                            <div class="custom-select-wrapper" data-placeholder="Pilih Opsi">
                            <select name="semester_number" id="semester_number" class="form-select @error('semester_number') is-invalid @enderror" required>
                                <option value="1" {{ old('semester_number') == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                <option value="2" {{ old('semester_number') == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                            </select>
                            </div>
                            @error('semester_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
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

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Aktifkan Semester Ini</label>
                            </div>
                            <div class="form-text fs-8">Mengaktifkan semester ini akan otomatis menonaktifkan semester aktif lainnya di sistem.</div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
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
