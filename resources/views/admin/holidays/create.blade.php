<x-app-layout>
    @section('title', 'Tambah Hari Libur')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.holidays.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Tambah Hari Libur</h3>
            <p class="text-muted mb-0">Tandai tanggal sebagai hari libur agar tidak ada absensi pada hari tersebut.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.holidays.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="academic_year_id" class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                            <div class="custom-select-wrapper" data-placeholder="Pilih Tahun Ajaran">
                                <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                                    <option value="">Pilih Tahun Ajaran</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', \App\Models\AcademicYear::active()->first()?->id) == $year->id ? 'selected' : '' }}>
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
                            <label for="name" class="form-label fw-semibold">Nama Hari Libur <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Hari Kemerdekaan RI" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date') }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label fw-semibold">Tipe Hari Libur <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Tipe">
                                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="">Pilih Tipe</option>
                                        <option value="Nasional" {{ old('type') == 'Nasional' ? 'selected' : '' }}>Nasional</option>
                                        <option value="Sekolah" {{ old('type') == 'Sekolah' ? 'selected' : '' }}>Sekolah</option>
                                        <option value="Khusus" {{ old('type') == 'Khusus' ? 'selected' : '' }}>Khusus</option>
                                    </select>
                                </div>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Keterangan</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Masukkan keterangan tambahan jika ada...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
